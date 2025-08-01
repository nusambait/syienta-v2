<?php
// Pastikan tidak ada output sebelumnya
ob_start();

include '../../config.php';
include '../config/config.php';

// Set header untuk file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Kunjungan_Nasabah_" . date('Y-m-d') . ".xls");

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
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="16" style="text-align: center; font-size: 14pt;">
                Data Kunjungan Nasabah
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Nomor Loan</th>
            <th>Nama Nasabah</th>
            <th>Tanggal Kunjungan</th>
            <th>Kunjungan Selanjutnya</th>
            <th>Komitmen Nasabah</th>
            <th>Respon</th>
            <th>Baki Debet</th>
            <th>Tunggakan Pokok</th>
            <th>Tunggakan Bunga</th>
            <th>Plafond</th>
            <th>Kolektabilitas</th>
            <th>Status</th>
            <th>Alamat</th>
            <th>Nama Penginput</th>
            <th>Tanggal Input</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_array($query)) {
            // Format angka ke format rupiah dengan pengecekan null dan string kosong
            $baki_debat = (!is_null($row['baki_debat']) && $row['baki_debat'] !== '') ? number_format((float)$row['baki_debat'], 0, ',', '.') : '0';
            $tunggakan_pokok = (!is_null($row['tunggakan_pokok']) && $row['tunggakan_pokok'] !== '') ? number_format((float)$row['tunggakan_pokok'], 0, ',', '.') : '0';
            $tunggakan_bunga = (!is_null($row['tunggakan_bunga']) && $row['tunggakan_bunga'] !== '') ? number_format((float)$row['tunggakan_bunga'], 0, ',', '.') : '0';
            $plafond = (!is_null($row['plafond']) && $row['plafond'] !== '') ? number_format((float)$row['plafond'], 0, ',', '.') : '0';

            echo "<tr>
                <td>$no</td>
                <td>{$row['nomor_loan']}</td>
                <td>{$row['nama_nasabah']}</td>
                <td>" . date('d-m-Y', strtotime($row['tanggal_kunjungan'])) . "</td>
                <td>" . date('d-m-Y', strtotime($row['kunjungan_selanjutnya'])) . "</td>
                <td>{$row['komitmen_nasabah']}</td>
                <td>{$row['respon']}</td>
                <td>Rp {$baki_debat}</td>
                <td>Rp {$tunggakan_pokok}</td>
                <td>Rp {$tunggakan_bunga}</td>
                <td>Rp {$plafond}</td>
                <td>{$row['kolektabilitas']}</td>
                <td>{$row['status']}</td>
                <td>{$row['alamat']}</td>
                <td>{$row['nama_penginput']}</td>
                <td>" . date('d-m-Y', strtotime($row['tgl_input'])) . "</td>
            </tr>";
            $no++;
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="16" style="text-align: center; font-style: italic;">
                Diekspor pada tanggal: <?php echo date('d-m-Y H:i:s'); ?>
            </td>
        </tr>
    </tfoot>
</table>
<?php
// Pastikan tidak ada output lain
ob_end_flush();
?>