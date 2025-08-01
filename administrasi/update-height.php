<?php
session_start();
include '../../config.php';

$response = ['success' => false];

if (isset($_POST['height'])) {
    // Ambil kode kantor dari tabel account berdasarkan username yang login
    $username = $_SESSION['username'];
    $query_kantor = mysqli_query($connect, "SELECT kantor FROM account WHERE username='$username'");
    $data_kantor = mysqli_fetch_array($query_kantor);
    $kode_kantor = $data_kantor['kantor'];

    $height = mysqli_real_escape_string($connect, $_POST['height']);

    // Cek apakah data sudah ada
    $check = mysqli_query($connect, "SELECT id FROM height WHERE id='$kode_kantor'");

    if (mysqli_num_rows($check) > 0) {
        // Update data yang ada
        $query = mysqli_query($connect, "UPDATE height SET value='$height' WHERE id='$kode_kantor'");
    } else {
        // Insert data baru
        $query = mysqli_query($connect, "INSERT INTO height (id, value) VALUES ('$kode_kantor', '$height')");
    }

    if ($query) {
        $response['success'] = true;
    }
}

echo json_encode($response);