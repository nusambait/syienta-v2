<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek jika tidak ada nikpenj yang diberikan
if (!isset($_GET['nikpenj'])) {
    header("Location: input-penjamin.php");
    exit();
}

$nikpenj = mysqli_real_escape_string($connect, $_GET['nikpenj']);

// Ambil data penjamin
$query = mysqli_query($connect, "SELECT * FROM penjamin WHERE nikpenj='$nikpenj'");
$penjamin = mysqli_fetch_assoc($query);

// Jika data tidak ditemukan
if (!$penjamin) {
    $_SESSION['status'] = "error";
    $_SESSION['message'] = "Data penjamin tidak ditemukan";
    header("Location: input-penjamin.php");
    exit();
}

// Proses update data
if (isset($_POST['update_penjamin'])) {
    $niknas = mysqli_real_escape_string($connect, $_POST['niknas']);
    $hub = mysqli_real_escape_string($connect, $_POST['hub']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $tmpt = mysqli_real_escape_string($connect, $_POST['tmpt']);
    $tgl = mysqli_real_escape_string($connect, $_POST['tgl']);
    $almt = mysqli_real_escape_string($connect, $_POST['almt']);
    $kec = mysqli_real_escape_string($connect, $_POST['kec']);
    $kab = mysqli_real_escape_string($connect, $_POST['kab']);
    $usia = mysqli_real_escape_string($connect, $_POST['usia']);
    $pek = mysqli_real_escape_string($connect, $_POST['pek']);
    $tlfn1 = mysqli_real_escape_string($connect, empty($_POST['tlfn1']) ? '+62' : $_POST['tlfn1']);
    $tlfn2 = mysqli_real_escape_string($connect, empty($_POST['tlfn2']) ? '+62' : $_POST['tlfn2']);
    $suis = mysqli_real_escape_string($connect, $_POST['suis']);
    $nik_suis = mysqli_real_escape_string($connect, $_POST['nik_suis']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    mysqli_begin_transaction($connect);

    try {
        $query = "UPDATE penjamin SET 
                    niknas='$niknas', hub='$hub', nama='$nama', tmpt='$tmpt',
                    tgl='$tgl', almt='$almt', kec='$kec', kab='$kab',
                    usia='$usia', pek='$pek', tlfn1='$tlfn1', tlfn2='$tlfn2',
                    suis='$suis', nik_suis='$nik_suis', status='$status'
                 WHERE nikpenj='$nikpenj'";

        if (mysqli_query($connect, $query)) {
            mysqli_commit($connect);
            $_SESSION['status'] = "success";
            $_SESSION['message'] = "Data penjamin berhasil diperbarui!";
            header("Location: input-penjamin.php");
            exit();
        } else {
            throw new Exception(mysqli_error($connect));
        }
    } catch (Exception $e) {
        mysqli_rollback($connect);
        $_SESSION['status'] = "error";
        $_SESSION['message'] = "Gagal memperbarui data: " . $e->getMessage();
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <h2>Edit Data Penjamin</h2>
                </div>
                <div class="col text-end">
                    <a href="input-penjamin.php" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIK Penjamin</label>
                                <input type="text" class="form-control" value="<?php echo $penjamin['nikpenj']; ?>"
                                    readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">NIK Nasabah</label>
                                <input type="text" class="form-control" name="niknas"
                                    value="<?php echo $penjamin['niknas']; ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hubungan dengan Nasabah</label>
                                <input type="text" class="form-control" name="hub"
                                    value="<?php echo $penjamin['hub']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama"
                                    value="<?php echo $penjamin['nama']; ?>" required>
                            </div>

                            <!-- Lanjutkan dengan field-field lainnya -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Lahir</label>
                                <input type="text" class="form-control" name="tmpt"
                                    value="<?php echo $penjamin['tmpt']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Lahir</label>
                                <input type="text" class="form-control date-input" name="tgl"
                                    value="<?php echo $penjamin['tgl']; ?>" required>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Alamat</label>
                                <textarea class="form-control" name="almt"
                                    required><?php echo $penjamin['almt']; ?></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kabupaten</label>
                                <select class="form-select" id="kabupaten" name="kab" required>
                                    <option value="">Pilih Kabupaten/Kota</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kecamatan</label>
                                <select class="form-select" id="kecamatan" name="kec" required>
                                    <option value="">Pilih Kecamatan</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Usia</label>
                                <input type="text" class="form-control" name="usia"
                                    value="<?php echo $penjamin['usia']; ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pekerjaan</label>
                                <select class="form-select" name="pek" required>
                                    <option value="">Pilih Pekerjaan</option>
                                    <?php
                                    $pekerjaan = [
                                        'PNS',
                                        'TNI/POLRI',
                                        'Karyawan Swasta',
                                        'Karyawan Honorer',
                                        'Sopir',
                                        'Wiraswasta',
                                        'Petani/Pekebun',
                                        'Nelayan',
                                        'Buruh',
                                        'Guru',
                                        'Dosen',
                                        'Dokter',
                                        'Perawat',
                                        'Pedagang',
                                        'Pengacara',
                                        'Notaris',
                                        'Arsitek',
                                        'Akuntan',
                                        'Konsultan',
                                        'Freelancer',
                                        'Ibu Rumah Tangga',
                                        'Pelajar',
                                        'Pensiunan',
                                        'Lainnya'
                                    ];
                                    foreach ($pekerjaan as $p) {
                                        $selected = ($p === $penjamin['pek']) ? 'selected' : '';
                                        echo "<option value='$p' $selected>$p</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon 1</label>
                                <input type="tel" class="form-control" name="tlfn1"
                                    value="<?php echo $penjamin['tlfn1']; ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">No. Telepon 2</label>
                                <input type="tel" class="form-control" name="tlfn2"
                                    value="<?php echo $penjamin['tlfn2']; ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Hubungan</label>
                                <select class="form-select" name="suis">
                                    <?php
                                    $suis_options = ['Tidak Ada', 'Suami', 'Istri'];
                                    foreach ($suis_options as $option) {
                                        $selected = ($option === $penjamin['suis']) ? 'selected' : '';
                                        echo "<option value='$option' $selected>$option</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pendamping</label>
                                <select class="form-select" name="nik_suis">
                                    <option value="">Pilih Pendamping</option>
                                    <option value="Tidak Ada"
                                        <?php echo ($penjamin['nik_suis'] === 'Tidak Ada') ? 'selected' : ''; ?>>Tidak
                                        Ada</option>
                                    <?php
                                    $query_pendamping = mysqli_query($connect, "SELECT nikpend, nama FROM pendamping WHERE niknas='{$penjamin['niknas']}' AND status='Tersedia'");
                                    while ($row = mysqli_fetch_assoc($query_pendamping)) {
                                        $selected = ($row['nikpend'] === $penjamin['nik_suis']) ? 'selected' : '';
                                        echo "<option value='{$row['nikpend']}' $selected>{$row['nama']} ({$row['nikpend']})</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <input type="hidden" name="status" value="<?php echo $penjamin['status']; ?>">
                        </div>

                        <div class="text-end mt-3">
                            <button type="submit" name="update_penjamin" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        // Load data wilayah
        document.addEventListener('DOMContentLoaded', function() {
            const selectedKab = '<?php echo $penjamin['kab']; ?>';
            const selectedKec = '<?php echo $penjamin['kec']; ?>';

            // Load kabupaten
            fetch('https://www.emsifa.com/api-wilayah-indonesia/api/regencies/32.json')
                .then(response => response.json())
                .then(kabupaten => {
                    let kabupatenSelect = document.getElementById('kabupaten');
                    kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';

                    kabupaten.forEach(kab => {
                        let formattedText = kab.name.replace(/KABUPATEN /i, '').toLowerCase()
                            .replace(/\b\w/g, letter => letter.toUpperCase());
                        let option = document.createElement('option');
                        option.value = formattedText;
                        option.textContent = formattedText;
                        option.setAttribute('data-id', kab.id);
                        if (formattedText === selectedKab) {
                            option.selected = true;
                        }
                        kabupatenSelect.appendChild(option);
                    });

                    // Trigger change event untuk load kecamatan
                    if (selectedKab) {
                        kabupatenSelect.dispatchEvent(new Event('change'));
                    }
                });

            // Event listener untuk perubahan kabupaten
            document.getElementById('kabupaten').addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                const kabId = selectedOption.getAttribute('data-id');

                if (kabId) {
                    fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${kabId}.json`)
                        .then(response => response.json())
                        .then(kecamatan => {
                            let kecamatanSelect = document.getElementById('kecamatan');
                            kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';

                            kecamatan.forEach(kec => {
                                let formattedText = kec.name.toLowerCase()
                                    .replace(/\b\w/g, letter => letter.toUpperCase());
                                let option = document.createElement('option');
                                option.value = formattedText;
                                option.textContent = formattedText;
                                if (formattedText === selectedKec) {
                                    option.selected = true;
                                }
                                kecamatanSelect.appendChild(option);
                            });
                        });
                }
            });

            // Event listener untuk input tanggal lahir
            document.querySelector('.date-input').addEventListener('input', function() {
                const usia = hitungUsia(this.value);
                document.querySelector('input[name="usia"]').value = usia;
            });
        });

        // Fungsi untuk menghitung usia
        function hitungUsia(input) {
            if (input.length === 10) {
                const [day, month, year] = input.split('-');
                const birthDate = new Date(year, month - 1, day);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();

                if (today.getMonth() < birthDate.getMonth() ||
                    (today.getMonth() === birthDate.getMonth() && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age;
            }
            return '';
        }
    </script>
</body>

</html>