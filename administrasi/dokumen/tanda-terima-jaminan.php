<?php
session_start();
require_once __DIR__ . '/../../vendor/autoload.php';
include '../../../config.php';

try {
    // Set error reporting
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // Gunakan direktori temporary sistem
    $tempDir = sys_get_temp_dir();

    // Inisialisasi mPDF dengan konfigurasi tempDir
    $mpdf = new \Mpdf\Mpdf([
        'margin_left' => 20,
        'margin_right' => 20,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'format' => 'A4',
        'orientation' => 'P',
        'debug' => true,
        'tempDir' => $tempDir
    ]);

    // Tambahkan query untuk mengambil data nasabah, pengajuan, dan droping
    $noreg = $_GET['noreg'];
    $query = "SELECT n.*, p.*, d.*
             FROM nasabah n 
             JOIN pengajuan p ON n.nik = p.niknas
             JOIN droping d ON p.noreg = d.noreg
             WHERE d.noreg = '$noreg'";
    $result = mysqli_query($connect, $query);
    $data = mysqli_fetch_assoc($result);

    // Tambahkan pengecekan session dan gunakan kd_kantor
    if (!isset($_SESSION['username']) || !isset($_SESSION['kantor'])) {
        die("Anda harus login terlebih dahulu");
    }

    $kd_kantor = $_SESSION['kantor']; // Ambil kd_kantor dari session
    $query_kantor = "SELECT nm_kantor, kab, almt, short, nm_perusahaan FROM kantor WHERE kd_kantor = '$kd_kantor'";
    $result_kantor = mysqli_query($connect, $query_kantor);
    $row_kantor = mysqli_fetch_assoc($result_kantor);
    $lokasi_kantor = $row_kantor['nm_kantor'] . " - " . $row_kantor['kab'];

    $kd_kantor = $_SESSION['kantor']; // Ambil kd_kantor dari session
    $query_pkno1 = "SELECT kode, isi FROM pkno1 WHERE kode = '$kd_kantor'";
    $result_pkno1 = mysqli_query($connect, $query_pkno1);
    $row_pkno1 = mysqli_fetch_assoc($result_pkno1);

    // Tambahkan query untuk mengambil data Direktur Utama
    $query_direktur = "SELECT nama, gelar, posisi, jabatan FROM staff WHERE jabatan = 'Direktur Utama' AND kantor = '$kd_kantor'";
    $result_direktur = mysqli_query($connect, $query_direktur);
    $row_direktur = mysqli_fetch_assoc($result_direktur);

    $nama_direktur = $row_direktur['nama'];
    $gelar_direktur = $row_direktur['gelar'];
    $direktur_lengkap = $nama_direktur . ($gelar_direktur ? ", " . $gelar_direktur : "");

    // Tambahkan query untuk mengambil data Penanggung Jawab
    $query_penanggung = "SELECT nama, gelar, posisi, jabatan FROM staff WHERE posisi = 'Penanggung Jawab" . $kd_kantor . "'";
    $result_penanggung = mysqli_query($connect, $query_penanggung);
    $row_penanggung = mysqli_fetch_assoc($result_penanggung);
    $penanggung_lengkap = $row_penanggung ? $row_penanggung['nama'] . ($row_penanggung['gelar'] ? ", " . $row_penanggung['gelar'] : "") : "[Belum ditentukan]";


    // Tambahkan query untuk mengambil data ADM
    $query_adm = "SELECT nama, gelar, posisi, jabatan FROM staff WHERE jabatan = 'Adm Kredit' AND kantor = '$kd_kantor'";
    $result_adm = mysqli_query($connect, $query_adm);
    $row_adm = mysqli_fetch_assoc($result_adm);

    // Tambahkan query untuk Kepala Cabang
    $query_kacab = "SELECT nama, gelar, posisi, jabatan FROM staff WHERE jabatan = 'Kepala Cabang' AND kantor = '$kd_kantor'";
    $result_kacab = mysqli_query($connect, $query_kacab);
    $row_kacab = mysqli_fetch_assoc($result_kacab);
    $kacab_lengkap = $row_kacab ? $row_kacab['nama'] . ($row_kacab['gelar'] ? ", " . $row_kacab['gelar'] : "") : "[Belum ditentukan]";

    // Tambahkan query untuk Kabid Operasional
    $query_kabid = "SELECT nama, gelar, posisi, jabatan FROM staff WHERE jabatan = 'Kabid. Operasional' AND kantor = '$kd_kantor'";
    $result_kabid = mysqli_query($connect, $query_kabid);
    $row_kabid = mysqli_fetch_assoc($result_kabid);
    $kabid_lengkap = $row_kabid ? $row_kabid['nama'] . ($row_kabid['gelar'] ? ", " . $row_kabid['gelar'] : "") : "[Belum ditentukan]";

    // Tambahkan query untuk Customer Services
    $query_cs = "SELECT nama, gelar, posisi, jabatan FROM staff WHERE jabatan = 'Customer Services' AND kantor = '$kd_kantor'";
    $result_cs = mysqli_query($connect, $query_cs);
    $row_cs = mysqli_fetch_assoc($result_cs);
    $cs_lengkap = $row_cs ? $row_cs['nama'] . ($row_cs['gelar'] ? ", " . $row_cs['gelar'] : "") : "[Belum ditentukan]";

    // Tambahkan pengecekan jika data ADM tidak ditemukan
    if ($row_adm) {
        $nama_adm = $row_adm['nama'];
        $gelar_adm = $row_adm['gelar'];
        $adm_lengkap = $nama_adm . ($gelar_adm ? ", " . $gelar_adm : "");
    } else {
        $adm_lengkap = "[Belum ditentukan]"; // Nilai default jika data ADM tidak ditemukan
    }

    $totalbunga = round($data['angbung'] * $data['jw']);
    $totalangsuran = round($data['angbung'] + $data['angpok']);

    // Tambahkan array untuk konversi bulan ke bahasa Indonesia
    $bulanIndonesia = array(
        'January' => 'Januari',
        'February' => 'Februari',
        'March' => 'Maret',
        'April' => 'April',
        'May' => 'Mei',
        'June' => 'Juni',
        'July' => 'Juli',
        'August' => 'Agustus',
        'September' => 'September',
        'October' => 'Oktober',
        'November' => 'November',
        'December' => 'Desember'
    );

    // Ubah format tanggal ke bahasa Indonesia
    $tanggal_dropping = date('d F Y', strtotime($data['tgl_droping']));
    $tanggal_dropping = str_replace(array_keys($bulanIndonesia), array_values($bulanIndonesia), $tanggal_dropping);

    // Tambahkan query untuk mengambil data jaminan dimana status = noreg
    $query_shm = mysqli_query($connect, "SELECT s.* FROM shm s WHERE s.status='$noreg'");
    $query_bpkb = mysqli_query($connect, "SELECT b.* FROM bpkb b WHERE b.status='$noreg'");
    $query_ajb = mysqli_query($connect, "SELECT a.* FROM ajb a WHERE a.status='$noreg'");
    $query_kios = mysqli_query($connect, "SELECT k.* FROM kios k WHERE k.status='$noreg'");
    $query_bilyet = mysqli_query($connect, "SELECT b.* FROM bilyet b WHERE b.status='$noreg'");
    $query_manulife = mysqli_query($connect, "SELECT m.* FROM manulife m WHERE m.status='$noreg'");
    $query_bpih = mysqli_query($connect, "SELECT b.* FROM bpih b WHERE b.status='$noreg'");
    $query_spph = mysqli_query($connect, "SELECT s.* FROM spph s WHERE s.status='$noreg'");

    // Tambahkan query untuk mengambil data pendamping dan penjamin
    $query_pendamping = "SELECT * FROM pendamping WHERE status = '$noreg'";
    $result_pendamping = mysqli_query($connect, $query_pendamping);

    $query_penjamin = "SELECT * FROM penjamin WHERE niknas = '$data[nik]'";
    $result_penjamin = mysqli_query($connect, $query_penjamin);



    // Gunakan perhitungan total nilai taksasi dari query jaminan
    $total_nilai_taksasi = 0;

    // Reset pointer query dan hitung total taksasi dari SHM
    mysqli_data_seek($query_shm, 0);
    while ($shm = mysqli_fetch_assoc($query_shm)) {
        $total_nilai_taksasi += !empty($shm['tak']) ? (float)$shm['tak'] : 0;
    }

    // Hitung total taksasi dari BPKB
    mysqli_data_seek($query_bpkb, 0);
    while ($bpkb = mysqli_fetch_assoc($query_bpkb)) {
        $total_nilai_taksasi += !empty($bpkb['tak']) ? (float)$bpkb['tak'] : 0;
    }

    // Hitung total taksasi dari AJB
    mysqli_data_seek($query_ajb, 0);
    while ($ajb = mysqli_fetch_assoc($query_ajb)) {
        $total_nilai_taksasi += !empty($ajb['tak']) ? (float)$ajb['tak'] : 0;
    }

    // Hitung total taksasi dari Kios
    mysqli_data_seek($query_kios, 0);
    while ($kios = mysqli_fetch_assoc($query_kios)) {
        $total_nilai_taksasi += !empty($kios['tak']) ? (float)$kios['tak'] : 0;
    }

    // Hitung total taksasi dari Bilyet
    mysqli_data_seek($query_bilyet, 0);
    while ($bilyet = mysqli_fetch_assoc($query_bilyet)) {
        $total_nilai_taksasi += !empty($bilyet['nom']) ? (float)$bilyet['nom'] : 0;
    }

    // Tambahkan pengecekan untuk $row_adm dan $row_penanggung sebelum mengakses propertinya
    $adm_jabatan = isset($row_adm['jabatan']) ? $row_adm['jabatan'] : '';
    $penanggung_jabatan = isset($row_penanggung['jabatan']) ? $row_penanggung['jabatan'] : '';
    $kabid_jabatan = isset($row_kabid['jabatan']) ? $row_kabid['jabatan'] : '';

    $html = '
    <style>
        body {
            font-size: 11pt;
            padding: 1px !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 5px;
        }
        .border-box {
            border: 1px solid black;
            padding: 8px;
            margin-bottom: 10px;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .signature-table td {
            border: 1px solid rgb(122, 122, 122);
            height: 120px;
            text-align: center;
            vertical-align: bottom;
            padding: 15px;
        }
        .signature-name {
            margin-top: 60px;
            text-decoration: underline;
        }
        .signature-title {
            font-size: 0.9em;
        }
        .checkbox {
            font-size: 24pt;
            margin-right: 8px;
            line-height: 1;
            vertical-align: middle;
            font-family: "Arial Unicode MS", sans-serif;
            letter-spacing: 1px;
        }
        .logo {
            width: 150px;
            float: left;
            margin-right: 50px;
        }
        .header-text {
            padding-top: 10px;
            font-size: 12pt;
        }
        .container::after {
            content: "";
            display: block;
            clear: both;
            margin-bottom: 30px;
        }
    </style>
    <div class="container">
        <img src="logo.png" class="logo">
        <div class="header-text">
            <strong>' . strtoupper($row_kantor['nm_perusahaan']) . '</strong><br>
            KANTOR ' . $row_kantor['nm_kantor'] . '<br>
            ' . $row_kantor['almt'] . '
        </div>
        <hr>

    <div style="text-align: center; margin-bottom: 20px;">
        <h3>TANDA TERIMA JAMINAN</h3>
    </div>

    <div class="border-box">
        <strong>TELAH TERIMA DARI</strong><br>
        <table>
            <tr>
                <td width="100">Nama</td>
                <td width="10">:</td>
                <td>' . $data['nama'] . '</td>
            </tr>
            <tr>
                <td>Pekerjaan</td>
                <td>:</td>
                <td>' . $data['pek'] . '</td>
            </tr>
            <tr>
                <td valign="top">Alamat</td>
                <td valign="top">:</td>
                <td>' . $data['almt'] . '</td>
            </tr>
        </table>
    </div>

    <div class="border-box">
        <strong>BERUPA :</strong><br>
        <table>
            <tr>
                <td width="120">Untuk Keperluan</td>
                <td width="10">:</td>
                <td>
                    <span class="checkbox">□</span> Kredit Baru &nbsp;&nbsp;&nbsp;&nbsp; 
                    <span class="checkbox">□</span> Penggantian &nbsp;&nbsp;&nbsp;&nbsp; 
                    <span class="checkbox">□</span> L/C
                </td>
            </tr>
            <tr>
                <td>Jaminan</td>
                <td>:</td>
                <td>
                    <span class="checkbox">□</span> Tambahan &nbsp;&nbsp;&nbsp;&nbsp; 
                    <span class="checkbox">□</span> Bank Guransi &nbsp;&nbsp;&nbsp;&nbsp; 
                    <span class="checkbox">□</span> Lainya
                </td>
            </tr>
        </table>
    </div>

    <div style="text-align: center; margin: 20px 0;">
        ' . $tanggal_dropping . '
    </div>

    <table class="signature-table">
        <tr>
            <td width="50%">
                Yang Menyerahkan<br><br><br><br><br><br>
                <div class="signature-name">(' . $data['nama'] . ')</div>
            </td>
            <td width="50%">
                Yang Menerima<br><br><br><br><br>
                <div class="signature-name">' . $adm_lengkap . '</div>
                <div class="signature-title">' . $adm_jabatan . '</div>
            </td>
        </tr>
    </table>

    <table class="signature-table" style="margin-top: -1px;">
        <tr>
            <td width="33.33%">
                Yang Mengarsip<br><br><br><br><br>
                <div class="signature-name">' . $adm_lengkap . '</div>
                <div class="signature-title">' . $adm_jabatan . '</div>
            </td>
            <td width="45%">
                Yang Mengetahui<br><br><br><br><br>
                <div class="signature-name">' . $penanggung_lengkap . '</div>
                <div class="signature-title">' . $penanggung_jabatan . '</div>
            </td>
            <td width="33.33%">
                Yang Memeriksa<br><br><br><br><br>
                <div class="signature-name">' . $kabid_lengkap . '</div>
                <div class="signature-title">' . $kabid_jabatan . '</div>
            </td>
        </tr>
    </table>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'Surat Tanda Terima Jaminan - ' . $data['nama'] . ' ' . $tanggal_dropping . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('Surat Tanda Terima Jaminan - ' . $data['nama'] . ' ' . $tanggal_dropping);
    $mpdf->WriteHTML($html);

    // Output PDF dengan nama file yang sudah disesuaikan
    $mpdf->Output($filename, 'I');
} catch (\Mpdf\MpdfException $e) {
    // Tampilkan error mPDF
    echo "mPDF error: " . $e->getMessage();
} catch (Exception $e) {
    // Tampilkan error umum
    echo "Error: " . $e->getMessage();
}