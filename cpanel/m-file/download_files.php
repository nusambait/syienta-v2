<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

// Cek apakah parameter tahun ada
if (!isset($_GET['year'])) {
    die('Parameter tahun tidak ditemukan');
}

$year = $_GET['year'];
$file_types = ['analisa', 'slik', 'lainnya'];

// Buat nama file zip
$zip_filename = "files_" . $year . "_" . date('YmdHis') . ".zip";
$temp_dir = "../../../restapi/rest-client/assets/temp";
$zip_path = $temp_dir . "/" . $zip_filename;

// Buat direktori temp jika belum ada
if (!is_dir($temp_dir)) {
    if (!@mkdir($temp_dir, 0755, true)) {
        die('Gagal membuat direktori temp. Silakan periksa permission folder.');
    }
}

// Pastikan direktori temp writable
if (!is_writable($temp_dir)) {
    die('Direktori temp tidak writable. Silakan periksa permission folder.');
}

// Buat objek ZipArchive
$zip = new ZipArchive();
$zip_result = $zip->open($zip_path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

if ($zip_result !== TRUE) {
    die('Tidak dapat membuat file ZIP. Error code: ' . $zip_result);
}

// Ambil data dari database berdasarkan tahun
$query = mysqli_query($connect, "SELECT * FROM komite");
if (!$query) {
    die('Gagal mengambil data dari database: ' . mysqli_error($connect));
}

$total_files = 0;
$added_files = array();

while ($row = mysqli_fetch_assoc($query)) {
    // Cek apakah noreg mengandung tahun yang dipilih
    if (strpos($row['noreg'], '-' . substr($year, -2)) !== false) {
        foreach ($file_types as $type) {
            if ($row[$type]) {
                $file_path = '../../../restapi/rest-client/assets/doc/' . $type . '/' . $row[$type];
                if (file_exists($file_path) && is_readable($file_path)) {
                    // Tambahkan file ke zip dengan struktur folder
                    if ($zip->addFile($file_path, $type . '/' . $row[$type])) {
                        $total_files++;
                        $added_files[] = $file_path;
                    }
                }
            }
        }
    }
}

// Tutup file zip
if (!$zip->close()) {
    die('Gagal menutup file ZIP');
}

if ($total_files > 0 && file_exists($zip_path)) {
    // Bersihkan output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }

    // Set header untuk download
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . $zip_filename . '"');
    header('Content-Length: ' . filesize($zip_path));
    header('Pragma: no-cache');
    header('Expires: 0');

    // Output file
    readfile($zip_path);

    // Hapus file zip setelah didownload
    @unlink($zip_path);
    exit;
} else {
    // Jika tidak ada file yang ditemukan
    $_SESSION['flash_message'] = "Tidak ada file yang ditemukan untuk tahun " . $year;
    $_SESSION['flash_type'] = "warning";
    header("Location: file-komite.php");
    exit;
}
