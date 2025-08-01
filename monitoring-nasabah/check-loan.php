<?php
include '../../config.php';
include '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomor_loan = mysqli_real_escape_string($connect, $_POST['nomor_loan']);
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // Jika action adalah check, cari data di tabel tb_nasabah
    if ($action === 'check') {
        // Cari di tb_nasabah
        $query = mysqli_query($connect, "SELECT * FROM tb_nasabah WHERE nomor_loan='$nomor_loan' ORDER BY id DESC LIMIT 1");

        if (mysqli_num_rows($query) > 0) {
            $nasabah = mysqli_fetch_assoc($query);
            echo json_encode([
                'found' => true,
                'nasabah' => $nasabah
            ]);
            exit;
        }
    }

    // Jika tidak ditemukan
    echo json_encode(['found' => false]);
}