<?php
session_start();
include '../../config.php';
include '../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Invalid request method']));
}

$nik = mysqli_real_escape_string($connect, $_POST['nik']);
$type = mysqli_real_escape_string($connect, $_POST['type']);
$value = mysqli_real_escape_string($connect, $_POST['value']);

// Map jaminan type to table and column names
$table_map = [
    'BPKB' => ['table' => 'bpkb', 'column' => 'bpkb'],
    'SHM' => ['table' => 'shm', 'column' => 'bukkep'],
    'AKTA' => ['table' => 'akta', 'column' => 'no_akta'],
    'KIOS' => ['table' => 'kios', 'column' => 'no_kios'],
    'BILYET' => ['table' => 'bilyet', 'column' => 'jentabdep'],
    'MANULIFE' => ['table' => 'manulife', 'column' => 'nojam'],
    'BPIH' => ['table' => 'bpih', 'column' => 'noval'],
    'SPPH' => ['table' => 'spph', 'column' => 'nopor']
];

if (!isset($table_map[$type])) {
    die(json_encode(['success' => false, 'message' => 'Invalid jaminan type']));
}

$table = $table_map[$type]['table'];
$column = $table_map[$type]['column'];

$query = mysqli_query($connect, "DELETE FROM $table WHERE nik='$nik' AND $column='$value'");

if ($query) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => mysqli_error($connect)]);
}
