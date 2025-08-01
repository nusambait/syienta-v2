<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek jika user belum login dan validasi semua session yang diperlukan
if (
    !isset($_SESSION['username']) || !isset($_SESSION['role_id']) ||
    !isset($_SESSION['nama']) || !isset($_SESSION['key_app']) ||
    !isset($_SESSION['foto']) || !isset($_SESSION['kantor']) ||
    !isset($_SESSION['kd_ao'])
) {
    header("Location: " . $base_url . "dashboard.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nomor_ket = $_POST['nomor_ket'];
    $judul_ket = $_POST['judul_ket'];

    // Gunakan format YYYY-MM-DD untuk database MySQL
    $ttl_terbit = $_POST['ttl_terbit']; // Sudah dalam format YYYY-MM-DD dari input date

    $nama_pengupload = $_SESSION['nama'];

    $file = $_FILES['file_surat'];
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validasi tipe file dan ukuran
    if ($fileExt != "pdf") {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'File harus berformat PDF!'
                });
              </script>";
        exit();
    }

    if ($fileSize > 10485760) { // 10MB dalam bytes
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Ukuran file maksimal 10MB!'
                });
              </script>";
        exit();
    }

    // Generate nama file baru dengan timestamp
    $newFileName = pathinfo($fileName, PATHINFO_FILENAME) . '_' . date('Ymd_His') . '.' . $fileExt;
    $uploadPath = 'uploads/' . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $query = "INSERT INTO ketentuanku_surat (nomor_ket, judul_ket, ttl_terbit, file_surat, nama_pengupload, tgl_upload) 
                 VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "sssss", $nomor_ket, $judul_ket, $ttl_terbit, $newFileName, $nama_pengupload);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "File berhasil diupload!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat mengupload file!";
            header("Location: upload-ketentuan.php");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Ketentuan</title>
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
            <?php
            // Cek akses berdasarkan key_app
            if (!isset($_SESSION['key_app']) || ($_SESSION['key_app'] != 'SKAI' && $_SESSION['key_app'] != 'ADMIN')) {
                echo "<script>
                    Swal.fire({
                        icon: 'error',
                        title: 'Akses Ditolak',
                        text: 'Anda tidak memiliki akses ke halaman ini',
                        showConfirmButton: true
                    }).then((result) => {
                        window.location.href = '../dashboard.php';
                    });
                </script>";
                exit;
            }
            ?>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Upload Ketentuan Baru</h5>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="nomor_ket" class="form-label">Nomor Ketentuan</label>
                                    <input type="text" class="form-control" id="nomor_ket" name="nomor_ket" required>
                                </div>
                                <div class="mb-3">
                                    <label for="judul_ket" class="form-label">Judul Ketentuan</label>
                                    <input type="text" class="form-control" id="judul_ket" name="judul_ket" required>
                                </div>
                                <div class="mb-3">
                                    <label for="ttl_terbit" class="form-label">Tanggal Terbit</label>
                                    <input type="date" class="form-control" id="ttl_terbit" name="ttl_terbit"
                                        value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="file_surat" class="form-label">File Surat (PDF, Maks. 10MB)</label>
                                    <input type="file" class="form-control" id="file_surat" name="file_surat"
                                        accept=".pdf" required>
                                </div>
                                <div class="d-flex gap-2 justify-content-end py-3">
                                    <a href="index.php" class="btn btn-secondary">Kembali</a>
                                    <button type="submit" class="btn btn-primary">Upload</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?php
    if (isset($_SESSION['error'])) {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '" . $_SESSION['error'] . "'
                });
              </script>";
        unset($_SESSION['error']);
    }
    ?>
</body>

</html>