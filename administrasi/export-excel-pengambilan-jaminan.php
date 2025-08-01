<?php
// Pastikan tidak ada output sebelumnya
ob_start();

include '../../config.php';
include '../config/config.php';

// Set header untuk file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=Data_Pengambilan_Jaminan.xls");

// Query untuk mengambil data
$query = mysqli_query($connect, "SELECT * FROM pengambilan ORDER BY id DESC");

// Buat output HTML yang akan dikonversi menjadi Excel
?>
<table border="1">
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-size: 14pt;">
                Data Pengambilan Jaminan
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Tanggal Pengambilan</th>
            <th>No. Pinjaman</th>
            <th>Nama Peminjam</th>
            <th>Jaminan</th>
            <th>Nama Pengambil</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_array($query)) {
            echo "<tr>
                <td>$no</td>
                <td>" . $row['tgl_pengambilan'] . "</td>
                <td>" . $row['loan'] . "</td>
                <td>" . $row['nm_peminjam'] . "</td>
                <td>" . $row['jaminan'] . "</td>
                <td>" . $row['nm_pengambil'] . "</td>
            </tr>";
            $no++;
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6" style="text-align: center; font-style: italic;">
                Diekspor pada tanggal: <?php echo date('d-m-Y H:i:s'); ?>
            </td>
        </tr>
    </tfoot>
</table>