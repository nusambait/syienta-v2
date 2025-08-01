<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil data sertifikat berdasarkan norut
$norut = $_GET['norut'];
$nik = $_GET['nik'];
$query_sertifikat = mysqli_query($connect, "SELECT * FROM shm WHERE norut='$norut' AND nik='$nik'");
$data_sertifikat = mysqli_fetch_array($query_sertifikat);

if (isset($_POST['submit'])) {
    $jenjam = $_POST['jenjam'];
    $bukkep = $_POST['bukkep'];
    $suruk = $_POST['suruk'];
    $lt = $_POST['lt'];
    $an = $_POST['an'];
    $almt = $_POST['almt'];
    $kec = $_POST['kec'];
    $kab = $_POST['kab'];
    $blok = $_POST['blok'];
    $tglter = $_POST['tglter'];
    $leg = $_POST['leg'];
    $njop = $_POST['njop'];
    $tak = $_POST['tak'];
    $pengikatan = $_POST['pengikatan'];
    $ketjam = $_POST['ketjam'];

    $query = mysqli_query($connect, "UPDATE shm SET 
        jenjam='$jenjam',
        bukkep='$bukkep',
        suruk='$suruk',
        lt='$lt',
        an='$an',
        almt='$almt',
        kec='$kec',
        kab='$kab',
        blok='$blok',
        tglter='$tglter',
        leg='$leg',
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
                    window.location.href = 'form-sertifikat.php?nik=" . $nik . "';
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
    <title>Edit Data Sertifikat</title>
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
                    <h4 class="mb-0 text-gray-800">Edit Data Sertifikat</h4>
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="../dashboard/index.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="input-jaminan.php">Input Jaminan</a></li>
                        <li class="breadcrumb-item"><a href="form-sertifikat.php?nik=<?php echo $nik; ?>">Form
                                Sertifikat</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Edit Sertifikat</li>
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
                                        <option value="Sebidang Tanah Darat dan Bangunan"
                                            <?php echo ($data_sertifikat['jenjam'] == 'Sebidang Tanah Darat dan Bangunan') ? 'selected' : ''; ?>>
                                            Sebidang Tanah Darat dan Bangunan</option>
                                        <option value="Sebidang Tanah Darat"
                                            <?php echo ($data_sertifikat['jenjam'] == 'Sebidang Tanah Darat') ? 'selected' : ''; ?>>
                                            Sebidang Tanah Darat</option>
                                        <option value="Sebidang Tanah Sawah"
                                            <?php echo ($data_sertifikat['jenjam'] == 'Sebidang Tanah Sawah') ? 'selected' : ''; ?>>
                                            Sebidang Tanah Sawah</option>
                                        <option value="Sebidang Bangunan"
                                            <?php echo ($data_sertifikat['jenjam'] == 'Sebidang Bangunan') ? 'selected' : ''; ?>>
                                            Sebidang Bangunan</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Bukti Kepemilikan</label>
                                    <input type="text" class="form-control" name="bukkep"
                                        value="<?php echo $data_sertifikat['bukkep']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Surat Ukur</label>
                                    <input type="text" class="form-control" name="suruk"
                                        value="<?php echo $data_sertifikat['suruk']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Luas Tanah</label>
                                    <input type="text" class="form-control" name="lt"
                                        value="<?php echo $data_sertifikat['lt']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Atas Nama</label>
                                    <input type="text" class="form-control" name="an"
                                        value="<?php echo $data_sertifikat['an']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Alamat</label>
                                    <textarea class="form-control" name="almt"
                                        required><?php echo $data_sertifikat['almt']; ?></textarea>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Kabupaten</label>
                                    <select type="text" class="form-control" name="kab" id="kabupaten" required>
                                        <option value="">Pilih Kabupaten</option>
                                        <option value="<?php echo $data_sertifikat['kab']; ?>" selected><?php echo $data_sertifikat['kab']; ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <select class="form-select" name="kec" id="kecamatan" required>
                                        <option value="">Pilih Kecamatan</option>
                                        <option value="<?php echo $data_sertifikat['kec']; ?>" selected><?php echo $data_sertifikat['kec']; ?></option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Blok</label>
                                    <input type="text" class="form-control" name="blok"
                                        value="<?php echo $data_sertifikat['blok']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Tanggal Terbit</label>
                                    <input type="text" class="form-control date-input" name="tglter"
                                        value="<?php echo $data_sertifikat['tglter']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Legalitas</label>
                                    <input type="number" class="form-control" name="leg"
                                        value="<?php echo $data_sertifikat['leg']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Nilai Jual Objek Pajak</label>
                                    <input type="number" class="form-control" name="njop"
                                        value="<?php echo $data_sertifikat['njop']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Taksasi</label>
                                    <input type="number" class="form-control" name="tak"
                                        value="<?php echo $data_sertifikat['tak']; ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required">Pengikatan</label>
                                    <select class="form-select" name="pengikatan" required>
                                        <option value="">Pilih Pengikatan</option>
                                        <option value="INTERN"
                                            <?php echo ($data_sertifikat['pengikatan'] == 'INTERN') ? 'selected' : ''; ?>>
                                            INTERN</option>
                                        <option value="SKMHT"
                                            <?php echo ($data_sertifikat['pengikatan'] == 'SKMHT') ? 'selected' : ''; ?>>
                                            SKMHT</option>
                                        <option value="APHT"
                                            <?php echo ($data_sertifikat['pengikatan'] == 'APHT') ? 'selected' : ''; ?>>
                                            APHT</option>
                                        <option value="LEGALISASI"
                                            <?php echo ($data_sertifikat['pengikatan'] == 'LEGALISASI') ? 'selected' : ''; ?>>
                                            LEGALISASI</option>
                                        <option value="ADDENDUM"
                                            <?php echo ($data_sertifikat['pengikatan'] == 'ADDENDUM') ? 'selected' : ''; ?>>
                                            ADDENDUM</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Keterangan</label>
                                    <textarea class="form-control"
                                        name="ketjam"><?php echo $data_sertifikat['ketjam']; ?></textarea>
                                </div>
                            </div>

                            <div class="mt-4 d-flex justify-content-between">
                                <a href="form-sertifikat.php?nik=<?php echo $nik; ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Kembali
                                </a>
                                <button type="submit" name="submit" class="btn btn-primary">
                                    <i class="bi bi-save me-1"></i> Update
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            // Simpan nilai yang ada di database
            const savedKabupaten = '<?php echo $data_sertifikat['kab']; ?>';
            const savedKecamatan = '<?php echo $data_sertifikat['kec']; ?>';

            // Tunggu sampai data kabupaten selesai dimuat
            await getKabupaten();

            // Set nilai kabupaten yang tersimpan
            const kabupatenSelect = document.getElementById('kabupaten');
            for (let i = 0; i < kabupatenSelect.options.length; i++) {
                if (kabupatenSelect.options[i].text === savedKabupaten) {
                    kabupatenSelect.selectedIndex = i;
                    // Ambil data-id dari option yang dipilih
                    const kabupatenId = kabupatenSelect.options[i].getAttribute('data-id');
                    // Load kecamatan berdasarkan kabupaten yang dipilih
                    await getKecamatan(kabupatenId);
                    break;
                }
            }

            // Set nilai kecamatan yang tersimpan
            setTimeout(() => {
                const kecamatanSelect = document.getElementById('kecamatan');
                for (let i = 0; i < kecamatanSelect.options.length; i++) {
                    if (kecamatanSelect.options[i].text === savedKecamatan) {
                        kecamatanSelect.selectedIndex = i;
                        break;
                    }
                }
            }, 500); // Tambahkan delay untuk memastikan data kecamatan sudah dimuat
        });
    </script>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/api-daerah.js"></script>
</body>

</html>