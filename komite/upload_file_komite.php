<?php
include '../../config.php';
include '../config/config.php';
header('Content-Type: application/json');

// Validasi input
$noreg = $_POST['noreg'] ?? '';
$type = $_POST['type'] ?? '';
$file = $_FILES['file'] ?? null;

$allowedTypes = ['analisa', 'slik', 'lainnya'];
$folderMap = [
    'analisa' => '../../restapi/rest-client/assets/doc/analisa/',
    'slik' => '../../restapi/rest-client/assets/doc/slik/',
    'lainnya' => '../../restapi/rest-client/assets/doc/lainnya/'
];

if (!$noreg || !$type || !$file || !in_array($type, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak valid!']);
    exit;
}

if ($file['type'] !== 'application/pdf') {
    echo json_encode(['success' => false, 'message' => 'Hanya file PDF yang diizinkan!']);
    exit;
}
if ($file['size'] > 300 * 1024 * 1024) {
    echo json_encode(['success' => false, 'message' => 'Ukuran file maksimal 300MB!']);
    exit;
}

$folder = $folderMap[$type];
if (!is_dir($folder)) {
    mkdir($folder, 0777, true);
}

// Ambil nama file lama dari DB
$stmt = $connect->prepare("SELECT `$type` FROM komite WHERE noreg = ?");
$stmt->bind_param('s', $noreg);
$stmt->execute();
$res = $stmt->get_result();
$oldFile = '';
if ($row = $res->fetch_assoc()) {
    $oldFile = $row[$type] ?? '';
}

// Hapus file lama jika ada
if ($oldFile && file_exists($folder . $oldFile)) {
    @unlink($folder . $oldFile);
}

// Rename file baru
$kodeunik = substr(md5(uniqid(rand(), true)), 0, 6);
$tgl = date('Ymd-His');
$ext = '.pdf';
$filename = $noreg . '-' . $tgl . '-' . $kodeunik . $ext;
$target = $folder . $filename;

if (!move_uploaded_file($file['tmp_name'], $target)) {
    echo json_encode(['success' => false, 'message' => 'Gagal upload file!']);
    exit;
}

// Update DB
$stmt = $connect->prepare("UPDATE komite SET `$type` = ? WHERE noreg = ?");
$stmt->bind_param('ss', $filename, $noreg);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode(['success' => true, 'filename' => $filename]);
} else {
    // Jika update gagal, hapus file yang baru diupload
    @unlink($target);
    echo json_encode(['success' => false, 'message' => 'Gagal update database!']);
}
