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

    // Ambil isi pkno1 untuk digunakan dalam dokumen
    $query_pkno1 = "SELECT kode, isi FROM pkno1 WHERE kode = '$kd_kantor'";
    $result_pkno1 = mysqli_query($connect, $query_pkno1);
    $row_pkno1 = mysqli_fetch_assoc($result_pkno1);
    $isi_pkno1 = $row_pkno1['isi'];

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
    function formatTanggalIndonesia($tanggal) {
        global $bulanIndonesia;
        $tanggal_format = date('d F Y', strtotime($tanggal));
        return str_replace(array_keys($bulanIndonesia), array_values($bulanIndonesia), $tanggal_format);
    }

    // Tambahkan fungsi terbilang untuk mengkonversi angka ke kata-kata
    function terbilang($angka) {
        $angka = abs($angka);
        $baca = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
        $terbilang = "";
        
        if ($angka < 12) {
            $terbilang = " " . $baca[$angka];
        } else if ($angka < 20) {
            $terbilang = terbilang($angka - 10) . " belas";
        } else if ($angka < 100) {
            $terbilang = terbilang(floor($angka / 10)) . " puluh" . terbilang($angka % 10);
        } else if ($angka < 200) {
            $terbilang = " seratus" . terbilang($angka - 100);
        } else if ($angka < 1000) {
            $terbilang = terbilang(floor($angka / 100)) . " ratus" . terbilang($angka % 100);
        } else if ($angka < 2000) {
            $terbilang = " seribu" . terbilang($angka - 1000);
        } else if ($angka < 1000000) {
            $terbilang = terbilang(floor($angka / 1000)) . " ribu" . terbilang($angka % 1000);
        } else if ($angka < 1000000000) {
            $terbilang = terbilang(floor($angka / 1000000)) . " juta" . terbilang($angka % 1000000);
        } else if ($angka < 1000000000000) {
            $terbilang = terbilang(floor($angka / 1000000000)) . " milyar" . terbilang($angka % 1000000000);
        } else if ($angka < 1000000000000000) {
            $terbilang = terbilang(floor($angka / 1000000000000)) . " trilyun" . terbilang($angka % 1000000000000);
        }
        
        return $terbilang;
    }

    // Tambahkan fungsi untuk mendapatkan nama hari dalam bahasa Indonesia
    function getNamaHari($tanggal) {
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
    
    // Ambil data SPPH berdasarkan status dan norut
    $row_spph = mysqli_fetch_assoc($query_spph);
    $an_spph = isset($row_spph['an']) ? $row_spph['an'] : '-';
    
    // Ambil data BPIH berdasarkan status dan norut
    $row_bpih = mysqli_fetch_assoc($query_bpih);
    $an_bpih = isset($row_bpih['an']) ? $row_bpih['an'] : '-';

    // Tambahkan query untuk mengambil data pendamping dan penjamin
    $query_pendamping = "SELECT * FROM pendamping WHERE status = '$noreg'";
    $result_pendamping = mysqli_query($connect, $query_pendamping);

    $query_penjamin = "SELECT * FROM penjamin WHERE niknas = '$data[nik]'";
    $result_penjamin = mysqli_query($connect, $query_penjamin);

    // Ambil data pengikatan jaminan dari tabel pengajuan
    $pengikatan_jaminan = !empty($data['pengikatan']) ? $data['pengikatan'] : '-';

    // Ubah HTML untuk membuat Addendum Haji sesuai dengan gambar
    $html = '
    <style>
        body {
            font-size:11pt;
            padding: 0 !important;
            margin: 0 !important;
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
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .cover-page {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            text-align: center;
        }
        .cover-title {
            font-size: 16pt;
            font-weight: bold;
            line-height: 1.5;
            margin: 0 auto;
            max-width: 80%;
        }
    </style>

    <div class="cover-page">
        <div class="cover-title"><u>
        
            ADDENDUM PERJANJIAN KREDIT PEMBIAYAAN PORSI<br>
            HAJI DENGAN PERUBAHAN JAMINAN<br>
            <span style="font-size: 12pt; font-weight: normal;">KKPO SEBAGAI PIHAK YANG MEWAKILI BANK</span>
        </u></div>
    </div>

    <pagebreak />

    <div class="center bold" style="font-size:14pt; margin-bottom: 1px;">ADDENDUM ATAS PERJANJIAN KREDIT</div>
    <div class="center bold" style="font-size:11pt; margin-bottom: 10px;">Nomor : '.$data['noloan'].'/BPR-TS/PK/'.$data['prodkre'].'/'.$data['bln_rmwi'].'/'.date('Y', strtotime($data['tgl_droping'])).'</div>
    
    <p>Yang Bertanda tangan di bawah ini :</p>
    
    <ol>
        <li>
           '.$isi_pkno1.'
        </li>
        <li>
            '.$data['nama'].', Umur '.date_diff(date_create($data['tgl']), date_create('today'))->y.' Tahun, Pekerjaan '.$data['pek'].', bertempat tinggal di Jl. '.$data['almt'].', pemegang Kartu Tanda Penduduk Nomor '.$data['nik'].' selanjutnya disebut PEMINJAM, yang mana dalam melaksanakan tindakan hukumnya dalam hal ini bertindak atas nama diri sendiri Yang turut serta membubuhkan tanda tangan dalam Addendum Perjanjian kredit ini.
        </li>
    </ol>
    
    <p>Masing-masing pihak tersebut diatas telah menyetujui dilakukannya Addendum beberapa perubahan serta penambahan terhadap Perjanjian Kredit Nomor : '.$data['noloan'].'/BPR-TS/PK/'.$data['prodkre'].'/'.$data['bln_rmwi'].'/'.date('Y', strtotime($data['tgl_droping'])).' tertanggal '.formatTanggalIndonesia($data['tgl_droping']).' dengan isi sebagai berikut :</p>
    
    <ol>
        <li>Mengacu pada Pasal 5</li>
    </ol>
    
    <p>Semula berbunyi :</p>
    
    <div class="center bold" style="margin: 10px 0;">PASAL 5<br>JAMINAN</div>
    
    <p>Guna menjamin ketertiban pembayaran/kewajiban PEMINJAM kepada BANK tepat pada waktu yang telah disepakati oleh BANK dan PEMINJAM berdasarkan PERJANJIAN ini, maka PEMINJAM dan/atau PENJAMIN berjanji dan dengan ini mengikatkan diri untuk membuat dan menandatangani pengikatan jaminan serta menyerahkan barang jaminannya kepada BANK sesuai dengan peraturan perundang-undangan yang berlaku, yang merupakan bagian yang tidak terpisahkan dari PERJANJIAN ini. Jenis barang jaminan yang diserahkan adalah berupa:</p>
    
    <ol>
        <li>Surat Pendaftaran Pergi Haji (SPPH) asli atas nama yang diterbitkan oleh Kantor Kementerian Agama Kabupaten</li>
        <li>Tanda Bukti Setoran Biaya Penyelenggaraan Ibadah Haji (BPIH) asli atas nama yang diterbitkan oleh Bank Muamalat Indonesia</li>
        <li>Buku Tabungan Jamaah Haji asli atas nama yang diterbitkan oleh Bank Muamalat Indonesia</li>
    </ol>
    
    <p>Dirubah menjadi :</p>
    
    <div class="center bold" style="margin: 10px 0;">PASAL 5<br>JAMINAN</div>
    
    <p>Guna menjamin ketertiban pembayaran/kewajiban PEMINJAM kepada BANK tepat pada waktu yang telah disepakati oleh BANK dan PEMINJAM berdasarkan PERJANJIAN ini, maka PEMINJAM dan/atau PENJAMIN berjanji dan dengan ini mengikatkan diri untuk membuat dan menandatangani pengikatan jaminan serta menyerahkan barang jaminannya kepada BANK sesuai dengan peraturan perundang-undangan yang berlaku, yang merupakan bagian yang tidak terpisahkan dari PERJANJIAN ini. Jenis barang jaminan yang diserahkan adalah berupa:</p>
    
    <ol>
        <li>Surat Pendaftaran Pergi Haji (SPPH) asli atas nama '.$an_spph.' yang diterbitkan oleh Kantor Kementerian Agama Kabupaten</li>
        <li>Tanda Bukti Setoran Biaya Penyelenggaraan Ibadah Haji (BPIH) asli atas nama '.$an_bpih.' yang diterbitkan oleh Bank Muamalat Indonesia</li>
        <li>Buku Tabungan Jamaah Haji asli atas nama yang diterbitkan oleh Bank Muamalat Indonesia</li>
    </ol>
    
    <p>2. Addendum perjanjian ini merupakan satu kesatuan yang tidak terpisahkan dari PERJANJIAN KREDIT Selengkapnya addendum ini dibuat dan ditanda tangani oleh PEMINJAM dan/atau PENJAMIN dan BANK di atas kertas yang bermaterai cukup dalam 2 (dua) rangkap yang masing-masing disimpan oleh BANK, dan PEMINJAM, dan masing-masing berlaku sebagai aslinya. Seluruh ketentuan diluar perubahan tersebut diatas dinyatakan tetap berlaku dan mengikat untuk para Pihak.</p>
    
    <p style="text-align: left; margin-top: 20px;">Demikian addendum ini dibuat dan ditandatangani oleh Para Pihak di '.$row_kantor['kab'].', Pada tanggal '.formatTanggalIndonesia($data['tgl_droping']).'</p>
    
    <table class="no-border" style="margin-top: 10px;">
        <tr>
            <td width="50%" style="text-align: center;"></td>
            <td style="text-align: center;">'.$row_kantor['kab'].', '.formatTanggalIndonesia($data['tgl_droping']).'</td>
        </tr>
        <tr>
            <td style="text-align: center;">Peminjam</td>
            <td style="text-align: center;">'.$row_kantor['nm_perusahaan'].'</td>
        </tr>
        <tr>
            <td style="height: 80px;"></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: center;"><strong>('.$data['nama'].')</strong></td>
            <td style="text-align: center;"><strong>('.$direktur_lengkap.')</strong><br>Direktur Utama</td>
        </tr>
    </table>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'Addendum Haji - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']) . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('Addendum Haji - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']));
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
