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
    $no_loan = $_POST['no_loan'];
    $nama_nasabah = $_POST['nama_nasabah'];
    $file_ke = $_POST['file_ke'];
    $kategori = $_POST['kategori'];
    $tgl_upload = date('Y-m-d'); // Format YYYY-MM-DD
    $nama_pengupload = $_SESSION['nama'];
    $kantor = $_SESSION['kantor'];

    $file = $_FILES['file'];
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
    $newFileName = $no_loan . '_' . str_replace(' ', '_', $nama_nasabah) . '_' . date('Ymd_His') . '.' . $fileExt;
    $uploadPath = 'uploads/' . $newFileName;

    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        $query = "INSERT INTO m_teller (no_loan, nama_nasabah, file_ke, kategori, tgl_upload, nama_pengupload, kantor, file) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($connect, $query);
        mysqli_stmt_bind_param($stmt, "ssisssss", $no_loan, $nama_nasabah, $file_ke, $kategori, $tgl_upload, $nama_pengupload, $kantor, $newFileName);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['success'] = "File berhasil diupload!";
            header("Location: index.php");
            exit();
        } else {
            $_SESSION['error'] = "Terjadi kesalahan saat mengupload file!";
            header("Location: upload-m-teller.php");
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
    <title>Form Upload</title>
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
            if (!isset($_SESSION['key_app']) || ($_SESSION['key_app'] != 'TELLER' && $_SESSION['key_app'] != 'CS' && $_SESSION['key_app'] != 'ADMIN')) {
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
                            <h5 class="card-title">Form Upload</h5>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="kategori" class="form-label">Kategori</label>
                                    <select class="form-control" id="kategori" name="kategori" required
                                        onchange="updateFileKeLabel()">
                                        <option value="KCCT">KCCT</option>
                                        <option value="Bukti Pengambilan Tabungan">Bukti Pengambilan Tabungan</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="no_loan" class="form-label">Nomor Loan</label>
                                    <input type="text" class="form-control" id="no_loan" name="no_loan" required>
                                </div>
                                <div class="mb-3">
                                    <label for="nama_nasabah" class="form-label">Nama Nasabah</label>
                                    <input type="text" class="form-control" id="nama_nasabah" name="nama_nasabah"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label for="file_ke" class="form-label" id="fileKeLabel">Pembukaan CIF Ke-</label>
                                    <input type="number" class="form-control" id="file_ke" name="file_ke" required>
                                </div>

                                <div class="mb-3">
                                    <label for="file" class="form-label">File (PDF, Maks. 10MB)</label>
                                    <input type="file" class="form-control" id="file" name="file" accept=".pdf"
                                        required>
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
    <script>
    function updateFileKeLabel() {
        const kategori = document.getElementById('kategori').value;
        const fileKeLabel = document.getElementById('fileKeLabel');

        if (kategori === 'KCCT') {
            fileKeLabel.textContent = 'Pembukaan CIF Ke-';
        } else if (kategori === 'Bukti Pengambilan Tabungan') {
            fileKeLabel.textContent = 'Penarikan Ke - (dihari yang sama)';
        }
    }

    // Set label awal saat halaman dimuat
    updateFileKeLabel();
    </script>
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