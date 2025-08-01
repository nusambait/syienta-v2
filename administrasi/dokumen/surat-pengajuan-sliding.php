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
    $query_kantor = "SELECT nm_kantor, kab, almt, short FROM kantor WHERE kd_kantor = '$kd_kantor'";
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

    $totalbunga = round($data['angbung'] * $data['jw']);
    $totalangsuran = round($data['plafond'] + $totalbunga);

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
            font-size: 9pt;
            padding: 1px !important;
        }
        .logo {
            width: 150px;
            position: absolute;
            top: 10px;
            left: 10px;
            margin-bottom: 30px;
        }
    </style>
    <div class="container">
        <img src="logo.png" class="logo">
        <table width="100%">
            <tr>
                <td style="text-align: left;">Nomor : ' . $data['nosurat'] . '-/BPR-TS/' . $row_kantor['short'] . '/ADM-KRD/' . $data['bln_rmwi'] . '/' . date('Y', strtotime($data['tgl_droping'])) . '</td>
                <td style="text-align: right;">' . $row_kantor['kab'] . ', ' . $tanggal_dropping . '</td>
            </tr>
        </table>

        <div class="mb-4">
            <p class="mb-0">Kepada Yth.<br>
            Direksi PT. BPR Nusamba Tanjungsari<br>
            ' . $row_kantor['almt'] . '<br>
            Up Bpk. ' . $direktur_lengkap . '</p>
        </div>

        <div class="mb-4">
            <strong><u>Perihal : Permohonan Persetujuan Kredit diatas BWMK</u></strong>
        </div>

        <div class="mb-4">
            <p>Dengan Hormat,</p>
            <p class="text-justify">Dengan ini kami mengajukan permohonan persetujuan kredit diatas BWMK, dengan Keterangan sebagai berikut :</p>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-borderless">
                <tr>
                    <td width="180">Nama Peminjam</td>
                    <td width="10">:</td>
                    <td>' . $data['nama'] . '</td>
                </tr>
                <tr>
                    <td>Alamat</td>
                    <td>:</td>
                    <td>' . $data['almt'] . '</td>
                </tr>
                <tr>
                    <td>Kec - Kab</td>
                    <td>:</td>
                    <td>Kec. ' . $data['kec'] . ' - ' . $data['kab'] . '</td>
                </tr>
                <tr>
                    <td>Pekerjaan</td>
                    <td>:</td>
                    <td>' . $data['pek'] . '</td>
                </tr>
                <tr>
                    <td>Ibu Kandung</td>
                    <td>:</td>
                    <td>' . $data['ibu'] . '</td>
                </tr>
            </table>
        </div>

        <div class="mb-3">
            <p>Keterangan Fasilitas Kredit yang kami ajukan adalah sebagai berikut:</p>
        </div>

        <div class="table-responsive mb-4">
            <table class="table table-borderless">
                <tr>
                    <td width="180">Jenis Kredit</td>
                    <td width="10">:</td>
                    <td>' . $data['jns_kredit'] . '</td>
                </tr>
                <tr>
                    <td>Plafond Kredit</td>
                    <td>:</td>
                    <td>Rp. ' . number_format($data['plafond'], 0, ',', '.') . ',- (' . $data['tplafond'] . ')</td>
                </tr>
                <tr>
                    <td>Jangka Waktu</td>
                    <td>:</td>
                    <td>' . $data['jw'] . ' Bulan</td>
                </tr>
                <tr>
                    <td>Suku Bunga</td>
                    <td>:</td>
                    <td>' . $data['sukbung'] . ' % P.a. ' . $data['metode'] . '</td>
                </tr>
                <tr>
                    <td>Provisi</td>
                    <td>:</td>
                    <td>' . $data['prov'] . ' %</td>
                </tr>
                <tr>
                    <td>Administrasi</td>
                    <td>:</td>
                    <td>Rp. ' . number_format($data['adm'], 0, ',', '.') . ',-</td>
                </tr>
                <tr>
                    <td>Keterangan Jaminan</td>
                    <td>:</td>
                    <td>';
    
    // Reset semua pointer query sebelum menampilkan keterangan jaminan
    mysqli_data_seek($query_shm, 0);
    mysqli_data_seek($query_bpkb, 0);
    mysqli_data_seek($query_ajb, 0);
    mysqli_data_seek($query_kios, 0);
    mysqli_data_seek($query_bilyet, 0);
    mysqli_data_seek($query_manulife, 0);
    mysqli_data_seek($query_bpih, 0);
    mysqli_data_seek($query_spph, 0);

    // Tampilkan SHM jika ada
    $jaminan_counter = 1;
    while ($shm = mysqli_fetch_assoc($query_shm)) {
        $html .= $jaminan_counter . '. ' .
            $shm['jenjam'] . " " .
            $shm['bukkep'] . " " .
            $shm['suruk'] . " " .
            $shm['almt'] . " " .
            $shm['tak'] . " " .
            $shm['pengikatan'] . " " .
            $shm['ketjam'] . "<br>";
        $jaminan_counter++;
    }

    // Tampilkan BPKB jika ada
    while ($bpkb = mysqli_fetch_assoc($query_bpkb)) {
        $html .= $jaminan_counter . '. BPKB ' .
            $bpkb['merk'] . " " .
            "No. Pol " . $bpkb['nopol'] . " " .
            "No. Rangka " . $bpkb['norang'] . " " .
            "No. Mesin " . $bpkb['nomes'] . " " .
            "Tahun " . $bpkb['thnpem'] . " " .
            "Warna " . $bpkb['war'] . " " .
            "An. " . $bpkb['an'] . " " .
            $bpkb['pengikatan'] . " " .
            $bpkb['ketjam'] . "<br>";
        $jaminan_counter++;
    }

    // Tampilkan AJB jika ada
    while ($ajb = mysqli_fetch_assoc($query_ajb)) {
        $html .= $jaminan_counter . '. ' .
            $ajb['jenjam'] . " " .
            $ajb['bukkep'] . " " .
            "Persil " . $ajb['persil'] . " " .
            "Kohir " . $ajb['kohir'] . " " .
            "Luas " . $ajb['lt'] . " m² " .
            "An. " . $ajb['an'] . " " .
            "Blok " . $ajb['blok'] . " " .
            "NJOP " . number_format($ajb['njop'], 0, ',', '.') . " " .
            $ajb['pengikatan'] . " " .
            $ajb['ketjam'] . "<br>";
        $jaminan_counter++;
    }

    // Tampilkan Kios jika ada
    while ($kios = mysqli_fetch_assoc($query_kios)) {
        $html .= $jaminan_counter . '. ' .
            $kios['jenjam'] . " " .
            $kios['bukkep'] . " " .
            "Ukuran " . $kios['ukuran'] . " " .
            "Blok " . $kios['blok'] . " " .
            "An. " . $kios['an'] . " " .
            "Alamat " . $kios['almt'] . " " .
            "Taksasi " . number_format($kios['tak'], 0, ',', '.') . " " .
            $kios['pengikatan'] . " " .
            $kios['ketjam'] . "<br>";
        $jaminan_counter++;
    }

    // Tampilkan Bilyet jika ada
    while ($bilyet = mysqli_fetch_assoc($query_bilyet)) {
        $html .= $jaminan_counter . '. ' .
            $bilyet['jenjam'] . " " .
            "No. Rek " . $bilyet['norek'] . " " .
            "No. Bilyet " . $bilyet['nobuk'] . " " .
            "Nominal " . number_format($bilyet['nom'], 0, ',', '.') . " " .
            "Jatuh Tempo " . date('d-m-Y', strtotime($bilyet['tgljthtempo'])) . " " .
            "An. " . $bilyet['an'] . " " .
            $bilyet['pengikatan'] . " " .
            $bilyet['ketjam'] . "<br>";
        $jaminan_counter++;
    }

    // Tampilkan Manulife jika ada
    while ($manulife = mysqli_fetch_assoc($query_manulife)) {
        $html .= $jaminan_counter . '. ' .
            "No. " . $manulife['nojam'] . " " .
            $manulife['jendok'] . " " .
            "An. " . $manulife['nadok'] . " " .
            "Tgl " . date('d-m-Y', strtotime($manulife['tgldok'])) . " " .
            $manulife['pengikatan'] . " " .
            $manulife['ketjam'] . "<br>";
        $jaminan_counter++;
    }

    // Tampilkan BPIH jika ada
    while ($bpih = mysqli_fetch_assoc($query_bpih)) {
        $html .= $jaminan_counter . '. BPIH ' .
            "No. Val " . $bpih['noval'] . " " .
            "No. Rek " . $bpih['norek'] . " " .
            "Tgl " . date('d-m-Y', strtotime($bpih['tgl_surat'])) . " " .
            "An. " . $bpih['an'] . "<br>";
        $jaminan_counter++;
    }

    // Tampilkan SPPH jika ada
    while ($spph = mysqli_fetch_assoc($query_spph)) {
        $html .= $jaminan_counter . '. SPPH ' .
            "No. Porsi " . $spph['nopor'] . " " .
            "No. Val " . $spph['noval'] . " " .
            "Tgl Surat " . date('d-m-Y', strtotime($spph['tgl_surat'])) . " " .
            "Kemenag " . $spph['kemenag'] . " " .
            "An. " . $spph['an'] . "<br>";
        $jaminan_counter++;
    }
    $html .= '</td>
                </tr>
                <tr>
                    <td>Total Nilai Taksasi</td>
                    <td>:</td>
                    <td>Rp. ' . number_format($total_nilai_taksasi, 0, ',', '.') . ',-</td>
                </tr>
                <tr>
                    <td>Pengikatan Kredit</td>
                    <td>:</td>
                    <td>' . $data['peng_kredit'] . '</td>
                </tr>
                <tr>
                    <td>Pengikatan Jaminan</td>
                    <td>:</td>
                    <td>';
    // Tampilkan pengikatan jaminan SHM
    $pengikatan_counter = 1;
    mysqli_data_seek($query_shm, 0);
    while ($shm = mysqli_fetch_assoc($query_shm)) {
        if (!empty($shm['pengikatan'])) {
            $html .= $pengikatan_counter . '. ' . $shm['pengikatan'] . '<br>';
            $pengikatan_counter++;
        }
    }

    // Tampilkan pengikatan jaminan BPKB
    mysqli_data_seek($query_bpkb, 0);
    while ($bpkb = mysqli_fetch_assoc($query_bpkb)) {
        if (!empty($bpkb['pengikatan'])) {
            $html .= $pengikatan_counter . '. ' . $bpkb['pengikatan'] . '<br>';
            $pengikatan_counter++;
        }
    }

    // Tampilkan pengikatan jaminan lainnya (AJB, Kios, dll)
    $other_queries = array($query_ajb, $query_kios, $query_bilyet, $query_manulife);
    foreach ($other_queries as $query) {
        mysqli_data_seek($query, 0);
        while ($row = mysqli_fetch_assoc($query)) {
            if (!empty($row['pengikatan'])) {
                $html .= $pengikatan_counter . '. ' . $row['pengikatan'] . '<br>';
                $pengikatan_counter++;
            }
        }
    }
    $html .= '</td>
                </tr>
                <tr>
                    <td valign="top">Penandatanganan PK</td>
                    <td valign="top">:</td>
                    <td>' . $data['nama'] . ' (PEMINJAM)<br>';

    // Tampilkan data pendamping
    while ($pendamping = mysqli_fetch_assoc($result_pendamping)) {
        $html .= $pendamping['nama'] . ' (' . $pendamping['hub'] . ' Peminjam)<br>';
    }

    // Tampilkan data penjamin
    while ($penjamin = mysqli_fetch_assoc($result_penjamin)) {
        $html .= $penjamin['nama'] . ' (Penjamin)<br>';
    }

    $html .= '</td>
                </tr>
                <tr>
                    <td>Pembayaran Pokok</td>
                    <td>:</td>
                    <td>Rp. ' . number_format($data['plafond'], 0, ',', '.') . ',-</td>
                </tr>
                <tr>
                    <td>Angsuran Bunga</td>
                    <td>:</td>
                    <td>Rp. ' . number_format($data['angbung'], 0, ',', '.') . ',- (+) Rp. ' . number_format($totalbunga, 0, ',', '.') . ',-</td>
                </tr>
                <tr>
                    <td>Total Angsuran</td>
                    <td>:</td>
                    <td>Rp. ' . number_format($totalangsuran, 0, ',', '.') . ',-</td>
                </tr>
                <tr>
                    <td>Keterangan lain-lain</td>
                    <td>:</td>
                    <td>' . $data['ketbwmk'] . '</td>
                </tr>
            </table>
        </div>

        <div class="mb-4">
            <p class="text-justify">Permohonan kredit tersebut adalah merupakan kredit ke ' . $data['pengajuan'] . ' di PT. BPR Nusamba Tanjungsari.<br>
            Demikian Permohonan Kredit ini kami ajukan, atas persetujuannya kami ucapkan terima kasih.</p>
        </div>

        <div class="mt-5">
            <p class="mb-1">PT. BPR NUSAMBA TANJUNGSARI<br>
            ' . $row_kantor['nm_kantor'] . '</p>
            <p class="mb-0" style="margin-top: 50px;"><strong><u>' . $direktur_lengkap . '</u></strong><br>
            Direktur Utama</p>
        </div>
    </div>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'Surat Pengajuan BWMK Sliding - ' . $data['nama'] . ' ' . $tanggal_dropping . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('Surat Pengajuan BWMK Sliding - ' . $data['nama'] . ' ' . $tanggal_dropping);
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
