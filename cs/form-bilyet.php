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

        $query = mysqli_query($connect, "DELETE FROM bilyet WHERE norut='$norut' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data Bilyet berhasil dihapus',
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
                        text: 'Data Bilyet gagal dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-bilyet.php?nik=" . $nik . "';
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

        $query = mysqli_query($connect, "UPDATE bilyet SET status='Tersedia' WHERE norut='$norut' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status Bilyet berhasil diubah',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-bilyet.php?nik=" . $nik . "';
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
    $status = 'Tersedia';

    $query = mysqli_query($connect, "INSERT INTO bilyet (nik, jenjam, norek, nobuk, nom, tak, tglbuka, tgljthtempo, an, jentabdep, pengikatan, ketjam, status) 
    VALUES ('$nik', '$jenjam', '$norek', '$nobuk', '$nom', '$tak', '$tglbuka', '$tgljthtempo', '$an', '$jentabdep', '$pengikatan', '$ketjam', '$status')");

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
                    <h4 class="mb-0 text-gray-800">Form Input Data Bilyet</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form Bilyet</li>
                    </ol>
                </div>

                <!-- Tambahan: Tabel Data Bilyet -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Data Bilyet Nasabah <?php echo $nasabah['nama']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>Jenis Jaminan</th>
                                        <th>No. Rekening</th>
                                        <th>No. Bilyet</th>
                                        <th>Nominal</th>
                                        <th>Atas Nama</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_bilyet = mysqli_query($connect, "SELECT * FROM bilyet WHERE nik='$nik'");
                                    $no = 1;
                                    if (mysqli_num_rows($query_bilyet) > 0) {
                                        while ($data_bilyet = mysqli_fetch_array($query_bilyet)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $data_bilyet['jenjam']; ?></td>
                                                <td><?php echo $data_bilyet['norek']; ?></td>
                                                <td><?php echo $data_bilyet['nobuk']; ?></td>
                                                <td><?php echo number_format($data_bilyet['nom'], 0, ',', '.'); ?></td>
                                                <td><?php echo $data_bilyet['an']; ?></td>
                                                <td><?php echo $data_bilyet['status']; ?></td>
                                                <td class="d-flex justify-content-center">
                                                    <a href="edit-bilyet.php?norut=<?php echo $data_bilyet['norut']; ?>&nik=<?php echo $nik; ?>"
                                                        class="btn btn-warning btn-sm me-1">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-success btn-sm me-1"
                                                        onclick="lepasBilyet('<?php echo $data_bilyet['norut']; ?>')">
                                                        <i class="bi bi-unlock"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="hapusBilyet('<?php echo $data_bilyet['norut']; ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="8" class="text-center">Belum ada data Bilyet</td>
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
                        <h5 class="card-title mb-4 bg-judul-card">Form Tambah Data Bilyet</h5>
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
                                        <option value="Tabungan Nusamba">Tabungan Nusamba</option>
                                        <option value="Tabungan Mitra Harmoni">Tabungan Mitra Harmoni</option>
                                        <option value="Tabunganku">Tabunganku</option>
                                        <option value="Tabungan Harmoni Plus">Tabungan Harmoni Plus</option>
                                        <option value="Deposito Nusamba">Deposito Nusamba</option>
                                        <option value="Depostio Super Plus">Depostio Super Plus</option>
                                        <option value="Deposito Berjangka">Deposito Berjangka</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Rekening</label>
                                    <input type="text" class="form-control" name="norek" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Bilyet</label>
                                    <input type="text" class="form-control" name="nobuk" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nominal</label>
                                    <input type="number" class="form-control" name="nom" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Taksasi</label>
                                    <input type="number" class="form-control" name="tak" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Buka</label>
                                    <input type="text" class="form-control date-input" name="tglbuka" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Jatuh Tempo</label>
                                    <input type="text" class="form-control date-input" name="tgljthtempo" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Jenis Tabungan/Deposito</label>
                                    <select class="form-select" name="jentabdep" required>
                                        <option value="">Pilih Jenis Tabungan/Deposito</option>
                                        <option value="Tabungan Nusamba">Tabungan Nusamba</option>
                                        <option value="Tabungan Mitra Harmoni">Tabungan Mitra Harmoni</option>
                                        <option value="Tabunganku">Tabunganku</option>
                                        <option value="Tabungan Harmoni Plus">Tabungan Harmoni Plus</option>
                                        <option value="Deposito Nusamba">Deposito Nusamba</option>
                                        <option value="Depostio Super Plus">Depostio Super Plus</option>
                                        <option value="Deposito Berjangka">Deposito Berjangka</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN">INTERN</option>
                                        <option value="GADAI">GADAI</option>
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
        function hapusBilyet(norut) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data Bilyet akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-bilyet.php?action=hapus&norut=' + norut +
                        '&nik=<?php echo $nik; ?>';
                }
            })
        }

        function lepasBilyet(norut) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Status Bilyet akan diubah menjadi Tersedia!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, lepas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-bilyet.php?action=lepas&norut=' + norut +
                        '&nik=<?php echo $nik; ?>';
                }
            })
        }
    </script>
</body>

</html>