<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek apakah parameter noreg ada
if (!isset($_GET['noreg'])) {
    $_SESSION['error_message'] = 'Nomor pengajuan tidak valid!';
    header('Location: input-pengajuan.php');
    exit;
}

$noreg = mysqli_real_escape_string($connect, $_GET['noreg']);

// Ambil data pengajuan berdasarkan noreg
$query = "SELECT p.status as status_pengajuan, p.*, n.* FROM pengajuan p 
          JOIN nasabah n ON p.niknas = n.nik 
          WHERE p.noreg = '$noreg'";
$result = mysqli_query($connect, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = 'Data pengajuan tidak ditemukan!';
    header('Location: input-pengajuan.php');
    exit;
}

$data = mysqli_fetch_assoc($result);

// Query untuk mengambil data AO dan FO
$query_ao = mysqli_query($connect, "SELECT kd_ao, nama FROM account WHERE jabatan = 'AO' ORDER BY nama ASC");
$query_fo = mysqli_query($connect, "SELECT a.id as FO, a.nama, a.jabatan 
    FROM account a 
    WHERE a.status = 'AKTIF' 
    ORDER BY 
        CASE a.jabatan
            WHEN 'FO' THEN 1
            WHEN 'AO' THEN 2
            WHEN 'ADM' THEN 3
            WHEN 'CS' THEN 4
            WHEN 'KABID' THEN 5
            WHEN 'KACAB' THEN 6
            WHEN 'PPK' THEN 7
            WHEN 'SEKPER' THEN 8
            WHEN 'DIREKSI' THEN 9
            ELSE 10
        END,
        a.nama ASC");

// Proses update data jika form disubmit
if (isset($_POST['update'])) {
    // Ambil data dari form
    $plafon_input = $_POST['jumlahPengajuan'];
    // Hapus titik ribuan, ganti koma desimal dengan titik
    $plafon_clean = str_replace('.', '', $plafon_input); // Hapus titik
    $plafon_clean = str_replace(',', '.', $plafon_clean); // Ganti koma jadi titik
    // Konversi ke float untuk memastikan format yang benar
    $plaf = (float)$plafon_clean;

    $tglPengajuan = mysqli_real_escape_string($connect, $_POST['tglPengajuan']);
    $norek = mysqli_real_escape_string($connect, $_POST['norek']);
    $jns_kredit = mysqli_real_escape_string($connect, $_POST['jns_kredit']);
    $penggunaan = mysqli_real_escape_string($connect, $_POST['penggunaan']);
    $prodkre = mysqli_real_escape_string($connect, $_POST['prodkre']);
    $jw = (int)$_POST['jw'];
    $sukbung = (float)$_POST['sukbung'];
    $angpok = (int)str_replace(['.', ','], '', $_POST['angpok']);
    $angbung = (int)str_replace(['.', ','], '', $_POST['angbung']);
    $totang = (int)str_replace(['.', ','], '', $_POST['totang']);
    $biaprov = (float)$_POST['biaprov'];
    $nomprov = (int)str_replace(['.', ','], '', $_POST['nomprov']);
    $adm = (int)str_replace(['.', ','], '', $_POST['adm']);
    $jaminan = mysqli_real_escape_string($connect, $_POST['jaminan']);
    $agen = mysqli_real_escape_string($connect, $_POST['agen']);
    $ket_peng = mysqli_real_escape_string($connect, $_POST['ket_peng']);
    $sumber = mysqli_real_escape_string($connect, $_POST['sumber']);
    $fo = mysqli_real_escape_string($connect, $_POST['fo']);
    $ao = mysqli_real_escape_string($connect, $_POST['ao'] ?? '');
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    // Query update dengan nilai plafon yang sudah dibersihkan
    $updateQuery = "UPDATE pengajuan SET 
                    tglpeng = '$tglPengajuan',
                    norek = '$norek',
                    plaf = $plaf,
                    jns_kredit = '$jns_kredit',
                    penggunaan = '$penggunaan',
                    prodkre = '$prodkre',
                    jw = $jw,
                    sukbung = $sukbung,
                    angpok = $angpok,
                    angbung = $angbung,
                    totang = $totang,
                    biaprov = $biaprov,
                    nomprov = $nomprov,
                    adm = $adm,
                    jaminan = '$jaminan',
                    ao = '$ao',
                    agen = '$agen',
                    ket_peng = '$ket_peng',
                    sumber = '$sumber',
                    FO = '$fo',
                    status = '$status'
                    WHERE noreg = '$noreg'";
    $updateDroping = "UPDATE droping SET status = '$status' WHERE noreg = '$noreg'";
    $updateKomite = "UPDATE komite SET status = '$status' WHERE noreg = '$noreg'";
    $success = mysqli_query($connect, $updateQuery);
    $successDroping = mysqli_query($connect, $updateDroping);
    $successKomite = mysqli_query($connect, $updateKomite);
    if ($success && $successDroping && $successKomite) {
        $_SESSION['success_message'] = 'Data pengajuan berhasil diperbarui!';
        header('Location: input-pengajuan.php');
        exit;
    } else {
        $errMsg = 'Gagal memperbarui data: ' . mysqli_error($connect);
        if (!$success) $errMsg .= "\nQuery pengajuan: $updateQuery";
        if (!$successDroping) $errMsg .= "\nQuery droping: $updateDroping";
        if (!$successKomite) $errMsg .= "\nQuery komite: $updateKomite";
        echo '<div class="alert alert-danger">' . nl2br($errMsg) . '</div>';
        $_SESSION['error_message'] = $errMsg;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pengajuan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2 class="mb-0">Edit Pengajuan</h2>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='input-pengajuan.php'">
                    <i class="bi bi-arrow-left"></i> Kembali
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <!-- Data Identitas -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Identitas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="noreg" class="form-label">Nomor Registrasi</label>
                                            <input type="text" class="form-control" id="noreg" name="noreg"
                                                value="<?php echo $data['noreg']; ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nik" class="form-label">NIK Nasabah</label>
                                            <input type="text" class="form-control" id="nik" name="nik"
                                                value="<?php echo $data['nik']; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama Nasabah</label>
                                            <input type="text" class="form-control" id="nama" name="nama"
                                                value="<?php echo $data['nama']; ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="norek" class="form-label">Nomor Rekening</label>
                                            <input type="text" class="form-control" id="norek" name="norek"
                                                value="<?php echo $data['norek']; ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Pengajuan -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Pengajuan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tglPengajuan" class="form-label">Tanggal Pengajuan</label>
                                            <input type="text" class="form-control date-input" id="tglPengajuan"
                                                name="tglPengajuan" value="<?php echo $data['tglpeng']; ?>"
                                                placeholder="dd-mm-yyyy" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="jumlahInput" class="form-label">Jumlah Pengajuan
                                                (Plafon)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control" id="jumlahInput"
                                                    name="jumlahPengajuan" value="<?php
                                                                                    // Format angka berdasarkan ada tidaknya desimal
                                                                                    $plaf = $data['plaf'];
                                                                                    $has_decimal = (floor($plaf) != $plaf);
                                                                                    echo $has_decimal ? number_format($plaf, 2, ',', '.') : number_format($plaf, 0, ',', '.');
                                                                                    ?>" required>
                                                <div class="input-group-text">
                                                    <input type="checkbox" name="enable_decimal" id="enable_decimal"
                                                        class="form-check-input mt-0" aria-label="Aktifkan desimal"
                                                        style="margin-right: 5px;"
                                                        <?php echo $has_decimal ? 'checked' : ''; ?>>
                                                    <small>Desimal</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="jw" class="form-label">Jangka Waktu (Bulan)</label>
                                            <input type="number" class="form-control" id="jw" name="jw"
                                                value="<?php echo $data['jw']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sukbung" class="form-label">Suku Bunga (%)</label>
                                            <input type="number" class="form-control" id="sukbung" name="sukbung"
                                                value="<?php echo $data['sukbung']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jns_kredit" class="form-label">Jenis Kredit</label>
                                            <select class="form-control" id="jns_kredit" name="jns_kredit" required>
                                                <option value="">Pilih Jenis Kredit</option>
                                                <option value="Instalment"
                                                    <?php echo ($data['jns_kredit'] == 'Installment') ? 'selected' : ''; ?>>
                                                    Installment</option>
                                                <option value="Konsumtif"
                                                    <?php echo ($data['jns_kredit'] == 'Konsumtif') ? 'selected' : ''; ?>>
                                                    Konsumtif</option>
                                                <option value="KRY"
                                                    <?php echo ($data['jns_kredit'] == 'KRY') ? 'selected' : ''; ?>>
                                                    Kredit Karyawan</option>
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
                                        <div class="mb-3">
                                            <label for="penggunaan" class="form-label">Penggunaan</label>
                                            <select class="form-control" id="penggunaan" name="penggunaan" required>
                                                <option value="">Pilih Penggunaan</option>
                                                <option value="Modal Kerja"
                                                    <?php echo ($data['penggunaan'] == 'Modal Kerja') ? 'selected' : ''; ?>>
                                                    Modal Kerja</option>
                                                <option value="Investasi"
                                                    <?php echo ($data['penggunaan'] == 'Investasi') ? 'selected' : ''; ?>>
                                                    Investasi</option>
                                                <option value="Konsumtif"
                                                    <?php echo ($data['penggunaan'] == 'Konsumtif') ? 'selected' : ''; ?>>
                                                    Konsumtif</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="prodkre" class="form-label">Produk Kredit</label>
                                            <input type="text" class="form-control" id="prodkre" name="prodkre"
                                                value="<?php echo $data['prodkre']; ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Angsuran -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Angsuran</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="angpok" class="form-label">Angsuran Pokok</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control readonly-blue" id="angpok"
                                                    name="angpok"
                                                    value="<?php echo number_format($data['angpok'], 0, ',', '.'); ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="angbung" class="form-label">Angsuran Bunga</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control readonly-blue" id="angbung"
                                                    name="angbung"
                                                    value="<?php echo number_format($data['angbung'], 0, ',', '.'); ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="totang" class="form-label">Total Angsuran</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control readonly-blue" id="totang"
                                                    name="totang"
                                                    value="<?php echo number_format($data['totang'], 0, ',', '.'); ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Biaya & Jaminan -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Biaya & Jaminan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="biaprov" class="form-label">Biaya Provisi (%)</label>
                                            <input type="number" step="any" class="form-control" id="biaprov"
                                                name="biaprov" value="<?php echo $data['biaprov']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nomprov" class="form-label">Nominal Provisi</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control" id="nomprov" name="nomprov"
                                                    value="<?php echo number_format($data['nomprov'], 0, ',', '.'); ?>"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="adm" class="form-label">Biaya Administrasi</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control" id="adm" name="adm"
                                                    value="<?php echo number_format($data['adm'], 0, ',', '.'); ?>"
                                                    required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jaminan" class="form-label">Jaminan</label>
                                            <textarea class="form-control" id="jaminan" name="jaminan" rows="2"
                                                required><?php echo $data['jaminan']; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Petugas & Sumber -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Petugas & Sumber</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Status Pengajuan</label>
                                            <?php if (isset($_SESSION['key_app']) && $_SESSION['key_app'] === 'ADMIN'): ?>
                                                <?php
                                                $status_options = [
                                                    'ACC_DIREKSI',
                                                    'ACC_KABID',
                                                    'ACC_KACAB',
                                                    'BATAL',
                                                    'CAIR',
                                                    'MCC_DIREKSI',
                                                    'MCC_KABID',
                                                    'MCC_KACAB',
                                                    'PENDING',
                                                    'PENDING_KACAB',
                                                    'PENGAJUAN_REKOMENDASI',
                                                    'REKOMENDASI',
                                                    'REVISI',
                                                    'SIAP CAIR',
                                                    'TOLAK',
                                                    'TOLAK_DIREKSI',
                                                    'TOLAK_KABID',
                                                    'TOLAK_KACAB'
                                                ];
                                                // Pastikan status dari DB ada di urutan pertama
                                                $current_status = $data['status_pengajuan'];
                                                $status_options = array_unique(array_merge([$current_status], $status_options));
                                                // Urutkan alfabet, tapi current_status tetap di atas
                                                $other_status = $status_options;
                                                unset($other_status[array_search($current_status, $other_status)]);
                                                $other_status = array_values($other_status);
                                                sort($other_status);
                                                $final_status_options = array_merge([$current_status], $other_status);
                                                ?>
                                                <select class="form-control" id="status" name="status" required>
                                                    <?php foreach ($final_status_options as $opt): ?>
                                                        <option value="<?php echo $opt; ?>" <?php echo ($current_status == $opt) ? 'selected' : ''; ?>><?php echo $opt; ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            <?php else: ?>
                                                <input type="text" class="form-control" id="status" name="status" value="<?php echo $data['status_pengajuan']; ?>" readonly>
                                            <?php endif; ?>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ao" class="form-label">Account Officer</label>
                                            <select class="form-control" id="ao" name="ao" required>
                                                <option value="">Pilih Account Officer</option>
                                                <?php while ($row = mysqli_fetch_assoc($query_ao)): ?>
                                                    <option value="<?php echo htmlspecialchars($row['kd_ao']); ?>"
                                                        <?php echo ($data['ao'] == $row['kd_ao']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($row['nama']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fo" class="form-label">Pembawa Berkas</label>
                                            <select class="form-control" id="fo" name="fo" required>
                                                <option value="">Pilih Pembawa Berkas</option>
                                                <?php while ($row = mysqli_fetch_assoc($query_fo)): ?>
                                                    <option value="<?php echo htmlspecialchars($row['FO']); ?>"
                                                        <?php echo ($data['FO'] == $row['FO']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($row['nama']); ?>
                                                    </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="agen" class="form-label">Agen</label>
                                            <input type="text" class="form-control" id="agen" name="agen"
                                                value="<?php echo $data['agen']; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="sumber" class="form-label">Sumber Pengajuan</label>
                                            <select class="form-control" id="sumber" name="sumber" required>
                                                <option value="">Pilih Sumber Pengajuan</option>
                                                <option value="50%"
                                                    <?php echo ($data['sumber'] == '50%') ? 'selected' : ''; ?>>50%
                                                </option>
                                                <option value="Promosi"
                                                    <?php echo ($data['sumber'] == 'Promosi') ? 'selected' : ''; ?>>
                                                    Promosi</option>
                                                <option value="Radio"
                                                    <?php echo ($data['sumber'] == 'Radio') ? 'selected' : ''; ?>>Radio
                                                </option>
                                                <option value="Spanduk"
                                                    <?php echo ($data['sumber'] == 'Spanduk') ? 'selected' : ''; ?>>
                                                    Spanduk</option>
                                                <option value="Baligho"
                                                    <?php echo ($data['sumber'] == 'Baligho') ? 'selected' : ''; ?>>
                                                    Baligho</option>
                                                <option value="Brosur"
                                                    <?php echo ($data['sumber'] == 'Brosur') ? 'selected' : ''; ?>>
                                                    Brosur</option>
                                                <option value="CGC"
                                                    <?php echo ($data['sumber'] == 'CGC') ? 'selected' : ''; ?>>CGC
                                                </option>
                                                <option value="Instagram"
                                                    <?php echo ($data['sumber'] == 'Instagram') ? 'selected' : ''; ?>>
                                                    Instagram</option>
                                                <option value="Facebook"
                                                    <?php echo ($data['sumber'] == 'Facebook') ? 'selected' : ''; ?>>
                                                    Facebook</option>
                                                <option value="Website"
                                                    <?php echo ($data['sumber'] == 'Website') ? 'selected' : ''; ?>>
                                                    Website</option>
                                                <option value="Google"
                                                    <?php echo ($data['sumber'] == 'Google') ? 'selected' : ''; ?>>
                                                    Google</option>
                                                <option value="X"
                                                    <?php echo ($data['sumber'] == 'X') ? 'selected' : ''; ?>>X
                                                    (Twitter)</option>
                                                <option value="Whatsapp"
                                                    <?php echo ($data['sumber'] == 'Whatsapp') ? 'selected' : ''; ?>>
                                                    Whatsapp</option>
                                                <option value="Komunal"
                                                    <?php echo ($data['sumber'] == 'Komunal') ? 'selected' : ''; ?>>
                                                    Komunal</option>
                                                <option value="Lainnya"
                                                    <?php echo ($data['sumber'] == 'Lainnya') ? 'selected' : ''; ?>>
                                                    Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ket_peng" class="form-label">Keterangan Pengajuan</label>
                                            <textarea class="form-control" id="ket_peng" name="ket_peng"
                                                rows="2"><?php echo $data['ket_peng']; ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('jumlahInput');
            const enableDecimal = document.getElementById('enable_decimal');
            if (input.value) {
                let value = input.value.replace(/\./g, '').replace(/,/g, '.');
                if (enableDecimal.checked) {
                    input.value = formatDecimal(parseFloat(value));
                } else {
                    input.value = formatRibuan(parseFloat(value));
                }
            }
        });

        function formatDecimal(num) {
            if (isNaN(num)) return '';
            let parts = num.toFixed(2).split('.');
            parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            return parts.join(',');
        }

        function formatRibuan(num) {
            if (isNaN(num)) return '';
            return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        document.getElementById('jumlahInput').addEventListener('input', function(e) {
            const enableDecimal = document.getElementById('enable_decimal').checked;
            let value = this.value.replace(/\./g, '').replace(/,/g, '.');
            if (enableDecimal) {
                if (value) {
                    const cursorPos = this.selectionStart;
                    const prevLength = this.value.length;
                    let num = parseFloat(value);
                    this.value = formatDecimal(num);
                    const newPos = cursorPos + (this.value.length - prevLength);
                    this.setSelectionRange(newPos, newPos);
                } else {
                    this.value = '';
                }
            } else {
                if (value) {
                    let num = parseFloat(value);
                    this.value = formatRibuan(num);
                } else {
                    this.value = '';
                }
            }
        });

        document.getElementById('enable_decimal').addEventListener('change', function() {
            const input = document.getElementById('jumlahInput');
            let value = input.value.replace(/\./g, '').replace(/,/g, '.');
            if (this.checked) {
                if (value) {
                    let numValue = parseFloat(value);
                    input.value = formatDecimal(numValue);
                }
            } else {
                if (value) {
                    let numValue = Math.round(parseFloat(value));
                    input.value = formatRibuan(numValue);
                }
            }
        });

        // Fungsi untuk menghitung angsuran
        function hitungAngsuran() {
            // Ambil nilai input dan bersihkan format ribuan/desimal
            const plafonStr = document.getElementById('jumlahInput').value.replace(/\./g, '').replace(/,/g, '.');
            const jw = parseInt(document.getElementById('jw').value) || 0;
            const sukbung = parseFloat(document.getElementById('sukbung').value) || 0;
            const biaprov = parseFloat(document.getElementById('biaprov').value) || 0;

            // Konversi plafon ke float
            const plafon = parseFloat(plafonStr);

            if (plafon && jw && sukbung) {
                // Hitung angsuran pokok per bulan (bulatkan ke bawah)
                const angsuranPokok = Math.floor(plafon / jw);

                // Hitung angsuran bunga per bulan (bulatkan ke bawah)
                // Rumus: (plafon * suku bunga tahunan) / 12 bulan
                const angsuranBunga = Math.floor((plafon * (sukbung / 100)) / 12);

                // Hitung total angsuran
                const totalAngsuran = angsuranPokok + angsuranBunga;

                // Update nilai input dengan format angka
                document.getElementById('angpok').value = formatNumber(angsuranPokok);
                document.getElementById('angbung').value = formatNumber(angsuranBunga);
                document.getElementById('totang').value = formatNumber(totalAngsuran);

                // Hitung nominal provisi
                if (plafon && biaprov) {
                    const nominalProvisi = Math.floor(plafon * (biaprov / 100));
                    document.getElementById('nomprov').value = formatNumber(nominalProvisi);
                }
            }
        }

        // Fungsi untuk format number dengan pemisah ribuan
        function formatNumber(num) {
            return Math.floor(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        // Tambahkan event listener untuk input yang mempengaruhi perhitungan
        document.getElementById('jumlahInput').addEventListener('input', hitungAngsuran);
        document.getElementById('jw').addEventListener('input', hitungAngsuran);
        document.getElementById('sukbung').addEventListener('input', hitungAngsuran);
        document.getElementById('biaprov').addEventListener('input', hitungAngsuran);

        // Jalankan hitungAngsuran saat halaman dimuat
        document.addEventListener('DOMContentLoaded', hitungAngsuran);
    </script>
</body>

</html>