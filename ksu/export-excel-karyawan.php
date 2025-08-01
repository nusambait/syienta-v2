<?php
// Pastikan tidak ada output sebelumnya
ob_start();

include '../../config.php';
include '../config/config.php';

// Ambil parameter filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
$where = [];

if (!empty($search)) {
    $where[] = "(nama LIKE '%$search%' OR nik LIKE '%$search%')";
}

if (isset($_GET['kantor']) && !empty($_GET['kantor'])) {
    $kantor = mysqli_real_escape_string($connect, $_GET['kantor']);
    $where[] = "kantor = '$kantor'";
}

if (isset($_GET['masa_kerja']) && !empty($_GET['masa_kerja'])) {
    $masa_kerja_tahun = (int)$_GET['masa_kerja'];
    $where[] = "TIMESTAMPDIFF(YEAR, tgl_masuk, CURDATE()) > $masa_kerja_tahun";
}

if (isset($_GET['milestone']) && $_GET['milestone'] == '1') {
    $milestone_years = implode(',', [5, 10, 15, 20, 25, 30, 35]);
    $where[] = "TIMESTAMPDIFF(YEAR, tgl_masuk, CURDATE()) IN ($milestone_years)";
    $where[] = "TIMESTAMPDIFF(MONTH, tgl_masuk, CURDATE()) % 12 < 12";
}

$where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

// Set nama file berdasarkan filter yang aktif
$filename = "Data_Karyawan";
if (isset($_GET['milestone']) && $_GET['milestone'] == '1') {
    $filename .= "_Milestone";
}
if (isset($_GET['kantor']) && !empty($_GET['kantor'])) {
    $filename .= "_" . str_replace(' ', '_', $_GET['kantor']);
}
if (isset($_GET['masa_kerja']) && !empty($_GET['masa_kerja'])) {
    $filename .= "_DiAtas_" . $_GET['masa_kerja'] . "_Tahun";
}
$filename .= "_" . date('Y-m-d') . ".xls";

// Set header untuk file Excel
header("Content-type: application/vnd-ms-excel");
header("Content-Disposition: attachment; filename=" . $filename);

// Query untuk mengambil data
$query = mysqli_query($connect, "SELECT * FROM ksu_karyawan $where_clause ORDER BY id ASC");
?>

<table border="1">
    <thead>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 14pt;">
                Data Karyawan KSU
                <?php
                if (isset($_GET['milestone']) && $_GET['milestone'] == '1') {
                    echo " - Milestone";
                }
                if (isset($_GET['kantor']) && !empty($_GET['kantor'])) {
                    echo " - Kantor " . $_GET['kantor'];
                }
                if (isset($_GET['masa_kerja']) && !empty($_GET['masa_kerja'])) {
                    echo " - Di Atas " . $_GET['masa_kerja'] . " Tahun";
                }
                ?>
            </th>
        </tr>
        <tr>
            <th colspan="10" style="text-align: center; font-size: 10pt;">
                Export: <?php echo date('Y-m-d H:i:s'); ?>
            </th>
        </tr>
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>NIK</th>
            <th>Jenis Kelamin</th>
            <th>Tanggal Lahir</th>
            <th>Jabatan</th>
            <th>Kantor</th>
            <th>Pendidikan</th>
            <th>Masa Kerja</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php
        $no = 1;
        while ($row = mysqli_fetch_array($query)) {
            echo "<tr>
                <td>$no</td>
                <td>" . $row['nama'] . "</td>
                <td>" . $row['nik'] . "</td>
                <td>" . $row['jk'] . "</td>
                <td>" . $row['tgl_lahir'] . "</td>
                <td>" . $row['jabatan'] . "</td>
                <td>" . $row['kantor'] . "</td>
                <td>" . $row['pendidikan'] . "</td>
                <td>" . $row['masa_kerja'] . "</td>
                <td>" . $row['status'] . "</td>
            </tr>";
            $no++;
        }
        ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="10" style="text-align: center; font-style: italic;">
                Total Data: <?php echo mysqli_num_rows($query); ?> Karyawan
            </td>
        </tr>
    </tfoot>
</table>

<?php
// Pastikan tidak ada output lain
ob_end_flush();
?>