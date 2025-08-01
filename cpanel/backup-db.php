<?php
session_start();
include '../../config.php';
include '../config/config.php';
include '../includes/check-admin.php';

// Set header untuk download file
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="backup_' . date('Y-m-d_H-i-s') . '.sql"');

// Fungsi untuk mendapatkan struktur tabel
function getTableStructure($table)
{
    global $connect;
    $structure = "";
    $result = mysqli_query($connect, "SHOW CREATE TABLE `$table`");
    $row = mysqli_fetch_row($result);
    $structure .= "\n\n" . $row[1] . ";\n\n";
    return $structure;
}

// Fungsi untuk mendapatkan data tabel
function getTableData($table)
{
    global $connect;
    $data = "";
    $result = mysqli_query($connect, "SELECT * FROM `$table`");

    while ($row = mysqli_fetch_assoc($result)) {
        $values = array_map(function ($value) use ($connect) {
            if ($value === null) return 'NULL';
            return "'" . mysqli_real_escape_string($connect, $value) . "'";
        }, $row);

        $data .= "INSERT INTO `$table` VALUES (" . implode(', ', $values) . ");\n";
    }

    return $data;
}

// Mendapatkan daftar tabel
$tables = array();
$result = mysqli_query($connect, "SHOW TABLES");
while ($row = mysqli_fetch_row($result)) {
    $tables[] = $row[0];
}

// Mendapatkan nama database
$db_name = mysqli_fetch_row(mysqli_query($connect, "SELECT DATABASE()"))[0];

// Header SQL
echo "-- Backup Database\n";
echo "-- Tanggal: " . date('Y-m-d H:i:s') . "\n";
echo "-- Database: " . $db_name . "\n\n";

// Backup setiap tabel
foreach ($tables as $table) {
    echo "-- Struktur tabel `$table`\n";
    echo getTableStructure($table);

    echo "-- Data tabel `$table`\n";
    echo getTableData($table);
}

// Log aktivitas backup
$username = $_SESSION['username'];
$tanggal = date('Y-m-d H:i:s');
$aktivitas = "Melakukan backup database";
$query_log = mysqli_query($connect, "INSERT INTO log_aktivitas (username, tanggal, aktivitas) VALUES ('$username', '$tanggal', '$aktivitas')");

// Redirect kembali ke halaman utama setelah selesai
echo "<script>
    alert('Backup database berhasil!');
    window.location.href = 'index.php';
</script>";
