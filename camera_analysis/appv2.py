import logging
import asyncio
import time
import os

import cv2
import numpy as np
import redis
import json

from ultralytics import YOLO  # Import Ultralytics Package
from ultralytics import solutions  # Import Ultralytics Package
# local kit
from ApiService import ApiService
from PlcService import PlcService
from image_storage import ImageStorage
from logging_config import configure_logging
from solution.analysis_model_zone import analysis

class MainApp:
    def __init__(self):

        configure_logging()  # Setup log
        self.logger = logging.getLogger("MainApp")
        self.time_logger = logging.getLogger("TimeLogger")

        self.api_service = ApiService(base_url=os.getenv("WEB_SERVICE_URL"))
        self.plc_servive = PlcService(plc_ip=os.getenv("PLC_IP"))
        self.last_sent_timestamps = {}
        self.SLEEP_INTERVAL = 0.1  # Set a reasonable sleep interval
        self.camera_frame_count = {}
        self.camera_alarm = {}


        self.redis_host = "redis"
        self.redis_port = 6379
        self.r = redis.Redis(host=self.redis_host, port=self.redis_port, db=0)
        self.image_storage = ImageStorage(self.r)

        self.init_dirs()
        self.init_models()

    def init_dirs(self):
        start_time = time.monotonic()
        self.BASE_SAVE_DIR = "logs"
        self.ALARM_SAVE_DIR = os.path.join(self.BASE_SAVE_DIR, "alarm_images")

        for dir_path in [
            self.ALARM_SAVE_DIR,
        ]:
            if not os.path.exists(dir_path):
                os.makedirs(dir_path)

        self.time_logger.debug(f"Directories initialized in {time.monotonic() - start_time:.2f} seconds")

    # TuanDA
    def init_models(self):
        start_time = time.monotonic()
        # Load the model from database and initialize the annotator
        self.models = {}  # Dictionary used to store models
        # Get model list
        self.model_list = self.api_service.get_model_list()
        if not self.model_list:
            self.logger.warning("No model for init")
            return
        for model in self.model_list:
            model_path = model.get("url")
            model_id = model.get("id")
            # Load model using ultralytics YOLO
            match model_id: #Detect
                case 1 | 5 | 6 | 7 | 8 | 9 | 10:
                    self.models[model_id] = {
                        'path':  YOLO(model_path),
                        'config': model.get("config"),
                    }                
                    # zone = [(20,400), (600,400), (600,360), (400,300), (200,300), (20,360)]
                    # zone = [(20,700), (1450,700), (1200,440), (120,440)]
                    # self.models[model_id] = ZoneDetect(
                    #     show=True,
                    #     region=zone,
                    #     model=model_path,
                    #     classes=[0],
                    #     verbose=False,
                    # )
                case 2: #Pose
                    self.models[model_id] = {
                        'path':  YOLO(model_path),
                        'config': model.get("config"),
                    }
                case 3:
                    # line_points = [(750, 500),(1200, 500),(1200, 530),(750,530)]
                    line_points = [(0, 330), (1700, 330)]
                    self.models[model_id] = {
                        'path':  solutions.ObjectCounter(
                        show=True,
                        region=line_points,
                        model=model_path,
                        classes=[0],
                        verbose=False,
                    ),
                        'config': model.get("config"),
                    }
                case 4:
                    self.models[model_id] = {
                        'path':  YOLO(model_path),
                        'config': model.get("config"),
                    }
            self.logger.debug(f"Model {model_id} loaded from {model_path}")
        self.time_logger.debug(f"Models and annotators initialized in {time.monotonic() - start_time:.2f} seconds")

    async def fetch_camera_status(self):
        # start_time = time.monotonic()
        loop = asyncio.get_event_loop()
        status = await loop.run_in_executor(None, self.image_storage.fetch_camera_status)
        # self.time_logger.debug(f"Snapshot for camera status fetched in {time.monotonic() - start_time:.2f} seconds")
        return status

    async def fetch_snapshot(self, camera_id):       
        """Get the latest image of the specified camera from Redis"""
        # start_time = time.monotonic()
        redis_key = f"camera_{camera_id}_latest_frame"
        loop = asyncio.get_event_loop()
        image = await loop.run_in_executor(None, self.image_storage.fetch_image, redis_key)
        # image = await self.image_storage.fetch_image(redis_key)
        # self.time_logger.debug(f"Snapshot for camera {camera_id} fetched in {time.monotonic() - start_time:.2f} seconds")
        return image

    async def process_camera(self, camera_id, camera_info, images_batches):
        """
        Handles image acquisition and recognition from a single camera
        """
        # Ensure the camera_id is initialized in the camera_frame_count dictionary
        if camera_id not in self.camera_frame_count:
            self.camera_frame_count[camera_id] = {
                'count': 0,
                'last_time': time.monotonic(),
            }

        # Your existing code for processing the camera
        try:
            img = await self.fetch_snapshot(camera_id)
            if img is not None:
                model_id = camera_info.get("model_id")
                if model_id in self.models:
                    # Add image and camera info to the batch
                    if model_id not in images_batches:
                        images_batches[model_id] = {'images': [], 'camera_info': []}
                    images_batches[model_id]['images'].append(img)
                    images_batches[model_id]['camera_info'].append((camera_id, camera_info))
                    
                    # Update frame count
                    self.camera_frame_count[camera_id]['count'] += 1
                    current_time = time.monotonic()
                    elapsed = current_time - self.camera_frame_count[camera_id]['last_time']
                    if elapsed >= 10.0:
                        fps = self.camera_frame_count[camera_id]['count'] / elapsed
                        self.camera_frame_count[camera_id]['count'] = 0
                        self.camera_frame_count[camera_id]['last_time'] = current_time
                        self.time_logger.debug(f"Camera {camera_id} FPS: {fps}")
                else:
                    self.logger.warning(f"No valid recognition model for camera {camera_id}")
            else:
                self.logger.warning(f"No image fetched for camera {camera_id}")
        except asyncio.CancelledError:
            self.logger.warning(f"Task {camera_id} has been STOPPED.")
            raise

    def call_model_batch(self, model_id, batch_data):
        """Process a batch of images using the specified model"""
        start_time = time.monotonic()

        model = self.models[model_id]['path']
        model_config = self.models[model_id]['config']
        images_batch = batch_data['images']
        camera_info_batch = batch_data['camera_info']
        # TuanDA Chia loai model de xu ly model1 = Tracking, model2 = Detector,Model3 = Counter...
        match model_id:
            case 1 | 5 | 6 | 7 | 8 | 9 | 10: #Detect           
                results, detection_flags = analysis.process_model(self,batch_data,model,model_config)
                for i, detections in enumerate(results):
                    camera_id, camera_info = camera_info_batch[i]
                    # annotated_image = detections.plot()
                    annotated_image = detections.orig_img
                    # Save images and send notifications
                    self.save_and_notify(annotated_image, camera_info, detection_flags[i])
            case 2: #Pose
                results = model(images_batch, conf=model_config.get("conf"), classes=model_config.get("label_conf"), verbose=False)
                for i, detections in enumerate(results):
                    annotated_image = detections.plot()                
                    camera_id, camera_info = camera_info_batch[i]             
                    # Save images and send notifications
                    self.save_and_notify(annotated_image, camera_info, False)    
            case 3: #count
                annotated_image = model.count(images_batch[0])
                camera_id, camera_info = camera_info_batch[0]             
                # Save images and send notifications
                self.save_and_notify(annotated_image, camera_info, False)  
            case 4: #tracking
                results = model.track(images_batch[0], conf=model_config.get("conf"), classes=model_config.get("label_conf"), verbose=False)
                annotated_image = results[0].plot()
                camera_id, camera_info = camera_info_batch[0]             
                # Save images and send notifications
                self.save_and_notify(annotated_image, camera_info, False)

        self.time_logger.info(f"Batch model {model_id} ({len(images_batch)} images) processing completed in {time.monotonic() - start_time:.2f} seconds")
    
    def save_and_notify( self, annotated_image, camera_info, detection_flag):
        
        start_time = time.monotonic()
        camera_id = camera_info.get("id")
        redis_key = f"camera_{camera_id}_boxed_image"
        self.image_storage.save_image(redis_key, annotated_image)
        # self.time_logger.debug(f"Camera {camera_id} save and notify completed in {time.monotonic() - start_time:.2f} seconds")

        #Caculate Alarm
        maxinframe =  2 if camera_info.get("config").get("maxinframe") is None else camera_info.get("config").get("maxinframe")
        maxoutframe =  5 if camera_info.get("config").get("maxoutframe") is None else camera_info.get("config").get("maxoutframe")
        self.camera_alarm[camera_id]['time'] = start_time
        if detection_flag:
            self.camera_alarm[camera_id]['inframe'] +=1
            if self.camera_alarm[camera_id]['status'] == False and self.camera_alarm[camera_id]['inframe'] >= maxinframe:
                #Set and send alarm here
                self.camera_alarm[camera_id]['status'] = True
                self.camera_alarm[camera_id]['outframe'] = 0
                alarm_img_path = os.path.join(self.ALARM_SAVE_DIR, f"{camera_id}_{start_time}.jpg")
                self.camera_alarm[camera_id]['url'] = alarm_img_path
                self.camera_alarm[camera_id]['camera_id']=camera_id
                # cv2.imwrite(alarm_img_path, annotated_image)
                self.logger.debug(f"[{camera_id}] Alarm image saved to {alarm_img_path} in {time.monotonic() - start_time:.2f} seconds")
                #Send web
                self.api_service.send_web_notify_message_v2(self.camera_alarm[camera_id])
                #Send PLC
                self.plc_servive.write_alarm_on()
            elif self.camera_alarm[camera_id]['status'] == True and self.camera_alarm[camera_id]['inframe'] >= maxinframe:
                #Send PLC
                self.plc_servive.write_alarm_on()    
        else:
            self.camera_alarm[camera_id]['outframe'] += 1
            if self.camera_alarm[camera_id]['status'] == True and self.camera_alarm[camera_id]['outframe'] >= maxoutframe:
                
                self.camera_alarm[camera_id]['status'] = False
                self.camera_alarm[camera_id]['inframe'] = 0
                self.camera_alarm[camera_id]['outframe'] = 0
                self.camera_alarm[camera_id]['url'] = ""
                #Send PLC
                self.plc_servive.write_alarm_off() 
                # Send web update
                self.api_service.update_web_notify_message(self.camera_alarm[camera_id])
            else:
                if self.camera_alarm[camera_id]['outframe'] > 5000:
                    self.camera_alarm[camera_id]['outframe'] = 0  
                
    async def main_loop(self):

        check_time = time.monotonic()
        camera_status = await self.fetch_camera_status()
        if not camera_status:
            self.logger.warning("No camera status received")

        for camera_id, status in camera_status.items():   
            self.camera_frame_count[int(camera_id)] = {
                'count': 0,
                'last_time': check_time,
            }
            self.camera_alarm[int(camera_id)] = {
                'time': check_time,
                'status': False,
                'inframe': 0,
                'outframe': 0,
                'url': ''
            }

        while True:
            start_time = time.monotonic()
            # self.logger.info("Check camera status...")
            elapsed = start_time - check_time
            if elapsed >= 5.0:
                check_time = start_time
                camera_status = await self.fetch_camera_status()
                if not camera_status:
                    self.logger.warning("No camera status received")
                    await asyncio.sleep(self.SLEEP_INTERVAL)
                    continue

            # Initialize batch dictionary
            images_batches = {}

            # Create processing tasks for each camera
            tasks = []
            for camera_id, status in camera_status.items():
                if status["alive"] == "True" and status["last_image_timestamp"] != 'unknown' and status["camera_info"]:
                    camera_info = json.loads(status["camera_info"])
                    if camera_info:
                        tasks.append(asyncio.create_task(self.process_camera(int(camera_id), camera_info, images_batches)))
            if tasks:
                # Perform processing tasks for all cameras asynchronously
                await asyncio.gather(*tasks)
                self.time_logger.info(f"Processing camera is completed, taking {time.monotonic() - start_time:.2f} seconds")
                # After all images are collected, batch inference is performed for each model type
                # model_tasks = []
                for model_id, batch_data in images_batches.items():
                    # model_tasks.append(asyncio.create_task(self.call_model_batch(model_id, batch_data)))
                    self.call_model_batch(model_id, batch_data)
                # await asyncio.gather(*model_tasks)
            else:
                self.logger.warning("No processing camera")

            # Dynamically adjust sleep time
            elapsed_time = time.monotonic() - start_time
            self.time_logger.info(f"Processing Analysis is completed, taking {elapsed_time:.2f} seconds")
            adjusted_sleep = max(0.001, self.SLEEP_INTERVAL - elapsed_time)
            await asyncio.sleep(adjusted_sleep)

if __name__ == "__main__":
    app = MainApp()
    asyncio.run(app.main_loop())