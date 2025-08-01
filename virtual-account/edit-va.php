<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../index.php");
    exit();
}

// Ambil data VA berdasarkan ID
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($connect, $_GET['id']);
    $query = mysqli_query($connect, "SELECT * FROM virtual_account WHERE id = '$id'");
    $data = mysqli_fetch_array($query);

    if (!$data) {
        echo "<script>
            alert('Data tidak ditemukan!');
            window.location.href = 'index.php';
        </script>";
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Virtual Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h2 class="mb-4">Virtual Account <?php echo ucwords(strtolower($data['nama'])); ?></h2>

            <div class="card mt-4">
                <div class="card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="id" value="<?php echo $data['id']; ?>">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Kode Kantor</label>
                                <input type="text" class="form-control" name="kd_kantor"
                                    value="<?php echo $data['kd_kantor']; ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">No. Rekening</label>
                                <input type="text" class="form-control" name="norek"
                                    value="<?php echo $data['norek']; ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Nama</label>
                                <input type="text" class="form-control" name="nama" value="<?php echo $data['nama']; ?>"
                                    required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">No. Loan</label>
                                <input type="text" class="form-control" name="noloan"
                                    value="<?php echo $data['noloan']; ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">No. VA</label>
                                <input type="text" class="form-control" name="no_va"
                                    value="<?php echo $data['no_va']; ?>" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Alamat</label>
                                <input type="text" class="form-control" name="alamat"
                                    value="<?php echo $data['alamat']; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tanggal Input</label>
                                <input type="date" class="form-control" name="tgl_input"
                                    value="<?php echo $data['tgl_input']; ?>" required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="index.php" class="btn btn-secondary">Kembali</a>
                            <button type="submit" name="update" class="btn btn-primary">Update Virtual Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }
    </script>
</body>

</html>

<?php
// Proses update data
if (isset($_POST['update'])) {
    $id = mysqli_real_escape_string($connect, $_POST['id']);
    $kd_kantor = mysqli_real_escape_string($connect, $_POST['kd_kantor']);
    $norek = mysqli_real_escape_string($connect, $_POST['norek']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $noloan = mysqli_real_escape_string($connect, $_POST['noloan']);
    $alamat = mysqli_real_escape_string($connect, $_POST['alamat']);
    $no_va = mysqli_real_escape_string($connect, $_POST['no_va']);
    $tgl_input = mysqli_real_escape_string($connect, $_POST['tgl_input']);

    $update = mysqli_query($connect, "UPDATE virtual_account SET 
        kd_kantor = '$kd_kantor',
        norek = '$norek',
        nama = '$nama',
        noloan = '$noloan',
        alamat = '$alamat',
        no_va = '$no_va',
        tgl_input = '$tgl_input'
        WHERE id = '$id'");

    if ($update) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'Data berhasil diupdate!',
                showConfirmButton: false,
                timer: 1500
            }).then(function() {
                window.location.href = 'index.php';
            });
        </script>";
    } else {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Gagal',
                text: 'Gagal mengupdate data!',
                showConfirmButton: false,
                timer: 1500
            }).then(function() {
                window.location.href = 'index.php';
            });
        </script>";
    }
}
?>