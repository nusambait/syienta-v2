<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil data manulife berdasarkan norut
if (isset($_GET['norut']) && isset($_GET['nik'])) {
    $norut = $_GET['norut'];
    $nik = $_GET['nik'];

    $query_manulife = mysqli_query($connect, "SELECT * FROM manulife WHERE norut='$norut' AND nik='$nik'");
    $data_manulife = mysqli_fetch_array($query_manulife);

    if (!$data_manulife) {
        echo "<script>
            alert('Data tidak ditemukan!');
            window.location.href = 'form-manulife.php?nik=" . $nik . "';
        </script>";
        exit();
    }
}

// Proses update data
if (isset($_POST['update'])) {
    $norut = $_POST['norut'];
    $nik = $_POST['nik'];
    $nojam = $_POST['nojam'];
    $jendok = $_POST['jendok'];
    $nadok = $_POST['nadok'];
    $tgldok = $_POST['tgldok'];
    $pengikatan = $_POST['pengikatan'];
    $ketjam = $_POST['ketjam'];

    $query = mysqli_query($connect, "UPDATE manulife SET 
        nojam='$nojam',
        jendok='$jendok',
        nadok='$nadok',
        tgldok='$tgldok',
        pengikatan='$pengikatan',
        ketjam='$ketjam'
        WHERE norut='$norut' AND nik='$nik'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data Manulife berhasil diupdate',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'form-manulife.php?nik=" . $nik . "';
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
                    text: 'Data Manulife gagal diupdate',
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
    <title>Edit Data Manulife</title>
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
                    <h4 class="mb-0 text-gray-800">Edit Data Manulife</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item"><a href="form-manulife.php?nik=<?php echo $nik; ?>">Form
                                Manulife</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Manulife</li>
                    </ol>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="norut" value="<?php echo $data_manulife['norut']; ?>">
                            <input type="hidden" name="nik" value="<?php echo $data_manulife['nik']; ?>">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">NIK</label>
                                    <input type="text" class="form-control" value="<?php echo $data_manulife['nik']; ?>"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Jaminan</label>
                                    <input type="text" class="form-control" name="nojam"
                                        value="<?php echo $data_manulife['nojam']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Jenis Dokumen</label>
                                    <select class="form-control" name="jendok" required>
                                        <option value="">Pilih Jenis Dokumen</option>
                                        <option value="BPJS"
                                            <?php echo ($data_manulife['jendok'] == 'BPJS') ? 'selected' : ''; ?>>BPJS
                                        </option>
                                        <option value="BPJS Ketenagakerjaan"
                                            <?php echo ($data_manulife['jendok'] == 'BPJS Ketenagakerjaan') ? 'selected' : ''; ?>>
                                            BPJS Ketenagakerjaan</option>
                                        <option value="Manulife"
                                            <?php echo ($data_manulife['jendok'] == 'Manulife') ? 'selected' : ''; ?>>
                                            Manulife</option>
                                        <option value="DPLK Manulife"
                                            <?php echo ($data_manulife['jendok'] == 'DPLK Manulife') ? 'selected' : ''; ?>>
                                            DPLK Manulife</option>
                                        <option value="Jamsostek"
                                            <?php echo ($data_manulife['jendok'] == 'Jamsostek') ? 'selected' : ''; ?>>
                                            Jamsostek</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nama Dokumen</label>
                                    <input type="text" class="form-control" name="nadok"
                                        value="<?php echo $data_manulife['nadok']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Dokumen</label>
                                    <input type="text" class="form-control date-input" name="tgldok"
                                        value="<?php echo $data_manulife['tgldok']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN"
                                            <?php echo ($data_manulife['pengikatan'] == 'INTERN') ? 'selected' : ''; ?>>
                                            INTERN</option>
                                        <option value="GADAI"
                                            <?php echo ($data_manulife['pengikatan'] == 'GADAI') ? 'selected' : ''; ?>>
                                            GADAI</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea class="form-control"
                                        name="ketjam"><?php echo $data_manulife['ketjam']; ?></textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="form-manulife.php?nik=<?php echo $nik; ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" name="update" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Update
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
</body>

</html>