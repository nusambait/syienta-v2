<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek apakah ada ID dan NIK yang dikirim
if (!isset($_GET['id']) || !isset($_GET['nik'])) {
    header("Location: input-jaminan.php");
    exit();
}

$id = $_GET['id'];
$nik = $_GET['nik'];

// Ambil data BPIH yang akan diedit
$query_bpih = mysqli_query($connect, "SELECT * FROM bpih WHERE id='$id' AND nik='$nik'");
$data_bpih = mysqli_fetch_array($query_bpih);

// Jika data tidak ditemukan
if (!$data_bpih) {
    header("Location: input-jaminan.php");
    exit();
}

// Proses update data
if (isset($_POST['submit'])) {
    $noval = $_POST['noval'];
    $norek = $_POST['norek'];
    $tgl_surat = $_POST['tgl_surat'];
    $an = $_POST['an'];

    $query = mysqli_query($connect, "UPDATE bpih SET 
        noval='$noval',
        norek='$norek',
        tgl_surat='$tgl_surat',
        an='$an'
        WHERE id='$id' AND nik='$nik'");

    if ($query) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data BPIH berhasil diperbarui',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-bpih.php?nik=" . $nik . "';
                }
            });
        });
        </script>";
    } else {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Data BPIH gagal diperbarui',
                confirmButtonText: 'OK'
            });
        });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data BPIH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0 text-gray-800">Edit Data BPIH</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item"><a href="form-bpih.php?nik=<?php echo $nik; ?>">Form BPIH</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit BPIH</li>
                    </ol>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">NIK</label>
                                    <input type="text" class="form-control" value="<?php echo $nik; ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Jaminan</label>
                                    <input type="text" class="form-control" name="noval"
                                        value="<?php echo $data_bpih['noval']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Rekening</label>
                                    <input type="text" class="form-control" name="norek"
                                        value="<?php echo $data_bpih['norek']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Surat</label>
                                    <input type="text" class="form-control date-input" name="tgl_surat"
                                        value="<?php echo $data_bpih['tgl_surat']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an"
                                        value="<?php echo $data_bpih['an']; ?>" required>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="form-bpih.php?nik=<?php echo $nik; ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Simpan Perubahan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
</body>

</html>