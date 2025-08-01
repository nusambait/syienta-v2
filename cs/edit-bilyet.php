<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek apakah parameter norut dan nik ada
if (!isset($_GET['norut']) || !isset($_GET['nik'])) {
    header("Location: input-jaminan.php");
    exit;
}

$norut = $_GET['norut'];
$nik = $_GET['nik'];

// Ambil data bilyet yang akan diedit
$query_bilyet = mysqli_query($connect, "SELECT * FROM bilyet WHERE norut='$norut' AND nik='$nik'");
$data_bilyet = mysqli_fetch_array($query_bilyet);

// Jika data tidak ditemukan
if (!$data_bilyet) {
    echo "<script>
        alert('Data tidak ditemukan!');
        window.location.href = 'form-bilyet.php?nik=" . $nik . "';
    </script>";
    exit;
}

// Proses update data
if (isset($_POST['submit'])) {
    $jenjam = $_POST['jenjam'];
    $norek = $_POST['norek'];
    $nobuk = $_POST['nobuk'];
    $nom = $_POST['nom'];
    $tak = $_POST['tak'];
    $tglbuka = $_POST['tglbuka'];
    $tgljthtempo = $_POST['tgljthtempo'];
    $an = $_POST['an'];
    $jentabdep = $_POST['jentabdep'];
    $pengikatan = $_POST['pengikatan'];
    $ketjam = $_POST['ketjam'];

    $query = mysqli_query($connect, "UPDATE bilyet SET 
        jenjam='$jenjam',
        norek='$norek',
        nobuk='$nobuk',
        nom='$nom',
        tak='$tak',
        tglbuka='$tglbuka',
        tgljthtempo='$tgljthtempo',
        an='$an',
        jentabdep='$jentabdep',
        pengikatan='$pengikatan',
        ketjam='$ketjam'
        WHERE norut='$norut' AND nik='$nik'");

    if ($query) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: 'Data berhasil diupdate',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'form-bilyet.php?nik=" . $nik . "';
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
                    text: 'Data gagal diupdate',
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
    <title>Edit Data Bilyet</title>
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
                    <h4 class="mb-0 text-gray-800">Edit Data Bilyet</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item"><a href="form-bilyet.php?nik=<?php echo $nik; ?>">Form Bilyet</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Bilyet</li>
                    </ol>
                </div>

                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">NIK</label>
                                    <input type="text" class="form-control" name="nik" value="<?php echo $nik; ?>"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Jenis Jaminan</label>
                                    <select class="form-control" name="jenjam" required>
                                        <option value="">Pilih Jenis Jaminan</option>
                                        <option value="Tabungan Nusamba"
                                            <?php echo ($data_bilyet['jenjam'] == 'Tabungan Nusamba') ? 'selected' : ''; ?>>
                                            Tabungan Nusamba</option>
                                        <option value="Tabungan Mitra Harmoni"
                                            <?php echo ($data_bilyet['jenjam'] == 'Tabungan Mitra Harmoni') ? 'selected' : ''; ?>>
                                            Tabungan Mitra Harmoni</option>
                                        <option value="Tabunganku"
                                            <?php echo ($data_bilyet['jenjam'] == 'Tabunganku') ? 'selected' : ''; ?>>
                                            Tabunganku</option>
                                        <option value="Tabungan Harmoni Plus"
                                            <?php echo ($data_bilyet['jenjam'] == 'Tabungan Harmoni Plus') ? 'selected' : ''; ?>>
                                            Tabungan Harmoni Plus</option>
                                        <option value="Deposito Nusamba"
                                            <?php echo ($data_bilyet['jenjam'] == 'Deposito Nusamba') ? 'selected' : ''; ?>>
                                            Deposito Nusamba</option>
                                        <option value="Depostio Super Plus"
                                            <?php echo ($data_bilyet['jenjam'] == 'Depostio Super Plus') ? 'selected' : ''; ?>>
                                            Depostio Super Plus</option>
                                        <option value="Deposito Berjangka"
                                            <?php echo ($data_bilyet['jenjam'] == 'Deposito Berjangka') ? 'selected' : ''; ?>>
                                            Deposito Berjangka</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Rekening</label>
                                    <input type="text" class="form-control" name="norek"
                                        value="<?php echo $data_bilyet['norek']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Bilyet</label>
                                    <input type="text" class="form-control" name="nobuk"
                                        value="<?php echo $data_bilyet['nobuk']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nominal</label>
                                    <input type="number" class="form-control" name="nom"
                                        value="<?php echo $data_bilyet['nom']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Taksasi</label>
                                    <input type="number" class="form-control" name="tak" value="<?php echo $data_bilyet['tak']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Buka</label>
                                    <input type="text" class="form-control date-input" name="tglbuka"
                                        value="<?php echo $data_bilyet['tglbuka']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Jatuh Tempo</label>
                                    <input type="text" class="form-control date-input" name="tgljthtempo"
                                        value="<?php echo $data_bilyet['tgljthtempo']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an"
                                        value="<?php echo $data_bilyet['an']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Jenis Tabungan/Deposito</label>
                                    <select class="form-select" name="jentabdep" required>
                                        <option value="">Pilih Jenis Tabungan/Deposito</option>
                                        <option value="Tabungan Nusamba"
                                            <?php echo ($data_bilyet['jentabdep'] == 'Tabungan Nusamba') ? 'selected' : ''; ?>>
                                            Tabungan Nusamba</option>
                                        <option value="Tabungan Mitra Harmoni"
                                            <?php echo ($data_bilyet['jentabdep'] == 'Tabungan Mitra Harmoni') ? 'selected' : ''; ?>>
                                            Tabungan Mitra Harmoni</option>
                                        <option value="Tabunganku"
                                            <?php echo ($data_bilyet['jentabdep'] == 'Tabunganku') ? 'selected' : ''; ?>>
                                            Tabunganku</option>
                                        <option value="Tabungan Harmoni Plus"
                                            <?php echo ($data_bilyet['jentabdep'] == 'Tabungan Harmoni Plus') ? 'selected' : ''; ?>>
                                            Tabungan Harmoni Plus</option>
                                        <option value="Deposito Nusamba"
                                            <?php echo ($data_bilyet['jentabdep'] == 'Deposito Nusamba') ? 'selected' : ''; ?>>
                                            Deposito Nusamba</option>
                                        <option value="Depostio Super Plus"
                                            <?php echo ($data_bilyet['jentabdep'] == 'Depostio Super Plus') ? 'selected' : ''; ?>>
                                            Depostio Super Plus</option>
                                        <option value="Deposito Berjangka"
                                            <?php echo ($data_bilyet['jentabdep'] == 'Deposito Berjangka') ? 'selected' : ''; ?>>
                                            Deposito Berjangka</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN"
                                            <?php echo ($data_bilyet['pengikatan'] == 'INTERN') ? 'selected' : ''; ?>>
                                            INTERN</option>
                                        <option value="GADAI"
                                            <?php echo ($data_bilyet['pengikatan'] == 'GADAI') ? 'selected' : ''; ?>>
                                            GADAI</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea class="form-control"
                                        name="ketjam"><?php echo $data_bilyet['ketjam']; ?></textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="form-bilyet.php?nik=<?php echo $nik; ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" name="submit" class="btn btn-primary">
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