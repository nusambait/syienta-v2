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

    // Tambahkan query untuk mengambil data Direktur Utama
    $query_direktur = "SELECT nama, gelar FROM staff WHERE jabatan = 'Direktur Utama' AND kantor = '$kd_kantor'";
    $result_direktur = mysqli_query($connect, $query_direktur);
    $row_direktur = mysqli_fetch_assoc($result_direktur);

    $nama_direktur = $row_direktur['nama'];
    $gelar_direktur = $row_direktur['gelar'];
    $direktur_lengkap = $nama_direktur . ($gelar_direktur ? ", " . $gelar_direktur : "");

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

    // Tambahkan fungsi untuk mengubah format tanggal ke bahasa Indonesia
    function formatTanggalIndonesia($tanggal)
    {
        global $bulanIndonesia;
        $tanggal_format = date('d F Y', strtotime($tanggal));
        return str_replace(array_keys($bulanIndonesia), array_values($bulanIndonesia), $tanggal_format);
    }

    // Tambahkan fungsi untuk mendapatkan nama hari dalam bahasa Indonesia
    function getNamaHari($tanggal)
    {
        $hari = date('l', strtotime($tanggal));
        $namaHari = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        return $namaHari[$hari];
    }

    // Dapatkan nama hari dari tanggal dropping
    $nama_hari = getNamaHari($data['tgl_droping']);

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

    $kd_kantor = $_SESSION['kantor']; // Ambil kd_kantor dari session
    $query_pkno1 = "SELECT kode, isi FROM pkno1 WHERE kode = '$kd_kantor'";
    $result_pkno1 = mysqli_query($connect, $query_pkno1);
    $row_pkno1 = mysqli_fetch_assoc($result_pkno1);

    // Ambil data pengikatan jaminan dari tabel pengajuan
    $pengikatan_jaminan = !empty($data['pengikatan']) ? $data['pengikatan'] : 'Fiducia';

    $html = '
    <style>
        body {
            font-size:8pt;
            padding: 0 !important;
            margin: 0 !important;
        }
        .logo {
            width: 120px;
            float: left;
            margin-right: 50px;
        }
        .header-text {
            padding-top: 5px;
            font-size: 10pt;
        }
        .container::after {
            content: "";
            display: block;
            clear: both;
            margin-bottom: 30px;
        }
        p, ol, li {
            text-align: justify;
        }
        .text-justify {
            text-align: justify;
        }
        table {
            width: 100%;
        }
        .no-border td {
            border: none;
        }
        .indent {
            padding-left: 20px;
        }
        .sub-item {
            padding-left: 20px;
        }
    </style>
    <div class="container">
        <img src="logo.png" class="logo">
        <div class="header-text">
            <strong>' . strtoupper($row_kantor['nm_perusahaan']) . '</strong><br>
            KANTOR ' . $row_kantor['nm_kantor'] . '<br>
            ' . $row_kantor['almt'] . '
        </div>
    </div>
    <hr>

    <table class="no-border">
        <tr>
            <td width="40%">NO : ' . $data['nospp'] . '/ADM/SMTPK/' . $data['bln_rmwi'] . '/' . date('Y', strtotime($data['tgl_droping'])) . '</td>
            <td width="20%"></td>
            <td width="40%" style="text-align: right;">' . $row_kantor['kab'] . ', ' . formatTanggalIndonesia($data['tgl_acc_direksi']) . '</td>
        </tr>
        <tr>
            <td>Kepada Yth</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="3">Bapak/Ibu. ' . $data['nama'] . '</td>
        </tr>
        <tr>
            <td colspan="3">' . $data['almt'] . '</td>
        </tr>
        <tr>
            <td colspan="3">Kec ' . $data['kec'] . ' Kab ' . $data['kab'] . '</td>
        </tr>
    </table>

    <p style="margin-top: 10px;"><strong>Perihal : Informasi Tentang Prodak Kredit</strong></p>

    <p>Dengan Hormat</p>
    <p>Sehubungan dengan pengajuan Kredit Bapak/Ibu kepada ' . $row_kantor['nm_perusahaan'] . ' PUSAT OPERASIONAL, dengan ini kami sampaikan informasi prodak kredit, dengan penjelasan sebagai berikut :</p>

    <table class="no-border" style="margin-left: 0px;">
        <tr>
            <td width="5%">1.</td>
            <td width="30%">Nama Produk / Jenis Kredit</td>
            <td width="5%">:</td>
            <td width="60%">' . $data['prodkre'] . ' / ' . $data['jns_kredit'] . '</td>
        </tr>
        <tr>
            <td>2.</td>
            <td>Persyaratan Kredit</td>
            <td>:</td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>a. Fotocopy KTP Suami Istri</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>b. Fotocopy KK dan Surat Nikah</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>c. Jaminan Asli</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>d. Photo Suami dan Istri</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>e. Lain-lain</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>3.</td>
            <td>Biaya-biaya</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td>a. Provisi</td>
            <td>:</td>
            <td>0% ( Persen)</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td>Rp. 0,- ( Rupiah)</td>
        </tr>
        <tr>
            <td></td>
            <td>b. Administrasi</td>
            <td>:</td>
            <td>Rp. 0,- ( Rupiah)</td>
        </tr>
        <tr>
            <td></td>
            <td>c. Asuransi Jiwa</td>
            <td>:</td>
            <td>Rp. 827.000,- (Delapan Ratus Dua Puluh Tujuh Ribu Rupiah)</td>
        </tr>
        <tr>
            <td></td>
            <td>d. Pengikatan Kredit</td>
            <td>:</td>
            <td>INTERN</td>
        </tr>
        <tr>
            <td></td>
            <td>&nbsp;&nbsp;&nbsp;&nbsp;Jaminan</td>
            <td>:</td>
            <td>Pengikatan Jaminan</td>
        </tr>
        <tr>
            <td></td>
            <td>e. Denda Keterlambatan Angsuran</td>
            <td>:</td>
            <td>2% /Bulan dari total angsuran yang tertunggak</td>
        </tr>
        <tr>
            <td></td>
            <td>f. Finalty Pelunasan Kedit Sebelum Jatuh Tempo</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3">i. Pelunasan sebelum tanggal valuta maka dikenakan kewajiban pembayaran bunga penuh sampai bulan pelunasan ditambah penalty 1 bulan bunga ke depan.</td>
        </tr>
        <tr>
            <td></td>
            <td colspan="3">ii. Pelunasan setelah tanggal valuta maka dikenakan kewajiban pembayaran bunga penuh sampai dengan bulan pelunasan ditambah penalty 2 bulan ke depan.</td>
        </tr>
        <tr>
            <td></td>
            <td>g. Biaya Lainnya</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>4.</td>
            <td>Suku Bunga</td>
            <td>:</td>
            <td>' . $data['sukbung'] . ' % (' . $data['tsukbung'] . ' Persen)</td>
        </tr>
        <tr>
            <td></td>
            <td>a. Metode Perhitungan</td>
            <td>:</td>
            <td>' . $data['metode'] . '</td>
        </tr>
        <tr>
            <td></td>
            <td>b. Cara Perhitungan</td>
            <td>:</td>
            <td>' . $data['cara'] . '</td>
        </tr>
        <tr>
            <td></td>
            <td>c. Pembebanan</td>
            <td>:</td>
            <td>Setiap tgl ' . date('d', strtotime($data['tgl_droping'])) . ' ' . str_replace(array_keys($bulanIndonesia), array_values($bulanIndonesia), date('F', strtotime($data['tgl_droping']))) . ' ' . date('Y', strtotime($data['tgl_droping'])) . ' s/d ' . date('d', strtotime($data['tgl_droping'])) . ' ' . str_replace(array_keys($bulanIndonesia), array_values($bulanIndonesia), date('F', strtotime($data['tgl_droping']))) . ' ' . date('Y') . '</td>
        </tr>
        <tr>
            <td></td>
            <td>d. Penyesuaian suku bunga pasar</td>
            <td>:</td>
            <td>Tetap sesuai perjanjian</td>
        </tr>
        <tr>
            <td>5.</td>
            <td>Jangka Waktu</td>
            <td>:</td>
            <td>' . $data['jw'] . ' (' . $data['tjw'] . ') Bulan</td>
        </tr>
        <tr>
            <td>6.</td>
            <td>Manfaat Asuransi Jiwa</td>
            <td>:</td>
            <td>Paket ' . $data['jns_asjw'] . '</td>
        </tr>
        <tr>
            <td>7.</td>
            <td>Hal-hal Lain</td>
            <td>:</td>
            <td>Perjanjian kredit telah di tandatangani</td>
        </tr>
    </table>

    <p>Demikian informasi ini kami sampaikan atas perhatian dan kerjasamanya diucapkan terima kasih</p>
    <div style="text-align: center;">Disetujui</div>
    <table class="no-border" style="margin-top: 10px;">
        <tr>
            <td width="50%" style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">Peminjam</td>
            <td style="text-align: center;">' . $row_kantor['nm_perusahaan'] . '</td>
        </tr>
        <tr>
            <td style="height: 40px;"></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: center;"><strong><u>(' . $data['nama'] . ')</u></strong></td>
            <td style="text-align: center;"><strong><u>(' . $direktur_lengkap . ')</u></strong><br>Direktur Utama</td>
        </tr>
    </table>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'SMTPK INST ANUITAS - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']) . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('SMTPK INST ANUITAS - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']));
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