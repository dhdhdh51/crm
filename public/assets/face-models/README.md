# Face-API.js Model Files

The face recognition system requires model weights from face-api.js.
Run the following commands from the project root to download them:

```bash
php scripts/download_face_models.php
```

Or manually download from:
https://github.com/justadudewhohacks/face-api.js/tree/master/weights

Required files (place in this directory):
- tiny_face_detector_model-weights_manifest.json
- tiny_face_detector_model-shard1
- face_landmark_68_tiny_model-weights_manifest.json
- face_landmark_68_tiny_model-shard1
- face_recognition_model-weights_manifest.json
- face_recognition_model-shard1
- face_recognition_model-shard2
