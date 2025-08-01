<?php
session_start();
require_once __DIR__ . '/../../config/init.php';
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

if (isset($_POST['submit'])) {
    $kd_kantor = mysqli_real_escape_string($connect, $_POST['kd_kantor']);
    $nm_kantor = mysqli_real_escape_string($connect, $_POST['nm_kantor']);
    $short = mysqli_real_escape_string($connect, $_POST['short']);
    $almt = mysqli_real_escape_string($connect, $_POST['almt']);
    $kec = mysqli_real_escape_string($connect, $_POST['kecamatan']);
    $kab = mysqli_real_escape_string($connect, $_POST['kabupaten']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $nm_perusahaan = mysqli_real_escape_string($connect, $_POST['nm_perusahaan']);

    $query = mysqli_query($connect, "UPDATE kantor SET 
                                   nm_kantor='$nm_kantor',
                                   short='$short',
                                   almt='$almt',
                                   kec='$kec',
                                   kab='$kab',
                                   status='$status',
                                   nm_perusahaan='$nm_perusahaan'
                                   WHERE kd_kantor='$kd_kantor'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data kantor berhasil diperbarui!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='index.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memperbarui data kantor!',
                    showConfirmButton: true
                });
            });
        </script>";
    }
}

// Ambil data kantor yang akan diedit
$kd_kantor = mysqli_real_escape_string($connect, $_GET['kd_kantor']);
$query = mysqli_query($connect, "SELECT * FROM kantor WHERE kd_kantor='$kd_kantor'");
$data = mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kantor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0 py-0">Edit Kantor</h3>
                            </div>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <input type="hidden" name="kd_kantor" value="<?php echo $data['kd_kantor']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Kode Kantor</label>
                                    <input type="text" class="form-control" value="<?php echo $data['kd_kantor']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Kantor</label>
                                    <input type="text" class="form-control" name="nm_kantor"
                                        value="<?php echo $data['nm_kantor']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Singkatan</label>
                                    <input type="text" class="form-control" name="short"
                                        value="<?php echo $data['short']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" name="almt"
                                        required><?php echo $data['almt']; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kabupaten</label>
                                    <input type="text" class="form-control" name="kabupaten"
                                        value="<?php echo $data['kab']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <input type="text" class="form-control" name="kecamatan"
                                        value="<?php echo $data['kec']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status</label>
                                    <select class="form-select" name="status" required>
                                        <option value="AKTIF"
                                            <?php echo ($data['status'] == 'AKTIF') ? 'selected' : ''; ?>>AKTIF</option>
                                        <option value="TIDAK AKTIF"
                                            <?php echo ($data['status'] == 'TIDAK AKTIF') ? 'selected' : ''; ?>>TIDAK
                                            AKTIF</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Perusahaan</label>
                                    <input type="text" class="form-control" name="nm_perusahaan"
                                        value="<?php echo $data['nm_perusahaan']; ?>" required>
                                </div>
                                <div class="d-flex gap-2 justify-content-end mt-1">
                                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                                    <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
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