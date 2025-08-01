<?php
// Pastikan tidak ada output sebelumnya
ob_start();

include '../../config.php';
include '../config/config.php';

// Set header untuk file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Prospekting_Nasabah_" . date('Y-m-d') . ".xls");

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
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="19" style="text-align: center; font-size: 14pt;">
                Data Prospekting Nasabah
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>No KTP</th>
            <th>Nama Nasabah</th>
            <th>Jenis Kelamin</th>
            <th>Usia</th>
            <th>Alamat</th>
            <th>Desa</th>
            <th>Kecamatan</th>
            <th>Kabupaten</th>
            <th>Kriteria Nasabah</th>
            <th>Kriteria Prospek</th>
            <th>Jenis Prospek</th>
            <th>Tanggal Kunjungan</th>
            <th>Hasil Kunjungan</th>
            <th>Respon Kunjungan</th>
            <th>Status Prospek</th>
            <th>Jenis Usaha</th>
            <th>Nama Penginput</th>
            <th>Tanggal Input</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_array($query)) {
            echo "<tr>
                <td>$no</td>
                <td>{$row['no_ktp']}</td>
                <td>{$row['nama_nasabah']}</td>
                <td>{$row['jenis_kelamin']}</td>
                <td>{$row['usia']}</td>
                <td>{$row['alamat_nasabah']}</td>
                <td>{$row['desa']}</td>
                <td>{$row['kecamatan']}</td>
                <td>{$row['kabupaten']}</td>
                <td>{$row['kriteria_nasabah']}</td>
                <td>{$row['kriteria_prospek']}</td>
                <td>{$row['jenis_prospek']}</td>
                <td>" . date('d-m-Y', strtotime($row['tgl_kunjungan'])) . "</td>
                <td>{$row['hasil_kunjungan']}</td>
                <td>{$row['respon_kunjungan']}</td>
                <td>{$row['status_prospek']}</td>
                <td>{$row['jenis_usaha']}</td>
                <td>{$row['nama_penginput']}</td>
                <td>" . date('d-m-Y', strtotime($row['tgl_input'])) . "</td>
            </tr>";
            $no++;
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="19" style="text-align: center; font-style: italic;">
                Diekspor pada tanggal: <?php echo date('d-m-Y H:i:s'); ?>
            </td>
        </tr>
    </tfoot>
</table>
<?php
// Pastikan tidak ada output lain
ob_end_flush();
?>