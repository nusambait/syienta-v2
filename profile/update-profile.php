<?php
session_start();
include '../../config.php';
include '../config/config.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['nik']) || !isset($_SESSION['nik']) || $_POST['nik'] != $_SESSION['nik']) {
        throw new Exception('Akses tidak valid!');
    }

    $nik = mysqli_real_escape_string($connect, $_POST['nik']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $tgl_lahir = mysqli_real_escape_string($connect, $_POST['tgl_lahir']);
    // Validasi tanggal lahir
    if (empty($tgl_lahir)) {
        throw new Exception('Tanggal lahir tidak boleh kosong!');
    }

    // Validasi format tanggal
    if (!strtotime($tgl_lahir)) {
        throw new Exception('Format tanggal lahir tidak valid!');
    }

    $no_wa = mysqli_real_escape_string($connect, $_POST['no_wa']);
    $bio = mysqli_real_escape_string($connect, $_POST['bio']);
    $alamat = mysqli_real_escape_string($connect, $_POST['alamat']);

    $query = "UPDATE account SET 
              nama = '$nama',
              email = '$email',
              tgl_lahir = '$tgl_lahir',
              no_wa = '$no_wa',
              bio = '$bio',
              alamat = '$alamat'
              WHERE nik = '$nik'";

    if (!mysqli_query($connect, $query)) {
        throw new Exception(mysqli_error($connect));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Profil berhasil diperbarui!'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}