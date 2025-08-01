<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil data penjamin berdasarkan nikpenj
$nikpenj = $_GET['nikpenj'];
$noreg = $_GET['noreg'];

$query = mysqli_query($connect, "SELECT * FROM penjamin WHERE nikpenj='$nikpenj'");
$data = mysqli_fetch_array($query);

// Proses update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $hub = mysqli_real_escape_string($connect, $_POST['hub']);
    $tmpt = mysqli_real_escape_string($connect, $_POST['tmpt']);
    $tgl = mysqli_real_escape_string($connect, $_POST['tgl']);
    $usia = mysqli_real_escape_string($connect, $_POST['usia']);
    $almt = mysqli_real_escape_string($connect, $_POST['almt']);
    $kec = mysqli_real_escape_string($connect, $_POST['kec']);
    $kab = mysqli_real_escape_string($connect, $_POST['kab']);
    $pek = mysqli_real_escape_string($connect, $_POST['pek']);
    $tlfn1 = mysqli_real_escape_string($connect, $_POST['tlfn1']);
    $tlfn2 = mysqli_real_escape_string($connect, $_POST['tlfn2']);
    $suis = mysqli_real_escape_string($connect, $_POST['suis']);
    $nik_suis = mysqli_real_escape_string($connect, $_POST['nik_suis']);

    $query = "UPDATE penjamin SET 
        nama = '$nama',
        hub = '$hub',
        tmpt = '$tmpt',
        tgl = '$tgl',
        usia = '$usia',
        almt = '$almt',
        kec = '$kec',
        kab = '$kab',
        pek = '$pek',
        tlfn1 = '$tlfn1',
        tlfn2 = '$tlfn2',
        suis = '$suis',
        nik_suis = '$nik_suis'
        WHERE nikpenj = '$nikpenj'";

    if (mysqli_query($connect, $query)) {
        echo "<script>
            alert('Data berhasil diperbarui!');
            window.location.href = 'data-penjamin.php?noreg=$noreg';
        </script>";
        exit;
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
    <title>Edit Data Penjamin</title>
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
                            <h4 class="card-title mb-4">Edit Data Penjamin</h4>

                            <form method="POST" action="">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">NIK Penjamin</label>
                                            <input type="text" class="form-control"
                                                value="<?php echo $data['nikpenj']; ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Nama</label>
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
                                        <div class="mb-3">
                                            <label class="form-label">Tanggal Lahir</label>
                                            <input type="text" name="tgl" class="form-control"
                                                value="<?php echo $data['tgl']; ?>" id="tanggalLahir"
                                                placeholder="dd-mm-yyyy" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Usia</label>
                                            <input type="number" name="usia" class="form-control"
                                                value="<?php echo $data['usia']; ?>" id="usia" readonly required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Alamat</label>
                                            <textarea name="almt" class="form-control"
                                                required><?php echo $data['almt']; ?></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Kecamatan</label>
                                            <input type="text" name="kec" class="form-control"
                                                value="<?php echo $data['kec']; ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Kabupaten</label>
                                            <input type="text" name="kab" class="form-control"
                                                value="<?php echo $data['kab']; ?>" required>
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
                                        <div class="mb-3">
                                            <label class="form-label">Status Suami/Istri</label>
                                            <select name="suis" class="form-control">
                                                <option value="">Pilih Status</option>
                                                <option value="SUAMI" <?php echo ($data['suis'] == 'SUAMI') ? 'selected' : ''; ?>>ISTRI</option>
                                                <option value="ISTRI" <?php echo ($data['suis'] == 'ISTRI') ? 'selected' : ''; ?>>SUAMI</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">NIK Suami/Istri</label>
                                            <input type="text" name="nik_suis" class="form-control"
                                                value="<?php echo $data['nik_suis']; ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <a href="data-penjamin.php?noreg=<?php echo $noreg; ?>"
                                        class="btn btn-secondary me-2">Kembali</a>
                                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
    <script>
        document.getElementById('tanggalLahir').addEventListener('change', function() {
            const tglLahir = this.value;
            if (tglLahir) {
                // Split tanggal dari format dd-mm-yyyy
                const [day, month, year] = tglLahir.split('-');
                const birthDate = new Date(year, month - 1, day);
                const today = new Date();

                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();

                // Kurangi 1 tahun jika belum ulang tahun
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }

                document.getElementById('usia').value = age;
            }
        });

        // Format input tanggal menjadi dd-mm-yyyy
        document.getElementById('tanggalLahir').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '').substring(0, 8);
            if (value.length >= 4 && value.length < 6) {
                value = value.substring(0, 2) + '-' + value.substring(2);
            } else if (value.length >= 6) {
                value = value.substring(0, 2) + '-' + value.substring(2, 4) + '-' + value.substring(4);
            }
            e.target.value = value;
        });
    </script>
</body>

</html>