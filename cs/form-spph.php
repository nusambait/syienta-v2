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

        $query = mysqli_query($connect, "DELETE FROM spph WHERE id='$id' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Data SPPH berhasil dihapus',
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
                        text: 'Data SPPH gagal dihapus',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-spph.php?nik=" . $nik . "';
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

        $query = mysqli_query($connect, "UPDATE spph SET status='Tersedia' WHERE id='$id' AND nik='$nik'");

        if ($query) {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status SPPH berhasil diubah',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'form-spph.php?nik=" . $nik . "';
                        }
                    });
                });
            </script>";
        }
    }
}

if (isset($_POST['submit'])) {
    $nik = $_POST['nik'];
    $nopor = $_POST['nopor'];
    $noval = $_POST['noval'];
    $tgl_surat = $_POST['tgl_surat'];
    $tgl_stt = $_POST['tgl_stt'];
    $kemenag = $_POST['kemenag'];
    $an = $_POST['an'];
    $status = 'Tersedia';

    $query = mysqli_query($connect, "INSERT INTO spph (nik, noval, nopor, tgl_surat, tgl_stt, kemenag, an, status) 
    VALUES ('$nik', '$noval', '$nopor', '$tgl_surat', '$tgl_stt', '$kemenag', '$an', '$status')");

    if ($query) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data SPPH berhasil disimpan',
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
                text: 'Data SPPH gagal disimpan',
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
                    <h4 class="mb-0 text-gray-800">Form Input Data SPPH</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Form SPPH</li>
                    </ol>
                </div>

                <!-- Tambahan: Tabel Data SPPH -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Data SPPH Nasabah <?php echo $nasabah['nama']; ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>No</th>
                                        <th>No. Porsi</th>
                                        <th>No. Validasi</th>
                                        <th>Tanggal Surat</th>
                                        <th>Tanggal STT</th>
                                        <th>Kemenag</th>
                                        <th>Atas Nama</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $query_spph = mysqli_query($connect, "SELECT * FROM spph WHERE nik='$nik'");
                                    $no = 1;
                                    if (mysqli_num_rows($query_spph) > 0) {
                                        while ($data_spph = mysqli_fetch_array($query_spph)) {
                                    ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo $data_spph['nopor']; ?></td>
                                                <td><?php echo $data_spph['noval']; ?></td>
                                                <td><?php echo $data_spph['tgl_surat']; ?></td>
                                                <td><?php echo $data_spph['tgl_stt']; ?></td>
                                                <td><?php echo $data_spph['kemenag']; ?></td>
                                                <td><?php echo $data_spph['an']; ?></td>
                                                <td><?php echo $data_spph['status']; ?></td>
                                                <td class="d-flex justify-content-center">
                                                    <a href="edit-spph.php?id=<?php echo $data_spph['id']; ?>&nik=<?php echo $nik; ?>"
                                                        class="btn btn-warning btn-sm me-1">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <?php if ($data_spph['status'] != 'Tersedia'): ?>
                                                        <button type="button" class="btn btn-success btn-sm me-1"
                                                            onclick="lepasSPPH('<?php echo $data_spph['id']; ?>')">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                        onclick="hapusSPPH('<?php echo $data_spph['id']; ?>')">
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
                        <h5 class="card-title mb-4 bg-judul-card">Form Tambah Data SPPH</h5>
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">NIK</label>
                                    <input type="text" class="form-control" name="nik" value="<?php echo $nik; ?>"
                                        readonly>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Porsi</label>
                                    <input type="text" class="form-control" name="nopor" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">No. Validasi</label>
                                    <input type="text" class="form-control" name="noval" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Surat</label>
                                    <input type="text" class="form-control date-input" name="tgl_surat" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal STT</label>
                                    <input type="text" class="form-control date-input" name="tgl_stt" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kemenag</label>
                                    <select class="form-control" name="kemenag" required>
                                        <option value="">Pilih Kemenag</option>
                                        <option value="Kemenag Kab. Bandung">Kemenag Kab. Bandung</option>
                                        <option value="Kemenag Kab. Bandung Barat">Kemenag Kab. Bandung Barat</option>
                                        <option value="Kemenag Kab. Bekasi">Kemenag Kab. Bekasi</option>
                                        <option value="Kemenag Kab. Bogor">Kemenag Kab. Bogor</option>
                                        <option value="Kemenag Kab. Ciamis">Kemenag Kab. Ciamis</option>
                                        <option value="Kemenag Kab. Cianjur">Kemenag Kab. Cianjur</option>
                                        <option value="Kemenag Kab. Cirebon">Kemenag Kab. Cirebon</option>
                                        <option value="Kemenag Kab. Garut">Kemenag Kab. Garut</option>
                                        <option value="Kemenag Kab. Indramayu">Kemenag Kab. Indramayu</option>
                                        <option value="Kemenag Kab. Karawang">Kemenag Kab. Karawang</option>
                                        <option value="Kemenag Kab. Kuningan">Kemenag Kab. Kuningan</option>
                                        <option value="Kemenag Kab. Majalengka">Kemenag Kab. Majalengka</option>
                                        <option value="Kemenag Kab. Pangandaran">Kemenag Kab. Pangandaran</option>
                                        <option value="Kemenag Kab. Purwakarta">Kemenag Kab. Purwakarta</option>
                                        <option value="Kemenag Kab. Subang">Kemenag Kab. Subang</option>
                                        <option value="Kemenag Kab. Sukabumi">Kemenag Kab. Sukabumi</option>
                                        <option value="Kemenag Kab. Sumedang">Kemenag Kab. Sumedang</option>
                                        <option value="Kemenag Kab. Tasikmalaya">Kemenag Kab. Tasikmalaya</option>
                                    </select>
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
        function hapusSPPH(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Data SPPH akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-spph.php?action=hapus&id=' + id + '&nik=<?php echo $nik; ?>';
                }
            })
        }

        function lepasSPPH(id) {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Status SPPH akan diubah menjadi Tersedia!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, lepas!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'form-spph.php?action=lepas&id=' + id + '&nik=<?php echo $nik; ?>';
                }
            })
        }
    </script>
</body>

</html>