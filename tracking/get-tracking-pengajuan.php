<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Periksa apakah parameter noreg ada
if (isset($_GET['noreg'])) {
    $noreg = mysqli_real_escape_string($connect, $_GET['noreg']);

    // Ambil data pengajuan dengan join ke tabel nasabah dan account untuk AO
    $query_pengajuan = mysqli_query($connect, "SELECT p.*, n.nama as nama_nasabah, a.nama as nama_ao, a.kd_ao 
                                              FROM pengajuan p 
                                              LEFT JOIN nasabah n ON p.niknas = n.nik 
                                              LEFT JOIN account a ON p.ao = a.kd_ao 
                                              WHERE p.noreg = '$noreg'");

    $pengajuan = mysqli_fetch_assoc($query_pengajuan);

    if ($pengajuan) {
        // Ambil data tracking dengan join ke tabel account untuk operator
        $query_tracking = mysqli_query($connect, "SELECT t.*, a.nama as nama_operator 
                                                FROM tracking t 
                                                LEFT JOIN account a ON t.op = a.username 
                                                WHERE t.noreg = '$noreg' 
                                                ORDER BY t.id DESC");

        $tracking = [];
        while ($row = mysqli_fetch_assoc($query_tracking)) {
            $tracking[] = $row;
        }

        // Kirim respons sukses
        echo json_encode([
            'success' => true,
            'pengajuan' => $pengajuan,
            'tracking' => $tracking
        ]);
    } else {
        // Kirim respons gagal
        echo json_encode([
            'success' => false,
            'message' => 'Data pengajuan tidak ditemukan'
        ]);
    }
} else {
    // Kirim respons gagal
    echo json_encode([
        'success' => false,
        'message' => 'Parameter noreg tidak ditemukan'
    ]);
}
