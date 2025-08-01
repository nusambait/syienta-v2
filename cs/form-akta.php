<?php
session_start();
include '../../config.php';
include '../config/config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($connect, "SELECT * FROM account WHERE username='$username' AND password='$password'");
    $data = mysqli_fetch_array($query);

    if (mysqli_num_rows($query) > 0) {
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = $data['role_id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['key_app'] = $data['key_app'];
        $_SESSION['foto'] = $data['foto'];

        header("Location: ../dashboard.php");
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}

// Ambil data nasabah berdasarkan NIK
$nik = $_GET['nik'];
$query_nasabah = mysqli_query($connect, "SELECT * FROM nasabah WHERE nik='$nik'");
$nasabah = mysqli_fetch_array($query_nasabah);

// Ubah logika hapus Sertifikat menjadi Akta
if (isset($_GET['action']) && $_GET['action'] == 'hapus') {
    if (isset($_GET['norut']) && isset($_GET['nik'])) {
        $norut = $_GET['norut'];
        $nik = $_GET['nik'];

        $query = mysqli_query($connect, "DELETE FROM ajb WHERE norut='$norut' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data Akta berhasil dihapus',
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
                        text: 'Data Akta gagal dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-akta.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

// Tambahkan handler untuk aksi lepas
if (isset($_GET['action']) && $_GET['action'] == 'lepas') {
    if (isset($_GET['norut']) && isset($_GET['nik'])) {
        $norut = $_GET['norut'];
        $nik = $_GET['nik'];

        $query = mysqli_query($connect, "UPDATE ajb SET status='Tersedia' WHERE norut='$norut' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status Sertifikat berhasil diubah',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-sertifikat.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

if (isset($_POST['submit'])) {
    $nik = $_POST['nik'];
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
    $status = 'Tersedia';

    $query = mysqli_query($connect, "INSERT INTO ajb (nik, jenjam, bukkep, persil, kohir, lt, an, almt, kec, kab, blok, tglter, njop, tak, pengikatan, ketjam, status) 
    VALUES ('$nik', '$jenjam', '$bukkep', '$persil', '$kohir', '$lt', '$an', '$almt', '$kec', '$kab', '$blok', '$tglter', '$njop', '$tak', '$pengikatan', '$ketjam', '$status')");

    if ($query) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil disimpan',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'input-jaminan.php';
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
                text: 'Data gagal disimpan',
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
    <title>Input Data Jaminan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">

            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h4 class="mb-0 text-gray-800">Form Input Data Akta</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form Akta</li>
                    </ol>
                </div>

                <!-- Tambahan: Tabel Data Akta -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Data Akta Nasabah <?php echo $nasabah['nama']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Jenis Jaminan</th>
                                        <th>Bukti Kepemilikan</th>
                                        <th>Persil</th>
                                        <th>Kohir</th>
                                        <th>Luas Tanah</th>
                                        <th>Atas Nama</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_akta = mysqli_query($connect, "SELECT * FROM ajb WHERE nik='$nik'");
                                    $no = 1;
                                    if (mysqli_num_rows($query_akta) > 0) {
                                        while ($data_akta = mysqli_fetch_array($query_akta)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $data_akta['jenjam']; ?></td>
                                                <td><?php echo $data_akta['bukkep']; ?></td>
                                                <td><?php echo $data_akta['persil']; ?></td>
                                                <td><?php echo $data_akta['kohir']; ?></td>
                                                <td><?php echo $data_akta['lt']; ?></td>
                                                <td><?php echo $data_akta['an']; ?></td>
                                                <td><?php echo $data_akta['status']; ?></td>
                                                <td class="d-flex justify-content-center">
                                                    <button type="button" class="btn btn-warning btn-sm me-1"
                                                        onclick="editAkta('<?php echo $data_akta['norut']; ?>')">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($data_akta['status'] != 'Tersedia'): ?>
                                                        <button type="button" class="btn btn-success btn-sm me-1"
                                                            onclick="lepasSertifikat('<?php echo $data_akta['norut']; ?>')">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="hapusSertifikat('<?php echo $data_akta['norut']; ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada data Akta</td>
                                        </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-4 bg-judul-card">Form Tambah Data Akta</h5>
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
                                        <option value="Sebidang Tahan Darat dan Bangunan">Sebidang Tahan Darat dan
                                            Bangunan</option>
                                        <option value="Sebidang Tanah Darat">Sebidang Tanah Darat</option>
                                        <option value="Sebidang Tanah Sawah">Sebidang Tanah Sawah</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Bukti Kepemilikan</label>
                                    <input type="text" class="form-control" name="bukkep"
                                        placeholder="Nama Dokumen / No Jaminan ex (AJB No. 12345)" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Persil</label>
                                    <input type="text" class="form-control" name="persil" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kohir</label>
                                    <input type="text" class="form-control" name="kohir" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Luas Tanah</label>
                                    <input type="text" class="form-control" name="lt" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Alamat</label>
                                    <textarea class="form-control" name="almt" required></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kabupaten</label>
                                    <select type="text" class="form-control" name="kab" id="kabupaten" required>
                                        <option value="">Pilih Kabupaten</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kecamatan</label>
                                    <select type="text" class="form-control" name="kec" id="kecamatan" required>
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Blok</label>
                                    <input type="text" class="form-control" name="blok" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Terbit</label>
                                    <input type="text" class="form-control date-input" name="tglter" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nilai Jual Objek Pajak</label>
                                    <input type="number" class="form-control" name="njop" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Taksasi</label>
                                    <input type="number" class="form-control" name="tak" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN">INTERN</option>
                                        <option value="SKMHT">SKMHT</option>
                                        <option value="APKHT">APKHT</option>
                                        <option value="LEGALISASI">LEGALISASI</option>
                                        <option value="ADDENDUM">ADDENDUM</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea class="form-control" name="ketjam"></textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="input-jaminan.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Simpan
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
    <script>
        function hapusSertifikat(norut) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data Akta akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-akta.php?action=hapus&norut=' + norut + '&nik=<?php echo $nik; ?>';
                }
            })
        }

        function lepasSertifikat(norut) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Status Akta akan diubah menjadi Tersedia!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, lepas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-akta.php?action=lepas&norut=' + norut + '&nik=<?php echo $nik; ?>';
                }
            })
        }

        function editAkta(norut) {
            window.location.href = 'edit-akta.php?norut=' + norut + '&nik=<?php echo $nik; ?>';
        }
    </script>
</body>

</html>