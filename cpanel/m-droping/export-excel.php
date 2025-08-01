<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

// Set header untuk download file Excel
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Data_Dropping_" . date('Y-m-d') . ".xls");

// Ambil parameter filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Query untuk mengambil data
$query = "SELECT d.noreg, d.nobwmk, d.nospp, d.nosurat, d.nostpk, d.bln_rmwi,
          d.nocif, d.noloan, d.tgl_acc_direksi, d.tgl_droping, d.jam_droping,
          d.tgl_peng_ao, d.penggunaan, d.plafond, d.tplafond, d.sukbung, d.tsukbung,
          d.jw, d.tjw, d.angpok, d.tangpok, d.angbung, d.tangbung, d.totang, d.ttotang,
          d.prov, d.tprov, d.nomprov, d.tnomprov, d.adm, d.tadm, d.asjw, d.tasjw,
          d.jns_asjw, d.asken, d.tasken, d.totasuransi, d.ttotasuransi, d.notaris,
          d.tnotaris, d.materai, d.tmaterai, d.total, d.ttotal, d.peng_kredit,
          d.nilpenj, d.tnilpenj, d.ketbwmk, d.status, d.metode, d.cara,
          a.nama as nama_ao, n.nama as nama_nasabah
          FROM droping d 
          INNER JOIN pengajuan p ON d.noreg = p.noreg
          INNER JOIN account a ON p.ao = a.kd_ao 
          LEFT JOIN nasabah n ON p.niknas = n.nik 
          WHERE 1=1";

if (!empty($search)) {
    $search = mysqli_real_escape_string($connect, $search);
    $query .= " AND (p.noreg LIKE '%$search%' OR n.nama LIKE '%$search%')";
}

if (!empty($status)) {
    $status = mysqli_real_escape_string($connect, $status);
    $query .= " AND d.status LIKE '$status%'";
}

if (!empty($year)) {
    $year = mysqli_real_escape_string($connect, $year);
    $query .= " AND d.tgl_droping LIKE '%-$year'";

    if (!empty($month)) {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $query .= " AND d.tgl_droping LIKE '%-$month-%'";
    }
}

$query .= " ORDER BY d.noreg DESC";

$result = mysqli_query($connect, $query);
?>

<html>

<head>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            vertical-align: middle;
            white-space: normal;
            word-wrap: break-word;
            padding: 5px;
            font-size: 11px;
        }

        td {
            padding: 4px;
            font-size: 10px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 200px;
        }

        .number-cell {
            text-align: right;
        }

        .text-cell {
            text-align: left;
        }

        .center-cell {
            text-align: center;
        }
    </style>
</head>

<body>
    <table>
        <thead>
            <tr>
                <th width="100">No. Registrasi</th>
                <th width="100">No. BWM</th>
                <th width="100">No. SPP</th>
                <th width="100">No. Surat</th>
                <th width="100">No. STPK</th>
                <th width="80">Bulan RMWI</th>
                <th width="100">No. CIF</th>
                <th width="100">No. Loan</th>
                <th width="100">Tanggal ACC</th>
                <th width="100">Tanggal Drop</th>
                <th width="80">Jam Drop</th>
                <th width="100">Tgl Pengajuan</th>
                <th width="150">Penggunaan</th>
                <th width="100">Plafond</th>
                <th width="150">Terbilang</th>
                <th width="80">Suku Bunga</th>
                <th width="150">Terbilang</th>
                <th width="80">JW</th>
                <th width="150">Terbilang</th>
                <th width="100">Angsuran Pokok</th>
                <th width="150">Terbilang</th>
                <th width="100">Angsuran Bunga</th>
                <th width="150">Terbilang</th>
                <th width="100">Total Angsuran</th>
                <th width="150">Terbilang</th>
                <th width="80">Provisi</th>
                <th width="150">Terbilang</th>
                <th width="100">Nominal Provisi</th>
                <th width="150">Terbilang</th>
                <th width="100">Administrasi</th>
                <th width="150">Terbilang</th>
                <th width="100">Asuransi Jiwa</th>
                <th width="150">Terbilang</th>
                <th width="150">Jenis Asuransi</th>
                <th width="100">Asuransi Kebakaran</th>
                <th width="150">Terbilang</th>
                <th width="100">Total Asuransi</th>
                <th width="150">Terbilang</th>
                <th width="100">Notaris</th>
                <th width="150">Terbilang</th>
                <th width="100">Materai</th>
                <th width="150">Terbilang</th>
                <th width="100">Total</th>
                <th width="150">Terbilang</th>
                <th width="150">Penggunaan Kredit</th>
                <th width="100">Nilai Penjaminan</th>
                <th width="150">Terbilang</th>
                <th width="150">Keterangan BWM</th>
                <th width="100">Status</th>
                <th width="100">Metode</th>
                <th width="100">Cara</th>
                <th width="150">Nama AO</th>
                <th width="150">Nama Nasabah</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['noreg'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['nobwmk'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['nospp'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['nosurat'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['nostpk'] ?? ''; ?></td>
                    <td class="center-cell"><?php echo $row['bln_rmwi'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['nocif'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['noloan'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['tgl_acc_direksi'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['tgl_droping'] ?? ''; ?></td>
                    <td class="center-cell"><?php echo $row['jam_droping'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'\@'"><?php echo $row['tgl_peng_ao'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['penggunaan'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['plafond'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tplafond'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'0.00'"><?php echo $row['sukbung'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['tsukbung'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'0'"><?php echo $row['jw'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['tjw'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['angpok'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tangpok'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['angbung'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tangbung'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['totang'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['ttotang'] ?? ''; ?></td>
                    <td class="center-cell" style="mso-number-format:'0.00'"><?php echo $row['prov'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['tprov'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['nomprov'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tnomprov'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['adm'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tadm'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['asjw'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tasjw'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['jns_asjw'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['asken'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tasken'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['totasuransi'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['ttotasuransi'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['notaris'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tnotaris'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['materai'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tmaterai'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['total'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['ttotal'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['peng_kredit'] ?? ''; ?></td>
                    <td class="number-cell" style="mso-number-format:'#,##0'"><?php echo $row['nilpenj'] ?? 0; ?></td>
                    <td class="text-cell"><?php echo $row['tnilpenj'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['ketbwmk'] ?? ''; ?></td>
                    <td class="center-cell"><?php echo strtoupper($row['status'] ?? ''); ?></td>
                    <td class="center-cell"><?php echo $row['metode'] ?? ''; ?></td>
                    <td class="center-cell"><?php echo $row['cara'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['nama_ao'] ?? ''; ?></td>
                    <td class="text-cell"><?php echo $row['nama_nasabah'] ?? ''; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>

</html>