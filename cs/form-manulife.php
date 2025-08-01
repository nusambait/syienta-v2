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

        $query = mysqli_query($connect, "DELETE FROM manulife WHERE norut='$norut' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data Manulife berhasil dihapus',
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
                        text: 'Data Manulife gagal dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-manulife.php?nik=" . $nik . "';
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

        $query = mysqli_query($connect, "UPDATE manulife SET status='Tersedia' WHERE norut='$norut' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status Manulife berhasil diubah',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-manulife.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

if (isset($_POST['submit'])) {
    $nik = $_POST['nik'];
    $nojam = $_POST['nojam'];
    $jendok = $_POST['jendok'];
    $nadok = $_POST['nadok'];
    $tgldok = $_POST['tgldok'];
    $pengikatan = $_POST['pengikatan'];
    $ketjam = $_POST['ketjam'];
    $status = 'Tersedia';

    $query = mysqli_query($connect, "INSERT INTO manulife (nik, nojam, jendok, nadok, tgldok, pengikatan, ketjam, status) 
    VALUES ('$nik', '$nojam', '$jendok', '$nadok', '$tgldok', '$pengikatan', '$ketjam', '$status')");

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
                    <h4 class="mb-0 text-gray-800">Form Input Data Manulife</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form Kios</li>
                    </ol>
                </div>

                <!-- Tambahan: Tabel Data Manulife -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Data Manulife Nasabah <?php echo $nasabah['nama']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>No. Jaminan</th>
                                        <th>Jenis Dokumen</th>
                                        <th>Nama Dokumen</th>
                                        <th>Tanggal Dokumen</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_manulife = mysqli_query($connect, "SELECT * FROM manulife WHERE nik='$nik'");
                                    $no = 1;
                                    if (mysqli_num_rows($query_manulife) > 0) {
                                        while ($data_manulife = mysqli_fetch_array($query_manulife)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $data_manulife['nojam']; ?></td>
                                                <td><?php echo $data_manulife['jendok']; ?></td>
                                                <td><?php echo $data_manulife['nadok']; ?></td>
                                                <td><?php echo $data_manulife['tgldok']; ?></td>
                                                <td><?php echo $data_manulife['status']; ?></td>
                                                <td class="d-flex justify-content-center">
                                                    <button type="button" class="btn btn-warning btn-sm me-1"
                                                        onclick="editManulife('<?php echo $data_manulife['norut']; ?>')">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($data_manulife['status'] != 'Tersedia'): ?>
                                                        <button type="button" class="btn btn-success btn-sm me-1"
                                                            onclick="lepasManulife('<?php echo $data_manulife['norut']; ?>')">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="hapusManulife('<?php echo $data_manulife['norut']; ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Belum ada data Manulife</td>
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
                        <h5 class="card-title mb-4 bg-judul-card">Form Tambah Data Manulife</h5>
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">NIK</label>
                                    <input type="text" class="form-control" name="nik" value="<?php echo $nik; ?>"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Jaminan</label>
                                    <input type="text" class="form-control" name="nojam" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Jenis Dokumen</label>
                                    <select class="form-control" name="jendok" required>
                                        <option value="">Pilih Jenis Dokumen</option>
                                        <option value="BPJS">BPJS</option>
                                        <option value="BPJS Ketenagakerjaan">BPJS Ketenagakerjaan</option>
                                        <option value="Manulife">Manulife</option>
                                        <option value="DPLK Manulife">DPLK Manulife</option>
                                        <option value="Jamsostek">Jamsostek</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nama Dokumen</label>
                                    <input type="text" class="form-control" name="nadok" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Dokumen</label>
                                    <input type="text" class="form-control date-input" name="tgldok" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN">INTERN</option>
                                        <option value="GADAI">GADAI</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3">
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
        function hapusManulife(norut) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data Manulife akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-manulife.php?action=hapus&norut=' + norut +
                        '&nik=<?php echo $nik; ?>';
                }
            })
        }

        function lepasManulife(norut) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Status Manulife akan diubah menjadi Tersedia!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, lepas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-manulife.php?action=lepas&norut=' + norut +
                        '&nik=<?php echo $nik; ?>';
                }
            })
        }

        function editManulife(norut) {
            window.location.href = 'edit-manulife.php?norut=' + norut + '&nik=<?php echo $nik; ?>';
        }
    </script>
</body>

</html>