<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil data pendamping berdasarkan nikpend
$nikpend = $_GET['nikpend'];
$noreg = $_GET['noreg'];

$query = mysqli_query($connect, "SELECT * FROM pendamping WHERE nikpend='$nikpend'");
$data = mysqli_fetch_array($query);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $hub = mysqli_real_escape_string($connect, $_POST['hub']);
    $tmpt = mysqli_real_escape_string($connect, $_POST['tmpt']);
    $tgl = mysqli_real_escape_string($connect, $_POST['tgl']);
    $pek = mysqli_real_escape_string($connect, $_POST['pek']);
    $tlfn1 = mysqli_real_escape_string($connect, $_POST['tlfn1']);
    $tlfn2 = mysqli_real_escape_string($connect, $_POST['tlfn2']);

    $query_update = "UPDATE pendamping SET 
        nama = '$nama',
        hub = '$hub',
        tmpt = '$tmpt',
        tgl = '$tgl',
        pek = '$pek',
        tlfn1 = '$tlfn1',
        tlfn2 = '$tlfn2'
        WHERE nikpend = '$nikpend'";

    if (mysqli_query($connect, $query_update)) {
        echo "<script>
            alert('Data berhasil diperbarui!');
            window.location.href = 'data-pendamping.php?noreg=$noreg';
        </script>";
    } else {
        echo "<script>
            alert('Gagal memperbarui data: " . mysqli_error($connect) . "');
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Pendamping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="card-title">Edit Data Pendamping</h4>
                                <a href="data-pendamping.php?noreg=<?php echo $noreg; ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>

                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">NIK Pendamping</label>
                                            <input type="text" class="form-control"
                                                value="<?php echo $data['nikpend']; ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama Lengkap</label>
                                            <input type="text" name="nama" class="form-control"
                                                value="<?php echo $data['nama']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Hubungan</label>
                                            <input type="text" name="hub" class="form-control"
                                                value="<?php echo $data['hub']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tempat Lahir</label>
                                            <input type="text" name="tmpt" class="form-control"
                                                value="<?php echo $data['tmpt']; ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Lahir</label>
                                            <input type="text" name="tgl" class="form-control date-input"
                                                value="<?php echo $data['tgl']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Pekerjaan</label>
                                            <input type="text" name="pek" class="form-control"
                                                value="<?php echo $data['pek']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">No. Telepon 1</label>
                                            <input type="text" name="tlfn1" class="form-control"
                                                value="<?php echo $data['tlfn1']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">No. Telepon 2</label>
                                            <input type="text" name="tlfn2" class="form-control"
                                                value="<?php echo $data['tlfn2']; ?>">
                                        </div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-save"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/date-input.js"></script>
</body>

</html>