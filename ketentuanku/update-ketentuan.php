<?php
session_start();
require_once __DIR__ . '/../config/init.php';
include '../../config.php';
include '../config/config.php';

if (!isset($_SESSION['key_app']) || ($_SESSION['key_app'] != 'SKAI' && $_SESSION['key_app'] != 'ADMIN')) {
    $_SESSION['error'] = "Anda tidak memiliki akses!";
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = mysqli_real_escape_string($connect, $_POST['id']);
    $nomor_ket = mysqli_real_escape_string($connect, $_POST['nomor_ket']);
    $judul_ket = mysqli_real_escape_string($connect, $_POST['judul_ket']);
    $ttl_terbit = mysqli_real_escape_string($connect, $_POST['ttl_terbit']);
    $old_file = mysqli_real_escape_string($connect, $_POST['old_file']);

    $file_name = $old_file;

    // Jika ada file baru yang diupload
    if (!empty($_FILES['file_surat']['name'])) {
        $file_name = time() . '_' . $_FILES['file_surat']['name'];
        $file_tmp = $_FILES['file_surat']['tmp_name'];
        $file_type = $_FILES['file_surat']['type'];
        $upload_path = 'uploads/' . $file_name;

        // Validasi tipe file
        if ($file_type != 'application/pdf') {
            $_SESSION['error'] = "File harus berformat PDF!";
            header("Location: edit-ketentuan.php?id=" . $id);
            exit();
        }

        // Upload file baru
        if (move_uploaded_file($file_tmp, $upload_path)) {
            // Hapus file lama jika berhasil upload file baru
            if (file_exists('uploads/' . $old_file)) {
                unlink('uploads/' . $old_file);
            }
        } else {
            $_SESSION['error'] = "Gagal mengupload file!";
            header("Location: edit-ketentuan.php?id=" . $id);
            exit();
        }
    }

    $query = "UPDATE ketentuanku_surat SET 
              nomor_ket = '$nomor_ket',
              judul_ket = '$judul_ket',
              ttl_terbit = '$ttl_terbit',
              file_surat = '$file_name'
              WHERE id = '$id'";

    if (mysqli_query($connect, $query)) {
        $_SESSION['success'] = "Data berhasil diperbarui!";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Gagal memperbarui data: " . mysqli_error($connect);
        header("Location: edit-ketentuan.php?id=" . $id);
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}