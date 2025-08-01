<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['noreg']) || !isset($_POST['file_type']) || !isset($_FILES['file'])) {
        throw new Exception('Parameter tidak lengkap');
    }

    $noreg = mysqli_real_escape_string($connect, $_POST['noreg']);
    $file_type = mysqli_real_escape_string($connect, $_POST['file_type']);
    $file = $_FILES['file'];

    // Validasi file
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_message = "Error uploading file: ";
        switch ($file['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $error_message .= "Ukuran file melebihi batas yang diizinkan.";
                break;
            case UPLOAD_ERR_PARTIAL:
                $error_message .= "File hanya terupload sebagian.";
                break;
            case UPLOAD_ERR_NO_FILE:
                $error_message .= "Tidak ada file yang diupload.";
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $error_message .= "Folder temporary tidak ditemukan.";
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $error_message .= "Gagal menulis file ke disk.";
                break;
            case UPLOAD_ERR_EXTENSION:
                $error_message .= "Upload dihentikan oleh ekstensi PHP.";
                break;
            default:
                $error_message .= "Unknown error code: " . $file['error'];
        }
        throw new Exception($error_message);
    }

    // Cek ukuran file
    $max_file_size = 600 * 1024 * 1024; // 600MB dalam bytes
    if ($file['size'] > $max_file_size) {
        throw new Exception("Ukuran file terlalu besar! Maksimal 600MB");
    }

    // Validasi tipe file
    if ($file['type'] !== 'application/pdf') {
        throw new Exception("Hanya file PDF yang diperbolehkan!");
    }

    // Generate nama file
    $timestamp = date('YmdHis');
    $unique_code = substr(md5(uniqid()), 0, 8);
    $new_filename = $noreg . '-' . $timestamp . '-' . $unique_code . '.pdf';

    // Tentukan direktori tujuan
    $upload_dir = '../../../nusamba2/assets/doc/' . $file_type . '/';
    if (!is_dir($upload_dir)) {
        if (!@mkdir($upload_dir, 0755, true)) {
            throw new Exception("Gagal membuat direktori upload. Silakan periksa permission folder.");
        }
    }

    // Pastikan direktori writable
    if (!is_writable($upload_dir)) {
        throw new Exception("Direktori upload tidak writable. Silakan periksa permission folder.");
    }

    // Hapus file lama jika ada
    $old_file_query = mysqli_query($connect, "SELECT $file_type FROM komite WHERE noreg = '$noreg'");
    if ($old_file_query && $row = mysqli_fetch_assoc($old_file_query)) {
        $old_file = $row[$file_type];
        if ($old_file && file_exists($upload_dir . $old_file)) {
            @unlink($upload_dir . $old_file);
        }
    }

    // Upload file baru
    if (!move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
        throw new Exception("Gagal mengupload file. Silakan coba lagi.");
    }

    // Update database
    $update_query = mysqli_query($connect, "UPDATE komite SET $file_type = '$new_filename' WHERE noreg = '$noreg'");
    if (!$update_query) {
        // Jika update database gagal, hapus file yang sudah terupload
        @unlink($upload_dir . $new_filename);
        throw new Exception("Gagal mengupdate database: " . mysqli_error($connect));
    }

    echo json_encode([
        'success' => true,
        'message' => 'File berhasil diupload!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
