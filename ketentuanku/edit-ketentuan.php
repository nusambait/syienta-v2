<?php
session_start();
require_once __DIR__ . '/../config/init.php';
include '../../config.php';
include '../config/config.php';

if (!isset($_SESSION['key_app']) || ($_SESSION['key_app'] != 'SKAI' && $_SESSION['key_app'] != 'ADMIN')) {
    $_SESSION['error'] = "Anda tidak memiliki akses ke halaman ini!";
    header("Location: index.php");
    exit();
}

$id = isset($_GET['id']) ? mysqli_real_escape_string($connect, $_GET['id']) : '';
$query = "SELECT * FROM ketentuanku_surat WHERE id = '$id'";
$result = mysqli_query($connect, $query);
$data = mysqli_fetch_assoc($result);

if (!$data) {
    $_SESSION['error'] = "Data tidak ditemukan!";
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Ketentuan</title>
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
            <div class="row mb-4">
                <div class="col">
                    <h2>Edit Ketentuan</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <form action="update-ketentuan.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="id" value="<?php echo $data['id']; ?>">
                                <input type="hidden" name="old_file" value="<?php echo $data['file_surat']; ?>">

                                <div class="mb-3">
                                    <label for="nomor_ket" class="form-label">Nomor Ketentuan</label>
                                    <input type="text" class="form-control" id="nomor_ket" name="nomor_ket"
                                        value="<?php echo $data['nomor_ket']; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="judul_ket" class="form-label">Judul</label>
                                    <input type="text" class="form-control" id="judul_ket" name="judul_ket"
                                        value="<?php echo $data['judul_ket']; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="ttl_terbit" class="form-label">Tanggal Terbit</label>
                                    <input type="date" class="form-control" id="ttl_terbit" name="ttl_terbit"
                                        value="<?php echo $data['ttl_terbit']; ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="file_surat" class="form-label">File Surat (PDF)</label>
                                    <input type="file" class="form-control" id="file_surat" name="file_surat"
                                        accept=".pdf">
                                    <small class="text-muted">Biarkan kosong jika tidak ingin mengubah file</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">File Saat Ini</label>
                                    <div>
                                        <a href="<?php echo $base_url; ?>ketentuanku/uploads/<?php echo $data['file_surat']; ?>"
                                            target="_blank" class="btn btn-info btn-sm">
                                            <i class="bi bi-file-earmark-text"></i> Lihat File
                                        </a>
                                    </div>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>