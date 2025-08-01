<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: data-pendamping.php?status=error&message=ID+Pendamping+tidak+ditemukan");
    exit;
}

$nikpend = $_GET['id'];

try {
    $connect->begin_transaction();

    // Check if data exists
    $check = $connect->prepare("SELECT nikpend FROM pendamping WHERE nikpend = ?");
    $check->bind_param("s", $nikpend);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Data pendamping tidak ditemukan");
    }

    // Delete data
    $stmt = $connect->prepare("DELETE FROM pendamping WHERE nikpend = ?");
    $stmt->bind_param("s", $nikpend);

    if (!$stmt->execute()) {
        throw new Exception("Gagal menghapus data: " . $stmt->error);
    }

    $connect->commit();
    header("Location: data-pendamping.php?status=success&message=Data+berhasil+dihapus");
} catch (Exception $e) {
    $connect->rollback();
    header("Location: data-pendamping.php?status=error&message=" . urlencode($e->getMessage()));
}
exit;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Data Pendamping - Nusamba</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
</body>

</html>