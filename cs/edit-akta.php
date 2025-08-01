<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil data akta berdasarkan norut dan nik
$norut = $_GET['norut'];
$nik = $_GET['nik'];
$query_akta = mysqli_query($connect, "SELECT * FROM ajb WHERE norut='$norut' AND nik='$nik'");
$data_akta = mysqli_fetch_array($query_akta);

if (isset($_POST['submit'])) {
    $jenjam = $_POST['jenjam'];
    $bukkep = $_POST['bukkep'];
    $persil = $_POST['persil'];
    $kohir = $_POST['kohir'];
    $lt = $_POST['lt'];
    $an = $_POST['an'];
    $almt = $_POST['almt'];
    $kec = $_POST['kec'];
    $kab = $_POST['kab'];
    $blok = $_POST['blok'];
    $tglter = $_POST['tglter'];
    $njop = $_POST['njop'];
    $tak = $_POST['tak'];
    $pengikatan = $_POST['pengikatan'];
    $ketjam = $_POST['ketjam'];

    $query = mysqli_query($connect, "UPDATE ajb SET 
        jenjam='$jenjam', 
        bukkep='$bukkep', 
        persil='$persil', 
        kohir='$kohir', 
        lt='$lt', 
        an='$an', 
        almt='$almt', 
        kec='$kec', 
        kab='$kab', 
        blok='$blok', 
        tglter='$tglter', 
        njop='$njop', 
        tak='$tak', 
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
                    window.location.href = 'form-akta.php?nik=" . $nik . "';
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
    <title>Edit Data Akta</title>
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
                    <h4 class="mb-0 text-gray-800">Edit Data Akta</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item"><a href="form-akta.php?nik=<?php echo $nik; ?>">Form Akta</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Akta</li>
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
                                        <option value="Sebidang Tahan Darat dan Bangunan"
                                            <?php echo ($data_akta['jenjam'] == 'Sebidang Tahan Darat dan Bangunan') ? 'selected' : ''; ?>>
                                            Sebidang Tahan Darat dan Bangunan</option>
                                        <option value="Sebidang Tanah Darat"
                                            <?php echo ($data_akta['jenjam'] == 'Sebidang Tanah Darat') ? 'selected' : ''; ?>>
                                            Sebidang Tanah Darat</option>
                                        <option value="Sebidang Tanah Sawah"
                                            <?php echo ($data_akta['jenjam'] == 'Sebidang Tanah Sawah') ? 'selected' : ''; ?>>
                                            Sebidang Tanah Sawah</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Bukti Kepemilikan</label>
                                    <input type="text" class="form-control" name="bukkep"
                                        value="<?php echo $data_akta['bukkep']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Persil</label>
                                    <input type="text" class="form-control" name="persil"
                                        value="<?php echo $data_akta['persil']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kohir</label>
                                    <input type="text" class="form-control" name="kohir"
                                        value="<?php echo $data_akta['kohir']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Luas Tanah</label>
                                    <input type="text" class="form-control" name="lt"
                                        value="<?php echo $data_akta['lt']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an"
                                        value="<?php echo $data_akta['an']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Alamat</label>
                                    <textarea class="form-control" name="almt"
                                        required><?php echo $data_akta['almt']; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kabupaten</label>
                                    <select type="text" class="form-control" name="kab" id="kabupaten" required>
                                        <option value="<?php echo $data_akta['kab']; ?>">
                                            <?php echo $data_akta['kab']; ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kecamatan</label>
                                    <select type="text" class="form-control" name="kec" id="kecamatan" required>
                                        <option value="<?php echo $data_akta['kec']; ?>">
                                            <?php echo $data_akta['kec']; ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Blok</label>
                                    <input type="text" class="form-control" name="blok"
                                        value="<?php echo $data_akta['blok']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Terbit</label>
                                    <input type="text" class="form-control date-input" name="tglter"
                                        value="<?php echo $data_akta['tglter']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nilai Jual Objek Pajak</label>
                                    <input type="number" class="form-control" name="njop"
                                        value="<?php echo $data_akta['njop']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Taksasi</label>
                                    <input type="number" class="form-control" name="tak"
                                        value="<?php echo $data_akta['tak']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN"
                                            <?php echo ($data_akta['pengikatan'] == 'INTERN') ? 'selected' : ''; ?>>
                                            INTERN</option>
                                        <option value="SKMHT"
                                            <?php echo ($data_akta['pengikatan'] == 'SKMHT') ? 'selected' : ''; ?>>SKMHT
                                        </option>
                                        <option value="APKHT"
                                            <?php echo ($data_akta['pengikatan'] == 'APKHT') ? 'selected' : ''; ?>>APKHT
                                        </option>
                                        <option value="LEGALISASI"
                                            <?php echo ($data_akta['pengikatan'] == 'LEGALISASI') ? 'selected' : ''; ?>>
                                            LEGALISASI</option>
                                        <option value="ADDENDUM"
                                            <?php echo ($data_akta['pengikatan'] == 'ADDENDUM') ? 'selected' : ''; ?>>
                                            ADDENDUM</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea class="form-control"
                                        name="ketjam"><?php echo $data_akta['ketjam']; ?></textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="form-akta.php?nik=<?php echo $nik; ?>" class="btn btn-secondary">
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/api-daerah.js"></script>
</body>

</html>