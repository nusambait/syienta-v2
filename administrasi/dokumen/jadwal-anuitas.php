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
        'margin_left' => 10,
        'margin_right' => 10,
        'margin_top' => 10,
        'margin_bottom' => 10,
        'format' => 'A4',
        'orientation' => 'L', // Ubah ke landscape
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

    // Hitung angsuran dengan metode anuitas
    $sukbung_bulanan = $data['sukbung'] / 12 / 100; // Suku bunga per bulan dalam desimal
    $jw = $data['jw']; // Jangka waktu dalam bulan
    $plafond = $data['plafond']; // Plafond pinjaman
    
    // Rumus anuitas: A = P * r * (1+r)^n / ((1+r)^n - 1)
    $angsuran_anuitas = $plafond * $sukbung_bulanan * pow(1 + $sukbung_bulanan, $jw) / (pow(1 + $sukbung_bulanan, $jw) - 1);
    
    // Sisa pokok awal
    $sisa_pokok = $data['plafond'];

    // Ambil tanggal dropping untuk perhitungan jadwal
    $tanggal_dropping = date('d', strtotime($data['tgl_droping']));
    $bulan_dropping = date('m', strtotime($data['tgl_droping']));
    $tahun_dropping = date('Y', strtotime($data['tgl_droping']));
    
    // Hitung tanggal jatuh tempo
    $tanggal_jatuh_tempo = date('d F Y', strtotime($data['tgl_droping'] . ' + ' . $data['jw'] . ' month'));
    $tanggal_jatuh_tempo = str_replace(array_keys($bulanIndonesia), array_values($bulanIndonesia), $tanggal_jatuh_tempo);

    $html = '
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8pt; /* Ukuran font diperkecil */
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table.header {
            margin-bottom: 10px;
            border: none;
        }
        table.jadwal {
            border: 1px solid #000;
        }
        table.jadwal th, table.jadwal td {
            border: 1px solid #000;
            padding: 2px; /* Padding diperkecil */
            text-align: center;
        }
        table.jadwal th {
            background-color: #f2f2f2;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        h2 {
            text-align: center;
            margin: 5px 0;
            font-size: 12pt; /* Ukuran judul diperkecil */
        }
        /* Menyamakan lebar kolom */
        .col-angsuran, .col-outstanding {
            width: 8%;
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
    </style>
    <img src="logo.png" class="logo">
        <div class="header-text">
            <strong>'.strtoupper($row_kantor['nm_perusahaan']).'</strong><br>
            KANTOR '.$row_kantor['nm_kantor'].'<br>
            '.$row_kantor['almt'].'
        </div>
        <hr>
    
    <h2>JADWAL ANGSURAN ANUITAS</h2>
    
    <table class="header">
        <tr>
            <td width="15%">Nama</td>
            <td width="2%">:</td>
            <td width="33%">' . $data['nama'] . '</td>
            <td width="15%">Plafond</td>
            <td width="2%">:</td>
            <td width="33%">Rp. ' . number_format($data['plafond'], 0, ',', '.') . '</td>
        </tr>
        <tr>
            <td>No. Rekening</td>
            <td>:</td>
            <td>' . $data['noloan'] . '</td>
            <td>Jangka waktu</td>
            <td>:</td>
            <td>' . $data['jw'] . ' Bulan</td>
        </tr>
        <tr>
            <td>Alamat</td>
            <td>:</td>
            <td>' . $data['almt'] . '</td>
            <td>Suku Bunga</td>
            <td>:</td>
            <td>' . $data['sukbung'] . '% ' . $data['cara'] . '</td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td>' . $data['kec'] . ' ' . $data['kab'] . '</td>
            <td>Angsuran per Bulan</td>
            <td>:</td>
            <td>Rp. ' . number_format($angsuran_anuitas, 0, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Tanggal Valuta</td>
            <td>:</td>
            <td>' . formatTanggalIndonesia($data['tgl_droping']) . '</td>
            <td>Total Pembayaran</td>
            <td>:</td>
            <td>Rp. ' . number_format($angsuran_anuitas * $data['jw'], 0, ',', '.') . '</td>
        </tr>
        <tr>
            <td>Tanggal Jatuh Tempo</td>
            <td>:</td>
            <td>' . $tanggal_jatuh_tempo . '</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
    
    <table class="jadwal">
        <tr>
            <th rowspan="2">No</th>
            <th rowspan="2">Tanggal Valuta</th>
            <th colspan="3">Jadwal Angsuran</th>
            <th rowspan="2">No</th>
            <th colspan="3">Realisasi Angsuran</th>
            <th rowspan="2" class="col-angsuran">Total Angsuran</th>
            <th colspan="2">Outstanding</th>
        </tr>
        <tr>
            <th>Pokok</th>
            <th>Bunga</th>
            <th>Total Angsuran</th>
            <th>Tanggal</th>
            <th>Pokok</th>
            <th>Bunga</th>
            <th class="col-outstanding">Pokok</th>
            <th class="col-outstanding">Bunga</th>
        </tr>';

    // Generate jadwal angsuran anuitas
    $total_bunga = 0;
    $total_pokok = 0;
    
    for ($i = 1; $i <= $data['jw']; $i++) {
        // Hitung tanggal angsuran
        $tanggal_angsuran = date('Y-m-d', mktime(0, 0, 0, $bulan_dropping + $i - 1, $tanggal_dropping, $tahun_dropping));
        $bulan = date('M', strtotime($tanggal_angsuran));
        $tahun = date('Y', strtotime($tanggal_angsuran));
        
        // Hitung bunga dengan metode anuitas
        $angsuran_bunga = $sisa_pokok * $sukbung_bulanan;
        $angsuran_pokok = $angsuran_anuitas - $angsuran_bunga;
        
        // Akumulasi total
        $total_bunga += $angsuran_bunga;
        $total_pokok += $angsuran_pokok;
        
        // Format angka untuk tampilan
        $angsuran_pokok_formatted = number_format($angsuran_pokok, 0, ',', '.');
        $angsuran_bunga_formatted = number_format($angsuran_bunga, 0, ',', '.');
        $total_angsuran_formatted = number_format($angsuran_anuitas, 0, ',', '.');
        
        // Kurangi sisa pokok untuk bulan berikutnya
        $sisa_pokok -= $angsuran_pokok;
        
        // Hitung sisa bunga (untuk bulan terakhir)
        $sisa_bunga = ($i == $data['jw']) ? 0 : ($angsuran_anuitas * $data['jw'] - $data['plafond'] - $total_bunga + $angsuran_bunga);
        
        // Format sisa pokok dan bunga
        $sisa_pokok_formatted = number_format(max(0, $sisa_pokok), 0, ',', '.');
        $sisa_bunga_formatted = number_format(max(0, $sisa_bunga), 0, ',', '.');
        
        $html .= '
        <tr>
            <td>' . $i . '</td>
            <td>' . $tanggal_dropping . ' ' . $bulan . ' ' . $tahun . '</td>
            <td class="text-right">' . $angsuran_pokok_formatted . '</td>
            <td class="text-right">' . $angsuran_bunga_formatted . '</td>
            <td class="text-right">' . $total_angsuran_formatted . '</td>
            <td>' . $i . '</td>
            <td></td>
            <td></td>
            <td></td>
            <td class="col-angsuran"></td>
            <td class="col-outstanding text-right">' . $sisa_pokok_formatted . '</td>
            <td class="col-outstanding text-right">' . $sisa_bunga_formatted . '</td>
        </tr>';
    }

    $html .= '
    </table>
    
    <table style="margin-top: 30px; border: none;">
        <tr>
            <td width="20%" style="text-align: center;">
                Mengetahui<br><br><br><br><br>
                <u><strong>' . $data['nama'] . '</strong></u><br>
            </td>
            <td width="60%"></td>
            <td width="20%" style="text-align: center;">
                ' . $row_kantor['nm_perusahaan'] . '<br><br><br><br><br>
                <u><strong>' . $direktur_lengkap . '</strong></u><br>
                Direktur Utama
            </td>
        </tr>
    </table>';

    // Buat nama file yang sesuai dengan judul
    $filename = 'Jadwal Angsuran Anuitas - ' . $data['nama'] . ' ' . formatTanggalIndonesia($data['tgl_droping']) . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('Jadwal Angsuran Anuitas - ' . $data['nama']);
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
