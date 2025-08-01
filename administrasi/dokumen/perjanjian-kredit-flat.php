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
    function formatTanggalIndonesia($tanggal) {
        global $bulanIndonesia;
        $tanggal_format = date('d F Y', strtotime($tanggal));
        return str_replace(array_keys($bulanIndonesia), array_values($bulanIndonesia), $tanggal_format);
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

    // Tambahkan query untuk mengambil data pendamping dan penjamin
    $query_pendamping = "SELECT * FROM pendamping WHERE status = '$noreg'";
    $result_pendamping = mysqli_query($connect, $query_pendamping);

    $query_penjamin = "SELECT * FROM penjamin WHERE niknas = '$data[nik]'";
    $result_penjamin = mysqli_query($connect, $query_penjamin);

    $kd_kantor = $_SESSION['kantor']; // Ambil kd_kantor dari session
    $query_pkno1 = "SELECT kode, isi FROM pkno1 WHERE kode = '$kd_kantor'";
    $result_pkno1 = mysqli_query($connect, $query_pkno1);
    $row_pkno1 = mysqli_fetch_assoc($result_pkno1);

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
        /* Tambahkan style untuk text justified */
        p, ol, li {
            text-align: justify;
        }
        .text-justify {
            text-align: justify;
        }
    </style>
    <div class="container">
        <img src="logo.png" class="logo">
        <div class="header-text">
            <strong>'.strtoupper($row_kantor['nm_perusahaan']).'</strong><br>
            KANTOR '.$row_kantor['nm_kantor'].'<br>
            '.$row_kantor['almt'].'
        </div>
        <hr>
    <div style="text-align: center; margin-bottom: 0px;">
        <h3><u>PERJANJIAN KREDIT</u></h3>
        <p style="text-align: center; margin-bottom: 20px;">Nomor : ' . $data['noloan'] . '/BPR-TS/' . $row_kantor['short'] . '/ADM-KRD/' . $data['bln_rmwi'] . '/' . date('Y', strtotime($data['tgl_droping'])) . '</p>
    </div>

    <p>Yang bertanda tangan dibawah ini:</p>
    <ol>
        <li>' . $row_pkno1['isi'] . '</li>
        <li>' . $data['nama'] . ' Umur ' . $data['usia'] . ' Tahun Pekerjaan ' . $data['pek'] . ' bertempat tinggal di ' . $data['almt'] . ' pemegang Kartu Tanda Penduduk Nomor ' . $data['nik'] . ' selanjutnya disebut PEMINJAM, yang
            mana dalam melakukan tindakan hukumnya dalam hal ini bertindak atas nama diri sendiri Yang turut
            serta membubuhkan tanda tangan dalam PERJANJIAN kredit ini.</li>
    </ol>

    <p>Dalam PERJANJIAN kredit ini BANK, PEMINJAM dan/atau PENJAMIN dapat juga disebut sebagai PARA PIHAK.</p>

    <p>Bahwa PEMINJAM telah mengajukan permohonan pinjam uang secara tertulis kepada BANK tanggal ' . formatTanggalIndonesia($data['tglpeng']) . ' dan BANK telah memberi persetujuan secara tertulis pada tanggal ' . formatTanggalIndonesia($data['tgl_droping']) . '. Selanjutnya ketentuan pokok yang telah disepakati PEMINJAM, selanjutnya ketentuan pokok tersebut akan diuraikan lebih lanjut di dalam PERJANJIAN Kredit ini dengan syarat-syarat dan ketentuan sebagai berikut:</p>

    <div style="text-align: center; margin: 5px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 1</h4>
        <h4 style="margin-top: 0;">POKOK PERJANJIAN</h4>
    </div>

    BANK telah memberikan kepada PEMINJAM:
    <ol>
        <li>Fasilitas kredit sebesar Rp. ' . number_format($data['plafond'], 0, ',', '.') . ',- (Rupiah) dan PEMINJAM menyatakan menerima fasilitas kredit tersebut yang terdiri dari:
            <ol type="a">
                <li>KREDIT FLAT sebesar Rp. ' . number_format($data['plafond'], 0, ',', '.') . ',- (Rupiah)</li>
                <li>KREDIT REGULAR Modal Kerja/Investasi/Konsumsi *) sebesar Rp .........................(terbilang.........................)</li>
            </ol>
        </li>
        <li>Terhadap fasilitas kredit tersebut dalam ayat (1) pasal ini dapat ditarik seketika oleh PEMINJAM pada saat penandatanganan PERJANJIAN kredit</li>
        <li>Penarikan sebagaimana dimaksud dalam ayat (2) pasal ini dilakukan dengan menerbitkan bukti pengambilan uang (Kwitansi);</li>
    </ol>
   
    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 2</h4>
        <h4 style="margin-top: 0;">BUNGA DAN ANGSURAN</h4>
    </div>

    BANK dan PEMINJAM bersepakat :
    <ol>
        <li>Bunga atas fasilitas kredit sebesar ' . $data['sukbung'] . '% (' . $data['tsukbung'] . ' Persen) ' . $data['cara'] . ' per tahun terhitung sejak tanggal penarikan fasilitas kredit oleh PEMINJAM hingga fasilitas kredit tersebut lunas</li>
        <li>Pembayaran angsuran kredit dilakukan oleh PEMINJAM kepada BANK pada tanggal ' . date('d', strtotime($data['tgl_droping'])) . ' setiap bulan berjalan melalui rekening simpanan PEMINJAM, dan oleh karenanya BANK berhak melakukan debet secara otomatis;</li>
        <li>Dalam hal pembayaran angsuran pokok dan bunga dilakukan oleh PEMINJAM secara tunai, maka BANK melakukan pencatatan sebagaimana mestinya;</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 3</h4>
        <h4 style="margin-top: 0;">BIAYA-BIAYA</h4>
    </div>

    <ol>
        <li>PEMINJAM berjanji dan dengan ini mengikatkan diri untuk menanggung seluruh biaya yang diperlukan berkenaan dengan pelaksanaan PERJANJIAN ini, sepanjang hal itu diberitahukan BANK kepada PEMINJAM sebelum ditandatanganinya PERJANJIAN ini, dan PEMINJAM menyatakann persetujuannya;</li>
        <li>Adapun biaya-biaya yang dimaksud pada ayat (1) pasal ini adalah:
            <ol type="a">
                <li>Provisi kredit sebesar '. $data['prov'] .' % ( '. $data['tprov'] .' Persen ) dari jumlah kredit atau sebesar Rp. '. number_format($data['nomprov'], 0, ',', '.') .' ('. $data['tprov'] .' Persen)</li>
                <li>Biaya Administrasi kredit sebesar Rp. '. number_format($data['adm'], 0, ',', '.') .' ('. $data['tadm'] .')</li>
                <li>Biaya Asuransi Jiwa Peminjam Rp. '. number_format($data['asjw'], 0, ',', '.') .' ('. $data['tasjw'] .')</li>
            </ol>
            Jumlah Total biaya Rp. '. number_format($data['total'], 0, ',', '.') .' ( '. $data['ttotal'] .') yang dibayarkan
            <li>Segala pajak yang timbul sehubungan dengan PERJANJIAN ini merupakan tanggung jawab dan wajib dibayar oleh PEMINJAM, kecuali Pajak Penghasilan BANK</li>
        </li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 4</h4>
        <h4 style="margin-top: 0;">JANGKA WAKTU, CARA DAN TEMPAT PEMBAYARAN</h4>
    </div>

    <ol>
        <li>Fasilitas Kredit Installment diberikan untuk jangka waktu 36 (Tiga Puluh Enam) bulan terhitung sejak tanggal 22 Desember 2023 sampai tanggal 22 Desember 2025 dan wajib dibayar dalam 36 kali angsuran pokok dan bunga setiap bulan sebesar Rp. 1.659.722,- (Satu Juta Enam Ratus Lima Puluh Sembilan Ribu Tujuh Ratus Dua Puluh Dua Rupiah)</li>
        <li>Jangka waktu kredit tersebut dalam ayat (1) pasal ini dapat diperpanjang jika memenuhi syarat-syarat serta ketentuan-ketentuan yang akan ditetapkan oleh BANK, dan diajukan secara tertulis oleh PEMINJAM kepada BANK dalam tenggang waktu 12 (dua belas) hari kerja sebelum masa PERJANJIAN kredit berakhir;</li>
        <li>Dalam hal tanggal jatuh tempo atau saat pembayaran angsuran tidak pada hari kerja BANK, maka PEMINJAM berjani dan dengan ini memerintahkan diri untuk menyediakan dana atau melakukan pembayaran kepada BANK pada 1 (satu) hari kerja sebelumnya;</li>
        <li>Setiap pembayaran kewajiban PEMINJAM kepada BANK dilakukan di Kantor BANK,atau ditempat lain yang ditunjuk BANK secara tunai atau melalui rekening yang dibuka oleh dan atas nama PEMINJAM di BANK;</li>
        <li>Dalam hal pembayaran dilakukan melalui rekening PEMINJAM di BANK, maka dengan ini PEMINJAM memberi kuasa kepada BANK untuk mendebet rekening PEMINJAM dengan nomor rekening 1000247242, guna pembayaran pelunasan kewajiban PEMINJAM;</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 5</h4>
        <h4 style="margin-top: 0;">JAMINAN</h4>
    </div>

    <p>Guna menjamin ketetiban pembayaran/kewajiban PEMINJAM kepada BANK tepat pada waktu yang telah disepakati oleh BANK dan PEMINJAM berdasarkan PERJANJIAN ini, maka PEMINJAM dan/atau PENJAMIN berarti dan dengan ini mengikatkan diri untuk membuat dan menandatangani pengikatan jaminan serta menyerahkan barang jaminannya kepada BANK sesuai dengan peraturan perundang-undangan yang berlaku, yang merupakan bagian yang tidak terpisahkan dari PERJANJIAN ini. Jenis barang jaminan yang diserahkan adalah berupa:</p>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 6</h4>
        <h4 style="margin-top: 0;">PERISTIWA CIDERA JANJI</h4>
    </div>

    <p>Menyimpang dari ketentuan dalam Pasal 4 PERJANJIAN ini, BANK berhak untuk menuntut/menagih pembayaran dari PEMINJAM atau siapa pun juga yang memperoleh hal darinya, atas sebagian atau seluruh pembayaran/kewajiban PEMINJAM kepada BANK berdasarkan PERJANJIAN ini, untuk dibayar dengan seketika dan sekaligus, tanpa diperlukan adanya surat pemberitahuan, surat tegoran, atau surat lainnya, apabila terjadi salahsatu hal atau peristiwa tersebut di bawah ini :</p>
    <ol>
        <li>PEMINJAM tidak melaksanakan kewajiban pembayaran/pelunasan tepat pada waktu yang diperjanjikan sesuai dengan tanggal jatuh tempo dan jadwal angsuran yang ditetapkan dan keterlambatan tersebut disebabkan karena kelalaian PEMINJAM, kecuali PEMINJAM dalam keadaan force majeure (disebabkan karena bencana alam seperti gempa bumi, kebanjiran, tanah longsor, kebakaran) apabila terjadi keadaan force majeure maka para pihak akan melakukan kesepakatan untuk menyelesaikan permasalahan yang dihadapi</li>
        <li>Dokumen atau keterangan yang dimasukkan/diserahkan PEMINJAM kepada BANK sebagaimana yang disebutkan dalam pasal 5 PERJANJIAN ini adalah palsu, tidak sah, atau tidak benar;</li>
        <li>PEMINJAM tidak memenuhi dan/atau melanggar salah satu ketentuan atau lebih sebagaimana ketentuan-ketentuan yang tercantum dalam Pasal 9 PERJANJIAN ini;</li>
        <li>Apabila karena sesuatu sebab, sebagian atau seluruh Akta Jaminan dinyatakan batal atau dibatalkan berdasarkan Putusan Pengadilan atau Badan Arbitrase;</li>
        <li>Apabila PEMINJAM dalam PERJANJIAN ini menjadi pemboros, pemabuk, atau dihukum berdasarkan Putusan Pengadilan yang telah berkekuatan tetap dan pasti (inkracht van gewijsde) karena tindak pidana yang dilakukannya, yang diancam dengan hukuman penjara atau kurungan selama satu tahun atau lebih;</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 7</h4>
        <h4 style="margin-top: 0;">AKIBAT CIDERA JANJI</h4>
    </div>

    <ol>
        <li>Dalam hal PEMINJAM cidera janji sebagaimana diatur dalam pasal 2 ayat (2) dan pasal 4 ayat (1), atau ayat (2), atau ayat (3) PERJANJIAN ini, BANK berhak melakukan tegoran atau peringatan lisan ataupun tertulis kepada PEMINJAM, dan BANK berhak menyerahkan barang jaminan atau agunan tersebut dalam pasal 5 PERJANJIAN ini, kepada pihak yang berwenang untuk dilakukan penjualan dan atau pelelangan.</li>
        <li>Terhadap hasil penjualan jaminan atau agunan tersebut dalam ayat (1) pasal ini, BANK mempunyai kedudukan Istimewa atau Hak didahulukan (privilleges) untuk mendapat pelunasan atas hutang pokok, bunga dan segala biaya yang timbul akibat dari adanya PERJANJIAN kredit ini antara lain biaya lelang, biaya perkara di Pengadilan, biaya operasional lainnya yang semuanya akan dibebankan dan menjadi tanggungan pihak PEMINJAM. Adapun jumlahnya akan diperhitungkan dan ditetapkan bersama-samaantara BANK dengan PEMINJAM, apabila tidak tercapai kesepakatan maka PEMINJAM setuju ditetapkan sendiri oleh BANK, yang disertai dengan bukti-bukti yang dapat dipertanggungjawabkan. Adapun biaya-biaya dimaksud akan diambil dari hasil penjualan atau pelelangan jaminan atau agunan;</li>
        <li>Apabila PEMINJAM cidera janji, maka PEMINJAM dan/atau PENJAMIN setuju bahwa BANK berhak untuk melakukan pemasangan papan nama pemberitahuan di lokasi bangunan dan atau tanah agunan dengan tulisan bangunan dan/atau tanah ini dijamin kan di PT. BPR Nusamba Tanjungsari dan atau untuk jaminan berupa kendaraan bank berhak untuk melakukan pengamanan jaminan.</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 8</h4>
        <h4 style="margin-top: 0;">PERNYATAAN DAN PENGAKUAN PEMINJAM</h4>
    </div>

    <p>PEMINJAM dengan ini menyatakan dan mengakui dengan sebenarnya, dan tidak lain dari yang sebenarnya, bahwa PEMINJAM berhak dan berwenang sepenuhnya untuk menandatangani PERJANJIAN ini dan semua surat dan dokumen yang melengkapinya.</p>
    <ol>
        <li>PEMINJAM menjamin bahwa segala dokumen dan akta yang ditandatangani oleh PEMINJAM berkaitan dengan PERJANJIAN ini, keberadaannya tidak melanggar atau bertentangan dengan peraturan perundang-undangan yang berlaku, sehingga karenanya sah, berkekuatan hukum, serta mengikat PEMINJAM dalam menjalankan PERJANJIAN ini, dan demikian pula tidak dapat menghalang-halangi pelaksanaannya;</li>
        <li>Jika PEMINJAM mewakili perusahaan yang berbadan hukum, maka PEMINJAM menjamin, bahwa segala surat dan dokumen serta akta yang PEMINJAM tandatangani dan/atau gunakan berkaitan dengan PERJANJIAN ini adalah benar, keberadaannya sah, tindakan PEMINJAM tidak melanggar atau bertentangan dengan Anggaran Dasar Perusahaan;</li>
        <li>Jika PEMINJAM mewakili perusahaan yang berbadan hukum, PEMINJAM menyatakan,bahwa pada saat penandatanganan PERJANJIAN ini Dewan Komisaris atau Pengawas perusahaan PEMINJAM telah mengetahui dan menyetujui hal-hal yang dilakukan PEMINJAM berkaitan dengan PERJANJIAN ini;</li>
        <li>Dalam hal belum dicukupinya barang jaminan untuk melunasi kewajiban PEMINJAM kepada BANK, PEMINJAM berjanji dan dengan ini mengikatkan diri untuk dari waktu ke waktu selama kewajibannya belum lunas akan menyerahkan kepada BANK, jaminan tambahan yang dinilai cukup oleh BANK;</li>
        <li>Sepanjang tidak bertentangan dengan peraturan perundang-undangan yang berlaku,PEMINJAM berjanji dan dengan ini mengikatkan diri mendahulukan untuk membayar dan melunasi kewajiban PEMINJAM kepada BANK dari kewajiban lainnya;</li>
        <li>Dalam hal hak yang berkaitan dengan ayat (1), (2) dan (3) pasal ini, PEMINJAM berjanji dan dengan ini mengikatkan diri untuk membebaskan BANK dari segala tuntutan atau gugatan yang datang dari pihak manapun dan/atau alasan apapun;</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 9</h4>
        <h4 style="margin-top: 0;">PEMBATASAN TERHADAP TINDAKAN PEMINJAM</h4>
    </div>

    <p>PEMINJAM berjanji dan dengan ini mengikatkan diri, bahwa selama masa berlangsungnya PERJANJIAN ini, kecuali setelah mendapatkan persetujuan tertulis dari BANK, PEMINJAM tidak akan melakukan salah satu, sebagian atau seluruh perbuatan-perbuatan sebagai berikut:</p>
    <ol>
        <li>Membuat utang lain kepada Pihak Ketiga dengan barang jaminan yang telah disebutkan dalam Pasal 5 PERJANJIAN ini;</li>
        <li>Memindahkan kedudukan/lokasi barang maupun barang jaminan dari kedudukan/lokasi barang itu semula atau sepatutnya berada, dan/atau mengalihkan hak atas barang atau barang jaminan yang bersangkutan kepada pihak lain;</li>
        <li>Melakukan akuisisi, merger, restrukturisasi dan/atau konsolidasi perusahaan PEMINJAM dengan perusahaan atau orang lain;</li>
        <li>Menjual, baik sebagian atau seluruh asset perusahaan PEMINJAM yang nyata-nyata akan mempengaruhi kemampuan atau cara membayar atau melunasi kewajiban atau sisa kewajiban PEMINJAM kepada BANK, kecuali menjual barang dagangan yang menjadi kegiatan usaha PEMINJAM;</li>
        <li>Mengubah Anggaran Dasar, susunan pemegang saham, Komisaris dan/atau Direksi perusahaan PEMINJAM;</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 10</h4>
        <h4 style="margin-top: 0;">RISIKO</h4>
    </div>

    <p>PEMINJAM atas beban dan tanggung jawabnya, berkewajiban melakukan pemeriksaan, dan karenanya bertanggung jawab baik terhadap keadaan fisik barang maupun terhadap sahnya bukti-bukti, surat-surat dan/atau dokumen-dokumen yang berkaitan dengan kepemilikan atau hak-hak lainnya atas barang dan barang-barang yang dijaminkan, sehingga karena itu PEMINJAM berjanji dan dengan ini membebaskan BANK dari segala tuntutan atau gugatan yang datang dari pihak manapun dan/atau berdasarkan alasan apapun.</p>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 11</h4>
        <h4 style="margin-top: 0;">PENGAWASAN DAN PEMERIKSAAN</h4>
    </div>

    <p>PEMINJAM berjanji dan dengan ini mengikatkan diri untuk memberikan izin kepada BANK, atau petugas yang ditunjuknya guna melaksanakan pengawasan/pemeriksaan terhadap barang maupun barang jaminan, serta pembukuan dan catatan-catatan pada setiap saat selama berlangsungnya PERJANJIAN ini, dan kepada petugas BANK tersebut diberi hak untuk mengambil gambar (foto), membuat fotocopi dan/atau catatan-catatan yang dianggap perlu.</p>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 12</h4>
        <h4 style="margin-top: 0;">DOMISILI DAN PEMBERITAHUAN</h4>
    </div>

    <ol>
        <li>Alamat BANK dan PEMINJAM sebagaimana yang tercantum pada kalimat-kalimat awal PERJANJIAN ini merupakan alamat tetap dan tidak berubah bagi masing-masing pihak yang bersangkutan, dan ke alamat-alamat itu pula secara sah segala surat menyurat atau komunikasi di antara kedua pihak akan dilakukan;</li>
        <li>Apabila dalam pelaksanaan PERJANJIAN ini terjadi perubahan alamat, maka pihak yang berubah alamatnya tersebut wajib memberitahukan kepada pihak lainnya alamat barunya dengan surat tercatat atau surat tertulis yang disertai tanda bukti penerimaan dari pihaknya;</li>
        <li>Selama tidak ada pemberitahuan tentang perubahan alamat sebagaimana dimaksud padaayat (2) pasal ini, maka surat menyurat atau komunikasi yang dilakukan ke alamat yang tercantum pada awal PERJANJIAN dianggap sah menurut hukum.</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 13</h4>
        <h4 style="margin-top: 0;">DENDA KETERLAMBATAN PEMBAYARAN</h4>
    </div>

    <p>Apabila dalam tenggang waktu berlakunya PERJANJIAN kredit, PEMINJAM tidak dapat melaksanakan kewajibannya tepat waktu dan/atau belum melunasi hutang pokok dan bunga pada saat jatuh tempo kredit berdasarkan PERJANJIAN ini, maka BANK berhak menghitung dan menetapkan denda (pinalty/overdue) sebesar 2 % ( ) perbulan dari seluruh kewajiban PEMINJAM yang tertunggak;</p>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 14</h4>
        <h4 style="margin-top: 0;">BERAKHIRNYA PERJANJIAN</h4>
    </div>

    <p>Berakhirnya PERJANJIAN kredit :</p>
    <ol>
        <li>Apabila semua kewajiban PEMINJAM berupa pokok kredit, bunga dan biaya-biaya yang telah disepakati sebelumnya, dilunasi seketika dan sekaligus oleh PEMINJAM;</li>
        <li>PEMINJAM dinyatakan pailit oleh lembaga peradilan yang berwenang untuk itu;</li>
        <li>PEMINJAM dinyatakan bubar atau dibubarkan dan harta kekayaannya tidak cukup untuk menutupi hutang- hutangnya kepada BANK;</li>
        <li>PEMINJAM dan PENJAMIN meninggal dunia atau ditaruh di bawah perwalian (curatele), atau karena sebab-sebab lainnya yang menyebabkan kehilangan hak untuk mengurus harta bendanya</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 15</h4>
        <h4 style="margin-top: 0;">PENYELESAIAN PERSELISIHAN</h4>
    </div>

    <ol>
    <li>Dalam hal terjadi perbedaan pendapat dalam memahami atau menafsirkan bagian-bagian dari isi, atau terjadi perselisihan dalam pelaksanakan PERJANJIAN ini, maka PEMINJAM dan BANK akan berusaha untuk menyelesaikan secara musyawarah dan mufakat;</li>
    <li>PEMINJAM dinyatakan pailit oleh lembaga peradilan yang berwenang untuk itu;</li>
    <li>PEMINJAM dinyatakan bubar atau dibubarkan dan harta kekayaannya tidak cukup untuk menutupi hutang- hutangnya kepada BANK;</li>
    <li>PEMINJAM dan PENJAMIN meninggal dunia atau ditaruh di bawah perwalian (curatele), atau karena sebab-sebab lainnya yang menyebabkan kehilangan hak untuk mengurus harta bendanya</li>
    <li>Dalam hal usaha menyelesaikan perbedaan pendapat atau perselisihan melalui musyawarah untuk mufakat tidak menghasilkan keputusan yang disepakati oleh kedua belah pihak, maka PEMINJAM dan BANK sepakat menyelesaikan sesuai dengan peraturan perundang-undangan yang berlaku;</li>
    </ol>

    <div style="text-align: center; margin: 0px 0;">
        <h4 style="margin-bottom: 5px;">PASAL 16</h4>
        <h4 style="margin-top: 0;">PENUTUP</h4>
    </div>

    <ol>
        <li>Sebelum PERJANJIAN ini ditandatangani oleh PEMINJAM dan/atau PENJAMIN, PEMINJAM dan/atau PENJAMIN mengakui dengan sebenarnya, bahwa PEMINJAM dan/atau PENJAMIN telah membaca dengan cermat atau dibacakan kepada PEMINJAM dan/atau PENJAMIN seluruh isi PERJANJIAN ini berikut semua surat dan/atau dokumen yang menjadi lampiran PERJANJIAN ini, serta PEMINJAM dan/atau PENJAMIN menyatakan pada saat menandatangani perjanjian ini dalam kondisi sadar dan tanpa paksaan dari pihak manapun sehingga oleh karena itu PEMINJAM dan/atau PENJAMIN memahami sepenuhnya segala yang akan menjadi akibat hukum setelah PEMINJAM dan/atau PENJAMIN menandatangani PERJANJIAN ini;</li>
        <li>PERJANJIAN ini mengikat Para Pihak yang sah, para pengganti atau pihak-pihak yang menerima hak dari masing-masing Para Pihak</li>
        <li>Dalam hal terjadi perubahan dan atau terdapat hal-hal yang belum cukup diatur dalam PERJANJIAN ini, BANK dan PEMINJAM dapat melakukan PERJANJIAN tambahan, baik berupa pernyataan maupun pemberian kuasa, BANK akan mengaturnya bersama secara musyawarah untuk mufakat dalam suatu Addendum.</li>
        <li>Tiap Addendum dari PERJANJIAN ini merupakan satu kesatuan yang tidak terpisahkan dari PERJANJIAN ini.</li>
        <li>Segala kuasa yang diberikan oleh PEMINJAM dan atau PENJAMIN kepada BANK tidak dapat dicabut kembali dan/atau berakhir karena sebab-sebab yang tercantum dalam pasal 1813 Kitab Undang-Undang Hukum Perdata, khususnya apabila PEMINJAM dinyatakan Pailit dan atau meninggal Dunia;</li>
        <li>PERJANJIAN ini telah disesuaikan dengan ketentuan peraturan perundang-undangan termasuk Ketentuan Peraturan Otoritas Jasa Keuangan NOMOR 22 TAHUN 2023 tentang perlindungan konsumen dan masyarakat di sektor jasa keuangan;</li>
        <li>Mengenai PERJANJIAN ini dan segala akibat hukum yang ditimbulkannya, BANK,PEMINJAM dan/atau PENJAMIN sepakat memilih domisili hukum dalam wilayah hukum Pengadilan Negeri</li>
        <li>Surat PERJANJIAN ini dibuat dan ditanda tangani oleh PEMINJAM dan/atau PENJAMIN dan BANK di atas kertas yang bermaterai cukup dalam 2 (dua) rangkap yang masing-masing disimpan oleh BANK dan PEMINJAM, dan masing-masing berlaku sebagai aslinya.</li>
    </ol>

    <div class="mb-4">
        <p class="text-justify">Demikian PERJANJIAN ini dibuat dan ditanda tangani di Kabupaten '.$row_kantor['kab'].' pada hari '.$nama_hari.', '.$tanggal_dropping.'<br>
    </div>

        <div style="margin-top: 40px;">
            <table width="100%">
                <tr>
                    <td width="50%" style="text-align: center;">
                        Peminjam<br><br><br><br><br><br>
                        <strong><u>'.$data['nama'].'</u></strong><br>
                    </td>
                    <td width="50%" style="text-align: center;">
                        '.$row_kantor['kab'].', '.$tanggal_dropping.'<br>
                        Yang diberi kuasa<br><br><br><br><br><br>
                        <strong><u>'.$direktur_lengkap.'</u></strong><br>
                        Direktur Utama
                    </td>
                </tr>
            </table>
        </div>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'Surat Perjanjian Kredit Flat - ' . $data['nama'] . ' ' . $tanggal_dropping . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('Surat Perjanjian Kredit Flat - ' . $data['nama'] . ' ' . $tanggal_dropping);
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
