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
            font-size:9pt;
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
            <td width="40%">NO : ' . $data['nospp'] . '/ADM/SPP/' . $data['bln_rmwi'] . '/' . date('Y', strtotime($data['tgl_droping'])) . '</td>
            <td width="20%"></td>
            <td width="40%" style="text-align: right;">' . $row_kantor['kab'] . ', ' . formatTanggalIndonesia($data['tgl_acc_direksi']) . '</td>
        </tr>
        <tr>
            <td>Kepada Yth.</td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td colspan="3">Bapak/Ibu: ' . $data['nama'] . '</td>
        </tr>
        <tr>
            <td colspan="3">' . $data['almt'] . '</td>
        </tr>
        <tr>
            <td colspan="3">KEC. ' . $data['kec'] . ' KAB. ' . $data['kab'] . '</td>
        </tr>
    </table>

    <p style=": 10px;"><strong>Perihal : Persetujuan Fasilitas Kredit</strong></p>

    <p>Dengan Hormat</p>
    <p>Menindaklanjuti permohonan Fasilitas Kredit Bapak/Ibu kepada ' . $row_kantor['nm_perusahaan'] . ' Kantor ' . $row_kantor['nm_kantor'] . ' tanggal ' . formatTanggalIndonesia($data['tglpeng']) . ', maka dengan ini kami sampaikan, bahwa pada prinsipnya permohonan tersebut telah disetujui pada tanggal ' . formatTanggalIndonesia($data['tgl_droping']) . ' dengan ketentuan sebagai berikut :</p>

    <table class="no-border">
        <tr>
            <td width="30%">Fasilitas Kredit</td>
            <td width="5%">:</td>
            <td width="65%">' . $data['cara'] . '</td>
        </tr>
        <tr>
            <td>Plafond</td>
            <td>:</td>
            <td>Rp. ' . number_format($data['plafond'], 0, ',', '.') . ',- (' . ucwords($data['tplafond']) . ' Rupiah)</td>
        </tr>
        <tr>
            <td>Suku Bunga</td>
            <td>:</td>
            <td>' . $data['sukbung'] . '% (' . ucwords($data['tsukbung']) . ') / ' . $data['metode'] . ' Pertahun</td>
        </tr>
        <tr>
            <td>Administrasi</td>
            <td>:</td>
            <td>Rp. ' . number_format($data['adm'], 0, ',', '.') . ',- (' . ucwords($data['tadm']) . ' Rupiah)</td>
        </tr>
        <tr>
            <td>Provisi</td>
            <td>:</td>
            <td>' . $data['prov'] . '% (' . ucwords($data['tprov']) . ' Persen)</td>
        </tr>
        <tr>
            <td>Jangka Waktu</td>
            <td>:</td>
            <td>' . $data['jw'] . ' (' . ucwords($data['tjw']) . ') Bulan</td>
        </tr>
        <tr>
            <td>Pengikatan Kredit</td>
            <td>:</td>
            <td>' . $data['peng_kredit'] . '</td>
        </tr>
        <tr>
            <td>Jaminan</td>
            <td>:</td>
            <td>';

    // Tambahkan jaminan berdasarkan data yang ada
    $jaminan_added = false;

    // Cek jaminan SHM
    if (mysqli_num_rows($query_shm) > 0) {
        $i = 1;
        while ($shm = mysqli_fetch_assoc($query_shm)) {
            $html .= ($jaminan_added ? '<br>' : '') . $i . ' SHM dengan no ' . $shm['bukkep'] . ' dengan pengikatan ' . $shm['pengikatan'];
            $jaminan_added = true;
            $i++;
        }
    }

    // Cek jaminan BPKB
    if (mysqli_num_rows($query_bpkb) > 0) {
        $i = 1;
        while ($bpkb = mysqli_fetch_assoc($query_bpkb)) {
            $html .= ($jaminan_added ? '<br>' : '') . $i . ' BPKB dengan no ' . $bpkb['bpkb'] . ' dengan pengikatan ' . $bpkb['pengikatan'];
            $jaminan_added = true;
            $i++;
        }
    }

    // Cek jaminan AJB
    if (mysqli_num_rows($query_ajb) > 0) {
        $i = 1;
        while ($ajb = mysqli_fetch_assoc($query_ajb)) {
            $html .= ($jaminan_added ? '<br>' : '') . $i . ' AJB dengan no ' . $ajb['noajb'] . ' dengan pengikatan ' . $ajb['pengikatan'];
            $jaminan_added = true;
            $i++;
        }
    }

    // Jika tidak ada jaminan yang ditemukan
    if (!$jaminan_added) {
        $html .= '-';
    }

    $html .= '</td>
        </tr>
    </table>

    <p style="margin-top: 10px;">Angsuran Per Bulan : Rp. ' . number_format($data['angbung'], 0, ',', '.') . ',- (' . ucwords($data['tangbung']) . ')<br>Terhadap Kredit Ini Dikenakan Biaya-biaya sbb :</p>

    <table class="no-border">
        <tr>
            <td width="30%" class="indent">Provisi</td>
            <td width="5%"></td>
            <td width="30%" style="text-align: right;">' . number_format($data['nomprov'], 0, ',', '.') . ',-</td>
            <td width="35%"></td>
        </tr>
        <tr>
            <td class="indent">Administrasi</td>
            <td></td>
            <td style="text-align: right;">' . number_format($data['adm'], 0, ',', '.') . ',-</td>
            <td></td>
        </tr>
        <tr>
            <td class="indent">Asuransi</td>
            <td></td>
            <td style="text-align: right;">' . number_format($data['asjw'], 0, ',', '.') . ',-</td>
            <td></td>
        </tr>
        <tr>
            <td class="indent">Ass Kend</td>
            <td></td>
            <td style="text-align: right;">' . number_format($data['asken'], 0, ',', '.') . ',-</td>
            <td></td>
        </tr>
        <tr>
            <td class="indent">Notaris</td>
            <td></td>
            <td style="text-align: right;">' . number_format($data['notaris'] ?? 0, 0, ',', '.') . ',-</td>
            <td></td>
        </tr>
        <tr>
            <td class="indent">Materai</td>
            <td></td>
            <td style="text-align: right;">' . number_format($data['materai'], 0, ',', '.') . ',-</td>
            <td></td>
        </tr>
        <tr>
            <td class="indent">Total</td>
            <td></td>
            <td style="text-align: right; border-top: 1px solid black;">' . number_format($data['total'], 0, ',', '.') . ',-</td>
            <td></td>
        </tr>
    </table>

    <p style="margin-top: 10px;">Persyaratan yang harus dipenuhi :</p>
    <p>- Penanda tanganan Perjanjian Kredit oleh Suami dan Istri<br>
    - Pemilik jaminan (suami dan istri) ikut menandatangani Pengikatan Kredit dan Jaminan<br>
    - Bukti kepemilikan jaminan harap dibawa<br>
    - KTP harap dibawa<br>
    - Kredit bisa cair setelah syarat administrasi lengkap.</p>

    <p>Sehubungan dengan hal tersebut maka kami mengharapkan kehadirannya pada :</p>
    <table class="no-border">
        <tr>
            <td width="30%">Hari/Tanggal</td>
            <td width="5%">:</td>
            <td width="65%">' . $nama_hari . ', ' . formatTanggalIndonesia($data['tgl_droping']) . '</td>
        </tr>
        <tr>
            <td>Jam</td>
            <td>:</td>
            <td>' . $data['jam_droping'] . '</td>
        </tr>
        <tr>
            <td>Tempat</td>
            <td>:</td>
            <td>' . $row_kantor['nm_perusahaan'] . ' Kantor ' . $row_kantor['nm_kantor'] . '</td>
        </tr>
    </table>

    <p>Demikian hal ini kami sampaikan atas perhatian dan kerjasamanya diucapkan terima kasih.</p>

    <table class="no-border" style="margin-top: 10px;">
        <tr>
            <td width="50%" style="text-align: center;">Hormat Kami</td>
            <td width="50%" style="text-align: center;">Peminjam</td>
        </tr>
        <tr>
            <td style="height: 45px;"></td>
            <td></td>
        </tr>
        <tr>
            <td style="text-align: center;"><strong><u>' . $direktur_lengkap . '</u></strong><br>Direktur Utama</td>
            <td style="text-align: center;"><strong><u>' . $data['nama'] . '</u></strong></td>
        </tr>
    </table>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'SPPK Sliding - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']) . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('SPPK Sliding - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']));
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