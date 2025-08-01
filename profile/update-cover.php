<?php
session_start();
include '../../config.php';
include '../config/config.php';

if (!isset($_SESSION['nik'])) {
    header("Location: " . $base_url . "login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nik = $_SESSION['nik'];

    if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['cover']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $filesize = $_FILES['cover']['size'];
        $max_size = 10 * 1024 * 1024; // 10MB dalam bytes

        if ($filesize > $max_size) {
            $_SESSION['error'] = true;
            $_SESSION['message'] = "Ukuran file terlalu besar! Maksimal 10MB.";
        } else if (in_array($ext, $allowed)) {
            // Generate nama file unik
            $newname = "cover_" . $nik . "_" . time() . "." . $ext;
            $destination = "../assets/media/profile/" . $newname;

            // Hapus cover lama jika ada
            $query = mysqli_query($connect, "SELECT cover FROM account WHERE nik='$nik'");
            $old_cover = mysqli_fetch_assoc($query)['cover'];
            if ($old_cover && $old_cover != 'default-cover.jpg') {
                $old_file = "../assets/media/profile/" . $old_cover;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }

            if (move_uploaded_file($_FILES['cover']['tmp_name'], $destination)) {
                $update = mysqli_query($connect, "UPDATE account SET cover='$newname' WHERE nik='$nik'");

                if ($update) {
                    $_SESSION['success'] = true;
                    $_SESSION['message'] = "Cover berhasil diperbarui!";
                } else {
                    $_SESSION['error'] = true;
                    $_SESSION['message'] = "Gagal memperbarui cover: " . mysqli_error($connect);
                }
            } else {
                $_SESSION['error'] = true;
                $_SESSION['message'] = "Gagal mengupload file!";
            }
        } else {
            $_SESSION['error'] = true;
            $_SESSION['message'] = "Format file tidak diizinkan!";
        }
    }
}

header("Location: index.php?nik=" . $nik);
exit();
