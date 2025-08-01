<?php
// Pastikan tidak ada output sebelumnya
ob_start();

require_once '../vendor/autoload.php';
include '../../config.php';
include '../config/config.php';

// Ambil parameter filter jika ada
$where = array();
if (isset($_GET['bulan']) && !empty($_GET['bulan'])) {
    $bulan = mysqli_real_escape_string($connect, $_GET['bulan']);
    $where[] = "MONTH(tanggal_kunjungan) = '$bulan'";
}

if (isset($_GET['tahun']) && !empty($_GET['tahun'])) {
    $tahun = mysqli_real_escape_string($connect, $_GET['tahun']);
    $where[] = "YEAR(tanggal_kunjungan) = '$tahun'";
}

if (isset($_GET['penginput']) && !empty($_GET['penginput'])) {
    $penginput = mysqli_real_escape_string($connect, $_GET['penginput']);
    $where[] = "nama_penginput = '$penginput'";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Query untuk mengambil data
$query = mysqli_query($connect, "SELECT * FROM tb_nasabah $where_clause ORDER BY id DESC");

$html = '<h2 style="text-align:center;">Data Kunjungan Nasabah</h2>';
$html .= '<table border="1" cellpadding="4" cellspacing="0" width="100%">';
$html .= '<thead><tr style="background:#eee; font-weight:bold; font-size:10pt;">';
$html .= '<th>No</th><th>Nomor Loan</th><th>Nama Nasabah</th><th>Tanggal Kunjungan</th><th>Kunjungan Selanjutnya</th><th>Komitmen Nasabah</th><th>Baki Debet</th><th>Tunggakan Pokok</th><th>Tunggakan Bunga</th><th>Kolektabilitas</th><th>Keterangan</th><th>Alamat</th><th>Respon</th><th>Status</th><th>Nomor Telepon</th><th>Nama Penginput</th><th>Tanggal Input</th><th>Kriteria Monitoring</th><th>Tanggal Drop</th><th>Plafond</th><th>Penggunaan Proposal</th><th>Realisasi Penggunaan</th><th>Jenis Jaminan</th><th>Kepemilikan Jaminan</th><th>Kesimpulan Pascadroping</th><th>Kode Kantor</th>';
$html .= '</tr></thead><tbody>';

$no = 1;
while ($row = mysqli_fetch_array($query)) {
    $baki_debat = (!is_null($row['baki_debat']) && $row['baki_debat'] !== '') ? number_format((float)$row['baki_debat'], 0, ',', '.') : '0';
    $tunggakan_pokok = (!is_null($row['tunggakan_pokok']) && $row['tunggakan_pokok'] !== '') ? number_format((float)$row['tunggakan_pokok'], 0, ',', '.') : '0';
    $tunggakan_bunga = (!is_null($row['tunggakan_bunga']) && $row['tunggakan_bunga'] !== '') ? number_format((float)$row['tunggakan_bunga'], 0, ',', '.') : '0';
    $plafond = (!is_null($row['plafond']) && $row['plafond'] !== '') ? number_format((float)$row['plafond'], 0, ',', '.') : '0';

    $html .= '<tr style="font-size:9pt;">';
    $html .= '<td>' . $no . '</td>';
    $html .= '<td>' . $row['nomor_loan'] . '</td>';
    $html .= '<td>' . $row['nama_nasabah'] . '</td>';
    $html .= '<td>' . date('d-m-Y', strtotime($row['tanggal_kunjungan'])) . '</td>';
    $html .= '<td>' . date('d-m-Y', strtotime($row['kunjungan_selanjutnya'])) . '</td>';
    $html .= '<td>' . $row['komitmen_nasabah'] . '</td>';
    $html .= '<td>Rp ' . $baki_debat . '</td>';
    $html .= '<td>Rp ' . $tunggakan_pokok . '</td>';
    $html .= '<td>Rp ' . $tunggakan_bunga . '</td>';
    $html .= '<td>' . $row['kolektabilitas'] . '</td>';
    $html .= '<td>' . $row['keterangan'] . '</td>';
    $html .= '<td>' . $row['alamat'] . '</td>';
    $html .= '<td>' . $row['respon'] . '</td>';
    $html .= '<td>' . $row['status'] . '</td>';
    $html .= '<td>' . $row['nomor_telepon'] . '</td>';
    $html .= '<td>' . $row['nama_penginput'] . '</td>';
    $html .= '<td>' . date('d-m-Y', strtotime($row['tgl_input'])) . '</td>';
    $html .= '<td>' . $row['kriteria_monitoring'] . '</td>';
    $html .= '<td>' . ($row['tgl_drop'] ? date('d-m-Y', strtotime($row['tgl_drop'])) : '') . '</td>';
    $html .= '<td>Rp ' . $plafond . '</td>';
    $html .= '<td>' . $row['penggunaan_proposal'] . '</td>';
    $html .= '<td>' . $row['realisasi_penggunaan'] . '</td>';
    $html .= '<td>' . $row['jenis_jaminan'] . '</td>';
    $html .= '<td>' . $row['kepemilikan_jaminan'] . '</td>';
    $html .= '<td>' . $row['kesimpulan_pascadroping'] . '</td>';
    $html .= '<td>' . $row['kd_kantor'] . '</td>';
    $html .= '</tr>';
    $no++;
}
$html .= '</tbody>';
$html .= '<tfoot><tr><td colspan="26" style="text-align:center; font-style:italic;">Diekspor pada tanggal: ' . date('d-m-Y H:i:s') . '</td></tr></tfoot>';
$html .= '</table>';

// Output PDF
$mpdf = new \Mpdf\Mpdf(['orientation' => 'L', 'format' => 'A4']);
$mpdf->SetTitle('Data Kunjungan Nasabah');
$mpdf->WriteHTML($html);
$mpdf->Output('Data_Kunjungan_Nasabah_' . date('Y-m-d') . '.pdf', 'D');

ob_end_flush();
