<?php
session_start();
include '../../config.php';
include '../config/config.php';

if (!isset($_GET['type']) || !isset($_GET['noreg'])) {
    die('Invalid request');
}

$type = $_GET['type'];
$noreg = $_GET['noreg'];

// Get file information from database
$query = mysqli_query($connect, "SELECT analisa, slik, lainnya FROM komite WHERE noreg = '$noreg'");
$data = mysqli_fetch_array($query);

$file_path = '../../restapi/rest-client/assets/doc/';
$file_name = '';

switch ($type) {
    case 'analisa':
        $file_path .= 'analisa/' . $data['analisa'];
        $file_name = $data['analisa'];
        break;
    case 'slik':
        $file_path .= 'slik/' . $data['slik'];
        $file_name = $data['slik'];
        break;
    case 'lainnya':
        $file_path .= 'lainnya/' . $data['lainnya'];
        $file_name = $data['lainnya'];
        break;
    default:
        die('Invalid file type');
}

// Check if file exists
if (!file_exists($file_path) || empty($file_name)) {
    echo "<script>
        alert('File tidak ditemukan');
        window.history.back();
    </script>";
    exit;
}

// Set headers for download
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($file_name) . '"');
header('Content-Length: ' . filesize($file_path));

// Output file
readfile($file_path);
exit;
