<?php
session_start();
include '../../config.php';
include '../config/config.php';

if (!isset($_GET['id']) || !isset($_GET['bulan']) || !isset($_GET['tahun'])) {
    echo "<script>window.close();</script>";
    exit;
}

$id = mysqli_real_escape_string($connect, $_GET['id']);
$bulan = (int)$_GET['bulan'];
$tahun = (int)$_GET['tahun'];

// Ambil data karyawan
$query_karyawan = mysqli_query($connect, "SELECT * FROM ksu_karyawan WHERE id='$id'");
$karyawan = mysqli_fetch_assoc($query_karyawan);

// Ambil data gaji
$query_gaji = mysqli_query($connect, "SELECT *, CAST(bulan AS SIGNED) as bulan_int 
    FROM ksu_gaji_karyawan 
    WHERE id_karyawan='$id' AND bulan='$bulan' AND tahun='$tahun' LIMIT 1");
$gaji = mysqli_fetch_assoc($query_gaji);

// Tambahkan query untuk mengambil nama petugas di bagian atas file
$query_petugas = mysqli_query($connect, "SELECT nama FROM account WHERE jabatan='KSU' LIMIT 1");
$petugas = mysqli_fetch_assoc($query_petugas);

// Fungsi format rupiah
function formatRupiah($angka)
{
    return "Rp " . number_format($angka, 0, ',', '.');
}

// Fungsi get nama bulan
function getNamaBulan($bulan)
{
    $nama_bulan = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    ];
    return isset($nama_bulan[(int)$bulan]) ? $nama_bulan[(int)$bulan] : '';
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Slip Gaji - <?php echo $karyawan['nama']; ?></title>
    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 7pt;
        line-height: 1.2;
        margin: 0;
        padding: 15px;
        background: #fff;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #1a5fb4;
        padding-bottom: 10px;
        position: relative;
    }

    .logo {
        position: absolute;
        left: 0;
        top: 0;
        width: 110px;
        height: auto;
    }

    .header h2 {
        font-size: 12pt;
        margin: 0 0 5px 0;
        color: #1a5fb4;
        font-weight: bold;
    }

    .header p {
        font-size: 9pt;
        margin: 0;
        color: #666;
    }

    .company-name {
        font-size: 14pt;
        font-weight: bold;
        color: #1a5fb4;
        margin: 0 0 5px 0;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }

    th,
    td {
        border: 0.5px solid #ddd;
        padding: 4px 6px;
    }

    th {
        text-align: left;
        background-color: #f8f9fa;
        color: #333;
    }

    th[colspan="2"] {
        text-align: center;
        background-color: #1a5fb4;
        color: white;
        font-size: 8pt;
        padding: 6px;
    }

    td.text-right {
        text-align: right;
    }

    .table-success {
        background-color: #d4edda;
    }

    .table-danger {
        background-color: #f8d7da;
    }

    .table-primary {
        background-color: #cce5ff;
    }

    .bg-light {
        background-color: #eef2f7;
    }

    .footer {
        margin-top: 20px;
        text-align: right;
        font-size: 6pt;
        color: #666;
        border-top: 1px solid #ddd;
        padding-top: 10px;
    }

    .info-table {
        margin-bottom: 20px;
    }

    .info-table th {
        width: 30%;
        background-color: #f8f9fa;
    }

    .info-table td {
        background-color: white;
    }

    strong {
        color: #1a5fb4;
    }

    @media print {
        @page {
            size: A4;
            margin: 0.5cm;
        }

        body {
            padding: 0;
        }

        .table-success {
            background-color: #d4edda !important;
        }

        .table-danger {
            background-color: #f8d7da !important;
        }

        .table-primary {
            background-color: #cce5ff !important;
        }

        .bg-light {
            background-color: #eef2f7 !important;
        }

        th[colspan="2"] {
            background-color: #1a5fb4 !important;
            color: white !important;


            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }

    .table-ttd {
        width: 100%;
        margin-top: 30px;
    }

    .table-ttd td {
        vertical-align: top;
        padding: 5px;
    }

    .table-ttd p {
        font-size: 8pt;
        line-height: 1.2;
    }

    .table-ttd strong {
        font-size: 8pt;
        color: #000;
        text-decoration: underline;
    }
    </style>
</head>

<body>
    <div class="header">
        <img src="../assets/logo-blue.png" alt="Logo Perusahaan" class="logo">
        <div class="company-name">PT. BPR NUSAMBA TANJUNGSARI</div>
        <h2>Slip Gaji Karyawan <?php echo $karyawan['nama']; ?></h2>
        <p>Periode: <?php echo getNamaBulan($bulan) . ' ' . $tahun; ?></p>
    </div>

    <table class="info-table">
        <tr>
            <th width="30%">Nama Karyawan</th>
            <td><?php echo $karyawan['nama']; ?></td>
        </tr>
        <tr>
            <th>NIK</th>
            <td><?php echo $karyawan['nik']; ?></td>
        </tr>
        <tr>
            <th>Jabatan</th>
            <td><?php echo $karyawan['jabatan']; ?></td>
        </tr>
    </table>

    <table>
        <tr>
            <th colspan="2" class="text-center">RINCIAN GAJI</th>
        </tr>
        <tr>
            <th>Gaji Pokok</th>
            <td class="text-right"><?php echo formatRupiah($gaji['gaji_pokok']); ?></td>
        </tr>
        <tr class="bg-light">
            <th colspan="2" class="text-center">TUNJANGAN</th>
        </tr>
        <tr>
            <th>Tunjangan Jabatan</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_jabatan']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan Makan</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_makan']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan Transport</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_transport']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan Pulsa</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_pulsa']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan Cuti</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_cuti']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan THR</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_thr']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan Lembur</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_lembur']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan Lainnya</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_lainnya']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan BPJS JHT</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_bpjs_jht']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan BPJS Pensiun</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_bpjs_pensiun']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan BPJS Kesehatan</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_bpjs_kesehatan']); ?></td>
        </tr>
        <tr>
            <th>Tunjangan DPLK</th>
            <td class="text-right"><?php echo formatRupiah($gaji['tunj_dplk']); ?></td>
        </tr>
        <tr class="table-success">
            <th>Jumlah Penghasilan</th>
            <td class="text-right"><strong><?php echo formatRupiah($gaji['jumlah_gaji']); ?></strong></td>
        </tr>
        <tr class="bg-light">
            <th colspan="2" class="text-center">POTONGAN</th>
        </tr>
        <tr>
            <th>Potongan BPJS JHT</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_bpjs_jht']); ?></td>
        </tr>
        <tr>
            <th>Potongan BPJS Pensiun</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_bpjs_pensiun']); ?></td>
        </tr>
        <tr>
            <th>Potongan BPJS Kesehatan</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_bpjs_kesehatan']); ?></td>
        </tr>
        <tr>
            <th>Potongan DPLK</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_dplk']); ?></td>
        </tr>
        <tr>
            <th>Potongan Pinjaman</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_pinjaman']); ?></td>
        </tr>
        <tr>
            <th>Potongan Simpanan Pokok</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_simpanan_pokok']); ?></td>
        </tr>
        <tr>
            <th>Potongan Simpanan Wajib</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_simpanan_wajib']); ?></td>
        </tr>
        <tr>
            <th>Potongan Simpanan Sukarela</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_simpanan_sukarela']); ?></td>
        </tr>
        <tr>
            <th>Potongan Iuran Piknik</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_iuran_piknik']); ?></td>
        </tr>
        <tr>
            <th>Potongan Pinjaman Koperasi</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_pinjaman_koperasi']); ?></td>
        </tr>
        <tr>
            <th>Potongan Bon Barang</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_bon_barang']); ?></td>
        </tr>
        <tr>
            <th>Potongan Koperasi Lainnya</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_koperasi_lainnya']); ?></td>
        </tr>
        <tr>
            <th>Potongan MHB</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_lainnya_ang_mhb']); ?></td>
        </tr>
        <tr>
            <th>Potongan Arisan</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_lainnya_arisan']); ?></td>
        </tr>
        <tr>
            <th>Potongan Platinum Pulsa</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_lainnya_platinum_pulsa']); ?></td>
        </tr>
        <tr>
            <th>Potongan Lainnya</th>
            <td class="text-right"><?php echo formatRupiah($gaji['potongan_lainnya']); ?></td>
        </tr>
        <tr>
            <th>Total Potongan</th>
            <td class="text-right"><strong><?php echo formatRupiah($gaji['total_potongan']); ?></strong></td>
        </tr>
        <tr class="table-primary">
            <th>Total Gaji Bersih</th>
            <td class="text-right"><strong><?php echo formatRupiah($gaji['total_gaji_bersih']); ?></strong></td>
        </tr>
    </table>

    <div class="footer">
        <table class="table-ttd" style="border: none; margin-top: 0px;">
            <tr>
                <td style="width: 65%; border: none;"></td>
                <td style="width: 35%; border: none; text-align: center;">
                    <p style="margin-bottom: 50px;">Petugas Gaji</p>
                    <p style="margin: 0;"><strong><?php echo $petugas['nama']; ?></strong></p>
                    <p style="margin: 0;">Kabid SDM & Umum</p>
                </td>
            </tr>
        </table>
        <p style="text-align: left; margin-top: 10px; font-size: 6pt; color: #666;">
            Dicetak pada: <?php echo date('d/m/Y H:i:s'); ?>
        </p>
    </div>

    <script>
    window.onload = function() {
        window.print();
    }
    </script>
</body>

</html>