<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include '../../config.php';
include '../config/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Ambil data droping berdasarkan noreg
$noreg = $_GET['noreg'];
$query = mysqli_query($connect, "SELECT d.*, p.niknas, p.norek, p.jns_kredit, n.nama as nama_nasabah 
    FROM droping d 
    LEFT JOIN pengajuan p ON p.noreg = d.noreg
    LEFT JOIN nasabah n ON n.nik = p.niknas
    WHERE d.noreg='$noreg'");
$data = mysqli_fetch_array($query);

// Proses update data
if (isset($_POST['submit'])) {
    try {
        $norek = $_POST['norek'];
        $nobwmk = $_POST['nobwmk'];
        $ketbwmk = $_POST['ketbwmk'];
        $nospp = $_POST['nospp'];
        $nosurat = $_POST['nosurat'];
        $nostpk = $_POST['nostpk'];
        $nocif = $_POST['nocif'];
        $noloan = $_POST['noloan'];
        $tgl_acc_direksi = $_POST['tgl_acc_direksi'];
        $bln_rmwi = $_POST['bln_rmwi'];
        $tgl_droping = $_POST['tgl_droping'];
        $jam_droping = $_POST['jam_droping'];
        $plafond = str_replace(',', '', $_POST['plafond']);

        // Tambahkan perhitungan nomprov
        $prov = $_POST['prov'];
        $nomprov = ($prov / 100) * $plafond;

        // Cek apakah input desimal diaktifkan
        $enable_decimal = isset($_POST['enable_decimal']) && $_POST['enable_decimal'] === 'on';

        if ($enable_decimal) {
            // Jika desimal diaktifkan, simpan dengan 2 digit desimal
            $plafond = sprintf("%.2f", (float)$plafond);
        } else {
            // Jika desimal tidak diaktifkan, bulatkan ke bilangan bulat
            $plafond = round((float)$plafond);
        }

        $sukbung = $_POST['sukbung'];
        $jw = $_POST['jw'];
        // Ganti pembuatan $tprov agar pakai terbilangProvisi
        $tprov = isset($_POST['tprov']) ? $_POST['tprov'] : terbilangProvisi($prov);
        // Tambahkan tsukbung dengan terbilangProvisi untuk suku bunga
        $tsukbung = isset($_POST['tsukbung']) ? $_POST['tsukbung'] : terbilangProvisi($sukbung);
        $angpok = str_replace(',', '', $_POST['angpok']);
        $angbung = str_replace(',', '', $_POST['angbung']);
        $totang = str_replace(',', '', $_POST['totang']);
        $jns_kredit = $_POST['jns_kredit'];

        // Generate terbilang
        $tangpok = terbilang((int)$angpok) . " Rupiah";
        $tangbung = terbilang((int)$angbung) . " Rupiah";
        $ttotang = terbilang((int)$totang) . " Rupiah";

        // Tambahan variable dengan pengecekan dan nilai default
        $metode = $_POST['metode'];
        $cara = $_POST['cara'];
        $peng_kredit = isset($_POST['peng_kredit']) ? $_POST['peng_kredit'] : 'INTERN';
        $jns_asjw = isset($_POST['jns_asjw']) ? $_POST['jns_asjw'] : '-';
        $adm = str_replace(',', '', $_POST['adm']);
        $materai = str_replace(',', '', $_POST['materai']);
        $notaris = str_replace(',', '', $_POST['notaris']);
        $asjw = str_replace(',', '', $_POST['asjw']);
        $asken = str_replace(',', '', $_POST['asken']);
        $totasuransi = str_replace(',', '', $_POST['totasuransi']);
        $total = isset($_POST['total']) ? str_replace(',', '', $_POST['total']) : '0';
        $total = empty($total) ? '0' : $total;

        // Tambahan untuk nilpenj dan tnilpenj
        $nilpenj = str_replace(',', '', $_POST['nilpenj']);
        $tnilpenj = isset($_POST['tnilpenj']) ? $_POST['tnilpenj'] : terbilang($nilpenj) . ' Rupiah';

        $status = $_POST['status'];

        // Tambahan variable terbilang
        $tasjw = isset($_POST['tasjw']) ? $_POST['tasjw'] : terbilang($asjw) . ' Rupiah';
        $tasken = isset($_POST['tasken']) ? $_POST['tasken'] : terbilang($asken) . ' Rupiah';
        $ttotasuransi = isset($_POST['ttotasuransi']) ? $_POST['ttotasuransi'] : terbilang($totasuransi) . ' Rupiah';
        $tnotaris = isset($_POST['tnotaris']) ? $_POST['tnotaris'] : terbilang($notaris) . ' Rupiah';
        $tmaterai = isset($_POST['tmaterai']) ? $_POST['tmaterai'] : terbilang($materai) . ' Rupiah';
        $ttotal = isset($_POST['hidden_ttotal']) ? $_POST['hidden_ttotal'] : terbilang($total) . ' Rupiah';
        $penggunaan = $_POST['penggunaan'];

        // Tambahkan variable tnomprov
        $tnomprov = isset($_POST['tnomprov']) ? $_POST['tnomprov'] : terbilang($nomprov) . ' Rupiah';

        // Tambahkan variable tadm
        $tadm = isset($_POST['tadm']) ? $_POST['tadm'] : terbilang($adm) . ' Rupiah';

        // Fungsi helper untuk mengkonversi nilai 0 atau null menjadi "-"
        function convertZeroToDash($value)
        {
            $value = trim($value);
            if ($value === '' || $value === '0' || $value === '0.00' || $value === null) {
                return '-';
            }
            return $value;
        }

        // Fungsi helper untuk mengkonversi terbilang jika nilai 0 atau null
        function convertTerbilangZeroToDash($value, $terbilang)
        {
            $value = trim($value);
            if ($value === '' || $value === '0' || $value === '0.00' || $value === null) {
                return '-';
            }
            return $terbilang;
        }

        // Konversi nilai-nilai yang mungkin 0 atau null menjadi "-"
        // Untuk field angka, JANGAN ubah 0 menjadi '-'. Biarkan 0 tetap 0.
        // $prov = convertZeroToDash($prov);
        // $nomprov = convertZeroToDash($nomprov);
        // $adm = convertZeroToDash($adm);
        // $materai = convertZeroToDash($materai);
        // $notaris = convertZeroToDash($notaris);
        // $asjw = convertZeroToDash($asjw);
        // $asken = convertZeroToDash($asken);
        // $totasuransi = convertZeroToDash($totasuransi);
        // $total = convertZeroToDash($total);
        // $nilpenj = convertZeroToDash($nilpenj);
        // Hanya gunakan convertZeroToDash untuk field string/keterangan jika perlu.

        // Konversi terbilang yang mungkin 0 atau null menjadi "-"
        $tprov = convertTerbilangZeroToDash($prov, $tprov);
        $tnomprov = convertTerbilangZeroToDash($nomprov, $tnomprov);
        $tadm = convertTerbilangZeroToDash($adm, $tadm);
        $tmaterai = convertTerbilangZeroToDash($materai, $tmaterai);
        $tnotaris = convertTerbilangZeroToDash($notaris, $tnotaris);
        $tasjw = convertTerbilangZeroToDash($asjw, $tasjw);
        $tasken = convertTerbilangZeroToDash($asken, $tasken);
        $ttotasuransi = convertTerbilangZeroToDash($totasuransi, $ttotasuransi);
        $ttotal = convertTerbilangZeroToDash($total, $ttotal);
        $tnilpenj = convertTerbilangZeroToDash($nilpenj, $tnilpenj);

        // Pastikan field integer tidak berisi '-'
        foreach (['materai', 'notaris', 'asjw', 'asken', 'totasuransi', 'total', 'nilpenj', 'adm', 'nomprov', 'prov'] as $f) {
            if ($$f === '-') $$f = '';
        }

        // Update status di tabel droping
        $query_droping = mysqli_query($connect, "UPDATE droping SET 
            nobwmk = '$nobwmk',
            ketbwmk = '$ketbwmk',
            nospp = '$nospp',
            nosurat = '$nosurat',
            nostpk = '$nostpk',
            nocif = '$nocif',
            noloan = '$noloan',
            tgl_acc_direksi = '$tgl_acc_direksi',
            tgl_droping = '$tgl_droping',
            jam_droping = '$jam_droping',
            plafond = $plafond,
            sukbung = '$sukbung',
            jw = '$jw',
            angpok = '$angpok',
            angbung = '$angbung',
            totang = '$totang',
            bln_rmwi = '$bln_rmwi',
            tangpok = '$tangpok',
            tangbung = '$tangbung',
            ttotang = '$ttotang',
            metode = '$metode',
            cara = '$cara',
            tprov = '$tprov',
            tsukbung = '$tsukbung',
            peng_kredit = '$peng_kredit',
            jns_asjw = '$jns_asjw',
            prov = NULLIF('$prov', ''),
            nomprov = NULLIF('$nomprov', ''),
            adm = NULLIF('$adm', ''),
            tadm = '$tadm',
            materai = NULLIF('$materai', ''),
            notaris = NULLIF('$notaris', ''),
            asjw = NULLIF('$asjw', ''),
            asken = NULLIF('$asken', ''),
            totasuransi = NULLIF('$totasuransi', ''),
            total = NULLIF('$total', ''),
            nilpenj = NULLIF('$nilpenj', ''),
            tnilpenj = '$tnilpenj',
            tasjw = '$tasjw',
            tasken = '$tasken',
            ttotasuransi = '$ttotasuransi',
            tnotaris = '$tnotaris',
            tmaterai = '$tmaterai',
            ttotal = '$ttotal',
            penggunaan = '$penggunaan',
            status = '$status',
            tnomprov = '$tnomprov'
            WHERE noreg = '$noreg'");

        // Update status di tabel pengajuan
        $query_pengajuan = mysqli_query($connect, "UPDATE pengajuan SET 
            norek = '$norek',
            jns_kredit = '$jns_kredit',
            status = '$status'
            WHERE noreg = '$noreg'");

        // Update status di tabel komite
        $query_komite = mysqli_query($connect, "UPDATE komite SET 
            status = '$status'
            WHERE noreg = '$noreg'");
    } catch (Throwable $e) {
        echo "<pre style='color:red'>EXCEPTION: " . $e->getMessage() . "</pre>";
        file_put_contents(__DIR__ . '/log-error-edit-droping.txt', date('[Y-m-d H:i:s]') . ' EXCEPTION: ' . $e->getMessage() . "\n", FILE_APPEND);
        exit;
    }

    if ($query_droping && $query_pengajuan && $query_komite) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data Droping berhasil diperbarui',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'edit-droping.php?noreg=" . $noreg . "';
                    }
                });
            });
        </script>";
    } else {
        // Tambahkan debug error MySQL dan logging ke file
        $log_file = __DIR__ . '/log-error-edit-droping.txt';
        $log_time = date('[Y-m-d H:i:s]');
        $log_content = '';

        if (!$query_droping) {
            $err = mysqli_error($connect);
            echo "<div style='color:red'>Error droping: $err</div>";
            $log_content .= "$log_time Error droping: $err\n";
        }
        if (!$query_pengajuan) {
            $err = mysqli_error($connect);
            echo "<div style='color:red'>Error pengajuan: $err</div>";
            $log_content .= "$log_time Error pengajuan: $err\n";
        }
        if (!$query_komite) {
            $err = mysqli_error($connect);
            echo "<div style='color:red'>Error komite: $err</div>";
            $log_content .= "$log_time Error komite: $err\n";
        }
        if (!empty($log_content)) {
            file_put_contents($log_file, $log_content, FILE_APPEND);
        }
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Gagal memperbarui data di salah satu atau beberapa tabel!'
                });
            });
        </script>";
    }
}

// Tambahkan fungsi terbilang di PHP
function terbilang($angka)
{
    $angka = (int)$angka;
    $bilangan = array(
        '',
        'Satu',
        'Dua',
        'Tiga',
        'Empat',
        'Lima',
        'Enam',
        'Tujuh',
        'Delapan',
        'Sembilan',
        'Sepuluh',
        'Sebelas'
    );

    if ($angka < 0) {
        return 'Minus ' . terbilang(abs($angka));
    }
    if ($angka === 0) {
        return 'Nol';
    }
    if ($angka < 12) {
        return $bilangan[$angka];
    } elseif ($angka < 20) {
        return $bilangan[$angka - 10] . ' Belas';
    } elseif ($angka < 100) {
        $puluh = floor($angka / 10);
        $sisa = $angka % 10;
        return $bilangan[$puluh] . ' Puluh' . ($sisa > 0 ? ' ' . terbilang($sisa) : '');
    } elseif ($angka < 200) {
        return 'Seratus' . ($angka - 100 > 0 ? ' ' . terbilang($angka - 100) : '');
    } elseif ($angka < 1000) {
        $ratus = floor($angka / 100);
        $sisa = $angka % 100;
        return $bilangan[$ratus] . ' Ratus' . ($sisa > 0 ? ' ' . terbilang($sisa) : '');
    } elseif ($angka < 2000) {
        return 'Seribu' . ($angka - 1000 > 0 ? ' ' . terbilang($angka - 1000) : '');
    } elseif ($angka < 1000000) {
        $ribu = floor($angka / 1000);
        $sisa = $angka % 1000;
        return terbilang($ribu) . ' Ribu' . ($sisa > 0 ? ' ' . terbilang($sisa) : '');
    } elseif ($angka < 1000000000) {
        $juta = floor($angka / 1000000);
        $sisa = $angka % 1000000;
        return terbilang($juta) . ' Juta' . ($sisa > 0 ? ' ' . terbilang($sisa) : '');
    } elseif ($angka < 1000000000000) {
        $milyar = floor($angka / 1000000000);
        $sisa = $angka % 1000000000;
        return terbilang($milyar) . ' Milyar' . ($sisa > 0 ? ' ' . terbilang($sisa) : '');
    }
}

// Tambahkan fungsi terbilangProvisi untuk angka desimal seperti di JS
function terbilangProvisi($number)
{
    $angka = ['Nol', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan'];
    $terbilang = '';
    $number = (string)$number;
    $parts = explode('.', $number);
    $wholeNumber = $parts[0];
    $decimal = isset($parts[1]) ? $parts[1] : '';
    // Bilangan bulat
    for ($i = 0; $i < strlen($wholeNumber); $i++) {
        $digit = (int)$wholeNumber[$i];
        if ($terbilang !== '') $terbilang .= ' ';
        $terbilang .= $angka[$digit];
    }
    // Desimal
    if ($decimal !== '') {
        $terbilang .= ' Koma';
        for ($i = 0; $i < strlen($decimal); $i++) {
            $digit = (int)$decimal[$i];
            $terbilang .= ' ' . $angka[$digit];
        }
    }
    return $terbilang;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Droping - <?php echo $data['noreg']; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <?php include '../includes/menu-droping.php'; ?>
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="card-title mb-4">Edit Data Droping</h4>
                            <form method="POST" action="">

                                <!-- Data Identitas -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Identitas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">No. Registrasi</label>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $data['noreg']; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Nasabah</label>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $data['nama_nasabah']; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">No. Rekening</label>
                                                    <input type="text" name="norek" class="form-control"
                                                        value="<?php echo $data['norek']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Nomor dan Identifikasi -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Nomor dan Identifikasi</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">No. CIF</label>
                                                    <input type="text" name="nocif" class="form-control"
                                                        value="<?php echo $data['nocif']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">No. Loan</label>
                                                    <input type="text" name="noloan" class="form-control"
                                                        value="<?php echo $data['noloan']; ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">No. STPK</label>
                                                    <input type="text" name="nostpk" class="form-control"
                                                        value="<?php echo $data['nostpk']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">No. SPP</label>
                                                    <input type="text" name="nospp" class="form-control"
                                                        value="<?php echo $data['nospp']; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">No. Surat</label>
                                                    <input type="text" name="nosurat" class="form-control"
                                                        value="<?php echo $data['nosurat']; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Non Tea</label>
                                                    <select name="set_bwmk" id="set_bwmk" class="form-control">
                                                        <option value="NON BWMK"
                                                            <?php echo ($data['nobwmk'] == '-' || empty($data['nobwmk'])) ? 'selected' : ''; ?>>
                                                            NON BWMK</option>
                                                        <option value="BWMK"
                                                            <?php echo ($data['nobwmk'] != '-' && !empty($data['nobwmk'])) ? 'selected' : ''; ?>>
                                                            BWMK</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">No. Surat Persetujuan Direksi</label>
                                                    <input type="text" name="nobwmk" id="nobwmk" class="form-control"
                                                        value="<?php echo !empty($data['nobwmk']) ? $data['nobwmk'] : '-'; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Keterangan Lainnya BWMK</label>
                                                    <textarea name="ketbwmk" id="ketbwmk" class="form-control"><?php echo !empty($data['ketbwmk']) ? $data['ketbwmk'] : '-'; ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Tanggal dan Waktu -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Tanggal dan Waktu</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal ACC Direksi</label>
                                                    <input type="text" name="tgl_acc_direksi"
                                                        class="form-control date-input"
                                                        value="<?php echo $data['tgl_acc_direksi']; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Tanggal Droping</label>
                                                    <input type="text" name="tgl_droping"
                                                        class="form-control date-input"
                                                        value="<?php echo $data['tgl_droping']; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Jam Droping</label>
                                                    <div class="input-group">
                                                        <input type="text" name="jam_droping" class="form-control"
                                                            id="jam_droping" value="<?php echo $data['jam_droping']; ?>"
                                                            placeholder="HH:mm">
                                                        <button type="button" class="btn btn-secondary"
                                                            onclick="setCurrentTime()">Set Jam Sekarang</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Kredit -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Kredit</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Plafond</label>
                                                    <div class="input-group">
                                                        <input type="text" name="plafond" id="plafond"
                                                            class="form-control" value="<?php
                                                                                        $plafond_value = (float)$data['plafond'];
                                                                                        $has_decimal = $plafond_value != floor($plafond_value);
                                                                                        echo number_format($plafond_value, $has_decimal ? 2 : 0, '.', ',');
                                                                                        ?>"
                                                            onkeyup="formatNumber(this); updateTerbilang('plafond'); hitungAngsuran();">
                                                        <div class="input-group-text">
                                                            <input type="checkbox" name="enable_decimal"
                                                                id="enable_decimal" class="form-check-input mt-0"
                                                                <?php echo $has_decimal ? 'checked' : ''; ?>
                                                                aria-label="Aktifkan desimal"
                                                                style="margin-right: 5px;">
                                                            <small>Desimal</small>
                                                        </div>
                                                    </div>
                                                    <small class="text-muted terbilang-text" id="tplafond"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Suku Bunga (%)</label>
                                                    <input type="text" name="sukbung" id="sukbung" class="form-control"
                                                        value="<?php echo $data['sukbung']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('sukbung'); hitungAngsuran();">
                                                    <small class="text-muted terbilang-text" id="tsukbung"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Jangka Waktu (Bulan)</label>
                                                    <input type="text" name="jw" id="jw" class="form-control"
                                                        value="<?php echo $data['jw']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('jw'); hitungAngsuran();">
                                                    <small class="text-muted terbilang-text" id="tjw"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Angsuran Pokok</label>
                                                    <input type="text" name="angpok" id="angpok"
                                                        class="form-control readonly-green"
                                                        value="<?php echo number_format($data['angpok'], 0, ',', ','); ?>"
                                                        readonly>
                                                    <small class="text-muted terbilang-text" id="tangpok"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Angsuran Bunga</label>
                                                    <input type="text" name="angbung" id="angbung"
                                                        class="form-control readonly-green"
                                                        value="<?php echo number_format($data['angbung'], 0, ',', ','); ?>"
                                                        readonly>
                                                    <small class="text-muted terbilang-text" id="tangbung"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Total Angsuran</label>
                                                    <input type="text" name="totang" id="totang"
                                                        class="form-control readonly-green"
                                                        value="<?php echo number_format($data['totang'], 0, ',', ','); ?>"
                                                        readonly>
                                                    <small class="text-muted terbilang-text" id="ttotang"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Biaya -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Biaya</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Provisi (%)</label>
                                                    <input type="text" name="prov" id="prov" class="form-control"
                                                        value="<?php echo $data['prov']; ?>"
                                                        onkeyup="formatNumberProvisi(this); updateTerbilangProvisi(); hitungNominalProvisi();">
                                                    <small class="text-muted terbilang-text" id="tprov"></small>
                                                    <input type="hidden" name="tprov" id="hidden_tprov" value="<?php echo isset($data['tprov']) ? $data['tprov'] : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Administrasi</label>
                                                    <input type="text" name="adm" id="adm" class="form-control"
                                                        value="<?php echo $data['adm']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('adm');">
                                                    <small class="text-muted terbilang-text" id="tadm"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Materai</label>
                                                    <input type="text" name="materai" id="materai" class="form-control"
                                                        value="<?php echo $data['materai']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('materai');">
                                                    <small class="text-muted terbilang-text" id="tmaterai"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Notaris</label>
                                                    <input type="text" name="notaris" id="notaris" class="form-control"
                                                        value="<?php echo $data['notaris']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('notaris');">
                                                    <small class="text-muted terbilang-text" id="tnotaris"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Asuransi Jiwa</label>
                                                    <input type="text" name="asjw" id="asjw" class="form-control"
                                                        value="<?php echo $data['asjw']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('asjw'); hitungTotalAsuransi();">
                                                    <small class="text-muted terbilang-text" id="tasjw"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Asuransi Kendaraan</label>
                                                    <input type="text" name="asken" id="asken" class="form-control"
                                                        value="<?php echo $data['asken']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('asken'); hitungTotalAsuransi();">
                                                    <small class="text-muted terbilang-text" id="tasken"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Total Asuransi</label>
                                                    <input type="text" name="totasuransi" id="totasuransi"
                                                        class="form-control readonly-blue"
                                                        value="<?php echo $data['totasuransi']; ?>" readonly>
                                                    <small class="text-muted terbilang-text" id="ttotasuransi"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Nilai Penjamin</label>
                                                    <input type="text" name="nilpenj" id="nilpenj"
                                                        class="form-control readonly-blue"
                                                        value="<?php echo number_format($data['nilpenj'], 0, ',', ','); ?>"
                                                        readonly>
                                                    <small class="text-muted terbilang-text" id="tnilpenj"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Nominal Provisi</label>
                                                    <input type="text" name="nomprov" id="nomprov"
                                                        class="form-control readonly-blue"
                                                        value="<?php echo number_format($data['nomprov'], 0, ',', ','); ?>"
                                                        readonly>
                                                    <small class="text-muted terbilang-text"
                                                        id="tnomprov"><?php echo $data['tnomprov']; ?></small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Total Biaya</label>
                                                    <input type="text" name="total" id="total"
                                                        class="form-control readonly-yellow"
                                                        value="<?php echo number_format($data['total'], 0, ',', ','); ?>"
                                                        readonly>
                                                    <small class="text-muted terbilang-text" id="ttotal"></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Status dan Jenis -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Status dan Jenis</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Bulan Romawi</label>
                                                    <select name="bln_rmwi" class="form-control" required>
                                                        <option value="I"
                                                            <?php echo ($data['bln_rmwi'] == 'I') ? 'selected' : ''; ?>>
                                                            I (Januari)</option>
                                                        <option value="II"
                                                            <?php echo ($data['bln_rmwi'] == 'II') ? 'selected' : ''; ?>>
                                                            II (Februari)</option>
                                                        <option value="III"
                                                            <?php echo ($data['bln_rmwi'] == 'III') ? 'selected' : ''; ?>>
                                                            III (Maret)</option>
                                                        <option value="IV"
                                                            <?php echo ($data['bln_rmwi'] == 'IV') ? 'selected' : ''; ?>>
                                                            IV (April)</option>
                                                        <option value="V"
                                                            <?php echo ($data['bln_rmwi'] == 'V') ? 'selected' : ''; ?>>
                                                            V (Mei)</option>
                                                        <option value="VI"
                                                            <?php echo ($data['bln_rmwi'] == 'VI') ? 'selected' : ''; ?>>
                                                            VI (Juni)</option>
                                                        <option value="VII"
                                                            <?php echo ($data['bln_rmwi'] == 'VII') ? 'selected' : ''; ?>>
                                                            VII (Juli)</option>
                                                        <option value="VIII"
                                                            <?php echo ($data['bln_rmwi'] == 'VIII') ? 'selected' : ''; ?>>
                                                            VIII (Agustus)</option>
                                                        <option value="IX"
                                                            <?php echo ($data['bln_rmwi'] == 'IX') ? 'selected' : ''; ?>>
                                                            IX (September)</option>
                                                        <option value="X"
                                                            <?php echo ($data['bln_rmwi'] == 'X') ? 'selected' : ''; ?>>
                                                            X (Oktober)</option>
                                                        <option value="XI"
                                                            <?php echo ($data['bln_rmwi'] == 'XI') ? 'selected' : ''; ?>>
                                                            XI (November)</option>
                                                        <option value="XII"
                                                            <?php echo ($data['bln_rmwi'] == 'XII') ? 'selected' : ''; ?>>
                                                            XII (Desember)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Jenis Kredit</label>
                                                    <select name="jns_kredit" class="form-control" required>
                                                        <option value="Installment"
                                                            <?php echo ($data['jns_kredit'] == 'Installment') ? 'selected' : ''; ?>>
                                                            Installment</option>
                                                        <option value="Konsumtif"
                                                            <?php echo ($data['jns_kredit'] == 'Konsumtif') ? 'selected' : ''; ?>>
                                                            Konsumtif</option>
                                                        <option value="KRY"
                                                            <?php echo ($data['jns_kredit'] == 'KRY') ? 'selected' : ''; ?>>
                                                            KRY
                                                        </option>
                                                        <option value="Mikro"
                                                            <?php echo ($data['jns_kredit'] == 'Mikro') ? 'selected' : ''; ?>>
                                                            Mikro</option>
                                                        <option value="Modal Kerja"
                                                            <?php echo ($data['jns_kredit'] == 'Modal Kerja') ? 'selected' : ''; ?>>
                                                            Modal Kerja</option>
                                                        <option value="Reguler"
                                                            <?php echo ($data['jns_kredit'] == 'Reguler') ? 'selected' : ''; ?>>
                                                            Reguler</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Penggunaan</label>
                                                    <select name="penggunaan" class="form-control">
                                                        <option value="Modal Kerja"
                                                            <?php echo ($data['penggunaan'] == 'Modal Kerja') ? 'selected' : ''; ?>>
                                                            Modal Kerja</option>
                                                        <option value="Investasi"
                                                            <?php echo ($data['penggunaan'] == 'Investasi') ? 'selected' : ''; ?>>
                                                            Investasi</option>
                                                        <option value="Konsumsi"
                                                            <?php echo ($data['penggunaan'] == 'Konsumsi') ? 'selected' : ''; ?>>
                                                            Konsumsi</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Status</label>
                                                    <select name="status" class="form-control">
                                                        <option value="CAIR"
                                                            <?php echo ($data['status'] == 'CAIR') ? 'selected' : ''; ?>>
                                                            CAIR
                                                        </option>
                                                        <option value="SIAP CAIR"
                                                            <?php echo ($data['status'] == 'SIAP CAIR') ? 'selected' : ''; ?>>
                                                            SIAP CAIR</option>
                                                        <option value="ACC_DIREKSI"
                                                            <?php echo ($data['status'] == 'ACC_DIREKSI') ? 'selected' : ''; ?>>
                                                            ACC DIREKSI</option>
                                                        <option value="ACC_KACAB"
                                                            <?php echo ($data['status'] == 'ACC_KACAB') ? 'selected' : ''; ?>>
                                                            ACC KACAB</option>
                                                        <option value="ACC_KABID"
                                                            <?php echo ($data['status'] == 'ACC_KABID') ? 'selected' : ''; ?>>
                                                            ACC KABID</option>
                                                        <option value="MCC_DIREKSI"
                                                            <?php echo ($data['status'] == 'MCC_DIREKSI') ? 'selected' : ''; ?>>
                                                            MCC DIREKSI</option>
                                                        <option value="MCC_KACAB"
                                                            <?php echo ($data['status'] == 'MCC_KACAB') ? 'selected' : ''; ?>>
                                                            MCC KACAB</option>
                                                        <option value="MCC_KABID"
                                                            <?php echo ($data['status'] == 'MCC_KABID') ? 'selected' : ''; ?>>
                                                            MCC KABID</option>
                                                        <option value="REKOMENDASI"
                                                            <?php echo ($data['status'] == 'REKOMENDASI') ? 'selected' : ''; ?>>
                                                            REKOMENDASI</option>
                                                        <option value="PENDING"
                                                            <?php echo ($data['status'] == 'PENDING') ? 'selected' : ''; ?>>
                                                            PENDING</option>
                                                        <option value="REVISI"
                                                            <?php echo ($data['status'] == 'REVISI') ? 'selected' : ''; ?>>
                                                            REVISI</option>
                                                        <option value="BATAL"
                                                            <?php echo ($data['status'] == 'BATAL') ? 'selected' : ''; ?>>
                                                            BATAL
                                                        </option>
                                                        <option value="TOLAK"
                                                            <?php echo ($data['status'] == 'TOLAK') ? 'selected' : ''; ?>>
                                                            TOLAK
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Pengikatan Kredit</label>
                                                    <select name="peng_kredit" class="form-control">
                                                        <option value="Addendum"
                                                            <?php echo ($data['peng_kredit'] == 'Addendum') ? 'selected' : ''; ?>>
                                                            Addendum</option>
                                                        <option value="INTERN"
                                                            <?php echo ($data['peng_kredit'] == 'INTERN') ? 'selected' : ''; ?>>
                                                            INTERN</option>
                                                        <option value="SPH"
                                                            <?php echo ($data['peng_kredit'] == 'SPH') ? 'selected' : ''; ?>>
                                                            SPH
                                                        </option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Jenis Asuransi</label>
                                                    <select name="jns_asjw" class="form-control">
                                                        <option value="-"
                                                            <?php echo ($data['jns_asjw'] == '') ? 'selected' : ''; ?>>
                                                            Tidak Ada</option>
                                                        <option value="A1"
                                                            <?php echo ($data['jns_asjw'] == 'A1') ? 'selected' : ''; ?>>
                                                            A1</option>
                                                        <option value="A2"
                                                            <?php echo ($data['jns_asjw'] == 'A2') ? 'selected' : ''; ?>>
                                                            A2</option>
                                                        <option value="A3"
                                                            <?php echo ($data['jns_asjw'] == 'A3') ? 'selected' : ''; ?>>
                                                            A3</option>
                                                        <option value="A4"
                                                            <?php echo ($data['jns_asjw'] == 'A4') ? 'selected' : ''; ?>>
                                                            A4</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Cara</label>
                                                    <select name="cara" class="form-control">
                                                        <option value="Flat"
                                                            <?php echo ($data['cara'] == 'Flat') ? 'selected' : ''; ?>>
                                                            Flat</option>
                                                        <option value="Sliding"
                                                            <?php echo ($data['cara'] == 'Sliding') ? 'selected' : ''; ?>>
                                                            Sliding</option>
                                                        <option value="Anuitas"
                                                            <?php echo ($data['cara'] == 'Anuitas') ? 'selected' : ''; ?>>
                                                            Anuitas</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Metode</label>
                                                    <select name="metode" class="form-control">
                                                        <option value="Fixed Rate"
                                                            <?php echo ($data['metode'] == 'Fixed Rate') ? 'selected' : ''; ?>>
                                                            Fixed Rate</option>
                                                        <option value="Flat Rate"
                                                            <?php echo ($data['metode'] == 'Flat Rate') ? 'selected' : ''; ?>>
                                                            Flat Rate</option>
                                                        <option value="Sliding Rate"
                                                            <?php echo ($data['metode'] == 'Sliding Rate') ? 'selected' : ''; ?>>
                                                            Sliding Rate</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Di dalam form, tambahkan hidden inputs -->
                                <input type="hidden" name="tasjw" id="hidden_tasjw"
                                    value="<?php echo $data['tasjw']; ?>">
                                <input type="hidden" name="tasken" id="hidden_tasken"
                                    value="<?php echo $data['tasken']; ?>">
                                <input type="hidden" name="ttotasuransi" id="hidden_ttotasuransi"
                                    value="<?php echo $data['ttotasuransi']; ?>">
                                <input type="hidden" name="tnotaris" id="hidden_tnotaris"
                                    value="<?php echo $data['tnotaris']; ?>">
                                <input type="hidden" name="tmaterai" id="hidden_tmaterai"
                                    value="<?php echo $data['tmaterai']; ?>">
                                <input type="hidden" name="ttotal" id="hidden_ttotal"
                                    value="<?php echo $data['ttotal']; ?>">
                                <input type="hidden" name="tnomprov" id="hidden_tnomprov"
                                    value="<?php echo $data['tnomprov']; ?>">
                                <input type="hidden" name="tadm" id="hidden_tadm"
                                    value="<?php echo $data['tadm']; ?>">
                                <input type="hidden" name="tnilpenj" id="hidden_tnilpenj" value="<?php echo isset($data['tnilpenj']) ? $data['tnilpenj'] : ''; ?>">

                                <div class="text-end">
                                    <a href="input-droping.php" class="btn btn-secondary me-2">Kembali</a>
                                    <button type="submit" name="submit" class="btn btn-primary">Simpan
                                        Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        function formatNumber(input) {
            if (typeof input === 'number') {
                return formatDecimal(input);
            }

            const enableDecimal = document.getElementById('enable_decimal').checked;

            if (input.name === 'sukbung') {
                // Khusus untuk suku bunga (format persentase)
                let value = input.value.replace(/[^\d.]/g, '');
                if (value.includes('.')) {
                    let parts = value.split('.');
                    value = parts[0] + '.' + (parts[1] || '').slice(0, 2);
                }
                input.value = value;
            } else if (input.id === 'plafond' && enableDecimal) {
                // Format plafond dengan desimal jika checkbox dicentang
                let value = input.value.replace(/[^\d.]/g, '');
                if (value) {
                    // Simpan posisi kursor
                    const cursorPos = input.selectionStart;
                    const prevLength = input.value.length;

                    // Format angka
                    let parts = value.split('.');
                    if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                    if (parts[1]) parts[1] = parts[1].slice(0, 2);

                    // Format ribuan hanya untuk bagian bilangan bulat
                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                    value = parts.join('.');
                    input.value = value;

                    // Kembalikan posisi kursor dengan memperhitungkan perubahan panjang
                    const newPos = cursorPos + (input.value.length - prevLength);
                    input.setSelectionRange(newPos, newPos);
                } else {
                    input.value = '';
                }
            } else {
                // Format bilangan bulat untuk input lainnya
                let value = input.value.replace(/[^\d]/g, '');
                if (value) {
                    input.value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                } else {
                    input.value = '';
                }
            }
        }

        function formatDecimal(num) {
            // Memformat angka dengan pemisah ribuan dan 2 desimal
            let parts = parseFloat(num).toFixed(2).toString().split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }

        // Tambahkan event listener untuk input plafond
        document.getElementById('plafond').addEventListener('keydown', function(e) {
            const enableDecimal = document.getElementById('enable_decimal').checked;
            if (enableDecimal) {
                // Izinkan titik desimal jika checkbox dicentang
                if (e.key === '.' && !this.value.includes('.')) {
                    return;
                }
            }
        });

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function set_bwmk() {
            var setBwmk = document.getElementById('set_bwmk').value;
            var nobwmk = document.getElementById('nobwmk');
            var ketbwmk = document.getElementById('ketbwmk');

            var originalNobwmk = '<?php echo addslashes($data['nobwmk']); ?>';
            var originalKetbwmk = '<?php echo addslashes($data['ketbwmk']); ?>';

            if (setBwmk === 'NON BWMK') {
                nobwmk.value = '-';
                ketbwmk.value = '-';
                nobwmk.readOnly = true;
                ketbwmk.readOnly = true;
                nobwmk.style.backgroundColor = '#e9ecef';
                ketbwmk.style.backgroundColor = '#e9ecef';
            } else {
                // Jika nilai original adalah '-' atau kosong, set ke string kosong
                nobwmk.value = (originalNobwmk === '-' || !originalNobwmk) ? '' : originalNobwmk;
                ketbwmk.value = (originalKetbwmk === '-' || !originalKetbwmk) ? '' : originalKetbwmk;
                nobwmk.readOnly = false;
                ketbwmk.readOnly = false;
                nobwmk.style.backgroundColor = '#fff';
                ketbwmk.style.backgroundColor = '#fff';
            }
        }

        // Pastikan DOM sudah dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Set kondisi awal
            set_bwmk();

            // Tambahkan event listener untuk perubahan
            document.getElementById('set_bwmk').addEventListener('change', set_bwmk);
        });

        // Tambahkan event listener untuk form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            var setBwmk = document.getElementById('set_bwmk').value;
            if (setBwmk === 'NON BWMK') {
                document.getElementById('nobwmk').value = '-';
                document.getElementById('ketbwmk').value = '-';
            }
        });

        function setCurrentTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            document.getElementById('jam_droping').value = `${hours}:${minutes}`;
        }

        function terbilang(angka) {
            var bilangan = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh',
                'Sebelas'
            ];
            angka = parseInt(angka);
            if (angka === 0) {
                return 'Nol';
            }
            if (angka < 12) {
                return bilangan[angka];
            } else if (angka < 20) {
                return bilangan[angka - 10] + ' Belas';
            } else if (angka < 100) {
                var puluh = Math.floor(angka / 10);
                var sisa = angka % 10;
                return bilangan[puluh] + ' Puluh' + (sisa > 0 ? ' ' + terbilang(sisa) : '');
            } else if (angka < 200) {
                return 'Seratus' + (angka - 100 > 0 ? ' ' + terbilang(angka - 100) : '');
            } else if (angka < 1000) {
                var ratus = Math.floor(angka / 100);
                var sisa = angka % 100;
                return bilangan[ratus] + ' Ratus' + (sisa > 0 ? ' ' + terbilang(sisa) : '');
            } else if (angka < 2000) {
                return 'Seribu' + (angka - 1000 > 0 ? ' ' + terbilang(angka - 1000) : '');
            } else if (angka < 1000000) {
                var ribu = Math.floor(angka / 1000);
                var sisa = angka % 1000;
                return terbilang(ribu) + ' Ribu' + (sisa > 0 ? ' ' + terbilang(sisa) : '');
            } else if (angka < 1000000000) {
                var juta = Math.floor(angka / 1000000);
                var sisa = angka % 1000000;
                return terbilang(juta) + ' Juta' + (sisa > 0 ? ' ' + terbilang(sisa) : '');
            } else if (angka < 1000000000000) {
                var milyar = Math.floor(angka / 1000000000);
                var sisa = angka % 1000000000;
                return terbilang(milyar) + ' Milyar' + (sisa > 0 ? ' ' + terbilang(sisa) : '');
            }
        }

        function updateTerbilang(field) {
            var value = document.getElementById(field).value.replace(/,/g, '');

            // Untuk field persentase, gunakan terbilangProvisi
            if (field === 'sukbung' || field === 'prov') {
                var number = parseFloat(value);
                if (!isNaN(number)) {
                    var hasil = terbilangProvisi(number);
                    document.getElementById('t' + field).textContent = hasil;
                    // Untuk prov, update hidden juga
                    if (field === 'prov') {
                        document.getElementById('hidden_tprov').value = hasil;
                    }
                } else {
                    document.getElementById('t' + field).textContent = '';
                    if (field === 'prov') {
                        document.getElementById('hidden_tprov').value = '';
                    }
                }
            } else if (field === 'jw') {
                var hasil = terbilang(parseInt(value));
                document.getElementById('t' + field).textContent = hasil + '';
            } else {
                var hasil = terbilang(parseInt(value));
                document.getElementById('t' + field).textContent = hasil + ' Rupiah';
            }

            // Update hidden inputs
            if (field === 'nilpenj') {
                document.getElementById('hidden_tnilpenj').value = hasil + ' Rupiah';
            } else if (field === 'asjw') {
                document.getElementById('hidden_tasjw').value = hasil + ' Rupiah';
            } else if (field === 'asken') {
                document.getElementById('hidden_tasken').value = hasil + ' Rupiah';
            } else if (field === 'totasuransi') {
                document.getElementById('hidden_ttotasuransi').value = hasil + ' Rupiah';
            } else if (field === 'notaris') {
                document.getElementById('hidden_tnotaris').value = hasil + ' Rupiah';
            } else if (field === 'materai') {
                document.getElementById('hidden_tmaterai').value = hasil + ' Rupiah';
            } else if (field === 'total') {
                document.getElementById('hidden_ttotal').value = hasil + ' Rupiah';
            } else if (field === 'adm') {
                document.getElementById('hidden_tadm').value = hasil + ' Rupiah';
            }
        }

        // Panggil updateTerbilang saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            updateTerbilang('angpok');
            updateTerbilang('angbung');
            updateTerbilang('totang');
            updateTerbilang('jw');
            updateTerbilang('prov');
            updateTerbilang('adm');
            updateTerbilang('asjw');
            updateTerbilang('asken');
            updateTerbilang('materai');
            updateTerbilang('totasuransi');
            updateTerbilang('notaris');
            updateTerbilang('sukbung');
            updateTerbilang('plafond');
            updateTerbilang('nilpenj');
            updateTerbilang('total');
        });

        // Tambahkan fungsi hitungAngsuran()
        function hitungAngsuran() {
            // Ambil nilai input
            let plafond = parseFloat(document.getElementById('plafond').value.replace(/,/g, '')) || 0;
            let sukuBunga = parseFloat(document.getElementById('sukbung').value) || 0;
            let jangkaWaktu = parseInt(document.getElementById('jw').value) || 0;

            // Hitung angsuran pokok (dengan 2 desimal)
            let angsuranPokok = (plafond / jangkaWaktu).toFixed(2);

            // Hitung angsuran bunga (bunga per bulan dengan 2 desimal)
            let angsuranBunga = ((plafond * (sukuBunga / 100)) / 12).toFixed(2);

            // Hitung total angsuran (dengan 2 desimal)
            let totalAngsuran = (parseFloat(angsuranPokok) + parseFloat(angsuranBunga)).toFixed(2);

            // Update nilai input dengan format ribuan dan 2 desimal
            document.getElementById('angpok').value = formatDecimal(angsuranPokok);
            document.getElementById('angbung').value = formatDecimal(angsuranBunga);
            document.getElementById('totang').value = formatDecimal(totalAngsuran);

            // Update terbilang
            updateTerbilang('angpok');
            updateTerbilang('angbung');
            updateTerbilang('totang');

            // Setelah menghitung total angsuran, hitung nilai penjamin
            hitungNilaiPenjamin();
        }

        function formatDecimal(num) {
            // Memformat angka dengan pemisah ribuan dan 2 desimal
            let parts = parseFloat(num).toFixed(2).toString().split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return parts.join('.');
        }

        function formatNumberProvisi(input) {
            // Hapus semua karakter kecuali angka dan titik
            let value = input.value.replace(/[^\d.]/g, '');

            // Pastikan hanya ada satu titik desimal
            let parts = value.split('.');
            if (parts.length > 2) {
                parts = [parts[0], parts.slice(1).join('')];
            }

            // Batasi 2 digit desimal
            if (parts[1]) {
                parts[1] = parts[1].slice(0, 2);
            }

            // Gabungkan kembali
            input.value = parts.join('.');
        }

        function updateTerbilangProvisi() {
            let input = document.getElementById('prov');
            let value = input.value;
            let number = parseFloat(value);

            if (!isNaN(number)) {
                let terbilang = terbilangProvisi(number);
                let terbilangText = terbilang + '';
                document.getElementById('tprov').textContent = terbilangText;
                document.getElementById('hidden_tprov').value = terbilangText;
            } else {
                document.getElementById('tprov').textContent = '';
                document.getElementById('hidden_tprov').value = '';
            }
        }

        function terbilangProvisi(number) {
            let angka = ['Nol', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan'];
            let terbilang = '';

            // Pisahkan bagian desimal
            let parts = number.toString().split('.');
            let wholeNumber = parts[0];
            let decimal = parts[1] ? parts[1] : '';

            // Konversi setiap digit bilangan bulat
            for (let i = 0; i < wholeNumber.length; i++) {
                let digit = parseInt(wholeNumber[i]);
                if (!isNaN(digit)) {
                    if (terbilang !== '') terbilang += ' ';
                    terbilang += angka[digit];
                }
            }

            // Konversi bagian desimal
            if (decimal) {
                terbilang += ' Koma';
                for (let i = 0; i < decimal.length; i++) {
                    let digit = parseInt(decimal[i]);
                    if (!isNaN(digit)) {
                        terbilang += ' ' + angka[digit];
                    }
                }
            }

            return terbilang;
        }

        function hitungTotalBiaya() {
            // Ambil nilai plafond dan provisi untuk menghitung nilai provisi
            let plafond = parseFloat(document.getElementById('plafond').value.replace(/,/g, '')) || 0;
            let provisiPersen = parseFloat(document.getElementById('prov').value) || 0;
            let nilaiProvisi = plafond * (provisiPersen / 100);

            // Ambil nilai komponen biaya lainnya
            let administrasi = parseFloat(document.getElementById('adm').value.replace(/,/g, '')) || 0;
            let notaris = parseFloat(document.getElementById('notaris').value.replace(/,/g, '')) || 0;
            let asuransiJiwa = parseFloat(document.getElementById('asjw').value.replace(/,/g, '')) || 0;
            let asuransiKendaraan = parseFloat(document.getElementById('asken').value.replace(/,/g, '')) || 0;

            // Hitung total biaya
            let totalBiaya = nilaiProvisi + administrasi + notaris + asuransiJiwa + asuransiKendaraan;

            // Update nilai input total
            document.getElementById('total').value = formatDecimal(totalBiaya);

            // Update terbilang
            updateTerbilang('total');
        }

        // Tambahkan event listener untuk semua input yang mempengaruhi total biaya
        document.getElementById('plafond').addEventListener('keyup', function() {
            formatNumber(this);
            updateTerbilang('plafond');
            hitungTotalBiaya();
        });

        document.getElementById('prov').addEventListener('keyup', function() {
            formatNumberProvisi(this);
            updateTerbilangProvisi();
            hitungTotalBiaya();
        });

        document.getElementById('adm').addEventListener('keyup', function() {
            formatNumber(this);
            updateTerbilang('adm');
            hitungTotalBiaya();
        });

        document.getElementById('notaris').addEventListener('keyup', function() {
            formatNumber(this);
            updateTerbilang('notaris');
            hitungTotalBiaya();
        });

        // Modifikasi event listener yang sudah ada untuk asuransi
        document.getElementById('asjw').addEventListener('keyup', function() {
            formatNumber(this);
            updateTerbilang('asjw');
            hitungTotalAsuransi();
            hitungTotalBiaya();
        });

        document.getElementById('asken').addEventListener('keyup', function() {
            formatNumber(this);
            updateTerbilang('asken');
            hitungTotalAsuransi();
            hitungTotalBiaya();
        });

        // Panggil hitungTotalBiaya saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            hitungTotalBiaya();
        });

        function hitungTotalAsuransi() {
            // Ambil nilai asuransi jiwa dan kendaraan
            let asuransiJiwa = parseFloat(document.getElementById('asjw').value.replace(/,/g, '')) || 0;
            let asuransiKendaraan = parseFloat(document.getElementById('asken').value.replace(/,/g, '')) || 0;

            // Hitung total asuransi
            let totalAsuransi = asuransiJiwa + asuransiKendaraan;

            // Update nilai input total asuransi
            document.getElementById('totasuransi').value = formatDecimal(totalAsuransi);

            // Update terbilang
            updateTerbilang('totasuransi');
        }

        function hitungNilaiPenjamin() {
            // Ambil nilai total angsuran dan jangka waktu
            let totalAngsuran = parseFloat(document.getElementById('totang').value.replace(/,/g, '')) || 0;
            let jangkaWaktu = parseInt(document.getElementById('jw').value) || 0;

            // Hitung nilai penjamin
            let nilaiPenjamin = totalAngsuran * jangkaWaktu;

            // Update nilai input nilai penjamin
            document.getElementById('nilpenj').value = formatDecimal(nilaiPenjamin);

            // Update terbilang
            updateTerbilang('nilpenj');
        }

        function formatJam(input) {
            // Simpan posisi kursor dan nilai sebelumnya
            let cursorPos = input.selectionStart;
            let oldValue = input.value;
            let oldLength = oldValue.length;

            // Hapus semua karakter non-digit
            let value = input.value.replace(/\D/g, '');

            // Batasi panjang input maksimal 4 digit
            value = value.substring(0, 4);

            // Format jam dan menit
            if (value.length >= 3) {
                let jam = parseInt(value.substring(0, 2));
                // Validasi jam tidak lebih dari 23
                if (jam > 23) {
                    jam = 23;
                    value = '23' + value.substring(2);
                }

                // Tambahkan separator ':'
                value = value.substring(0, 2) + ':' + value.substring(2);
            } else if (value.length === 2) {
                let jam = parseInt(value);
                if (jam > 23) {
                    value = '23';
                }
                // Hanya tambahkan ':' jika bukan hasil dari backspace
                if (oldLength < value.length) {
                    value += ':';
                }
            }

            // Jika ada menit, validasi tidak lebih dari 59
            if (value.length > 3) {
                let menit = parseInt(value.substring(3));
                if (menit > 59) {
                    menit = 59;
                    value = value.substring(0, 3) + '59';
                }
            }

            input.value = value;

            // Hitung posisi kursor yang benar
            let newCursorPos = cursorPos;
            if (oldLength > value.length) {
                // Jika menghapus
                newCursorPos = Math.max(0, cursorPos - 1);
                // Jika menghapus tepat sebelum ':', mundur satu lagi
                if (oldValue.charAt(cursorPos) === ':') {
                    newCursorPos = Math.max(0, newCursorPos - 1);
                }
            } else if (value.length > oldLength) {
                // Jika menambah karakter
                newCursorPos = cursorPos + (value.length - oldLength);
                // Jika melewati ':', maju satu
                if (value.length >= 3 && cursorPos >= 2) {
                    newCursorPos++;
                }
            }

            // Terapkan posisi kursor
            setTimeout(() => {
                input.setSelectionRange(newCursorPos, newCursorPos);
            }, 0);
        }

        // Tambahkan event listener untuk input jam_droping
        document.getElementById('jam_droping').addEventListener('input', function(e) {
            formatJam(this);
        });

        document.getElementById('enable_decimal').addEventListener('change', function() {
            const plafondInput = document.getElementById('plafond');
            const currentValue = plafondInput.value.replace(/,/g, '');

            if (this.checked) {
                // Jika checkbox dicentang, izinkan 2 desimal
                if (currentValue) {
                    const numValue = parseFloat(currentValue);
                    plafondInput.value = formatDecimal(numValue);
                }
            } else {
                // Jika checkbox tidak dicentang, bulatkan ke bilangan bulat
                if (currentValue) {
                    const numValue = Math.round(parseFloat(currentValue));
                    plafondInput.value = numValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                }
            }

            // Hitung ulang angsuran
            hitungAngsuran();
        });

        // Tambahkan inisialisasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const plafondInput = document.getElementById('plafond');
            const enableDecimal = document.getElementById('enable_decimal');

            // Cek apakah nilai plafond memiliki desimal
            const value = plafondInput.value.replace(/,/g, '');
            const hasDecimal = value.includes('.') && parseFloat(value) % 1 !== 0;

            // Set checkbox sesuai dengan ada tidaknya desimal
            enableDecimal.checked = hasDecimal;

            // Format ulang nilai plafond sesuai dengan status checkbox
            if (hasDecimal) {
                plafondInput.value = formatDecimal(parseFloat(value));
            }
        });

        function hitungNominalProvisi() {
            // Ambil nilai plafond dan provisi
            let plafond = parseFloat(document.getElementById('plafond').value.replace(/,/g, '')) || 0;
            let provisiPersen = parseFloat(document.getElementById('prov').value) || 0;

            // Hitung nominal provisi
            let nominalProvisi = plafond * (provisiPersen / 100);

            // Update nilai input nominal provisi
            document.getElementById('nomprov').value = formatDecimal(nominalProvisi);

            // Update terbilang
            let terbilangNominalProvisi = terbilang(Math.round(nominalProvisi)) + ' Rupiah';
            document.getElementById('tnomprov').textContent = terbilangNominalProvisi;
            document.getElementById('hidden_tnomprov').value = terbilangNominalProvisi;

            // Update total biaya
            hitungTotalBiaya();
        }

        // Cegah ENTER di textarea ketbwmk
        document.addEventListener('DOMContentLoaded', function() {
            var ketbwmk = document.getElementById('ketbwmk');
            if (ketbwmk) {
                ketbwmk.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>

</html>