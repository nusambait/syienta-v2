<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek jika user belum login
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil data kios berdasarkan norut dan nik
if (isset($_GET['norut']) && isset($_GET['nik'])) {
    $norut = $_GET['norut'];
    $nik = $_GET['nik'];

    $query_kios = mysqli_query($connect, "SELECT * FROM kios WHERE norut='$norut' AND nik='$nik'");
    $data_kios = mysqli_fetch_array($query_kios);

    if (!$data_kios) {
        echo "<script>
            alert('Data tidak ditemukan!');
            window.location.href = 'form-kios.php?nik=" . $nik . "';
        </script>";
        exit();
    }
} else {
    header("Location: input-jaminan.php");
    exit();
}

// Proses update data
if (isset($_POST['update'])) {
    $jenjam = $_POST['jenjam'];
    $bukkep = $_POST['bukkep'];
    $ukuran = $_POST['ukuran'];
    $an = $_POST['an'];
    $blok = $_POST['blok'];
    $almt = $_POST['almt'];
    $nm_pemjam = $_POST['nm_pemjam'];
    $almt_pemjam = $_POST['almt_pemjam'];
    $kec_pemjam = $_POST['kec_pemjam'];
    $kab_pemjam = $_POST['kab_pemjam'];
    $pek_pemjam = $_POST['pek_pemjam'];
    $tgltrbt = $_POST['tgltrbt'];
    $mb = $_POST['mb'];
    $jenus = $_POST['jenus'];
    $tak = $_POST['tak'];
    $psrwjr = $_POST['psrwjr'];
    $nilpenj = $_POST['nilpenj'];
    $pengikatan = $_POST['pengikatan'];
    $ketjam = $_POST['ketjam'];

    $query = mysqli_query($connect, "UPDATE kios SET 
        jenjam='$jenjam',
        bukkep='$bukkep',
        ukuran='$ukuran',
        an='$an',
        blok='$blok',
        almt='$almt',
        nm_pemjam='$nm_pemjam',
        almt_pemjam='$almt_pemjam',
        kec_pemjam='$kec_pemjam',
        kab_pemjam='$kab_pemjam',
        pek_pemjam='$pek_pemjam',
        tgltrbt='$tgltrbt',
        mb='$mb',
        jenus='$jenus',
        tak='$tak',
        psrwjr='$psrwjr',
        nilpenj='$nilpenj',
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
                        window.location.href = 'form-kios.php?nik=" . $nik . "';
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
    <title>Edit Data Kios</title>
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
                    <h4 class="mb-0 text-gray-800">Edit Data Kios</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item"><a href="form-kios.php?nik=<?php echo $nik; ?>">Form Kios</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Kios</li>
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
                                        <option value="Kios"
                                            <?php echo ($data_kios['jenjam'] == 'Kios') ? 'selected' : ''; ?>>Kios
                                        </option>
                                        <option value="Surat Kios"
                                            <?php echo ($data_kios['jenjam'] == 'Surat Kios') ? 'selected' : ''; ?>>
                                            Surat Kios</option>
                                        <option value="Surat Kios/Jongko"
                                            <?php echo ($data_kios['jenjam'] == 'Surat Kios/Jongko') ? 'selected' : ''; ?>>
                                            Surat Kios/Jongko</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Bukti Kepemilikan</label>
                                    <input type="text" class="form-control" name="bukkep"
                                        value="<?php echo $data_kios['bukkep']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Ukuran</label>
                                    <input type="text" class="form-control" name="ukuran"
                                        value="<?php echo $data_kios['ukuran']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an"
                                        value="<?php echo $data_kios['an']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Blok</label>
                                    <input type="text" class="form-control" name="blok"
                                        value="<?php echo $data_kios['blok']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Alamat</label>
                                    <textarea class="form-control" name="almt"
                                        required><?php echo $data_kios['almt']; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nama Peminjam</label>
                                    <input type="text" class="form-control" name="nm_pemjam"
                                        value="<?php echo $data_kios['nm_pemjam']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Alamat Peminjam</label>
                                    <textarea class="form-control" name="almt_pemjam"
                                        required><?php echo $data_kios['almt_pemjam']; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kabupaten Peminjam</label>
                                    <select type="text" class="form-control" name="kab_pemjam" id="kabupaten" required>
                                        <option value="<?php echo $data_kios['kab_pemjam']; ?>">
                                            <?php echo $data_kios['kab_pemjam']; ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kecamatan Peminjam</label>
                                    <select type="text" class="form-control" name="kec_pemjam" id="kecamatan" required>
                                        <option value="<?php echo $data_kios['kec_pemjam']; ?>">
                                            <?php echo $data_kios['kec_pemjam']; ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pekerjaan Peminjam</label>
                                    <input type="text" class="form-control" name="pek_pemjam"
                                        value="<?php echo $data_kios['pek_pemjam']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Terbit</label>
                                    <input type="text" class="form-control date-input" name="tgltrbt"
                                        value="<?php echo $data_kios['tgltrbt']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Masa Berlaku</label>
                                    <input type="text" class="form-control date-input" name="mb"
                                        value="<?php echo $data_kios['mb']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Jenis Usaha</label>
                                    <input type="text" class="form-control" name="jenus"
                                        value="<?php echo $data_kios['jenus']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Taksasi</label>
                                    <input type="number" class="form-control" name="tak"
                                        value="<?php echo $data_kios['tak']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pasar Wajar</label>
                                    <input type="number" class="form-control" name="psrwjr"
                                        value="<?php echo $data_kios['psrwjr']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nilai Penjualan</label>
                                    <input type="number" class="form-control" name="nilpenj"
                                        value="<?php echo $data_kios['nilpenj']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN"
                                            <?php echo ($data_kios['pengikatan'] == 'INTERN') ? 'selected' : ''; ?>>
                                            INTERN</option>
                                        <option value="FEO Notaril"
                                            <?php echo ($data_kios['pengikatan'] == 'FEO Notaril') ? 'selected' : ''; ?>>
                                            FEO Notaril</option>
                                        <option value="Fiducia"
                                            <?php echo ($data_kios['pengikatan'] == 'Fiducia') ? 'selected' : ''; ?>>
                                            Fiducia</option>
                                        <option value="Fiducia (Terdaftar)"
                                            <?php echo ($data_kios['pengikatan'] == 'Fiducia (Terdaftar)') ? 'selected' : ''; ?>>
                                            Fiducia (Terdaftar)</option>
                                        <option value="WAARMERKING"
                                            <?php echo ($data_kios['pengikatan'] == 'WAARMERKING') ? 'selected' : ''; ?>>
                                            WAARMERKING</option>
                                        <option value="ADDENDUM"
                                            <?php echo ($data_kios['pengikatan'] == 'ADDENDUM') ? 'selected' : ''; ?>>
                                            ADDENDUM</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea class="form-control"
                                        name="ketjam"><?php echo $data_kios['ketjam']; ?></textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="form-kios.php?nik=<?php echo $nik; ?>" class="btn btn-secondary">
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
    <script src="<?php echo $base_url; ?>assets/js/api-daerah.js"></script>
</body>

</html>