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
    if (isset($_GET['id']) && isset($_GET['nik'])) {
        $id = $_GET['id'];
        $nik = $_GET['nik'];

        $query = mysqli_query($connect, "DELETE FROM bpih WHERE id='$id' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data BPIH berhasil dihapus',
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
                        text: 'Data BPIH gagal dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-bpih.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

// Tambahkan handler untuk aksi lepas
if (isset($_GET['action']) && $_GET['action'] == 'lepas') {
    if (isset($_GET['id']) && isset($_GET['nik'])) {
        $id = $_GET['id'];
        $nik = $_GET['nik'];

        $query = mysqli_query($connect, "UPDATE bpih SET status='Tersedia' WHERE id='$id' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status BPIH berhasil diubah',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-bpih.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

if (isset($_POST['submit'])) {
    $nik = $_POST['nik'];
    $noval = $_POST['noval'];
    $norek = $_POST['norek'];
    $tgl_surat = $_POST['tgl_surat'];
    $an = $_POST['an'];
    $status = 'Tersedia';

    $query = mysqli_query($connect, "INSERT INTO bpih (nik, noval, norek, tgl_surat, an, status) 
    VALUES ('$nik', '$noval', '$norek', '$tgl_surat', '$an', '$status')");

    if ($query) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data BPIH berhasil disimpan',
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
                text: 'Data BPIH gagal disimpan',
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
                    <h4 class="mb-0 text-gray-800">Form Input Data BPIH</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form BPIH</li>
                    </ol>
                </div>

                <!-- Tambahan: Tabel Data BPIH -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Data BPIH Nasabah <?php echo $nasabah['nama']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>No. Validasi</th>
                                        <th>No. Rekening</th>
                                        <th>Tanggal Surat</th>
                                        <th>Atas Nama</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_bpih = mysqli_query($connect, "SELECT * FROM bpih WHERE nik='$nik'");
                                    $no = 1;
                                    if (mysqli_num_rows($query_bpih) > 0) {
                                        while ($data_bpih = mysqli_fetch_array($query_bpih)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $data_bpih['noval']; ?></td>
                                                <td><?php echo $data_bpih['norek']; ?></td>
                                                <td><?php echo $data_bpih['tgl_surat']; ?></td>
                                                <td><?php echo $data_bpih['an']; ?></td>
                                                <td><?php echo $data_bpih['status']; ?></td>
                                                <td class="d-flex justify-content-center">
                                                    <button type="button" class="btn btn-warning btn-sm me-1"
                                                        onclick="window.location.href='edit-bpih.php?id=<?php echo $data_bpih['id']; ?>&nik=<?php echo $nik; ?>'">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($data_bpih['status'] != 'Tersedia'): ?>
                                                        <button type="button" class="btn btn-success btn-sm me-1"
                                                            onclick="lepasBPIH('<?php echo $data_bpih['id']; ?>')">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="hapusBPIH('<?php echo $data_bpih['id']; ?>')">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php
                                        }
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="7" class="text-center">Belum ada data BPIH</td>
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
                        <h5 class="card-title mb-4 bg-judul-card">Form Tambah Data BPIH</h5>
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">NIK</label>
                                    <input type="text" class="form-control" name="nik" value="<?php echo $nik; ?>"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Jaminan</label>
                                    <input type="text" class="form-control" name="noval" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Rekening</label>
                                    <input type="text" class="form-control" name="norek" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Surat</label>
                                    <input type="text" class="form-control date-input" name="tgl_surat" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an" required>
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
        function hapusBPIH(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data BPIH akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-bpih.php?action=hapus&id=' + id + '&nik=<?php echo $nik; ?>';
                }
            })
        }

        function lepasBPIH(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Status BPIH akan diubah menjadi Tersedia!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, lepas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-bpih.php?action=lepas&id=' + id + '&nik=<?php echo $nik; ?>';
                }
            })
        }
    </script>
</body>

</html>