<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil data droping berdasarkan noreg
$noreg = $_GET['noreg'];
$query = mysqli_query($connect, "SELECT d.*, p.niknas, p.norek, n.nama as nama_nasabah 
    FROM droping d 
    LEFT JOIN pengajuan p ON p.noreg = d.noreg
    LEFT JOIN nasabah n ON n.nik = p.niknas
    WHERE d.noreg='$noreg'");
$data = mysqli_fetch_array($query);

// Proses update data
if (isset($_POST['submit'])) {
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
    $sukbung = $_POST['sukbung'];
    $jw = $_POST['jw'];
    $prov = $_POST['prov'];
    $tprov = isset($_POST['tprov']) ? $_POST['tprov'] : terbilang($prov) . '-';
    $angpok = str_replace(',', '', $_POST['angpok']);
    $angbung = str_replace(',', '', $_POST['angbung']);
    $totang = str_replace(',', '', $_POST['totang']);

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
        plafond = '$plafond',
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
        peng_kredit = '$peng_kredit',
        jns_asjw = '$jns_asjw',
        prov = NULLIF('$prov', ''),
        adm = NULLIF('$adm', ''),
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
        status = '$status'
        WHERE noreg = '$noreg'");

    // Update status di tabel pengajuan
    $query_pengajuan = mysqli_query($connect, "UPDATE pengajuan SET 
        norek = '$norek',
        status = '$status'
        WHERE noreg = '$noreg'");

    // Update status di tabel komite
    $query_komite = mysqli_query($connect, "UPDATE komite SET 
        status = '$status'
        WHERE noreg = '$noreg'");

    if ($query_droping && $query_pengajuan && $query_komite) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil diperbarui di semua tabel!',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'edit-droping.php?noreg=" . $noreg . "';
                    }
                });
            });
        </script>";
    } else {
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

    if ($angka < 12) {
        return $bilangan[$angka];
    } elseif ($angka < 20) {
        return $bilangan[$angka - 10] . ' Belas';
    } elseif ($angka < 100) {
        return $bilangan[floor($angka / 10)] . ' Puluh ' . $bilangan[$angka % 10];
    } elseif ($angka < 200) {
        return 'Seratus ' . terbilang($angka - 100);
    } elseif ($angka < 1000) {
        return $bilangan[floor($angka / 100)] . ' Ratus ' . terbilang($angka % 100);
    } elseif ($angka < 2000) {
        return 'Seribu ' . terbilang($angka - 1000);
    } elseif ($angka < 1000000) {
        return terbilang(floor($angka / 1000)) . ' Ribu ' . terbilang($angka % 1000);
    } elseif ($angka < 1000000000) {
        return terbilang(floor($angka / 1000000)) . ' Juta ' . terbilang($angka % 1000000);
    } elseif ($angka < 1000000000000) {
        return terbilang(floor($angka / 1000000000)) . ' Milyar ' . terbilang($angka % 1000000000);
    }
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
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">No. Registrasi</label>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $data['noreg']; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="mb-3">
                                                    <label class="form-label">Nama Nasabah</label>
                                                    <input type="text" class="form-control"
                                                        value="<?php echo $data['nama_nasabah']; ?>" readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Surat dan Nomor -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Surat dan Nomor</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
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
                                                <div class="mb-3">
                                                    <label class="form-label">No.Surat Persetujuan Direksi</label>
                                                    <input type="text" name="nobwmk" id="nobwmk" class="form-control"
                                                        value="<?php echo !empty($data['nobwmk']) ? $data['nobwmk'] : '-'; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Keterangan Lainnya BWMK</label>
                                                    <textarea name="ketbwmk" id="ketbwmk"
                                                        class="form-control"><?php echo !empty($data['ketbwmk']) ? $data['ketbwmk'] : '-'; ?></textarea>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">No. SPP</label>
                                                    <input type="text" name="nospp" class="form-control"
                                                        value="<?php echo $data['nospp']; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">No. Surat</label>
                                                    <input type="text" name="nosurat" class="form-control"
                                                        value="<?php echo $data['nosurat']; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">No. STPK</label>
                                                    <input type="text" name="nostpk" class="form-control"
                                                        value="<?php echo $data['nostpk']; ?>">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">No. CIF</label>
                                                    <input type="text" name="nocif" class="form-control"
                                                        value="<?php echo $data['nocif']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">No. Loan</label>
                                                    <input type="text" name="noloan" class="form-control"
                                                        value="<?php echo $data['noloan']; ?>" required>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">No. Rekening</label>
                                                    <input type="text" name="norek" class="form-control"
                                                        value="<?php echo $data['norek']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <!-- Data Tanggal dan Metode -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Tanggal dan Metode</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4">
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
                                        </div>
                                        <div class="row">
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
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Metode Perhitungan</label>
                                                    <select name="metode" class="form-control">
                                                        <option value="Fixed Rate"
                                                            <?php echo ($data['metode'] == 'Fixed Rate') ? 'selected' : ''; ?>>
                                                            Fixed Rate</option>
                                                        <option value="Sliding Rate"
                                                            <?php echo ($data['metode'] == 'Sliding Rate') ? 'selected' : ''; ?>>
                                                            Sliding Rate</option>
                                                        <option value="Flat Rate"
                                                            <?php echo ($data['metode'] == 'Flat Rate') ? 'selected' : ''; ?>>
                                                            Flat Rate</option>
                                                        <option value="Floating Rate"
                                                            <?php echo ($data['metode'] == 'Floating Rate') ? 'selected' : ''; ?>>
                                                            Floating Rate</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Cara Perhitungan</label>
                                                    <select name="cara" class="form-control">
                                                        <option value="Flat"
                                                            <?php echo ($data['cara'] == 'Flat') ? 'selected' : ''; ?>>
                                                            Flat</option>
                                                        <option value="Anuitas"
                                                            <?php echo ($data['cara'] == 'Anuitas') ? 'selected' : ''; ?>>
                                                            Anuitas</option>
                                                        <option value="Sliding"
                                                            <?php echo ($data['cara'] == 'Sliding') ? 'selected' : ''; ?>>
                                                            Sliding</option>
                                                        <option value="Bullet Payment"
                                                            <?php echo ($data['cara'] == 'Bullet Payment') ? 'selected' : ''; ?>>
                                                            Bullet Payment</option>
                                                    </select>
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
                                                    <label class="form-label">Plafond</label>
                                                    <input type="text" name="plafond" id="plafond" class="form-control"
                                                        value="<?php echo number_format($data['plafond'], 0, ',', ','); ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('plafond'); hitungAngsuran();">
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
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Provisi (%)</label>
                                                    <input type="text" name="prov" id="prov" class="form-control"
                                                        value="<?php echo $data['prov']; ?>"
                                                        onkeyup="formatNumberProvisi(this); updateTerbilangProvisi();">
                                                    <small class="text-muted terbilang-text" id="tprov"></small>
                                                    <input type="hidden" name="tprov" id="hidden_tprov"
                                                        value="<?php echo isset($data['tprov']) ? $data['tprov'] : ''; ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Administrasi</label>
                                                    <input type="text" name="adm" id="adm" class="form-control"
                                                        value="<?php echo $data['adm']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('adm');">
                                                    <small class="text-muted terbilang-text" id="tadm"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Materai</label>
                                                    <input type="text" name="materai" id="materai" class="form-control"
                                                        value="<?php echo $data['materai']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('materai');">
                                                    <small class="text-muted terbilang-text" id="tmaterai"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="mb-3">
                                                    <label class="form-label">Notaris</label>
                                                    <input type="text" name="notaris" id="notaris" class="form-control"
                                                        value="<?php echo $data['notaris']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('notaris');">
                                                    <small class="text-muted terbilang-text" id="tnotaris"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Asuransi Jiwa</label>
                                                    <input type="text" name="asjw" id="asjw" class="form-control"
                                                        value="<?php echo $data['asjw']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('asjw');">
                                                    <small class="text-muted terbilang-text" id="tasjw"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Asuransi Kendaraan</label>
                                                    <input type="text" name="asken" id="asken" class="form-control"
                                                        value="<?php echo $data['asken']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('asken');">
                                                    <small class="text-muted terbilang-text" id="tasken"></small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Total Asuransi</label>
                                                    <input type="text" name="totasuransi" id="totasuransi"
                                                        class="form-control" value="<?php echo $data['totasuransi']; ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('totasuransi');">
                                                    <small class="text-muted terbilang-text" id="ttotasuransi"></small>
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
                                                        onkeyup="formatNumber(this); updateTerbilang('angpok');"
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
                                                        onkeyup="formatNumber(this); updateTerbilang('angbung');"
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
                                                        onkeyup="formatNumber(this); updateTerbilang('totang');"
                                                        readonly>
                                                    <small class="text-muted terbilang-text" id="ttotang"></small>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Nilai Penjaminan</label>
                                                    <input type="text" name="nilpenj" id="nilpenj" class="form-control"
                                                        value="<?php echo number_format($data['nilpenj'], 0, ',', ','); ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('nilpenj');">
                                                    <small class="text-muted terbilang-text" id="tnilpenj"></small>
                                                    <input type="hidden" name="tnilpenj" id="hidden_tnilpenj"
                                                        value="<?php echo $data['tnilpenj']; ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Di dalam card Data Biaya, tambahkan fields berikut -->

                                        <!-- Setelah field asuransi -->
                                        <div class="row">
                                            <div class="col-md-4">
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
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">Total Biaya</label>
                                                    <input type="text" name="total" id="total" class="form-control"
                                                        value="<?php echo number_format($data['total'], 0, ',', ','); ?>"
                                                        onkeyup="formatNumber(this); updateTerbilang('total');"
                                                        readonly>
                                                    <small class="text-muted terbilang-text" id="ttotal"></small>
                                                    <input type="hidden" name="hidden_ttotal" id="hidden_ttotal"
                                                        value="<?php echo $data['ttotal']; ?>">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Pengajuan -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Status Pengajuan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <select name="status" class="form-control">
                                                <option value="CAIR"
                                                    <?php echo ($data['status'] == 'CAIR') ? 'selected' : ''; ?>>CAIR
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
                                                <option value="REVISI"
                                                    <?php echo ($data['status'] == 'REVISI') ? 'selected' : ''; ?>>
                                                    REVISI</option>
                                                <option value="BATAL"
                                                    <?php echo ($data['status'] == 'BATAL') ? 'selected' : ''; ?>>BATAL
                                                </option>
                                                <option value="TOLAK"
                                                    <?php echo ($data['status'] == 'TOLAK') ? 'selected' : ''; ?>>TOLAK
                                                </option>
                                            </select>
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
            return new Intl.NumberFormat().format(input);
        }

        if (input.name === 'sukbung') {
            // Khusus untuk suku bunga (format persentase)
            let value = input.value.replace(/[^\d.]/g, ''); // Hanya izinkan angka dan titik
            // Batasi hingga 2 desimal
            if (value.includes('.')) {
                let parts = value.split('.');
                value = parts[0] + '.' + (parts[1] || '').slice(0, 2);
            }
            input.value = value;
        } else {
            // Format ribuan untuk field lainnya
            let value = input.value.replace(/\D/g, '');
            input.value = new Intl.NumberFormat().format(value);
        }
    }

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
        if (angka < 12) {
            return bilangan[angka];
        } else if (angka < 20) {
            return bilangan[angka - 10] + ' Belas';
        } else if (angka < 100) {
            return bilangan[Math.floor(angka / 10)] + ' Puluh ' + bilangan[angka % 10];
        } else if (angka < 200) {
            return 'Seratus ' + terbilang(angka - 100);
        } else if (angka < 1000) {
            return bilangan[Math.floor(angka / 100)] + ' Ratus ' + terbilang(angka % 100);
        } else if (angka < 2000) {
            return 'Seribu ' + terbilang(angka - 1000);
        } else if (angka < 1000000) {
            return terbilang(Math.floor(angka / 1000)) + ' Ribu ' + terbilang(angka % 1000);
        } else if (angka < 1000000000) {
            return terbilang(Math.floor(angka / 1000000)) + ' Juta ' + terbilang(angka % 1000000);
        } else if (angka < 1000000000000) {
            return terbilang(Math.floor(angka / 1000000000)) + ' Milyar ' + terbilang(angka % 1000000000);
        }
    }

    function updateTerbilang(field) {
        var value = document.getElementById(field).value.replace(/,/g, '');
        var hasil = terbilang(parseInt(value));

        // Update terbilang text
        if (field === 'sukbung') {
            document.getElementById('t' + field).textContent = hasil + '';
        } else if (field === 'jw') {
            document.getElementById('t' + field).textContent = hasil + '';
        } else {
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

        // Hitung angsuran pokok
        let angsuranPokok = plafond / jangkaWaktu;

        // Hitung angsuran bunga (bunga per bulan)
        let angsuranBunga = (plafond * (sukuBunga / 100)) / 12;

        // Hitung total angsuran
        let totalAngsuran = angsuranPokok + angsuranBunga;

        // Update nilai input
        document.getElementById('angpok').value = formatNumber(Math.round(angsuranPokok));
        document.getElementById('angbung').value = formatNumber(Math.round(angsuranBunga));
        document.getElementById('totang').value = formatNumber(Math.round(totalAngsuran));

        // Update terbilang
        updateTerbilang('angpok');
        updateTerbilang('angbung');
        updateTerbilang('totang');
    }

    // Panggil hitungAngsuran saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        hitungAngsuran();
        // ... existing code ...
    });

    function formatNumberProvisi(input) {
        // Hanya izinkan angka dan satu titik desimal
        let value = input.value.replace(/[^\d.]/g, '');

        // Pastikan hanya ada satu titik desimal
        let parts = value.split('.');
        if (parts.length > 2) {
            parts = [parts[0], parts.slice(1).join('')];
        }

        // Batasi hingga 2 angka desimal
        if (parts[1]) {
            parts[1] = parts[1].slice(0, 2);
        }

        input.value = parts.join('.');
    }

    function terbilangDesimal(angka) {
        let parts = angka.toString().split('.');
        let hasil = terbilang(parseInt(parts[0]));

        if (parts.length > 1) {
            // Tangani bagian desimal
            let decimal = parts[1];
            if (decimal.length === 1) decimal += '0';
            let desimalTerbilang = '';

            // Konversi setiap digit desimal
            for (let i = 0; i < decimal.length; i++) {
                if (i > 0) desimalTerbilang += ' ';
                desimalTerbilang += terbilang(parseInt(decimal[i]));
            }

            return hasil + ' Koma ' + desimalTerbilang;
        }

        return hasil;
    }

    function updateTerbilangProvisi() {
        let value = document.getElementById('prov').value;
        if (value === '') {
            document.getElementById('tprov').textContent = '';
            return;
        }

        let hasil = terbilangDesimal(parseFloat(value));
        document.getElementById('tprov').textContent = hasil;
    }

    function hitungTotalBiaya() {
        // Ambil nilai plafond dan provisi
        let plafond = parseFloat(document.getElementById('plafond').value.replace(/,/g, '')) || 0;
        let provisi = parseFloat(document.getElementById('prov').value) || 0;

        // Hitung total (plafond x provisi%)
        let total = plafond * (provisi / 100);

        // Update nilai input total
        document.getElementById('total').value = formatNumber(Math.round(total));

        // Update terbilang
        updateTerbilang('total');
    }

    // Modifikasi event listener untuk plafond dan provisi
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

    // Panggil hitungTotalBiaya saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        hitungTotalBiaya();
        // ... existing code ...
    });
    </script>
</body>

</html>