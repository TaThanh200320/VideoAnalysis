-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               PostgreSQL 15.10 (Debian 15.10-1.pgdg120+1) on x86_64-pc-linux-gnu, compiled by gcc (Debian 12.2.0-14) 12.2.0, 64-bit
-- Server OS:                    
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES  */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table public.cameras: 10 rows
/*!40000 ALTER TABLE "cameras" DISABLE KEYS */;
INSERT INTO "cameras" ("id", "name", "stream_url", "status", "config", "model_id", "created_at", "updated_at") VALUES
	(2, 'Camera 2', 'rtsp://admin:Admin123456*@@192.168.8.193:554/Streaming/channels/101', 1, '{"fps":5}', 1, '2024-11-16 16:28:28', '2024-11-16 16:28:28'),
	(4, 'Camera 4', 'rtsp://admin:GSSFXW@192.168.8.107:554/ch1/main', 1, '{"fps":5}', 1, '2024-11-16 16:31:24', '2024-11-16 16:31:24'),
	(6, 'Camera 6', 'rtsp://admin:abcd1234@192.168.0.214:554/cam/realmonitor?channel=2%26subtype=0', 1, '{"fps":5}', 1, '2024-11-16 16:57:40', '2024-11-16 16:57:40'),
	(5, 'Camera 5', 'rtsp://admin:abcd1234@192.168.0.214:554/cam/realmonitor?channel=1%26subtype=0', 1, '{"fps":5}', 1, '2024-11-16 16:56:49', '2024-11-16 16:56:49'),
	(7, 'Camera 7', 'rtsp://admin:abcd1234@192.168.0.214:554/cam/realmonitor?channel=3%26subtype=0', 1, '{"fps":5}', 1, '2024-11-16 16:57:40', '2024-11-16 16:57:40'),
	(8, 'Camera 8', 'rtsp://admin:abcd1234@192.168.0.214:554/cam/realmonitor?channel=4%26subtype=0', 1, '{"fps":5}', 1, '2024-11-16 16:57:40', '2024-11-16 16:57:40'),
	(9, 'Camera 9', 'rtsp://admin:abcd1234@192.168.0.214:554/cam/realmonitor?channel=7%26subtype=0', 1, '{"fps":5}', 1, '2024-11-16 16:57:40', '2024-11-16 16:57:40'),
	(10, 'Camera 10', 'rtsp://admin:abcd1234@192.168.0.214:554/cam/realmonitor?channel=8%26subtype=0', 1, '{"fps":5}', 1, '2024-11-16 16:57:40', '2024-11-16 16:57:40'),
	(3, 'Camera 3', 'rtsp://admin:Stc%40vielina.com@192.168.8.192:554/Streaming/channels/101', 1, '{"fps":5,"mask":{"polygon1":[[1357,835],[1314,1068],[966,1061],[1000,840]],"polygon2":[[815,737],[815,862],[1038,811],[971,706]]}}', 1, '2024-11-16 16:30:33', '2024-11-27 15:30:12'),
	(1, 'Camera 1', 'rtsp://admin:Admin123456*@@192.168.8.191:554/Streaming/channels/101', 1, '{"fps":5}', 1, '2024-11-16 16:28:28', '2024-11-16 16:28:28');
/*!40000 ALTER TABLE "cameras" ENABLE KEYS */;

-- Dumping data for table public.models: 4 rows
/*!40000 ALTER TABLE "models" DISABLE KEYS */;
INSERT INTO "models" ("id", "name", "url", "config", "status", "created_at", "updated_at") VALUES
	(1, 'Model Default', 'model/yolo11n.pt', '{"conf":0.5,"label_conf":[0],"annotators":{"box_annotator":{"type":"BoxAnnotator","thickness":2},"label_annotator":{"type":"LabelAnnotator","text_position":"TOP_CENTER","text_thickness":2,"text_scale":1}}}', 1, NULL, NULL),
	(2, 'Model Pose', 'model/yolo11n-pose.pt', '{"conf":0.5,"label_conf":[0],"annotators":{"box_annotator":{"type":"BoxAnnotator","thickness":2},"label_annotator":{"type":"LabelAnnotator","text_position":"TOP_CENTER","text_thickness":2,"text_scale":1}}}', 1, NULL, NULL),
	(3, 'Model Count', 'model/yolo11n.pt', '{"conf":0.5,"label_conf":[0],"annotators":{"box_annotator":{"type":"BoxAnnotator","thickness":2},"label_annotator":{"type":"LabelAnnotator","text_position":"TOP_CENTER","text_thickness":2,"text_scale":1}}}', 1, NULL, NULL),
	(4, 'Model Tracking', 'model/yolo11n.pt', '{"conf":0.5,"label_conf":[0],"annotators":{"box_annotator":{"type":"BoxAnnotator","thickness":2},"label_annotator":{"type":"LabelAnnotator","text_position":"TOP_CENTER","text_thickness":2,"text_scale":1}}}', 1, NULL, NULL);
/*!40000 ALTER TABLE "models" ENABLE KEYS */;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;