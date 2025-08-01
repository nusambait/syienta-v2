<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil data SPPH berdasarkan ID
if (isset($_GET['id']) && isset($_GET['nik'])) {
    $id = $_GET['id'];
    $nik = $_GET['nik'];

    $query_spph = mysqli_query($connect, "SELECT * FROM spph WHERE id='$id' AND nik='$nik'");
    $data_spph = mysqli_fetch_array($query_spph);

    if (!$data_spph) {
        header("Location: form-spph.php?nik=" . $nik);
        exit();
    }
} else {
    header("Location: ../dashboard.php");
    exit();
}

// Proses update data
if (isset($_POST['update'])) {
    $nopor = $_POST['nopor'];
    $noval = $_POST['noval'];
    $tgl_surat = $_POST['tgl_surat'];
    $tgl_stt = $_POST['tgl_stt'];
    $kemenag = $_POST['kemenag'];
    $an = $_POST['an'];

    $query = mysqli_query($connect, "UPDATE spph SET 
        nopor='$nopor',
        noval='$noval',
        tgl_surat='$tgl_surat',
        tgl_stt='$tgl_stt',
        kemenag='$kemenag',
        an='$an'
        WHERE id='$id' AND nik='$nik'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data SPPH berhasil diupdate',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'form-spph.php?nik=" . $nik . "';
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
                    text: 'Data SPPH gagal diupdate',
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
    <title>Edit Data SPPH</title>
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
                    <h4 class="mb-0">Edit Data SPPH</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item"><a href="form-spph.php?nik=<?php echo $nik; ?>">Form SPPH</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit SPPH</li>
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
                                    <label class="form-label required">No. Porsi</label>
                                    <input type="text" class="form-control" name="nopor"
                                        value="<?php echo $data_spph['nopor']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Validasi</label>
                                    <input type="text" class="form-control" name="noval"
                                        value="<?php echo $data_spph['noval']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Surat</label>
                                    <input type="text" class="form-control date-input" name="tgl_surat"
                                        value="<?php echo $data_spph['tgl_surat']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal STT</label>
                                    <input type="text" class="form-control date-input" name="tgl_stt"
                                        value="<?php echo $data_spph['tgl_stt']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kemenag</label>
                                    <select class="form-control" name="kemenag" required>
                                        <option value="">Pilih Kemenag</option>
                                        <?php
                                        $kemenag_list = array(
                                            "Kemenag Kab. Bandung",
                                            "Kemenag Kab. Bandung Barat",
                                            "Kemenag Kab. Bekasi",
                                            "Kemenag Kab. Bogor",
                                            "Kemenag Kab. Ciamis",
                                            "Kemenag Kab. Cianjur",
                                            "Kemenag Kab. Cirebon",
                                            "Kemenag Kab. Garut",
                                            "Kemenag Kab. Indramayu",
                                            "Kemenag Kab. Karawang",
                                            "Kemenag Kab. Kuningan",
                                            "Kemenag Kab. Majalengka",
                                            "Kemenag Kab. Pangandaran",
                                            "Kemenag Kab. Purwakarta",
                                            "Kemenag Kab. Subang",
                                            "Kemenag Kab. Sukabumi",
                                            "Kemenag Kab. Sumedang",
                                            "Kemenag Kab. Tasikmalaya"
                                        );

                                        foreach ($kemenag_list as $kemenag_option) {
                                            $selected = ($kemenag_option == $data_spph['kemenag']) ? 'selected' : '';
                                            echo "<option value=\"$kemenag_option\" $selected>$kemenag_option</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an"
                                        value="<?php echo $data_spph['an']; ?>" required>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="form-spph.php?nik=<?php echo $nik; ?>" class="btn btn-secondary">
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