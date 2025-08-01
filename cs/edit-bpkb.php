<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil data BPKB berdasarkan nopol dan nik
$nopol = $_GET['nopol'];
$nik = $_GET['nik'];
$query_bpkb = mysqli_query($connect, "SELECT * FROM bpkb WHERE nopol='$nopol' AND nik='$nik'");
$data_bpkb = mysqli_fetch_array($query_bpkb);

if (isset($_POST['update'])) {
    $kodjam = $_POST['kodjam'];
    $bpkb = $_POST['bpkb'];
    $nopol = $_POST['nopol'];
    $merk = $_POST['merk'];
    $norang = $_POST['norang'];
    $nomes = $_POST['nomes'];
    $war = $_POST['war'];
    $thnpem = $_POST['thnpem'];
    $bahbak = $_POST['bahbak'];
    $an = $_POST['an'];
    $almt = $_POST['almt'];
    $kec = $_POST['kec'];
    $kab = $_POST['kab'];
    $nm_pemjam = $_POST['nm_pemjam'];
    $almt_pemjam = $_POST['almt_pemjam'];
    $kec_pemjam = $_POST['kec_pemjam'];
    $kab_pemjam = $_POST['kab_pemjam'];
    $pek_pemjam = $_POST['pek_pemjam'];
    $psrwjr = $_POST['psrwjr'];
    $tak = $_POST['tak'];
    $nilpenj = $_POST['nilpenj'];
    $pengikatan = $_POST['pengikatan'];
    $ketjam = $_POST['ketjam'];

    $query = mysqli_query($connect, "UPDATE bpkb SET 
        kodjam='$kodjam',
        bpkb='$bpkb',
        merk='$merk',
        norang='$norang',
        nomes='$nomes',
        war='$war',
        thnpem='$thnpem',
        bahbak='$bahbak',
        an='$an',
        almt='$almt',
        kec='$kec',
        kab='$kab',
        nm_pemjam='$nm_pemjam',
        almt_pemjam='$almt_pemjam',
        kec_pemjam='$kec_pemjam',
        kab_pemjam='$kab_pemjam',
        pek_pemjam='$pek_pemjam',
        psrwjr='$psrwjr',
        tak='$tak',
        nilpenj='$nilpenj',
        pengikatan='$pengikatan',
        ketjam='$ketjam'
        WHERE nopol='$nopol' AND nik='$nik'");

    if ($query) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil diperbarui',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-bpkb.php?nik=" . $nik . "';
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
                text: 'Data gagal diperbarui',
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
    <title>Edit Data BPKB</title>
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
                    <h4 class="mb-0 text-gray-800">Edit Data BPKB</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item"><a href="form-bpkb.php?nik=<?php echo $nik; ?>">Form BPKB</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit BPKB</li>
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
                                    <label class="form-label required">Kode Jaminan</label>
                                    <select class="form-control" name="kodjam" required>
                                        <option value="">Pilih Kode Jaminan</option>
                                        <option value="R2 - MOTOR"
                                            <?php echo ($data_bpkb['kodjam'] == 'R2 - MOTOR') ? 'selected' : ''; ?>>R2 -
                                            MOTOR</option>
                                        <option value="R4 - MOBIL"
                                            <?php echo ($data_bpkb['kodjam'] == 'R4 - MOBIL') ? 'selected' : ''; ?>>R4 -
                                            MOBIL</option>
                                        <option value="R4 - PICKUP"
                                            <?php echo ($data_bpkb['kodjam'] == 'R4 - PICKUP') ? 'selected' : ''; ?>>R4
                                            - PICKUP</option>
                                    </select>
                                </div>

                                <!-- Sisanya sama seperti form-bpkb.php, tapi dengan value dari $data_bpkb -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. BPKB</label>
                                    <input type="text" class="form-control" name="bpkb"
                                        value="<?php echo $data_bpkb['bpkb']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Polisi</label>
                                    <input type="text" class="form-control" name="nopol"
                                        value="<?php echo $data_bpkb['nopol']; ?>" readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Merk/Type</label>
                                    <input type="text" class="form-control" name="merk"
                                        value="<?php echo $data_bpkb['merk']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Rangka</label>
                                    <input type="text" class="form-control" name="norang"
                                        value="<?php echo $data_bpkb['norang']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Mesin</label>
                                    <input type="text" class="form-control" name="nomes"
                                        value="<?php echo $data_bpkb['nomes']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Warna</label>
                                    <input type="text" class="form-control" name="war"
                                        value="<?php echo $data_bpkb['war']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tahun Pembuatan</label>
                                    <input type="text" class="form-control" name="thnpem"
                                        value="<?php echo $data_bpkb['thnpem']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Bahan Bakar</label>
                                    <input type="text" class="form-control" name="bahbak"
                                        value="<?php echo $data_bpkb['bahbak']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an"
                                        value="<?php echo $data_bpkb['an']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Alamat</label>
                                    <input type="text" class="form-control" name="almt"
                                        value="<?php echo $data_bpkb['almt']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kecamatan</label>
                                    <input type="text" class="form-control" name="kec"
                                        value="<?php echo $data_bpkb['kec']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kabupaten</label>
                                    <input type="text" class="form-control" name="kab"
                                        value="<?php echo $data_bpkb['kab']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nama Peminjam</label>
                                    <input type="text" class="form-control" name="nm_pemjam"
                                        value="<?php echo $data_bpkb['nm_pemjam']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Alamat Peminjam</label>
                                    <input type="text" class="form-control" name="almt_pemjam"
                                        value="<?php echo $data_bpkb['almt_pemjam']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kecamatan Peminjam</label>
                                    <input type="text" class="form-control" name="kec_pemjam"
                                        value="<?php echo $data_bpkb['kec_pemjam']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kabupaten Peminjam</label>
                                    <input type="text" class="form-control" name="kab_pemjam"
                                        value="<?php echo $data_bpkb['kab_pemjam']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pekerjaan Peminjam</label>
                                    <input type="text" class="form-control" name="pek_pemjam"
                                        value="<?php echo $data_bpkb['pek_pemjam']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pasar Wajar</label>
                                    <input type="number" class="form-control" name="psrwjr"
                                        value="<?php echo $data_bpkb['psrwjr']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Taksiran</label>
                                    <input type="number" class="form-control" name="tak"
                                        value="<?php echo $data_bpkb['tak']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nilai Penjaminan</label>
                                    <input type="number" class="form-control" name="nilpenj"
                                        value="<?php echo $data_bpkb['nilpenj']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <input type="text" class="form-control" name="pengikatan"
                                        value="<?php echo $data_bpkb['pengikatan']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Keterangan Jaminan</label>
                                    <textarea class="form-control" name="ketjam"
                                        required><?php echo $data_bpkb['ketjam']; ?></textarea>
                                </div>

                                <div class="mt-4 d-flex justify-content-between">
                                    <a href="form-bpkb.php?nik=<?php echo $nik; ?>" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Kembali
                                    </a>
                                    <button type="submit" name="update" class="btn btn-primary">
                                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/input-date.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/api-daerah.js"></script>
</body>

</html>