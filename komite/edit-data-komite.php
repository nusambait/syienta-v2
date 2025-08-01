<?php
// Include file koneksi database
include '../../config.php';
include '../config/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php");
    exit();
}

// Validasi user dari database
$username = $_SESSION['username'];
$query = "SELECT * FROM account WHERE username = ?";
$stmt = mysqli_prepare($connect, $query);
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}

$user = mysqli_fetch_assoc($result);
$_SESSION['role_id'] = $user['role_id'];
$_SESSION['nama'] = $user['nama'];
$_SESSION['kantor'] = $user['kantor'];
$_SESSION['key_app'] = $user['key_app'];

// Ambil id atau noreg dari URL
$noreg = isset($_GET['noreg']) ? $_GET['noreg'] : '';

// Jika noreg kosong, redirect ke halaman sebelumnya
if (!$noreg) {
    header('Location: ../index.php');
    exit();
}

// Query untuk mendapatkan semua status unik dari tabel komite
$statusQuery = "SELECT DISTINCT status FROM komite ORDER BY status ASC";
$statusResult = $connect->query($statusQuery);

if ($statusResult->num_rows == 0) {
    die("Status tidak ditemukan");
}

$statusList = [];
while ($statusRow = $statusResult->fetch_assoc()) {
    $statusList[] = $statusRow['status'];
}

// Query untuk mendapatkan data berdasarkan noreg
$sql = "SELECT * FROM komite WHERE noreg = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param('s', $noreg);
$stmt->execute();
$result = $stmt->get_result();

// Cek apakah data ditemukan
if ($result->num_rows == 0) {
    echo '<div style="text-align: center; margin: 20px;">';
    echo "Data tidak ada / belum masuk ke tabel komite, tunggu sampai minimal status data adalah 'PENGAJUAN_REKOMENDASI'";
    echo '<br><br>';
    echo '<a href="../index.php" class="komite-edit-back-btn" style="background: #6c757d; color: white; padding: 8px 15px; border-radius: 4px; text-decoration: none;">Kembali</a>';
    echo '</div>';
    die();
}

$row = $result->fetch_assoc();

// Ambil kantor dan key_app dari session
$userKantor = $_SESSION['kantor'];
$userKeyApp = $_SESSION['key_app'];

// Query untuk mendapatkan data account:
if ($userKeyApp == 'ADMIN') {
    // Jika ADMIN, tampilkan semua data tanpa batasan kantor
    $accountQuery = "SELECT nik, nama, kantor FROM account 
                    WHERE key_app IN ('ADM', 'AO', 'KABID', 'KACAB', 'DIREKSI')
                    ORDER BY kantor ASC, nama ASC";
    $stmt = $connect->prepare($accountQuery);
    $stmt->execute();
} else {
    // Untuk user lain:
    // 1. Data sesuai kantor user (ADM, AO, KABID, KACAB)
    // 2. Semua data DIREKSI dari semua kantor
    $accountQuery = "SELECT nik, nama, kantor FROM account 
                    WHERE (key_app IN ('ADM', 'AO', 'KABID', 'KACAB') AND kantor = ?)
                    OR key_app = 'DIREKSI'
                    ORDER BY kantor ASC, nama ASC";
    $stmt = $connect->prepare($accountQuery);
    $stmt->bind_param('s', $userKantor);
    $stmt->execute();
}

$accountResult = $stmt->get_result();
$accountList = [];
while ($accountRow = $accountResult->fetch_assoc()) {
    $accountList[] = $accountRow;
}

// Proses penyimpanan ketika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $pengaju = $_POST['pengaju'];
    $ket = empty(trim($_POST['ket'])) ? null : $_POST['ket'];
    $sandi = $_POST['sandi'];
    $komite1 = $_POST['komite1'];
    $ket1 = empty(trim($_POST['ket1'])) ? null : $_POST['ket1'];
    $komite2 = $_POST['komite2'];
    $ket2 = empty(trim($_POST['ket2'])) ? null : $_POST['ket2'];
    $komite3 = $_POST['komite3'];
    $ket3 = empty(trim($_POST['ket3'])) ? null : $_POST['ket3'];
    $komite4 = $_POST['komite4'];
    $ket4 = empty(trim($_POST['ket4'])) ? null : $_POST['ket4'];
    $kepatuhan = $_POST['kepatuhan'];
    $opini = $_POST['opini'];
    $analisa = $_POST['analisa'];
    $slik = $_POST['slik'];
    $lainnya = $_POST['lainnya'];

    // Mulai transaksi database
    $connect->begin_transaction();

    try {
        // Query update tabel komite menggunakan prepared statement
        $updateSql = "UPDATE komite SET 
            status = ?, 
            pengaju = ?,
            ket = ?,
            sandi = ?,
            komite1 = ?,
            ket1 = ?,
            komite2 = ?,
            ket2 = ?,
            komite3 = ?,
            ket3 = ?,
            komite4 = ?,
            ket4 = ?,
            kepatuhan = ?,
            opini = ?,
            analisa = ?,
            slik = ?,
            lainnya = ?
            WHERE noreg = ?";

        $stmt = $connect->prepare($updateSql);
        $stmt->bind_param('ssssssssssssssssss', $status, $pengaju, $ket, $sandi, $komite1, $ket1, $komite2, $ket2, $komite3, $ket3, $komite4, $ket4, $kepatuhan, $opini, $analisa, $slik, $lainnya, $noreg);
        $stmt->execute();

        // Update status di tabel pengajuan jika ada
        $updatePengajuanSql = "UPDATE pengajuan SET status = ? WHERE noreg = ?";
        $stmtPengajuan = $connect->prepare($updatePengajuanSql);
        $stmtPengajuan->bind_param('ss', $status, $noreg);
        $stmtPengajuan->execute();
        $pengajuanUpdated = $stmtPengajuan->affected_rows > 0;

        // Update status di tabel droping jika ada
        $updateDropingSql = "UPDATE droping SET status = ? WHERE noreg = ?";
        $stmtDroping = $connect->prepare($updateDropingSql);
        $stmtDroping->bind_param('ss', $status, $noreg);
        $stmtDroping->execute();
        $dropingUpdated = $stmtDroping->affected_rows > 0;

        // Log perubahan status
        $logMessage = "Status diubah menjadi: $status untuk noreg: $noreg";
        if ($pengajuanUpdated) $logMessage .= " (pengajuan updated)";
        if ($dropingUpdated) $logMessage .= " (droping updated)";
        $logMessage .= " (komite updated)";

        // Catat log ke file atau database jika diperlukan
        error_log("[" . date('Y-m-d H:i:s') . "] " . $logMessage . " - User: " . $_SESSION['username']);

        // Commit transaksi jika semua berhasil
        $connect->commit();

        // Buat pesan sukses yang informatif
        $successMessage = "Data berhasil diperbarui di tabel komite";
        if ($pengajuanUpdated) $successMessage .= ", pengajuan";
        if ($dropingUpdated) $successMessage .= ", droping";
        $successMessage .= ".";

        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '" . addslashes($successMessage) . "',
                    showConfirmButton: false,
                    timer: 2000
                }).then(() => {
                    window.location.href = '../administrasi/data-komite.php?noreg=" . $noreg . "';
                });
            });
        </script>";
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $connect->rollback();
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal!',
                    text: 'Gagal memperbarui data: " . addslashes($e->getMessage()) . "'
                });
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Komite - <?php echo $noreg; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .form-textarea {
            min-height: 60px !important;
            font-size: 0.8rem;
            resize: vertical;
            width: 100%;
            height: 220px;
            font-family: monospace;
        }

        .form-textarea-normal {
            min-height: 60px !important;
            font-size: 0.8rem;
            resize: vertical;
            width: 100%;
            height: 80px;
            font-family: monospace;
        }

        select {
            font-size: 0.8rem;
            font-family: monospace;
        }
    </style>

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
                            <h4 class="card-title mb-4">Edit Data Komite</h4>
                            <form method="POST" action="">
                                <!-- Data Identitas -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Identitas</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">No. Registrasi</label>
                                                    <input type="text" class="form-control" value="<?php echo $noreg; ?>" readonly>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Status Pengajuan</label>
                                                    <select name="status" class="form-select">
                                                        <?php foreach ($statusList as $statusItem) : ?>
                                                            <option value="<?php echo htmlspecialchars($statusItem ?? ''); ?>"
                                                                <?php echo ($row['status'] == $statusItem) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($statusItem ?? ''); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <div class="form-text text-info">
                                                        <i class="bi bi-info-circle"></i>
                                                        Perubahan status akan diupdate di tabel pengajuan, droping, dan komite secara otomatis.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Pengaju -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Pengaju</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" id="label-pengaju">Pengaju: <?php echo htmlspecialchars($row['pengaju'] ?? ''); ?></label>
                                                    <select name="pengaju" id="select-pengaju" class="form-select" onchange="updateLabelAndKeterangan('pengaju')">
                                                        <option value="" data-nama="">Pilih Pengaju</option>
                                                        <?php foreach ($accountList as $account) : ?>
                                                            <option value="<?php echo htmlspecialchars($account['nik'] ?? ''); ?>"
                                                                data-nama="<?php echo htmlspecialchars($account['nama'] ?? ''); ?>"
                                                                <?php echo ($row['pengaju'] == $account['nik']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($account['nama'] ?? '') . ($account['kantor'] ? ' ' . htmlspecialchars($account['kantor'] ?? '') : ''); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Keterangan</label>
                                                    <textarea id="ket" name="ket" class="form-control form-textarea"><?php echo htmlspecialchars($row['ket'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="mb-3">
                                                    <label class="form-label">Sandi</label>
                                                    <textarea id="sandi" name="sandi" class="form-control form-textarea-normal"><?php echo htmlspecialchars($row['sandi'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Komite -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Komite</h5>
                                    </div>
                                    <div class="card-body">
                                        <!-- Komite 1 -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" id="label-komite1">Komite 1: <?php echo htmlspecialchars($row['komite1'] ?? ''); ?></label>
                                                    <select name="komite1" id="select-komite1" class="form-select" onchange="updateLabelAndKeterangan(1)">
                                                        <option value="" data-nama="">Pilih Komite 1</option>
                                                        <?php foreach ($accountList as $account) : ?>
                                                            <option value="<?php echo htmlspecialchars($account['nik'] ?? ''); ?>"
                                                                data-nama="<?php echo htmlspecialchars($account['nama'] ?? ''); ?>"
                                                                <?php echo ($row['komite1'] == $account['nik']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($account['nama'] ?? '') . ($account['kantor'] ? ' ' . htmlspecialchars($account['kantor'] ?? '') : ''); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Keterangan 1</label>
                                                    <textarea id="ket1" name="ket1" class="form-control form-textarea"><?php echo htmlspecialchars($row['ket1'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Komite 2 -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" id="label-komite2">Komite 2: <?php echo htmlspecialchars($row['komite2'] ?? ''); ?></label>
                                                    <select name="komite2" id="select-komite2" class="form-select" onchange="updateLabelAndKeterangan(2)">
                                                        <option value="" data-nama="">Pilih Komite 2</option>
                                                        <?php foreach ($accountList as $account) : ?>
                                                            <option value="<?php echo htmlspecialchars($account['nik'] ?? ''); ?>"
                                                                data-nama="<?php echo htmlspecialchars($account['nama'] ?? ''); ?>"
                                                                <?php echo ($row['komite2'] == $account['nik']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($account['nama'] ?? '') . ($account['kantor'] ? ' ' . htmlspecialchars($account['kantor'] ?? '') : ''); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Keterangan 2</label>
                                                    <textarea id="ket2" name="ket2" class="form-control form-textarea"><?php echo htmlspecialchars($row['ket2'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Komite 3 -->
                                        <div class="row mb-4">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" id="label-komite3">Komite 3: <?php echo htmlspecialchars($row['komite3'] ?? ''); ?></label>
                                                    <select name="komite3" id="select-komite3" class="form-select" onchange="updateLabelAndKeterangan(3)">
                                                        <option value="" data-nama="">Pilih Komite 3</option>
                                                        <?php foreach ($accountList as $account) : ?>
                                                            <option value="<?php echo htmlspecialchars($account['nik'] ?? ''); ?>"
                                                                data-nama="<?php echo htmlspecialchars($account['nama'] ?? ''); ?>"
                                                                <?php echo ($row['komite3'] == $account['nik']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($account['nama'] ?? '') . ($account['kantor'] ? ' ' . htmlspecialchars($account['kantor'] ?? '') : ''); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Keterangan 3</label>
                                                    <textarea id="ket3" name="ket3" class="form-control form-textarea"><?php echo htmlspecialchars($row['ket3'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Komite 4 -->
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label" id="label-komite4">Komite 4: <?php echo htmlspecialchars($row['komite4'] ?? ''); ?></label>
                                                    <select name="komite4" id="select-komite4" class="form-select" onchange="updateLabelAndKeterangan(4)">
                                                        <option value="" data-nama="">Pilih Komite 4</option>
                                                        <?php foreach ($accountList as $account) : ?>
                                                            <option value="<?php echo htmlspecialchars($account['nik'] ?? ''); ?>"
                                                                data-nama="<?php echo htmlspecialchars($account['nama'] ?? ''); ?>"
                                                                <?php echo ($row['komite4'] == $account['nik']) ? 'selected' : ''; ?>>
                                                                <?php echo htmlspecialchars($account['nama'] ?? '') . ($account['kantor'] ? ' ' . htmlspecialchars($account['kantor'] ?? '') : ''); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Keterangan 4</label>
                                                    <textarea id="ket4" name="ket4" class="form-control form-textarea"><?php echo htmlspecialchars($row['ket4'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Data Tambahan -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">Data Tambahan</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Kepatuhan</label>
                                                    <input type="text" name="kepatuhan" class="form-control" value="<?php echo htmlspecialchars($row['kepatuhan'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label class="form-label">Opini</label>
                                                    <textarea id="opini" name="opini" class="form-control form-textarea"><?php echo htmlspecialchars($row['opini'] ?? ''); ?></textarea>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="mb-1">
                                                <span class="form-text text-muted">
                                                    File yang diizinkan: <strong>PDF</strong>, ukuran maksimal <strong>300MB</strong>.<br>
                                                    <span class="text-danger">Catatan: Jika Anda menekan tombol hapus (<i class="bi bi-x"></i>), file yang sudah diunggah akan dihapus secara permanen dari server. Pastikan Anda yakin sebelum menghapus file.</span>
                                                </span>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">File Analisa</label>
                                                    <div class="input-group">
                                                        <input type="text" name="analisa" id="analisa" class="form-control" value="<?php echo htmlspecialchars($row['analisa'] ?? ''); ?>" readonly>
                                                        <input type="file" id="file-analisa" accept="application/pdf" style="display:none">
                                                        <button class="btn btn-success" type="button" id="upload-analisa" title="Upload PDF"><i class="bi bi-upload"></i></button>
                                                        <button class="btn btn-danger" type="button" id="clear-analisa" title="Hapus File" disabled><i class="bi bi-x"></i></button>
                                                    </div>
                                                    <div class="form-text text-danger" id="analisa-error"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">File SLIK</label>
                                                    <div class="input-group">
                                                        <input type="text" name="slik" id="slik" class="form-control" value="<?php echo htmlspecialchars($row['slik'] ?? ''); ?>" readonly>
                                                        <input type="file" id="file-slik" accept="application/pdf" style="display:none">
                                                        <button class="btn btn-success" type="button" id="upload-slik" title="Upload PDF"><i class="bi bi-upload"></i></button>
                                                        <button class="btn btn-danger" type="button" id="clear-slik" title="Hapus File" disabled><i class="bi bi-x"></i></button>
                                                    </div>
                                                    <div class="form-text text-danger" id="slik-error"></div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label class="form-label">File Lainnya</label>
                                                    <div class="input-group">
                                                        <input type="text" name="lainnya" id="lainnya" class="form-control" value="<?php echo htmlspecialchars($row['lainnya'] ?? ''); ?>" readonly>
                                                        <input type="file" id="file-lainnya" accept="application/pdf" style="display:none">
                                                        <button class="btn btn-success" type="button" id="upload-lainnya" title="Upload PDF"><i class="bi bi-upload"></i></button>
                                                        <button class="btn btn-danger" type="button" id="clear-lainnya" title="Hapus File" disabled><i class="bi bi-x"></i></button>
                                                    </div>
                                                    <div class="form-text text-danger" id="lainnya-error"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <a href="../index.php" class="btn btn-secondary me-2">Kembali</a>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi konfirmasi saat tombol Simpan Perubahan ditekan
        function confirmSave() {
            return confirm("Apakah Anda yakin ingin menyimpan perubahan?");
        }
    </script>
    <script>
        // Script untuk toggle sidebar
        document.querySelector('.toggle-btn').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>

    <script>
        function showNotification(message) {
            // Buat elemen notifikasi
            const notification = document.createElement('div');
            notification.classList.add('notification');
            notification.textContent = message;

            // Tambahkan notifikasi ke dalam body
            document.body.appendChild(notification);

            // Tampilkan notifikasi dengan kelas 'show'
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);

            // Sembunyikan notifikasi setelah 3 detik
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => {
                    notification.remove();
                }, 500);
            }, 3000);
        }
    </script>

    <script>
        function updateLabelAndKeterangan(type) {
            let select, label, keteranganField;
            let selectedNik, selectedNama;

            if (type === 'pengaju') {
                select = document.getElementById('select-pengaju');
                label = document.getElementById('label-pengaju');
                keteranganField = document.getElementById('ket');
                selectedNik = select.value;
                selectedNama = select.options[select.selectedIndex].getAttribute('data-nama');

                label.textContent = `Pengaju: ${selectedNik}`;
                updateJsonContent(keteranganField, 'nik', selectedNik, selectedNama);

            } else {
                const komiteNumber = type;
                select = document.getElementById(`select-komite${komiteNumber}`);
                label = document.getElementById(`label-komite${komiteNumber}`);

                // Perbaikan logika untuk mendapatkan keterangan field yang benar
                const keteranganNumber = komiteNumber === 1 ? '' : (komiteNumber - 1);
                keteranganField = document.getElementById(`ket${keteranganNumber}`);

                selectedNik = select.value;
                selectedNama = select.options[select.selectedIndex].getAttribute('data-nama');

                label.textContent = `Komite ${komiteNumber}: ${selectedNik}`;

                // Jika komite2 diubah tanpa pilihan, kosongkan ket1
                if (komiteNumber === 2 && !selectedNik) {
                    const ket1Field = document.getElementById('ket1');
                    if (ket1Field) {
                        ket1Field.value = '';
                    }
                }

                // Update keterangan jika berupa JSON
                updateJsonContent(keteranganField, 'komite', selectedNik, selectedNama);

                // Update keterangan berikutnya untuk field 'nik'
                const nextKeteranganField = document.getElementById(`ket${komiteNumber}`);
                if (nextKeteranganField) {
                    updateJsonContent(nextKeteranganField, 'nik', selectedNik, selectedNama);
                }
            }
        }

        function updateJsonContent(field, key, newNik, newNama) {
            try {
                let content = field.value.trim();

                // Jika content kosong, biarkan textarea kosong
                if (!content) {
                    field.value = '';
                    return;
                }

                if (content.startsWith('{') && content.endsWith('}')) {
                    // Parse JSON
                    let jsonObj = JSON.parse(content);

                    // Update nilai yang sesuai
                    if (jsonObj[key] !== undefined) {
                        if (!newNik) {
                            field.value = ''; // Kosongkan field jika tidak ada nilai
                        } else {
                            jsonObj[key] = newNik;
                            field.value = JSON.stringify(jsonObj);
                        }
                    }
                } else if (content === newNik || content === `${newNik}` || content.startsWith(`${newNik} `)) {
                    // Jika bukan JSON, update tanpa tanda '-'
                    field.value = newNama ? `${newNik} ${newNama}` : (newNik || '');
                }
            } catch (e) {
                console.log('Error parsing JSON:', e);
                field.value = ''; // Kosongkan field jika terjadi error
            }
        }

        // Jalankan update untuk semua field saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Update pengaju
            updateLabelAndKeterangan('pengaju');

            // Update semua komite
            for (let i = 1; i <= 4; i++) {
                updateLabelAndKeterangan(i);
            }
        });
    </script>
    <script>
        function setClearButtonState(inputId, clearBtnId) {
            const input = document.getElementById(inputId);
            const clearBtn = document.getElementById(clearBtnId);
            clearBtn.disabled = !input.value;
        }

        setClearButtonState('analisa', 'clear-analisa');
        setClearButtonState('slik', 'clear-slik');
        setClearButtonState('lainnya', 'clear-lainnya');

        // Handle tombol upload
        ['analisa', 'slik', 'lainnya'].forEach(function(type) {
            document.getElementById('upload-' + type).addEventListener('click', function() {
                document.getElementById('file-' + type).click();
            });
            document.getElementById('file-' + type).addEventListener('change', function(e) {
                const file = e.target.files[0];
                const errorDiv = document.getElementById(type + '-error');
                errorDiv.textContent = '';
                if (!file) return;
                if (file.type !== 'application/pdf') {
                    errorDiv.textContent = 'Hanya file PDF yang diizinkan!';
                    e.target.value = '';
                    return;
                }
                if (file.size > 300 * 1024 * 1024) {
                    errorDiv.textContent = 'Ukuran file maksimal 300MB!';
                    e.target.value = '';
                    return;
                }
                // Upload via AJAX
                const formData = new FormData();
                formData.append('file', file);
                formData.append('noreg', '<?php echo $noreg; ?>');
                formData.append('type', type);
                fetch('upload_file_komite.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById(type).value = data.filename;
                            setClearButtonState(type, 'clear-' + type);
                            errorDiv.textContent = '';
                        } else {
                            errorDiv.textContent = data.message || 'Gagal upload file!';
                        }
                    })
                    .catch(() => {
                        errorDiv.textContent = 'Gagal upload file!';
                    });
            });
            document.getElementById('clear-' + type).addEventListener('click', function() {
                if (this.disabled) return;
                if (!confirm('Yakin hapus file ini?')) return;
                fetch('hapus_file_komite.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: 'noreg=<?php echo $noreg; ?>&type=' + type + '&filename=' + encodeURIComponent(document.getElementById(type).value)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById(type).value = '';
                            setClearButtonState(type, 'clear-' + type);
                            document.getElementById(type + '-error').textContent = '';
                        } else {
                            document.getElementById(type + '-error').textContent = data.message || 'Gagal hapus file!';
                        }
                    })
                    .catch(() => {
                        document.getElementById(type + '-error').textContent = 'Gagal hapus file!';
                    });
            });
            // Update tombol X saat input berubah
            document.getElementById(type).addEventListener('input', function() {
                setClearButtonState(type, 'clear-' + type);
            });
        });
    </script>
    <script>
        // Event listener untuk perubahan status
        document.addEventListener('DOMContentLoaded', function() {
            const statusSelect = document.querySelector('select[name="status"]');
            const originalStatus = statusSelect.value;

            statusSelect.addEventListener('change', function() {
                const newStatus = this.value;
                if (newStatus !== originalStatus) {
                    Swal.fire({
                        title: 'Perubahan Status',
                        text: 'Status akan diubah di tabel pengajuan, droping, dan komite. Lanjutkan?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Ubah Status',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (!result.isConfirmed) {
                            // Kembalikan ke status sebelumnya jika user membatalkan
                            this.value = originalStatus;
                        }
                    });
                }
            });
        });
    </script>
</body>

</html>