<?php
include '../../config.php';
include '../config/config.php';
header('Content-Type: application/json');

$noreg = $_POST['noreg'] ?? '';
$type = $_POST['type'] ?? '';
$filename = $_POST['filename'] ?? '';

$allowedTypes = ['analisa', 'slik', 'lainnya'];
$folderMap = [
    'analisa' => '../../restapi/rest-client/assets/doc/analisa/',
    'slik' => '../../restapi/rest-client/assets/doc/slik/',
    'lainnya' => '../../restapi/rest-client/assets/doc/lainnya/'
];

if (!$noreg || !$type || !in_array($type, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid!']);
    exit;
}

$folder = $folderMap[$type];
if ($filename && file_exists($folder . $filename)) {
    @unlink($folder . $filename);
}

// Update DB
$stmt = $connect->prepare("UPDATE komite SET `$type` = NULL WHERE noreg = ?");
$stmt->bind_param('s', $noreg);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal update database!']);
}
