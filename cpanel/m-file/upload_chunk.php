<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['upload_chunk'])) {
        throw new Exception('Invalid request');
    }

    $noreg = mysqli_real_escape_string($connect, $_POST['noreg']);
    $file_type = mysqli_real_escape_string($connect, $_POST['file_type']);
    $current_chunk = (int)$_POST['current_chunk'];
    $total_chunks = (int)$_POST['total_chunks'];
    $filename = $_POST['filename'];

    // Validasi input
    if (!$noreg || !$file_type || !isset($_FILES['chunk'])) {
        throw new Exception('Parameter tidak lengkap');
    }

    // Buat direktori temp jika belum ada
    $temp_dir = "../../../nusamba2/assets/temp";
    if (!is_dir($temp_dir)) {
        if (!@mkdir($temp_dir, 0755, true)) {
            throw new Exception('Gagal membuat direktori temp');
        }
    }

    // Buat direktori upload jika belum ada
    $upload_dir = "../../../nusamba2/assets/doc/" . $file_type;
    if (!is_dir($upload_dir)) {
        if (!@mkdir($upload_dir, 0755, true)) {
            throw new Exception('Gagal membuat direktori upload');
        }
    }

    // Generate nama file final
    $timestamp = date('YmdHis');
    $unique_code = substr(md5(uniqid()), 0, 8);
    $final_filename = $noreg . '-' . $timestamp . '-' . $unique_code . '.pdf';
    $temp_file = $temp_dir . '/' . $final_filename;

    // Proses chunk
    $chunk = $_FILES['chunk'];
    if ($chunk['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Error uploading chunk: ' . $chunk['error']);
    }

    // Jika ini adalah chunk pertama, hapus file temp yang mungkin ada
    if ($current_chunk === 0 && file_exists($temp_file)) {
        @unlink($temp_file);
    }

    // Pindahkan chunk ke file temporary
    if (!move_uploaded_file($chunk['tmp_name'], $temp_file)) {
        throw new Exception('Gagal memindahkan chunk ke file temporary');
    }

    // Jika ini adalah chunk terakhir
    if ($current_chunk === $total_chunks - 1) {
        // Verifikasi file PDF
        if (!is_readable($temp_file)) {
            throw new Exception('File tidak dapat dibaca setelah upload');
        }

        // Cek ukuran file
        $file_size = filesize($temp_file);
        if ($file_size === 0) {
            @unlink($temp_file);
            throw new Exception('File kosong setelah upload');
        }

        // Cek header PDF
        $handle = fopen($temp_file, 'rb');
        if (!$handle) {
            throw new Exception('Gagal membuka file untuk verifikasi');
        }

        $header = fread($handle, 5);
        fclose($handle);

        if ($header !== '%PDF-') {
            @unlink($temp_file);
            throw new Exception('File bukan PDF yang valid');
        }

        // Hapus file lama jika ada
        $old_file_query = mysqli_query($connect, "SELECT $file_type FROM komite WHERE noreg = '$noreg'");
        if ($old_file_query && $row = mysqli_fetch_assoc($old_file_query)) {
            $old_file = $row[$file_type];
            if ($old_file && file_exists($upload_dir . '/' . $old_file)) {
                @unlink($upload_dir . '/' . $old_file);
            }
        }

        // Pindahkan file ke direktori final
        $final_path = $upload_dir . '/' . $final_filename;
        if (!copy($temp_file, $final_path)) {
            @unlink($temp_file);
            throw new Exception('Gagal memindahkan file ke direktori final');
        }

        // Verifikasi file setelah dipindahkan
        if (!is_readable($final_path)) {
            @unlink($final_path);
            @unlink($temp_file);
            throw new Exception('File tidak dapat dibaca setelah dipindahkan');
        }

        // Update database
        $update_query = mysqli_query($connect, "UPDATE komite SET $file_type = '$final_filename' WHERE noreg = '$noreg'");
        if (!$update_query) {
            @unlink($final_path);
            @unlink($temp_file);
            throw new Exception('Gagal mengupdate database: ' . mysqli_error($connect));
        }

        // Hapus file temporary
        @unlink($temp_file);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Chunk berhasil diupload'
    ]);
} catch (Exception $e) {
    // Hapus file temporary jika ada error
    if (isset($temp_file) && file_exists($temp_file)) {
        @unlink($temp_file);
    }

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
