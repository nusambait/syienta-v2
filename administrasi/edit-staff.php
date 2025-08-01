<?php
include '../../config.php';

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kd_staff = mysqli_real_escape_string($connect, $_POST['kd_staff']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $jabatan = mysqli_real_escape_string($connect, $_POST['jabatan']);
    $gelar = mysqli_real_escape_string($connect, $_POST['gelar']);
    $kantor = mysqli_real_escape_string($connect, $_POST['kantor']);

    $query = mysqli_query($connect, "UPDATE staff SET 
        nama = '$nama',
        jabatan = '$jabatan',
        gelar = '$gelar',
        kantor = '$kantor'
        WHERE kd_staff = '$kd_staff'");

    if ($query) {
        $response['success'] = true;
        $response['message'] = 'Data berhasil diperbarui';
    } else {
        $response['message'] = 'Gagal memperbarui data: ' . mysqli_error($connect);
    }
}

echo json_encode($response);
?>