<?php
session_start();
include '../../config.php';
include '../config/config.php';

if (!isset($_POST['id']) || !isset($_POST['bulan']) || !isset($_POST['tahun'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required parameters']);
    exit;
}

$id = mysqli_real_escape_string($connect, $_POST['id']);
$bulan = mysqli_real_escape_string($connect, $_POST['bulan']);
$tahun = mysqli_real_escape_string($connect, $_POST['tahun']);

$query = mysqli_query($connect, "SELECT * FROM ksu_gaji_karyawan 
    WHERE id_karyawan='$id' AND bulan='$bulan' AND tahun='$tahun' LIMIT 1");

if ($data = mysqli_fetch_assoc($query)) {
    echo json_encode($data);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Data not found']);
}