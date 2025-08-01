<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Pastikan user sudah login
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kd_staff'])) {
    $kd_staff = mysqli_real_escape_string($connect, $_POST['kd_staff']);
    
    // Dapatkan kantor user yang sedang login
    $username = $_SESSION['username'];
    $query_user = mysqli_query($connect, "SELECT kantor FROM account WHERE username='$username'");
    $user_data = mysqli_fetch_assoc($query_user);
    $user_kantor = $user_data['kantor'];
    
    // Hapus data staff dengan pengecekan kantor
    $query = mysqli_query($connect, "DELETE FROM staff WHERE kd_staff='$kd_staff' AND kantor='$user_kantor'");
    
    if ($query) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => mysqli_error($connect)]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
?>