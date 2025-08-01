<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek jika user belum login
if (!isset($_SESSION['username']) || !isset($_SESSION['key_app'])) {
    header("Location: " . $base_url . "dashboard.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Ambil data file sebelum dihapus
    $query_check = "SELECT * FROM m_teller WHERE id = ?";
    $stmt_check = mysqli_prepare($connect, $query_check);
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $result = mysqli_stmt_get_result($stmt_check);
    $data = mysqli_fetch_assoc($result);

    if ($data) {
        $can_delete = false;

        // Cek apakah user adalah ADMIN
        if ($_SESSION['key_app'] == 'ADMIN') {
            $can_delete = true;
        }
        // Cek apakah user adalah pengupload file tersebut
        else if ($data['nama_pengupload'] == $_SESSION['nama']) {
            $can_delete = true;
        }

        if ($can_delete) {
            // Hapus file fisik
            $file_path = 'uploads/' . $data['file'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }

            // Hapus record dari database
            $query_delete = "DELETE FROM m_teller WHERE id = ?";
            $stmt_delete = mysqli_prepare($connect, $query_delete);
            mysqli_stmt_bind_param($stmt_delete, "i", $id);

            if (mysqli_stmt_execute($stmt_delete)) {
                $_SESSION['success'] = "Data berhasil dihapus!";
            } else {
                $_SESSION['error'] = "Gagal menghapus data!";
            }
        } else {
            $_SESSION['error'] = "Anda tidak memiliki izin untuk menghapus data ini!";
        }
    } else {
        $_SESSION['error'] = "Data tidak ditemukan!";
    }

    header("Location: index.php");
    exit();
} else {
    header("Location: index.php");
    exit();
}