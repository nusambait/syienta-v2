<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Pastikan tidak ada output sebelumnya
ob_start();

require_once '../vendor/autoload.php';
include '../../config.php';
include '../config/config.php';

// Ambil parameter filter jika ada
$where = array();
if (isset($_GET['bulan']) && !empty($_GET['bulan'])) {
    $bulan = mysqli_real_escape_string($connect, $_GET['bulan']);
    $where[] = "MONTH(tgl_kunjungan) = '$bulan'";
}

if (isset($_GET['tahun']) && !empty($_GET['tahun'])) {
    $tahun = mysqli_real_escape_string($connect, $_GET['tahun']);
    $where[] = "YEAR(tgl_kunjungan) = '$tahun'";
}

if (isset($_GET['penginput']) && !empty($_GET['penginput'])) {
    $penginput = mysqli_real_escape_string($connect, $_GET['penginput']);
    $where[] = "nama_penginput = '$penginput'";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Query untuk mengambil data
$query = mysqli_query($connect, "SELECT * FROM tb_nasabah_prosfecting $where_clause ORDER BY id DESC");

$html = '<h2 style="text-align:center;">Data Prospekting Nasabah</h2>';
$html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%">';
$html .= '<thead><tr style="background:#eee; font-weight:bold; font-size:10pt;">';
$html .= '<th>No</th><th>No KTP</th><th>Nama Nasabah</th><th>Jenis Kelamin</th><th>Usia</th><th>Alamat</th><th>Desa</th><th>Kecamatan</th><th>Kabupaten</th><th>Kriteria Nasabah</th><th>Kriteria Prospek</th><th>Jenis Prospek</th><th>Tanggal Kunjungan</th><th>Hasil Kunjungan</th><th>Respon Kunjungan</th><th>Status Prospek</th><th>Jenis Usaha</th><th>Nama Penginput</th><th>Tanggal Input</th>';
$html .= '</tr></thead><tbody>';

$no = 1;
while ($row = mysqli_fetch_array($query)) {
    $html .= '<tr style="font-size:9pt;">';
    $html .= '<td>' . $no . '</td>';
    $html .= '<td>' . $row['no_ktp'] . '</td>';
    $html .= '<td>' . $row['nama_nasabah'] . '</td>';
    $html .= '<td>' . $row['jenis_kelamin'] . '</td>';
    $html .= '<td>' . $row['usia'] . '</td>';
    $html .= '<td>' . $row['alamat_nasabah'] . '</td>';
    $html .= '<td>' . $row['desa'] . '</td>';
    $html .= '<td>' . $row['kecamatan'] . '</td>';
    $html .= '<td>' . $row['kabupaten'] . '</td>';
    $html .= '<td>' . $row['kriteria_nasabah'] . '</td>';
    $html .= '<td>' . $row['kriteria_prospek'] . '</td>';
    $html .= '<td>' . $row['jenis_prospek'] . '</td>';
    $html .= '<td>' . date('d-m-Y', strtotime($row['tgl_kunjungan'])) . '</td>';
    $html .= '<td>' . $row['hasil_kunjungan'] . '</td>';
    $html .= '<td>' . $row['respon_kunjungan'] . '</td>';
    $html .= '<td>' . $row['status_prospek'] . '</td>';
    $html .= '<td>' . $row['jenis_usaha'] . '</td>';
    $html .= '<td>' . $row['nama_penginput'] . '</td>';
    $html .= '<td>' . date('d-m-Y', strtotime($row['tgl_input'])) . '</td>';
    $html .= '</tr>';
    $no++;
}
$html .= '</tbody>';
$html .= '<tfoot><tr><td colspan="19" style="text-align:center; font-style:italic;">Diekspor pada tanggal: ' . date('d-m-Y H:i:s') . '</td></tr></tfoot>';
$html .= '</table>';

// Output PDF
$mpdf = new \Mpdf\Mpdf(['orientation' => 'L', 'format' => 'A4']);
$mpdf->SetTitle('Data Prospekting Nasabah');
$mpdf->WriteHTML($html);
$mpdf->Output('Data_Prospekting_Nasabah_' . date('Y-m-d') . '.pdf', 'D');

ob_end_flush();
