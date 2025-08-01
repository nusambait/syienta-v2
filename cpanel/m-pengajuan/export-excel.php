<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

// Inisialisasi variabel pencarian dan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Membuat nama file yang deskriptif
$filename = "Data_Pengajuan";
if (!empty($status)) {
    $filename = $status;
}
if (!empty($year)) {
    $filename .= "_" . $year;
    if (!empty($month)) {
        // Konversi nomor bulan ke nama bulan dalam bahasa Indonesia
        $bulan = [
            '01' => 'Januari',
            '02' => 'Februari',
            '03' => 'Maret',
            '04' => 'April',
            '05' => 'Mei',
            '06' => 'Juni',
            '07' => 'Juli',
            '08' => 'Agustus',
            '09' => 'September',
            '10' => 'Oktober',
            '11' => 'November',
            '12' => 'Desember'
        ];
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $filename .= "_" . $bulan[$month];
    } else {
        $filename .= "_Semua_Bulan";
    }
} else {
    $filename .= "_Semua_Tahun";
}

// Set header untuk download file Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="' . $filename . '.xls"');

// Query untuk data
$query = "SELECT 
            p.*,
            a.nama as nama_ao,
            n.nama as nama_nasabah
          FROM pengajuan p 
          INNER JOIN account a ON p.ao = a.kd_ao 
          LEFT JOIN nasabah n ON p.niknas = n.nik 
          WHERE 1=1";

if (!empty($search)) {
    $search = mysqli_real_escape_string($connect, $search);
    $query .= " AND (p.noreg LIKE '%$search%' OR n.nama LIKE '%$search%')";
}

if (!empty($status)) {
    $status = mysqli_real_escape_string($connect, $status);
    $query .= " AND p.status LIKE '$status%'";
}

if (!empty($year)) {
    $query .= " AND p.tglpeng LIKE '%-$year'";

    if (!empty($month)) {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $query .= " AND p.tglpeng LIKE '%-$month-%'";
    }
}

$query .= " ORDER BY p.noreg DESC";

// Debug query (uncomment jika perlu)
// echo $query; exit;

// Eksekusi query
$result = mysqli_query($connect, $query);
if (!$result) {
    die("Error dalam query: " . mysqli_error($connect));
}
?>
<table border="1">
    <thead>
        <tr style="background-color: #f0f0f0;">
            <th>No. Registrasi</th>
            <th>NIK Nasabah</th>
            <th>No. Rekening</th>
            <th>Tanggal Pengajuan</th>
            <th>Pengajuan</th>
            <th>Jenis Kredit</th>
            <th>Penggunaan</th>
            <th>Produk Kredit</th>
            <th>Plafond</th>
            <th>Jangka Waktu</th>
            <th>Suku Bunga</th>
            <th>Angsuran Pokok</th>
            <th>Angsuran Bunga</th>
            <th>Total Angsuran</th>
            <th>Biaya Provisi</th>
            <th>Nominal Provisi</th>
            <th>Administrasi</th>
            <th>Tujuan Penggunaan</th>
            <th>Jaminan</th>
            <th>AO</th>
            <th>Agen</th>
            <th>Status</th>
            <th>Keterangan</th>
            <th>Sumber</th>
            <th>FO</th>
            <th>Update At</th>
        </tr>
    </thead>
    <tbody>
        <?php
        if (mysqli_num_rows($result) > 0):
            while ($row = mysqli_fetch_assoc($result)):
                // Format angka untuk nilai moneter
                $plafond = number_format($row['plaf'] ?? 0, 0, ',', '.');
                $angpok = number_format($row['angpok'] ?? 0, 0, ',', '.');
                $angbung = number_format($row['angbung'] ?? 0, 0, ',', '.');
                $totang = number_format($row['totang'] ?? 0, 0, ',', '.');
                $biaprov = number_format($row['biaprov'] ?? 0, 0, ',', '.');
                $nomprov = number_format($row['nomprov'] ?? 0, 0, ',', '.');
                $adm = number_format($row['adm'] ?? 0, 0, ',', '.');
        ?>
                <tr>
                    <td><?php echo $row['noreg'] ?? ''; ?></td>
                    <td><?php echo $row['niknas'] ?? ''; ?></td>
                    <td><?php echo $row['norek'] ?? ''; ?></td>
                    <td style="mso-number-format:'\@'"><?php echo $row['tglpeng'] ?? ''; ?></td>
                    <td><?php echo $row['pengajuan'] ?? ''; ?></td>
                    <td><?php echo $row['jns_kredit'] ?? ''; ?></td>
                    <td><?php echo $row['penggunaan'] ?? ''; ?></td>
                    <td><?php echo $row['prodkre'] ?? ''; ?></td>
                    <td style="mso-number-format:'#,##0'"><?php echo $plafond; ?></td>
                    <td><?php echo $row['jw'] ?? ''; ?></td>
                    <td><?php echo $row['sukbung'] ?? ''; ?></td>
                    <td style="mso-number-format:'#,##0'"><?php echo $angpok; ?></td>
                    <td style="mso-number-format:'#,##0'"><?php echo $angbung; ?></td>
                    <td style="mso-number-format:'#,##0'"><?php echo $totang; ?></td>
                    <td style="mso-number-format:'#,##0'"><?php echo $biaprov; ?></td>
                    <td style="mso-number-format:'#,##0'"><?php echo $nomprov; ?></td>
                    <td style="mso-number-format:'#,##0'"><?php echo $adm; ?></td>
                    <td><?php echo $row['tujpeng'] ?? ''; ?></td>
                    <td><?php echo $row['jaminan'] ?? ''; ?></td>
                    <td><?php echo $row['nama_ao'] ?? ''; ?></td>
                    <td><?php echo $row['agen'] ?? ''; ?></td>
                    <td><?php echo strtoupper($row['status'] ?? ''); ?></td>
                    <td><?php echo $row['ket_peng'] ?? ''; ?></td>
                    <td><?php echo $row['sumber'] ?? ''; ?></td>
                    <td><?php echo $row['FO'] ?? ''; ?></td>
                    <td style="mso-number-format:'\@'"><?php echo $row['update_at'] ?? ''; ?></td>
                </tr>
            <?php
            endwhile;
        else:
            ?>
            <tr>
                <td colspan="26" style="text-align: center;">Tidak ada data yang ditemukan</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>