<?php
/**
 * Download face-api.js model weights needed for face recognition attendance.
 * Run: php scripts/download_face_models.php
 */

$baseUrl  = 'https://github.com/justadudewhohacks/face-api.js/raw/master/weights/';
$destDir  = __DIR__ . '/../public/assets/face-models/';

$files = [
    'tiny_face_detector_model-weights_manifest.json',
    'tiny_face_detector_model-shard1',
    'face_landmark_68_tiny_model-weights_manifest.json',
    'face_landmark_68_tiny_model-shard1',
    'face_recognition_model-weights_manifest.json',
    'face_recognition_model-shard1',
    'face_recognition_model-shard2',
];

if (!is_dir($destDir)) {
    mkdir($destDir, 0755, true);
}

foreach ($files as $file) {
    $url  = $baseUrl . $file;
    $dest = $destDir . $file;

    if (file_exists($dest)) {
        echo "[SKIP]  {$file} (already exists)\n";
        continue;
    }

    echo "[DOWN]  {$file} ...\n";
    $data = @file_get_contents($url);
    if ($data === false) {
        echo "[FAIL]  Could not download {$file}\n";
        continue;
    }
    file_put_contents($dest, $data);
    echo "[OK]    {$file}\n";
}

echo "\nDone! Model files saved to: {$destDir}\n";
