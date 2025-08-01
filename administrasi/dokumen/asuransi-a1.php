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
            font-size: 10pt;
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
    <img src="logo.png" class="logo">
        <div class="header-text">
            <strong>'.strtoupper($row_kantor['nm_perusahaan']).'</strong><br>
            KANTOR '.$row_kantor['nm_kantor'].'<br>
            '.$row_kantor['almt'].'
        </div>
        <hr>
    <div style="text-align: right; margin-bottom: 0px;">
        '.$row_kantor['kab'].', '.formatTanggalIndonesia($data['tgl_droping']).'
    </div>
    
    <div style="margin-bottom: -10px;">
        <p>Kepada Yth<br>
        Bapak/Ibu. '.$data['nama'].'<br>
        '.$data['almt'].'<br>
        Kecamatan '.$data['kec'].' Kabupaten '.$data['kab'].'</p>
        
        <p><strong><u>Perihal : Lampiran Asuransi Personal Accident Plus (Paket A1)</strong></u></p>
    </div>
    
    <div>
        <p><strong>A. Jenis Paket Asuransi Personal Accident Plus :</strong></p>
        <ol>
            <li>Paket A1 adalah paket yang menjamin dari total nilai plafond pinjaman yang disetujui oleh Bank kepada debitur pada saat akad kredit.</li>
        </ol>
        
        <p><strong>B. Luas Jaminan dan Batas Nilai Ganti Rugi :</strong></p>
        <p>Luas jaminan untuk setiap jenis Asuransi berdasarkan pilihan paket yang disediakan dan dipilih oleh debitur BPR sebagai berikut :</p>
        
        <ol>
            <li>Paket A1 (Tetap)</li>
        </ol>
        
        <p>Nilai pertanggungan Asuransi personal accident Plus :<br>
        - Usia 18 Tahun s/d 50 Tahun : Rp. 300.000.000,-<br>
        - Usia 18 Tahun s/d 55 Tahun : Rp. 200.000.000,-<br>
        - Usia 18 Tahun s/d 65 Tahun : Rp. 100.000.000,-</p>
        
        <table border="1" cellpadding="5" cellspacing="0" width="100%" style="border-collapse: collapse; margin-top: 15px;">
            <tr>
                <th style="text-align: center; font-weight: bold; background-color: #f2f2f2;">LUAS JAMINAN SCOPE OF COVER</th>
                <th style="text-align: center; font-weight: bold; background-color: #f2f2f2;">BATAS NILAI GANTI RUGI/NASABAH</th>
            </tr>
            <tr>
                <td>1. Kematian karena Sakit (Jiwa Nasabah)</td>
                <td>Sesuai dengan nilai pertanggungan didalam polis maksimum sebesar nilai Perjanjian Kredit</td>
            </tr>
            <tr>
                <td>2. Kematian karena Kecelakaan (Jiwa Nasabah)</td>
                <td>Sesuai dengan nilai pertanggungan didalam polis maksimum sebesar nilai Perjanjian Kredit</td>
            </tr>
            <tr>
                <td>3. Cacat tetap permanen (Jiwa Nasabah)</td>
                <td>Sesuai dengan tabel limit asuransi PA, berdasarkan 100% nilai pertanggungan dipolis</td>
            </tr>
            <tr>
                <td>4. Biaya pengobatan Rumah Sakit karena kecelakaan</td>
                <td>Berdasarkan kuitansi pengobatan dari Rumah Sakit namun maksimum penggantian sebesar Rp. 1.000.000,- per Nasabah berlaku selama periode pertanggungan.</td>
            </tr>
            <tr>
                <td>5. Biaya pengobatan Klinik karena kecelakaan</td>
                <td>Berdasarkan kuitansi pengobatan dari Rumah Sakit namun maksimum penggantian sebesar Rp. 1.000.000,- per Nasabah berlaku selama periode pertanggungan.</td>
            </tr>
            <tr>
                <td>6. PHK (khusus bagi debitur (PNS))</td>
                <td>5 (lima) kali Gaji Pokok Maksimum sisa kredit atau Rp.25.000.000,-/kejadian mana yang lebih kecil.</td>
            </tr>
            <tr>
                <td>7. Kebakaran (bangunan dan atau stock barang )</td>
                <td>Sebesar Nilai kerugian atau kredit awal, mana saja yang lebih kecil maksimum sebesar Rp. 25.000.000,-/kejadian berlaku selama periode pertanggungan.</td>
            </tr>
            <tr>
                <td>8. RSMD (bangunan dan atau stock barang )</td>
                <td>Sebesar Nilai kerugian atau kredit awal, mana saja yang lebih kecil maksimum sebesar Rp. 25.000.000,-/kejadian berlaku selama periode pertanggungan.</td>
            </tr>
            <tr>
                <td>9. Gempa bumi (bangunan dan/atau stock barang )</td>
                <td>Sebesar Nilai kerugian atau kredit awal, mana saja yang lebih kecil maksimum sebesar Rp. 25.000.000,-/kejadian berlaku selama periode pertanggungan.</td>
            </tr>
            <tr>
                <td>10. Banjir,Badai/Topan (bangunan dan/atau stock barang)</td>
                <td>Sebesar Nilai kerugian atau kredit awal, mana saja yang lebih kecil maksimum sebesar Rp. 25.000.000,-/kejadian berlaku selama periode pertanggungan.</td>
            </tr>
            <tr>
                <td>11. Tanah longsor (bangunan dan/atau stock barang )</td>
                <td>Sebesar Nilai kerugian atau kredit awal, mana saja yang lebih kecil maksimum sebesar Rp. 25.000.000,-/kejadian berlaku selama periode pertanggungan.</td>
            </tr>
            <tr>
                <td>12. Kebongkaran (Stock barang )</td>
                <td>Sebesar Nilai kerugian atau kredit awal, mana saja yang lebih kecil maksimum sebesar Rp. 25.000.000,-/kejadian berlaku selama periode pertanggungan.</td>
            </tr>
        </table>
        
        <p style="margin-top: 15px;">Dengan catatan maksimum tanggungan Penanggung :</p>
        <ol>
            <li>Atas peristiwa kebakaran yang terjadi disatu lokal pasar/okupasi pasar di bawah peluasan jaminan Paket A1, A2 dan A3 sebagai akibat satu kejadian yang sama tidak melebihi Rp. 500.000.000,- (lima ratus juta Rupiah)</li>
            <li>Atas peristiwa Gempa Bumi, Letusan Gunung Merapi, Tsunami dibawah perluasan jaminan Paket A1, A2, dan A3 yang terjadi dalam satu provinsi dalam kurun waktu 72 jam tidak melebihi Rp. 1.000.000.000,- (satu milyar rupiah).</li>
            <li>Banjir, angin ribut dan tanah longsor dibawah perluasan jaminan paket A1, A2 dan A3 yang terjadi dalam satu kejadian tidak melebihi Rp. 1.000.000.000,- (satu milyar rupiah).</li>
        </ol>
        
        <p><strong>C. Pengembalian Premi</strong></p>
        <ol>
            <li>Periode polis kurang dari atau sama dengan 2 tahun, maka tidak ada pengembalian premi manakala polis dibatalkan.</li>
        </ol>
        
        <p>Dalam hal periode polis lebih dari 2 tahun, maka pengembalian premi akan dihitung sebagai berikut:</p>
        <ul>
            <li>Jika pada saat tanggal efektif pembatalan, polis telah berjalan kurang dari 2 tahun maka minimum
            premi yang ditahan adalah premi untuk masa pertanggungan 2 tahun sehingga premi yang
            dikembalikan dihitung dari total premi yang telah dibayarkan dikurangi dengan premi untuk masa
            pertanggungan 2 tahun (minimum premi ditahan)</li>
            <li>Jika pada saat tanggal efektif pembatalan, polis telah berjalan lebih dari 2 tahun maka pengembalian
            premi dihitung dari total premi yang telah dibayarkan dikurangi dengan premi untuk masa
            pertanggungan 2 tahun (premi ditahan) dan dikurangi prorata premi yang telah dijalani terhitung dari
            masa pertanggungan sampai dengan tanggal efektif pembatalan polis.</li>
        </ul>
        
        <ol start="2">
            <li>Pengembalian premi dapat dilakukan dengan syarat tidak adanya klaim.</li>
        </ol>
    </div>
    
    <div style="margin-top: 40px;">
        <table width="100%">
            <tr>
                <td width="50%" style="text-align: center;">
                    <p>'.formatTanggalIndonesia($data['tgl_droping']).'</p>
                    Peminjam<br><br><br><br><br><br>
                    <strong><u>'.$data['nama'].'</u></strong><br>
                </td>
                <td width="50%" style="text-align: center;">
                    
                </td>
            </tr>
        </table>
    </div>
    ';

    // Buat nama file yang sesuai dengan judul
    $filename = 'Lampiran Asuransi Personal Accident Plus (Paket A1) - ' . $data['nama'] . ' ' . $tanggal_dropping . '.pdf';

    // Tulis HTML ke PDF
    $mpdf->SetTitle('Lampiran Asuransi Personal Accident Plus (Paket A1) - ' . $data['nama'] . ' ' . $tanggal_dropping);
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
