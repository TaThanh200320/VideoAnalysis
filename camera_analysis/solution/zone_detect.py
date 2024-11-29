import cv2
from time import time
from ultralytics.solutions.queue_management import QueueManager
from ultralytics.utils.plotting import Annotator, colors


class ZoneDetect(QueueManager):
    def __init__(self, **kwargs):
        """Initializes the QueueManager with parameters for tracking and counting objects in a video stream."""
        super().__init__(**kwargs)
        self.start_time = 0
        self.end_time = 0
        
    def display_fps(self, im0):
        """Displays the FPS on an image `im0` by calculating and overlaying as white text on a black rectangle."""
        self.end_time = time()
        fps = 1 / round(self.end_time - self.start_time, 2)
        text = f"FPS: {int(fps)}"
        text_size = cv2.getTextSize(text, cv2.FONT_HERSHEY_SIMPLEX, 1.0, 2)[0]
        gap = 10
        cv2.rectangle(
            im0,
            (20 - gap, 70 - text_size[1] - gap),
            (20 + text_size[0] + gap, 70 + gap),
            (0, 255, 0),
            -1,
        )
        cv2.putText(im0, text, (20, 70), cv2.FONT_HERSHEY_SIMPLEX, 1.0, (0, 0, 0), 2)
        
    def zone_counter(self, im0):
        self.start_time = time()
        self.process_queue(im0)
        self.display_fps(im0)
        return im0
    
    def zone_detect(self, im0):
        self.counts = 0
        self.annotator = Annotator(im0, line_width=self.line_width)  # Initialize annotator
        self.extract_tracks(im0)  # Extract tracks

        self.annotator.draw_region(
            reg_pts=self.region, color=(0, 0, 255), thickness=self.line_width * 2
        )  # Draw region

        for box, track_id, cls in zip(self.boxes, self.track_ids, self.clss):
            # Draw bounding box and counting region
            obj_label = f"{track_id}-{self.names[cls]}"
            self.annotator.box_label(box, label=obj_label, color=(255, 0, 0))
            self.store_tracking_history(track_id, box)  # Store track history

            # Draw tracks of objects
            self.annotator.draw_centroid_and_tracks(
                self.track_line, color=(0, 255, 0), track_thickness=self.line_width
            )

            # Cache frequently accessed attributes
            track_history = self.track_history.get(track_id, [])

            # store previous position of track and check if the object is inside the counting region
            prev_position = None
            if len(track_history) > 1:
                prev_position = track_history[-2]
            if self.region_length >= 3 and prev_position and self.r_s.contains(self.Point(self.track_line[-1])):
                self.counts += 1

                # Display warning
                self.annotator.queue_counts_display(
                    f"WARNING",
                    fontScale = 10,
                    points=self.region,
                    region_color=(0, 0, 255),
                    txt_color=(104, 31, 17),
                )

        return im0  # return output image for more usage
