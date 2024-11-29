import numpy as np
import cv2
from shapely.geometry import Polygon, box
class analysis():
    def __init__(self):
        self.test = 0
    def process_model(self,batch_data,model,model_config):
        images_batch = batch_data['images']
        camera_info_batch = batch_data['camera_info']
        results = model(images_batch, conf=model_config.get("conf"), classes=model_config.get("label_conf"), verbose=False)
        # self.time_logger.info(f"Mask: {results[0]}")
        detection_flags = []
        for i, detections in enumerate(results):       
            image = images_batch[i]     
            camera_id, camera_info = camera_info_batch[i]
            annotated_image, detection_flag = analysis.annotate_image(self, image, detections, camera_info)
            # self.time_logger.info(f"results: {results}")
            results[i].orig_img = annotated_image
            detection_flags.append(detection_flag)
        return results,detection_flags
     
    def annotate_image(self, image, detections, camera_info):
        """
        Mark the image and filter the matching areas based on the mask.
        :param image: original image (np.ndarray)
        :param detections: YOLO model detection results
        :param camera_info: additional information about the camera
        :param model_name: model name
        :return: the annotated image (np.ndarray), whether the target is detected (bool), the detected tag name (str)
        """
        detection_flag = False
        detected_labels = []
        mask = camera_info.get("config").get("mask")
        camera_id = camera_info.get("id")
        annotated_image = image.copy()
        # self.time_logger.info(f"mask: {mask['polygon1']}")
        # If a mask is provided, checks if the target is within the mask range
        if mask:  
            
            # Draw each polygon on the image
            for key, polygon in mask.items():
                # Convert points to a numpy array and reshape for OpenCV
                pts = np.array(polygon, np.int32).reshape((-1, 1, 2))
                # Draw the polygon (image, points, isClosed, color, thickness)
                cv2.polylines(annotated_image, [pts], isClosed=True, color=(0, 255, 0), thickness=2)

            # Make sure the test results are valid
            if detections.boxes is None or len(detections.boxes) == 0:
                self.logger.debug(f"[{camera_id}] No detections found to annotate.")
                return annotated_image, False
            
            # Extract test results
            boxes = detections.boxes.xyxy.cpu().numpy()
            confidences = detections.boxes.conf.cpu().numpy()
            class_ids = detections.boxes.cls.cpu().numpy().astype(int)

            for i, (bbox, conf, cls_id) in enumerate(zip(boxes, confidences, class_ids)):
                label = detections.names[int(cls_id)]
                detected_labels.append(f"{label} {conf:.2f}")
                x1, y1, x2, y2 = map(int, bbox)
                # Create a Shapely box object for the bounding box
                bbox_polygon = box(*bbox)
                for key, polygon_vertices  in mask.items():
                    polygon = Polygon(polygon_vertices)
                    if polygon.contains(bbox_polygon):
                        detection_flag = True
                        color = (0, 0, 255)  # Red
                        cv2.rectangle(annotated_image, (x1, y1), (x2, y2), color, 2) 
                        # draw labels
                        label_text = f"{label} {conf:.2f}"
                        cv2.putText(
                            annotated_image,
                            label_text,
                            (x1, y1 - 10),
                            cv2.FONT_HERSHEY_SIMPLEX,
                            1.5,
                            color,
                            2,
                        )
                        # If there is a detection result, set the flag
                        detection_flag = True   
                    elif polygon.intersects(bbox_polygon):
                        detection_flag = True
                        # draw border
                        color = (0, 210, 255)  # Orange
                        cv2.rectangle(annotated_image, (x1, y1), (x2, y2), color, 2) 
                        # draw labels
                        label_text = f"{label} {conf:.2f}"
                        cv2.putText(
                            annotated_image,
                            label_text,
                            (x1, y1 - 10),
                            cv2.FONT_HERSHEY_SIMPLEX,
                            1.5,
                            color,
                            2,
                        )
                        # If there is a detection result, set the flag
                        detection_flag = True   
                    else:
                        # draw border
                        color = (0, 255, 0)  # Default green
                        cv2.rectangle(annotated_image, (x1, y1), (x2, y2), color, 2)                     
                        # draw labels
                        label_text = f"{label} {conf:.2f}"
                        cv2.putText(
                            annotated_image,
                            label_text,
                            (x1, y1 - 10),
                            cv2.FONT_HERSHEY_SIMPLEX,
                            1.5,
                            color,
                            2,
                        ) 
        else:
            self.logger.debug(f"[{camera_id}] Mask is None. Processing the entire image.")
        # Return results
        return annotated_image, detection_flag