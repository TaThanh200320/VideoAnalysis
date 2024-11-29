from flask import Flask, request, jsonify, send_file, render_template, Response
from flask_restx import Api, Resource, fields
from redis_utils import init_redis, get_all_camera_status
from camera_manager import CameraManager
import time
from PIL import Image, ImageDraw
import base64
import io
from flask_cors import CORS
import os
from urllib.parse import unquote
from werkzeug.utils import safe_join
import logging
import sys
import json
from imutils import build_montages
import numpy as np

app = Flask(__name__)
api = Api(app)
CORS(app)
# Allow access from specific domains
# allowed_origins = ["https://pytest.intemotech.com"]

# cors = CORS(app, resources={
#     r"/*": {"origins": allowed_origins}
# })

# initialization Redis
r = init_redis()
manager = CameraManager()
manager.run()

camera_model = api.model('Camera', {
    'camera_id': fields.String(required=True, description='The camera identifier'),
    'url': fields.String(required=True, description='The URL of the camera stream')
})

camera_ids_model = api.model('CameraIds', {
    'camera_ids': fields.List(fields.String, required=True, description='List of camera identifiers')
})

polygon_model = api.model('Polygon', {
    'points': fields.List(fields.Nested(api.model('Point', {
        'x': fields.Float(required=True, description='X coordinate of the point'),
        'y': fields.Float(required=True, description='Y coordinate of the point'),
    }))),
    'camera_id': fields.String(required=True, description='Camera ID associated with this polygon')
})

@api.route('/camera_status')
class CameraStatus(Resource):
    def get(self):
        status = get_all_camera_status(r)
        return status, 200

@app.route('/get_snapshot/<camera_id>')
def get_latest_frame(camera_id):
    image_data = r.get(f'camera_{camera_id}_latest_frame')
    if image_data:
        return Response(image_data, mimetype='image/jpeg')
    else:
        return send_file('no_single.jpg', mimetype='image/jpeg')

@app.route('/image/<path:image_path>')
def get_image(image_path):
    try:
        return send_file(image_path, mimetype='image/jpeg')
    except FileNotFoundError:
        return send_file('no_single.jpg', mimetype='image/jpeg')

def generate_frames(camera_id):
    while True:
        frame_key = f'camera_{camera_id}_latest_frame'
        frame_data = r.get(frame_key)
        if frame_data:
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + frame_data + b'\r\n')
        else:
            print(f'generate_frames {camera_id} is null')
            break
        time.sleep(0.08)

def generate_recognized_frames(camera_id):
    # count = 0
    while True:
        frame_key = f'camera_{camera_id}_boxed_image'
        frame_data = r.get(frame_key)
        if frame_data: 
            # count = count + 1           
            # print(f'generate_recognized_frames {camera_id} is {count}')
            yield (b'--frame\r\n'
                   b'Content-Type: image/jpeg\r\n\r\n' + frame_data + b'\r\n')
        else:
            print(f'generate_recognized_frames {camera_id} is null')
            break
        time.sleep(0.08)

def generate_recognized_multi_frames(keys, row, column):
    while True:
        images = []
        for camera_id in keys:
            frame_key = f'camera_{camera_id}_boxed_image'
            frame_data = r.get(frame_key)
            if frame_data:
                # Decode the byte stream to an image array
                image = Image.open(io.BytesIO(frame_data))           
                images.append(np.array(image))               
            else:
                print(f'generate_recognized_frames {camera_id} is null')
                break
        # create montage
        # build_montages(images_list, (width,height), (column,row))
        montages = build_montages(images, (1024,768), (column,row)) # return numpy array        
        montage_image = Image.fromarray(montages[0])
        # Save the combined image to a byte stream
        byte_stream = io.BytesIO()
        montage_image.save(byte_stream, format='JPEG')
        byte_stream.seek(0)
        yield (b'--frame\r\n'
                b'Content-Type: image/jpeg\r\n\r\n' + byte_stream.getvalue() + b'\r\n')
        time.sleep(0.08)
 
# Identify streaming routes (via redis)
@app.route('/recognized_stream/<ID>')
def recognized_stream(ID):
    return Response(generate_recognized_frames(ID), mimetype='multipart/x-mixed-replace; boundary=frame')

# Identify streaming routes (via redis)
@app.route('/recognized_stream', methods=['GET'])
def recognized_stream_multi():
    # Get video keys from request parameter
    keys_param = request.args.get('camera_ids')  
    row = int(request.args.get('row', 3))
    column = int(request.args.get('column', 4))
    if not keys_param:
        return {"error": "No video keys provided"}, 400
    # Split keys by comma
    keys = keys_param.split(',')
    # Stream videos with multipart/x-mixed-replace
    return Response(generate_recognized_multi_frames(keys, row, column), mimetype='multipart/x-mixed-replace; boundary=frame')

# Streaming Router
@app.route('/get_stream/<int:ID>')
def get_stream(ID):
    return Response(generate_frames(ID), mimetype='multipart/x-mixed-replace; boundary=frame')

# Snapshot UI routing
@app.route('/snapshot_ui/<ID>')
def snapshot_ui(ID):
    image_key = f'camera_{ID}_latest_frame'
    image_data = r.get(image_key)
    if image_data:
        # Encode the image as Base64 and pass it to the template
        encoded_image = base64.b64encode(image_data).decode('utf-8')
        return render_template('snapshot_ui.html', camera_id=ID, image_data=encoded_image)
    else:
        return send_file('no_single.jpg', mimetype='image/jpeg')

# Display image GET method
@app.route('/showimage/<path:image_path>', methods=['GET'])
def show_image_get(image_path):
    # Decode path of URL
    image_path = unquote(image_path)
    
    # remove prefix
    prefix = 'saved_images/annotated_images/'
    if image_path.startswith(prefix):
        image_path = image_path[len(prefix):]
    
    # Set base directory
    base_dir = os.path.join(app.root_path, 'saved_images', 'annotated_images')
    
    # Combine the complete path
    image_full_path = safe_join(base_dir, image_path)
    
    print(f"Requested image path: {image_full_path}")
    
    # Confirm image exists
    if not os.path.exists(image_full_path):
        print(f"Image not found at path: {image_full_path}")
        return jsonify({'error': 'Image not found', 'path': image_full_path}), 404

    try:
        # Return to picture
        return send_file(image_full_path, mimetype='image/jpeg')
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    logging.basicConfig(level=logging.INFO, stream=sys.stdout, force=True)
    app.run(host='0.0.0.0', port=5000)
    # app.run()
