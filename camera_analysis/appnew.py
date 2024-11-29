import logging
import asyncio
import time
import datetime
import os
from tempfile import NamedTemporaryFile

import cv2
import numpy as np
import redis
from PIL import Image, ImageDraw, ImageFont
import json

# Third-party packages
# import supervision as sv
# from supervision.draw.color import Color
from ultralytics import YOLO  # Import Ultralytics Package
from ultralytics import solutions  # Import Ultralytics Package

# local kit
from ApiService import ApiService
from image_storage import ImageStorage
from logging_config import configure_logging
from stc_solutions.zone_detect import ZoneDetect

class MainApp:
    def __init__(self):
        configure_logging()  # Setup log
        self.logger = logging.getLogger("MainApp")
        self.time_logger = logging.getLogger("TimeLogger")

        self.api_service = ApiService(base_url=os.getenv("WEB_SERVICE_URL"))
        self.last_sent_timestamps = {}
        self.SLEEP_INTERVAL = 5  # Set a reasonable sleep interval

        # Add the following two lines for each camera's tracker and annotator
        self.trackers = {}
        self.trace_annotators = {}

        self.redis_host = "redis"
        self.redis_port = 6379
        self.r = redis.Redis(host=self.redis_host, port=self.redis_port, db=0)
        self.image_storage = ImageStorage(self.r)

        self.init_dirs()
        self.init_models()

    def init_dirs(self):
        start_time = time.monotonic()
        self.BASE_SAVE_DIR = "saved_images"
        self.RAW_SAVE_DIR = os.path.join(self.BASE_SAVE_DIR, "raw_images")
        self.ANNOTATED_SAVE_DIR = os.path.join(self.BASE_SAVE_DIR, "annotated_images")
        self.STREAM_SAVE_DIR = os.path.join(self.BASE_SAVE_DIR, "stream")

        for dir_path in [
            self.RAW_SAVE_DIR,
            self.ANNOTATED_SAVE_DIR,
            self.STREAM_SAVE_DIR,
        ]:
            if not os.path.exists(dir_path):
                os.makedirs(dir_path)

        self.time_logger.debug(
            f"Directories initialized in {time.monotonic() - start_time:.2f} seconds"
        )
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
            match model_id:
                case 1 | 5 | 6 | 7 | 8 | 9 | 10:
                    self.models[model_id] = YOLO(model_path)
                    # zone = [(20,400), (600,400), (600,360), (400,300), (200,300), (20,360)]
                    # zone = [(20,700), (1450,700), (1200,440), (120,440)]
                    # self.models[model_id] = ZoneDetect(
                    #     show=True,
                    #     region=zone,
                    #     model=model_path,
                    #     classes=[0],
                    #     verbose=False,
                    # )
                case 2:
                    self.models[model_id] = YOLO(model_path)
                case 3:
                    # line_points = [(750, 500),(1200, 500),(1200, 530),(750,530)]
                    line_points = [(0, 330), (1700, 330)]
                    self.models[model_id] = solutions.ObjectCounter(
                        show=True,
                        region=line_points,
                        model=model_path,
                        classes=[0],
                        verbose=False,
                    )
                case 4:
                    self.models[model_id] = YOLO(model_path)
            self.logger.debug(f"Model {model_id} loaded from {model_path}")
        self.time_logger.debug(
            f"Models and annotators initialized in {time.monotonic() - start_time:.2f} seconds"
        )

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

    async def process_camera(self, camera_id, camera_info):
        """
        Handles image acquisition and recognition from a single camera
        """
        try:
            frame_count = 0
            last_time = time.monotonic()
            while True:
                # start_time = time.monotonic()
                # print(f"Processing camera {camera_id}")
                img = await self.fetch_snapshot(camera_id)        
                if img is not None:
                    # self.logger.debug(f"Image from camera {camera_id} ready for processing")
                    # Identification based on camera model type
                    model_id = camera_info.get("model_id")
                    # print(f"{camera_id} camera_info: {camera_info}")
                    # print(f"{camera_id} models: {self.models}")
                    if model_id in self.models:
                        self.call_model_single(camera_id, img, model_id, camera_info)
                        # self.time_logger.debug(f"Camera {camera_id} processing for completed in {time.monotonic() - start_time:.2f} seconds")
                        #FPS calcule TuanDA
                        frame_count += 1
                        current_time = time.monotonic()
                        elapsed = current_time - last_time
                        if elapsed >= 10.0:
                            fps = frame_count / elapsed
                            frame_count = 0
                            last_time = current_time
                            self.time_logger.debug(f"Camera {camera_id} FPS: {fps}")
                    else:
                        self.logger.warning(f"No valid recognition model for camera {camera_id}")
                else:
                    self.logger.warning(f"No image fetched for camera {camera_id}")
                await asyncio.sleep(0.03)
        except asyncio.CancelledError:
            self.logger.warning(f"Task {camera_id} has been STOPPED.")
            raise            

    def call_model_single(self, camera_id, image, model_id, camera_info):
        """
        Separately process images from one camera for model identification
        """
        start_time = time.monotonic()
        model_config = camera_info.get("config")
        # print(f"model config: {model_config}")
        model = self.models[model_id]
        # TuanDA Chia loai model de xu ly model1 = Tracking, model2 = Detector,Model3 = Counter...
        match model_id:
            case 1 | 5 | 6 | 7 | 8 | 9 | 10: #Detect
                results = model(image, conf=model_config.get("conf"), verbose=False, classes=[0])
                annotated_image = results[0].plot()
                # annotated_image = model.zone_detect(image)
                detection_flag = True
                label = ""
            case 2: #Pose
                results = model(image, conf=model_config.get("conf"), verbose=False)
                annotated_image = results[0].plot()
                detection_flag = True
                label = ""
            case 3: #count
                annotated_image = model.count(image)
                detection_flag = True
                label = ""
            case 4: #tracking
                results = model.predict(image, conf=model_config.get("conf"), verbose=False)
                detections = results[0]
                if not detections.boxes:
                    self.logger.warning(f"No detections found for camera {camera_id}")
                    return
                # Filter test results
                filtered_boxes = []
                filtered_labels = []
                filtered_confs = []

                for box, conf, cls_id in zip(detections.boxes.xyxy, detections.boxes.conf, detections.boxes.cls):
                    class_name = model.names[int(cls_id)]
                    filtered_boxes.append(box)
                    filtered_labels.append(class_name)
                    filtered_confs.append(conf)
                    detection_flag = True
                    label = ""
                # Annotate images
                annotated_image, detection_flag, label = self.annotate_image(
                image, detections, model.names, camera_info, model_id
                )
        # Save and notify       
        self.save_and_notify(camera_id, annotated_image, camera_info, model_id, detection_flag, label)
        # self.time_logger.debug(f"Model {model_id} processing for camera {camera_id} completed in {time.monotonic() - start_time:.2f} seconds")

    def annotate_image(self, image, detections, model_names, camera_info, model_id):
        """
        Mark the image and filter the matching areas based on the mask.

        :param image: original image (np.ndarray)
        :param detections: YOLO model detection results
        :param mask: Mask image (np.ndarray), if None, the entire image will be processed
        :param model_names: list of model tag names
        :param camera_info: additional information about the camera
        :param model_name: model name
        :return: the annotated image (np.ndarray), whether the target is detected (bool), the detected tag name (str)
        """
        annotated_image = image.copy()
        # Make sure the test results are valid
        if detections.boxes is None or len(detections.boxes) == 0:
            self.logger.warning("No detections found to annotate.")
            return annotated_image, False, ""

        #Extract detection results
        boxes = detections.boxes.xyxy.cpu().numpy()
        confidences = detections.boxes.conf.cpu().numpy()
        class_ids = detections.boxes.cls.cpu().numpy().astype(int)

        detection_flag = False
        detected_labels = []

        for i, (box, conf, cls_id) in enumerate(zip(boxes, confidences, class_ids)):
            label = model_names[int(cls_id)]
            detected_labels.append(f"{label} {conf:.2f}")

            x1, y1, x2, y2 = map(int, box)

            # If a mask is provided, check if the target is within the mask range TuanDA
            mask = camera_info.get("config").get("mask")
            if mask is not None:
                # Check if it is a full black mask
                if np.sum(mask) == 0:
                    self.logger.debug("Mask is completely black. Processing the entire image as if no mask is set.")
                    mask = None  #Set mask to None, and then process the entire image directly
                else:
                    mask_crop = mask[y1:y2, x1:x2]
                    if mask_crop.size == 0 or np.sum(mask_crop == 255) / mask_crop.size < 0.5:
                        self.logger.debug(f"Detection {label} skipped due to mask filtering.")
                        continue # Skip this detection box, but continue processing the next detection box
            else:
                self.logger.debug("Mask is None. Processing the entire image.")

            # Draw borders
            color = (0, 255, 0)  #Default green
            cv2.rectangle(annotated_image, (x1, y1), (x2, y2), color, 2)

            # draw labels
            label_text = f"{label} {conf:.2f}"
            cv2.putText(
                annotated_image,
                label_text,
                (x1, y1 - 10),
                cv2.FONT_HERSHEY_SIMPLEX,
                0.5,
                color,
                1,
            )

            self.logger.info(f"Annotated: {label} at [{x1}, {y1}, {x2}, {y2}] with confidence {conf:.2f}")

            # If there is a detection result, set the flag
            detection_flag = True

        # Save framed image (for DEBUG)
        debug_folder = os.path.join(self.BASE_SAVE_DIR, "debug_images")
        os.makedirs(debug_folder, exist_ok=True)
        debug_image_path = os.path.join(debug_folder, f"{camera_info['id']}_annotated.jpg")
        cv2.imwrite(debug_image_path, annotated_image)
        self.logger.info(f"Annotated debug image saved at {debug_image_path}")

        # Return the labeling results
        return annotated_image, detection_flag, ", ".join(detected_labels)

    def save_and_notify( self, camera_id, annotated_image, camera_info, model_id, detection_flag, label):
        start_time = time.monotonic()
        # annotated_img_path = os.path.join(
        #     self.ANNOTATED_SAVE_DIR, f"{camera_id}_{timestamp}.jpg"
        # )
        # cv2.imwrite(annotated_img_path, annotated_image)
        # self.logger.debug(f"Annotated image saved to {annotated_img_path}")

        # # Save latest images to streaming directory
        # stream_img_path = os.path.join(self.STREAM_SAVE_DIR, f"{camera_id}.jpg")
        # cv2.imwrite(
        #     stream_img_path, annotated_image, [cv2.IMWRITE_JPEG_QUALITY, 70]
        # )
        # self.logger.debug(
        #     f"Latest image (with or without annotations) saved to {stream_img_path} for camera {camera_id}"
        # )
        redis_key = f"camera_{camera_id}_boxed_image"
        self.image_storage.save_image(redis_key, annotated_image)
        # self.time_logger.debug(f"Camera {camera_id} save and notify completed in {time.monotonic() - start_time:.2f} seconds")

    active_camera_tasks = {}
    async def main_loop(self):
        # last_time = time.monotonic()
        while True:
            start_time = time.monotonic()
            # self.logger.debug("Checking cameras...")
            camera_status = await self.fetch_camera_status()
            if not camera_status:
                self.logger.warning("No camera status received")
                await asyncio.sleep(self.SLEEP_INTERVAL)
                continue
            # else:
            #     self.logger.debug(f"Camera status: {camera_status}")
            self.logger.debug(f"Get camera status in {time.monotonic() - start_time:.2f} seconds")
            new_camera_ids = set(camera_status.keys())
            existing_camera_ids = set(self.active_camera_tasks.keys())
            # self.logger.warning(f"new_camera_ids {new_camera_ids}.")
            # self.logger.warning(f"existing_camera_ids {existing_camera_ids}.")
            # Stop camera threads that are no longer needed
            # Find the camera that needs to be stopped (in the old list, but not in the new list)
            for camera_id in existing_camera_ids:
                if camera_id not in new_camera_ids or camera_status[camera_id]["alive"] != "True" \
                or camera_status[camera_id]["last_image_timestamp"] == "unknown" or not camera_status[camera_id]["camera_info"]:
                    camera_task = self.active_camera_tasks.pop(camera_id)
                    camera_task.cancel()
                    try:
                        await camera_task  # Wait for the task to cancel properly
                    except asyncio.CancelledError:
                        self.logger.warning(f"Camera {camera_id} has been successfully cancelled.")
            # Create a task list to work on all cameras simultaneously
            for camera_id_str, status in camera_status.items():
                camera_id = int(camera_id_str)
                if status["alive"] == "True" and status["last_image_timestamp"] != 'unknown' and status["camera_info"]:
                    camera_info = json.loads(status["camera_info"])
                    # print("camera_info", camera_info)
                    if camera_info and camera_id_str not in self.active_camera_tasks:
                        camera_task = asyncio.create_task(self.process_camera(camera_id, camera_info))
                        self.active_camera_tasks[camera_id_str] = camera_task
                        self.logger.debug(f"Camera {camera_id} has been successfully started.")   
                else:
                    if  camera_id in self.active_camera_tasks:
                        camera_task = self.active_camera_tasks.pop(camera_id)
                        camera_task.cancel()
                        try:
                            await camera_task  # Wait for the task to cancel properly
                        except asyncio.CancelledError:
                            self.logger.warning(f"Camera {camera_id} has been successfully cancelled.")
            # self.time_logger.debug(f"Processing completed in {time.monotonic() - start_time:.2f} seconds")                              
            await asyncio.sleep(self.SLEEP_INTERVAL)  
                          

if __name__ == "__main__":
    app = MainApp()
    asyncio.run(app.main_loop())
