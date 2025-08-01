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

    // Tambahkan query untuk mengambil data Staff
    $query_staff = "SELECT nama, gelar, jabatan FROM staff WHERE posisi = 'Penanggung Jawab$kd_kantor' AND kantor = '$kd_kantor'";
    $result_staff = mysqli_query($connect, $query_staff);
    $row_staff = mysqli_fetch_assoc($result_staff);

    $nama_staff = $row_staff['nama'];
    $gelar_staff = $row_staff['gelar'];
    $staff_lengkap = $nama_staff . ($gelar_staff ? ", " . $gelar_staff : "");

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

    $html = '
    <style>
        body {
            font-size: 11pt;
            padding: 1px !important;
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
        <div style="text-align: center;">
            <h3 style="margin-top: 20px;">SURAT PEMBERITAHUAN</h3>
        </div>
        
        <div style="margin-bottom: 20px;">
            <p style="text-align: justify;">BERDASARKAN ATAS PERATURAN OTORITAS JASA KEUANGAN (POJK) NOMOR 18/POJK.03/2017 TENTANG PELAPORAN DAN PERMINTAAN INFORMASI DEBITUR MELALUI SISTEM LAYANAN INFORMASI KEUANGAN yaitu:</p>
            
            <ol>
                <li style="text-align: justify;">Pelapor wajib menyampaikan informasi kepada Debitur terkait pelaporan Penyediaan Dana ke dalam Sistem Layanan Informasi Keuangan.</li>
                <li style="text-align: justify;">Penyampaian informasi sebagaimana dimaksud pada BAB V Pasal 10 tentang Penyampaian Laporan Debitur Ayat 1 Pelapor hanya dapat menyampaikan Laporan Debitur dan/atau koreksi Laporan Debitur secara daring (online) melalui SLIK.</li>
            </ol>
            
            <p style="text-align: justify;">Maka berdasarkan hal tersebut diatas yang bertandatangan di bawah ini:</p>
            
            <p style="text-align: justify;">Dengan ini memberitahukan bahwa kami akan menyampaikan informasi Debitur terkait pelaporan Penyediaan Dana dan hal lain terkait atas pemberian fasilitas kredit yang diberikan oleh ' . $row_kantor['nm_perusahaan'] . ' dalam Sistem Layanan Informasi Keuangan</p>
        </div>

        <div style="margin-top: 40px;">
            <table width="100%">
                <tr>
                    <td width="50%" style="text-align: center;">
                        Peminjam<br><br><br><br><br><br>
                        <strong><u>' . $data['nama'] . '</u></strong><br>
                    </td>
                    <td width="50%" style="text-align: center;">
                        ' . $row_kantor['kab'] . ', ' . $tanggal_dropping . '<br>
                        Yang diberi kuasa<br><br><br><br><br><br>
                        <strong><u>' . $staff_lengkap . '</u></strong><br>
                        ' . $row_staff['jabatan'] . '
                    </td>
                </tr>
            </table>
        </div>
    </div>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'Surat Pemberitahuan - ' . $data['nama'] . ' ' . $tanggal_dropping . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('Surat Pemberitahuan - ' . $data['nama'] . ' ' . $tanggal_dropping);
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