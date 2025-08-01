<?php
session_start();
include '../../config.php';
include '../config/config.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['kd_staff']) && isset($_POST['position'])) {
    $kd_staff = mysqli_real_escape_string($connect, $_POST['kd_staff']);
    $new_position = mysqli_real_escape_string($connect, $_POST['position']);

    // Dapatkan informasi staff yang akan diupdate
    $query_staff = mysqli_query($connect, "SELECT kantor FROM staff WHERE kd_staff='$kd_staff'");
    $staff_data = mysqli_fetch_assoc($query_staff);
    $kantor = $staff_data['kantor'];

    // Jika posisi "Tidak Ada", tambahkan kode kantor
    if ($new_position === "Tidak Ada") {
        $new_position_with_kantor = "TidakAda" . $kantor;
    } else if (empty($new_position)) {
        $new_position_with_kantor = "";
    } else {
        $new_position_with_kantor = $new_position . $kantor;
    }

    // Mulai transaction
    mysqli_begin_transaction($connect);

    try {
        // Cek apakah ada staff lain dengan posisi yang sama di kantor yang sama
        $query_existing = mysqli_query($connect, "SELECT kd_staff FROM staff WHERE posisi='$new_position_with_kantor' AND kantor='$kantor'");
        if ($existing_staff = mysqli_fetch_assoc($query_existing)) {
            $existing_kd_staff = $existing_staff['kd_staff'];

            // Dapatkan posisi staff yang akan diupdate
            $query_current = mysqli_query($connect, "SELECT posisi FROM staff WHERE kd_staff='$kd_staff'");
            $current_data = mysqli_fetch_assoc($query_current);
            $current_position = $current_data['posisi'];

            // Update posisi staff yang existing
            mysqli_query($connect, "UPDATE staff SET posisi='$current_position' WHERE kd_staff='$existing_kd_staff'");
        }

        // Update posisi staff yang dipilih dengan kode kantor
        mysqli_query($connect, "UPDATE staff SET posisi='$new_position_with_kantor' WHERE kd_staff='$kd_staff'");

        mysqli_commit($connect);
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        mysqli_rollback($connect);
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}