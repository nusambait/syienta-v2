<?php
include '../../config.php';
include '../config/config.php';

if (isset($_GET['noreg'])) {
    $noreg = mysqli_real_escape_string($connect, $_GET['noreg']);
    
    // Cek apakah noreg sudah ada di database
    $query = mysqli_query($connect, "SELECT COUNT(*) as count FROM pengajuan WHERE noreg = '$noreg'");
    $result = mysqli_fetch_assoc($query);
    
    // Kirim response dalam format JSON
    header('Content-Type: application/json');
    echo json_encode(['exists' => $result['count'] > 0]);
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No noreg provided']);
}