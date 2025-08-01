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

// Tambahkan query untuk mengambil data nasabah, pengajuan, droping dan restruk
$noreg = $_GET['noreg'];
$query = "SELECT n.*, p.*, d.*, r.noreg_baru, r.noreg_lama 
         FROM nasabah n 
         JOIN pengajuan p ON n.nik = p.niknas
         JOIN droping d ON p.noreg = d.noreg
         LEFT JOIN restruk r ON p.noreg = r.noreg_lama
         WHERE d.noreg = '$noreg'";
$result = mysqli_query($connect, $query);
$data = mysqli_fetch_assoc($result);

// Ambil noreg_baru dan noreg_lama dari data
$noreg_baru = $data['noreg_baru'];
$noreg_lama = $data['noreg_lama'];

// Tambahkan query untuk data perjanjian lama
$query_lama = "SELECT p.*, d.* 
               FROM pengajuan p 
               JOIN droping d ON p.noreg = d.noreg 
               WHERE p.noreg = '$noreg_lama'";
$result_lama = mysqli_query($connect, $query_lama);
$data_lama = mysqli_fetch_assoc($result_lama);

// Tambahkan query untuk data pengajuan dan droping baru
$query_baru = "SELECT p.*, d.* 
               FROM pengajuan p 
               JOIN droping d ON p.noreg = d.noreg 
               WHERE p.noreg = '$noreg_baru'";
$result_baru = mysqli_query($connect, $query_baru);
$data_baru = mysqli_fetch_assoc($result_baru);

// Tambahkan query untuk data droping baru
$query_droping_baru = "SELECT * FROM droping WHERE noreg = '$noreg_baru'";
$result_droping_baru = mysqli_query($connect, $query_droping_baru);
$droping_baru = mysqli_fetch_assoc($result_droping_baru);

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
    $angka = abs((int)$angka); // Konversi eksplisit ke integer
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
    $query_pendamping = "SELECT * FROM pendamping WHERE status = '$noreg_baru'";
    $result_pendamping = mysqli_query($connect, $query_pendamping);

    $query_penjamin = "SELECT * FROM penjamin WHERE niknas = '$data[nik]'";
    $result_penjamin = mysqli_query($connect, $query_penjamin);

    // Ambil data pengikatan jaminan dari tabel pengajuan
    $pengikatan_jaminan = !empty($data['pengikatan']) ? $data['pengikatan'] : '-';

    // Gunakan noreg_baru dan noreg_lama dari tabel restruk
    $nomor_addendum = $data['noreg_baru'] ? $data['noreg_baru'] : $data['noloan'];
    $nomor_perjanjian_lama = $data['noreg_lama'] ? $data['noreg_lama'] : $data['noloan'];

    // Tambahkan pengecekan jenis kredit untuk data lama dan baru
    if ($data_lama['jns_kredit'] == 'Installment') {
        $jenis_kredit_lama = 'KREDIT INSTALLMENT ' . $data_lama['penggunaan'] . ' sebesar Rp. ' . number_format($data_lama['plafond'], 2, ",", ".") . ',- (' . $data_lama['tplafond'] . ')';
    } else {
        $jenis_kredit_lama = 'KREDIT REGULER sebesar Rp. ' . number_format($data_lama['plafond'], 2, ",", ".") . ',- (' . $data_lama['tplafond'] . ')';
    }

    if ($data_baru['jns_kredit'] == 'Installment') {
        $jenis_kredit_baru = 'KREDIT INSTALLMENT Perpanjangan Jangka Waktu Kredit sebesar Rp. ' . number_format($data_baru['plafond'], 2, ",", ".") . ',- (' . $data_baru['tplafond'] . ')';
    } else {
        $jenis_kredit_baru = 'KREDIT REGULER Perpanjangan Jangka Waktu Kredit sebesar Rp. ' . number_format($data_baru['plafond'], 2, ",", ".") . ',- (' . $data_baru['tplafond'] . ')';
    }

    // Tambahkan fungsi untuk menghitung tanggal jatuh tempo
    function hitungTanggalJatuhTempo($tanggal_droping, $jangka_waktu) {
        $date = date_create($tanggal_droping);
        date_add($date, date_interval_create_from_date_string($jangka_waktu . " months"));
        return date_format($date, "Y-m-d");
    }

    // Hitung tanggal jatuh tempo untuk data lama dan baru
    $tgl_jt_lama = hitungTanggalJatuhTempo($data_lama['tgl_droping'], $data_lama['jw']);
    $tgl_jt_baru = hitungTanggalJatuhTempo($data_baru['tgl_droping'], $data_baru['jw']);

// Hitung angsuran jika tidak ada
$angs_lama = isset($data_lama['angs']) ? $data_lama['angs'] : round($data_lama['plafond'] * $data_lama['sukbung'] / 100 / 12, 2);
$tangs_lama = isset($data_lama['tangs']) ? $data_lama['tangs'] : terbilang((int)$angs_lama);

$angs_baru = isset($data_baru['angs']) ? $data_baru['angs'] : round($data_baru['plafond'] * $data_baru['sukbung'] / 100 / 12, 2);
$tangs_baru = isset($data_baru['tangs']) ? $data_baru['tangs'] : terbilang((int)$angs_baru);

    // Cek apakah ada pendamping dari tabel pendamping
    $query_pendamping = mysqli_query($connect, "SELECT * FROM pendamping WHERE status = '$noreg_baru'");
    $data_pendamping = mysqli_fetch_array($query_pendamping);
    $nama_pendamping = '';
    $ttd_pendamping = '';
    $hub_pendamping = '';

    if (mysqli_num_rows($query_pendamping) > 0) {
        $nama_pendamping = $data_pendamping['nama'];
        $ttd_pendamping = '<strong>('.$nama_pendamping.')</strong>';
        $hub_pendamping = $data_pendamping['hub'];
    } else {
        $ttd_pendamping = '<strong>(................................)</strong>';
        $hub_pendamping = 'Pendamping';
    }

    // Ubah HTML untuk membuat Addendum Haji sesuai dengan gambar
    $html = '
    <style>
        body {
            font-size:10pt;
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
            ADDENDUM PERJANJIAN KREDIT (RESTRUKTURISASI)<br>
            <span style="font-size: 12pt; font-weight: normal;">KKPO SEBAGAI PIHAK YANG MEWAKILI BANK</span>
        </u></div>
    </div>

    <pagebreak />

<div class="center bold" style="font-size:14pt; margin-bottom: 1px;">ADDENDUM ATAS PERJANJIAN KREDIT</div>
<div class="center bold" style="font-size:11pt; margin-bottom: 10px;">Nomor : '.$data_lama['noloan'].'/BPR-TS/PK/'.$data_lama['prodkre'].'/'.$data_lama['bln_rmwi'].'/'.date('Y', strtotime($data_lama['tgl_droping'])).'</div>

<div class="center bold" style="font-size:11pt; margin-bottom: 10px;">Nomor Addendum : '.$data_baru['noloan'].'/BPR-TS/ADENDUM/'.$data_baru['prodkre'].'/'.$data_baru['bln_rmwi'].'/'.date('Y', strtotime($data_baru['tgl_droping'])).'</div>
    
    <p>Yang Bertanda tangan di bawah ini :</p>
    
    <ol>
        <li>
           '.$isi_pkno1.'
        </li>
        <li>
            '.$data['nama'].', Umur '.date_diff(date_create($data['tgl']), date_create('today'))->y.' Tahun, Pekerjaan '.$data['pek'].', bertempat tinggal di Jl. '.$data['almt'].', pemegang Kartu Tanda Penduduk Nomor '.$data['nik'].' selanjutnya disebut PEMINJAM, yang mana dalam melaksanakan tindakan hukumnya dalam hal ini bertindak atas nama diri sendiri Yang turut serta membubuhkan tanda tangan dalam Addendum Perjanjian kredit ini.
        </li>
    </ol>
    
    <p>Masing-masing pihak termaksud diatas telah menyetujui dilakukannya Restrukturisasi Kredit berkaitan dengan perpanjangan waktu perjanjian terhadap Pinjaman atas nama PEMINJAM tersebut diatas, maka dengan ini BANK dan PEMINJAM bersepakat untuk melakukan Addendum terhadap Perjanjian Kredit Nomor : '.$data['noloan'].'/BPR-TS/PK/'.$data['prodkre'].'/'.$data['bln_rmwi'].'/'.date('Y', strtotime($data['tgl_droping'])).' tertanggal '.formatTanggalIndonesia($data['tgl_droping']).' dengan isi sebagai berikut :</p>
    
    <div style="margin-top: 20px; margin-bottom: 20px;">
        <div style="font-weight: bold; margin-bottom: 10px;">1. Mengacu Pada Pasal 1</div>
        <div style="margin-left: 20px; margin-bottom: 10px;">Semula Berbunyi</div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">PASAL 1</div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">POKOK PERJANJIAN</div>
        
        <div style="margin-bottom: 10px;">BANK telah memberikan kepada PEMINJAM :</div>
        <div style="margin-bottom: 10px;">(1) Fasilitas kredit sebesar Rp. '.number_format($data_lama['plafond'], 2, ",", ".").',- ('.$data_lama['tplafond'].') dan PEMINJAM menyatakan menerima fasilitas kredit tersebut, yang terdiri dari :</div>
        
        <div style="margin-left: 20px; margin-bottom: 10px;">'.$jenis_kredit_lama.'</div>
        
        <div style="margin-bottom: 10px;">(2) Terhadap fasilitas kredit tersebut dalam ayat (1) pasal ini dapat ditarik seketika oleh PEMINJAM pada saat penandatanganan PERJANJIAN kredit</div>
        <div style="margin-bottom: 20px;">(3) Penarikan sebagaimana dimaksud dalam ayat (2) pasal ini dilakukan dengan menerbitkan bukti pengambilan uang (Kwitansi) ;</div>
        
        <div style="margin-left: 20px; margin-bottom: 10px;">Dirubah Menjadi</div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">PASAL 1</div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">POKOK PERJANJIAN</div>
        
        <div style="margin-bottom: 10px;">BANK telah memberikan kepada PEMINJAM :</div>
        <div style="margin-bottom: 10px;">(1) Fasilitas kredit sebesar Rp. '.number_format($data_baru['plafond'], 2, ",", ".").',- ('.$data_baru['tplafond'].') dan PEMINJAM menyatakan menerima fasilitas kredit tersebut, yang terdiri dari :</div>
        
        <div style="margin-left: 20px; margin-bottom: 10px;">'.$jenis_kredit_baru.'</div>
        
        <div style="margin-bottom: 20px;">(2) Terhadap Fasilitas Kredit ini merupakan restrukturisasi atas kredit perpanjangan waktu perjanjian/perubahan jumlah? dari Fasilitas dan perjanjian kredit sebelumnya yang merupakan satu bagian yang tidak dapat dipisahkan</div>
    </div>

    <div style="margin-top: 20px; margin-bottom: 20px;">
        <div style="font-weight: bold; margin-bottom: 10px;">2. Mengacu Pada Pasal 2</div>
        <div style="margin-left: 20px; margin-bottom: 10px;">Semula Berbunyi</div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">PASAL 2</div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">BUNGA DAN ANGSURAN</div>
        
        <div style="margin-bottom: 10px;">BANK dan PEMINJAM bersepakat :</div>
        <div style="margin-bottom: 10px;">(1) Bunga atas fasilitas kredit sebesar '.$data_lama['sukbung'].'% (' . $data_lama['tsukbung'] . ' Persen) '.$data_lama['cara'] . ' per tahun terhitung sejak tanggal penarikan fasilitas kredit oleh PEMINJAM hingga fasilitas kredit tersebut lunas;</div>
        <div style="margin-bottom: 10px;">(2) Pembayaran angsuran kredit dilakukan oleh PEMINJAM kepada BANK pada tanggal '.date('d', strtotime($data_lama['tgl_droping'])).' setiap bulan berjalan melalui rekening simpanan PEMINJAM, dan oleh karenanya BANK berhak melakukan debet secara otomatis;</div>
        <div style="margin-bottom: 20px;">(3) Dalam hal pembayaran angsuran pokok dan bunga dilakukan oleh PEMINJAM secara tunai, maka BANK melakukan pencatatan sebagaimana mestinya;</div>
        
        <div style="margin-left: 20px; margin-bottom: 10px;">Dirubah Menjadi</div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">PASAL 2</div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">BUNGA DAN ANGSURAN</div>
        
        <div style="margin-bottom: 10px;">BANK dan PEMINJAM bersepakat :</div>
        <div style="margin-bottom: 10px;">(1) Bunga atas fasilitas kredit sebesar '.$data_baru['sukbung'].' (' . $data_baru['tsukbung'] . ') '.$data_baru['cara'] . ' per tahun terhitung sejak tanggal penarikan fasilitas kredit oleh PEMINJAM hingga fasilitas kredit tersebut lunas;</div>
        <div style="margin-bottom: 10px;">(2) Pembayaran angsuran kredit dilakukan oleh PEMINJAM kepada BANK pada tanggal '.date('d', strtotime($data_baru['tgl_droping'])).' setiap bulan berjalan melalui rekening simpanan PEMINJAM, dan oleh karenanya BANK berhak melakukan debet secara otomatis;</div>
        <div style="margin-bottom: 20px;">(3) Dalam hal pembayaran angsuran pokok dan bunga dilakukan oleh PEMINJAM secara tunai, maka BANK melakukan pencatatan sebagaimana mestinya;</div>
    </div>

    <div style="margin-top: 20px; margin-bottom: 20px;">
        <div style="font-weight: bold; margin-bottom: 10px;">3. Mengacu Pada Pasal 3</div>
        <div style="margin-left: 20px; margin-bottom: 10px;">Semula Berbunyi</div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">PASAL 3</div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">BIAYA-BIAYA</div>
        
        <div style="margin-bottom: 10px;">(1) PEMINJAM berjanji dan dengan ini mengikatkan diri untuk menanggung seluruh biaya yang diperlukan sehubungan dengan pelaksanaan PERJANJIAN ini, sepanjang hal ini diberitahukan BANK kepada PEMINJAM sebelum ditandatanganinya PERJANJIAN ini, dan PEMINJAM menyatakan persetujuannya;</div>
        <div style="margin-bottom: 10px;">(2) Adapun biaya-biaya yang dimaksud pada ayat (1) pasal ini adalah:</div>
        <div style="margin-left: 20px; margin-bottom: 5px;">(a) Provisi kredit sebesar '.$data_lama['prov'].' (' . $data_lama['tprov'] . ') dari jumlah kredit atau sebesar Rp. '.number_format($data_lama['plafond'], 2, ",", ".").',- ('.$data_lama['tprov'].')</div>
        <div style="margin-left: 20px; margin-bottom: 5px;">(b) Biaya Administrasi kredit sebesar Rp. '.number_format($data_lama['adm'], 2, ",", ".").',- ('.$data_lama['tadm'].')</div>
        <div style="margin-left: 20px; margin-bottom: 5px;">(c) Biaya Notaris '.number_format($data_lama['notaris'], 2, ",", ".").' ('.$data_lama['tnotaris'].')</div>
        <div style="margin-left: 20px; margin-bottom: 5px;">(d) Biaya Asuransi Jiwa Peminjam '.number_format($data_lama['asjw'], 2, ",", ".").' ('.$data_lama['tasjw'].')</div>
        <div style="margin-left: 20px; margin-bottom: 10px;">(e) Jumlah Total biaya Rp. '.number_format($data_lama['total'], 2, ",", ".").' ('.$data_lama['ttotal'].') yang dibayarkan seketika waktu penerimaan kredit, di debet dari rekening simpanan PEMINJAM;</div>
        <div style="margin-bottom: 20px;">(3) Segala pajak yang timbul sehubungan dengan PERJANJIAN ini merupakan tanggung jawab dan wajib dibayar oleh PEMINJAM, kecuali Pajak Penghasilan BANK;</div>
        
        <div style="margin-left: 20px; margin-bottom: 10px;">Dirubah Menjadi</div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">PASAL 3</div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">BIAYA-BIAYA</div>
        
        <div style="margin-bottom: 10px;">(1) PEMINJAM berjanji dan dengan ini mengikatkan diri untuk menanggung seluruh biaya yang diperlukan sehubungan dengan pelaksanaan PERJANJIAN ini, sepanjang hal ini diberitahukan BANK kepada PEMINJAM sebelum ditandatanganinya PERJANJIAN ini, dan PEMINJAM menyatakan persetujuannya;</div>
        <div style="margin-bottom: 10px;">(2) Adapun biaya-biaya yang dimaksud pada ayat (1) pasal ini adalah:</div>
        <div style="margin-left: 20px; margin-bottom: 5px;">(a) Provisi kredit sebesar 0% ( Persen ) dari jumlah kredit atau sebesar Rp. 0,- ( Rupiah)</div>
        <div style="margin-left: 20px; margin-bottom: 5px;">(b) Biaya Administrasi kredit sebesar Rp. 0,- ( Rupiah)</div>
        <div style="margin-left: 20px; margin-bottom: 5px;">(c) Biaya Notaris '.number_format($data_baru['notaris'], 2, ",", ".").' ('.$data_baru['tnotaris'].')</div>
        <div style="margin-left: 20px; margin-bottom: 5px;">(d) Biaya Asuransi Jiwa Peminjam '.number_format($data_baru['asjw'], 2, ",", ".").' ('.$data_baru['tasjw'].')</div>
        <div style="margin-left: 20px; margin-bottom: 10px;">(e) Jumlah Total biaya '.number_format($data_baru['total'], 2, ",", ".").' ( '.$data_baru['ttotal'].' ) yang dibayarkan seketika waktu penerimaan kredit, di debet dari rekening simpanan PEMINJAM;</div>
    </div>

    <div style="margin-top: 20px; margin-bottom: 20px;">
        <div style="font-weight: bold; margin-bottom: 10px;">4. Mengacu Pada Pasal 4</div>
        <div style="margin-left: 20px; margin-bottom: 10px;">Semula Berbunyi</div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">PASAL 4</div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">JANGKA WAKTU, CARA DAN TEMPAT PEMBAYARAN</div>
        
        <div style="margin-bottom: 10px;">(1) Fasilitas Kredit Reguler diberikan untuk jangka waktu '.$data_lama['jw'].' ('.$data_lama['tjw'].') bulan terhitung sejak tanggal '.formatTanggalIndonesia($data_lama['tgl_droping']).' sampai dengan tanggal '.formatTanggalIndonesia($tgl_jt_lama).' dan wajib dibayar dalam '.$data_lama['jw'].' kali angsuran, dan bunga dibayar setiap bulan sebesar Rp. '.number_format($angs_lama, 0, ",", ".").',- ('.$tangs_lama.') Sedangkan pokok di bayar paling lambat pada saat jatuh tempo pada tanggal '.formatTanggalIndonesia($tgl_jt_lama).' Sebesar Rp. '.number_format($data_lama['plafond'], 0, ",", ".").',- ('.$data_lama['tplafond'].')</div>
        
        <div style="margin-bottom: 10px;">(2) Jangka waktu kredit tersebut dalam ayat (1) pasal ini dapat diperpanjang jika memenuhi syarat-syarat serta ketentuan-ketentuan yang akan ditetapkan oleh BANK, dan diajukan secara tertulis oleh PEMINJAM kepada BANK dalam tenggang waktu 12 (dua belas) hari kerja sebelum masa PERJANJIAN kredit berakhir;</div>
        <div style="margin-bottom: 10px;">(3) Dalam hal terjadi jatuh tempo atau saat pembayaran angsuran tidak pada hari kerja BANK, maka PEMINJAM berjani dan dengan ini mengikatkan diri untuk menyediakan dana atau melakukan pembayaran kepada BANK pada 1 (satu) hari kerja</div>
        <div style="margin-bottom: 10px;">(4) Setiap pembayaran kewajiban PEMINJAM kepada BANK dilakukan di Kantor BANK, atau ditempat lain yang ditunjuk BANK secara tunai atau melalui rekening yang dibuka oleh dan atas nama PEMINJAM di BANK</div>
        <div style="margin-bottom: 20px;">(5) Dalam hal pembayaran dilakukan melalui rekening PEMINJAM di BANK, maka dengan ini PEMINJAM memberi kuasa kepada BANK untuk mendebet rekening PEMINJAM dengan nomor rekening '.$data_baru['norek'].', guna pembayaran/pelunasan kewajiban PEMINJAM</div>
        
        <div style="margin-left: 20px; margin-bottom: 10px;">Dirubah Menjadi</div>
        
        <div style="text-align: center; font-weight: bold; margin-bottom: 5px;">PASAL 4</div>
        <div style="text-align: center; font-weight: bold; margin-bottom: 10px;">JANGKA WAKTU, CARA DAN TEMPAT PEMBAYARAN</div>
        
        <div style="margin-bottom: 10px;">(1) Fasilitas Kredit Reguler diberikan untuk jangka waktu '.$data_baru['jw'].' ('.$data_baru['tjw'].') bulan terhitung sejak tanggal '.formatTanggalIndonesia($data_baru['tgl_droping']).'. Pokok Kredit beserta bunga wajib dibayar paling lambat tanggal '.formatTanggalIndonesia($tgl_jt_baru).' sebesar Rp. '.number_format($data_baru['plafond'], 0, ",", ".").'</div>
        
        <div style="margin-bottom: 10px;">(2) Jangka waktu kredit tersebut dalam ayat (1) pasal ini dapat diperpanjang jika memenuhi syarat-syarat serta ketentuan-ketentuan yang akan ditetapkan oleh BANK, dan diajukan secara tertulis oleh PEMINJAM kepada BANK dalam tenggang waktu 12 (dua belas) hari kerja sebelum masa PERJANJIAN kredit berakhir;</div>
        <div style="margin-bottom: 10px;">(3) Dalam hal terjadi jatuh tempo atau saat pembayaran tidak pada hari kerja BANK, maka PEMINJAM berjani dan dengan ini mengikatkan diri untuk menyediakan dana atau melakukan pembayaran kepada BANK pada 1 (satu) hari kerja</div>
        <div style="margin-bottom: 10px;">(4) Setiap pembayaran kewajiban PEMINJAM kepada BANK dilakukan di Kantor BANK, atau ditempat lain yang ditunjuk BANK secara tunai atau melalui rekening yang dibuka oleh dan atas nama PEMINJAM di BANK</div>
        <div style="margin-bottom: 20px;">(5) Dalam hal pembayaran dilakukan melalui rekening PEMINJAM di BANK, maka dengan ini PEMINJAM memberi kuasa kepada BANK untuk mendebet rekening PEMINJAM dengan nomor rekening '.$data_baru['norek'].', guna pembayaran/pelunasan kewajiban PEMINJAM</div>
    </div>

    <div style="margin-top: 20px; margin-bottom: 20px;">
        <div style="font-weight: bold; margin-bottom: 10px;">5. Ketentuan Lainnya</div>
        
        <div style="margin-bottom: 20px;">6. Tiap Addendum dari PERJANJIAN ini merupakan satu kesatuan yang tidak terpisahkan dari PERJANJIAN KREDIT Sebelumnya. Surat PERJANJIAN ini dibuat dan ditanda tangani oleh PEMINJAM dan/atau PENJAMIN dan BANK di atas kertas yang bermaterai cukup dalam 2 (dua) rangkap yang masing-masing dianggap oleh BANK dan PEMINJAM, dan masing-masing mempunyai kekuatan aslinya. Seluruh ketentuan diluar perubahan tersebut diatas dinyatakan tetap berlaku dan mengikat untuk para pihak.</div>
    </div>

    

    <p style="text-align: left; margin-top: 20px;">Demikian addendum ini dibuat dan ditandatangani oleh Para Pihak di '.$row_kantor['kab'].', Pada tanggal '.formatTanggalIndonesia($data_baru['tgl_droping']).'</p>
    
    <table class="no-border" style="margin-top: 10px;">
        <tr>
            <td width="33%" style="text-align: center;"></td>
            <td width="33%" style="text-align: center;"></td>
            <td style="text-align: center;">'.$row_kantor['kab'].', '.formatTanggalIndonesia($data_baru['tgl_droping']).'</td>
        </tr>
        <tr>
            <td style="text-align: center;">Peminjam</td>
            <td style="text-align: center;">'.$hub_pendamping.' Peminjam</td>
            <td style="text-align: center;">'.$row_kantor['nm_perusahaan'].'</td>
        </tr>
        <tr>
            <td style="height: 80px;"></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: center;"><strong>('.$data['nama'].')</strong></td>
            <td style="text-align: center;">'.$ttd_pendamping.'</td>
            <td style="text-align: center;"><strong style="white-space: nowrap;">('.$direktur_lengkap.')</strong><br> Direktur Utama</td>
        </tr>
    </table>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'PK Res Addendum Bullet - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']) . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('PK Res Addendum Bullet - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']));
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
