<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil parameter dari URL
$jenis = $_GET['jenis'];
$id = $_GET['id'];

// Validasi jenis jaminan
$allowed_types = ['bpkb', 'shm', 'ajb', 'kios', 'bilyet', 'manulife', 'bpih', 'spph'];
if (!in_array($jenis, $allowed_types)) {
    die("Jenis jaminan tidak valid");
}

// Ambil data jaminan berdasarkan jenis dan id
$query = mysqli_query($connect, "SELECT * FROM $jenis WHERE " . ($jenis == 'bpih' || $jenis == 'spph' ? 'id' : 'norut') . "='$id'");
$data = mysqli_fetch_array($query);

if (!$data) {
    die("Data jaminan tidak ditemukan");
}

// Proses update data jaminan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['success' => false, 'message' => ''];

    // Siapkan query update berdasarkan jenis jaminan
    switch ($jenis) {
        case 'bpkb':
            $query = mysqli_query($connect, "UPDATE bpkb SET 
                bpkb = '{$_POST['bpkb']}',
                nopol = '{$_POST['nopol']}',
                merk = '{$_POST['merk']}',
                norang = '{$_POST['norang']}',
                nomes = '{$_POST['nomes']}',
                war = '{$_POST['war']}',
                thnpem = '{$_POST['thnpem']}',
                an = '{$_POST['an']}',
                almt = '{$_POST['almt']}',
                psrwjr = '{$_POST['psrwjr']}',
                tak = '{$_POST['tak']}',
                bahbak = '{$_POST['bahbak']}',
                pengikatan = '{$_POST['pengikatan']}'
                WHERE norut = '$id'");
            break;

        case 'shm':
            $query = mysqli_query($connect, "UPDATE shm SET 
                bukkep = '{$_POST['bukkep']}',
                suruk = '{$_POST['suruk']}',
                lt = '{$_POST['lt']}',
                an = '{$_POST['an']}',
                almt = '{$_POST['almt']}',
                kec = '{$_POST['kec']}',
                kab = '{$_POST['kab']}',
                blok = '{$_POST['blok']}',
                tglter = '{$_POST['tglter']}',
                njop = '{$_POST['njop']}',
                tak = '{$_POST['tak']}',
                pengikatan = '{$_POST['pengikatan']}'
                WHERE norut = '$id'");
            break;

        case 'ajb':
            $query = mysqli_query($connect, "UPDATE ajb SET 
                bukkep = '{$_POST['bukkep']}',
                persil = '{$_POST['persil']}',
                kohir = '{$_POST['kohir']}',
                lt = '{$_POST['lt']}',
                an = '{$_POST['an']}',
                almt = '{$_POST['almt']}',
                kec = '{$_POST['kec']}',
                kab = '{$_POST['kab']}',
                blok = '{$_POST['blok']}',
                tglter = '{$_POST['tglter']}',
                njop = '{$_POST['njop']}',
                tak = '{$_POST['tak']}',
                pengikatan = '{$_POST['pengikatan']}'
                WHERE norut = '$id'");
            break;

        case 'kios':
            $query = mysqli_query($connect, "UPDATE kios SET 
                bukkep = '{$_POST['bukkep']}',
                ukuran = '{$_POST['ukuran']}',
                an = '{$_POST['an']}',
                blok = '{$_POST['blok']}',
                almt = '{$_POST['almt']}',
                tgltrbt = '{$_POST['tgltrbt']}',
                jenus = '{$_POST['jenus']}',
                psrwjr = '{$_POST['psrwjr']}',
                tak = '{$_POST['tak']}',
                pengikatan = '{$_POST['pengikatan']}'
                WHERE norut = '$id'");
            break;

        case 'bilyet':
            $query = mysqli_query($connect, "UPDATE bilyet SET 
                norek = '{$_POST['norek']}',
                nobuk = '{$_POST['nobuk']}',
                nom = '{$_POST['nom']}',
                tglbuka = '{$_POST['tglbuka']}',
                tgljthtempo = '{$_POST['tgljthtempo']}',
                an = '{$_POST['an']}',
                jentabdep = '{$_POST['jentabdep']}',
                pengikatan = '{$_POST['pengikatan']}'
                WHERE norut = '$id'");
            break;

        case 'manulife':
            $query = mysqli_query($connect, "UPDATE manulife SET 
                nojam = '{$_POST['nojam']}',
                jendok = '{$_POST['jendok']}',
                nadok = '{$_POST['nadok']}',
                tgldok = '{$_POST['tgldok']}',
                pengikatan = '{$_POST['pengikatan']}'
                WHERE norut = '$id'");
            break;

        case 'bpih':
            $query = mysqli_query($connect, "UPDATE bpih SET 
                noval = '{$_POST['noval']}',
                norek = '{$_POST['norek']}',
                tgl_surat = '{$_POST['tgl_surat']}',
                an = '{$_POST['an']}'
                WHERE id = '$id'");
            break;

        case 'spph':
            $query = mysqli_query($connect, "UPDATE spph SET 
                nopor = '{$_POST['nopor']}',
                noval = '{$_POST['noval']}',
                tgl_surat = '{$_POST['tgl_surat']}',
                tgl_stt = '{$_POST['tgl_stt']}',
                kemenag = '{$_POST['kemenag']}',
                an = '{$_POST['an']}'
                WHERE id = '$id'");
            break;
    }

    if ($query) {
        $response['success'] = true;
        $response['message'] = "Data jaminan berhasil diupdate";
    } else {
        $response['message'] = "Gagal mengupdate data jaminan";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Jaminan - <?php echo ucfirst($jenis); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card mt-3">
                        <div class="card-header bg-white">
                            <h4 class="card-title mb-0">Edit Jaminan <?php echo ucfirst($jenis); ?></h4>
                        </div>
                        <div class="card-body">
                            <form id="editForm" method="POST">
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="alert alert-info mb-3">
                                            <strong>Perhatian:</strong> Pastikan semua data yang diisi sudah benar sebelum menyimpan.
                                        </div>

                                        <!-- Form Fields -->
                                        <?php if ($jenis == 'bpkb'): ?>
                                            <!-- BPKB Fields Group -->
                                            <div class="col-12">
                                                <div class="card border">
                                                    <div class="card-header bg-light">
                                                        <h5 class="mb-0">Informasi Kendaraan</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">No BPKB</label>
                                                                <input type="text" class="form-control" name="bpkb"
                                                                    value="<?php echo $data['bpkb']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">No Polisi</label>
                                                                <input type="text" class="form-control" name="nopol"
                                                                    value="<?php echo $data['nopol']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Merk</label>
                                                                <input type="text" class="form-control" name="merk"
                                                                    value="<?php echo $data['merk']; ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">No Rangka</label>
                                                                <input type="text" class="form-control" name="norang"
                                                                    value="<?php echo $data['norang']; ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">No Mesin</label>
                                                                <input type="text" class="form-control" name="nomes"
                                                                    value="<?php echo $data['nomes']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Warna</label>
                                                                <input type="text" class="form-control" name="war"
                                                                    value="<?php echo $data['war']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Tahun Pembuatan</label>
                                                                <input type="text" class="form-control" name="thnpem"
                                                                    value="<?php echo $data['thnpem']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Atas Nama</label>
                                                                <input type="text" class="form-control" name="an"
                                                                    value="<?php echo $data['an']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Alamat</label>
                                                                <input type="text" class="form-control" name="almt"
                                                                    value="<?php echo $data['almt']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Nilai Pasar Wajar</label>
                                                                <input type="number" class="form-control" name="psrwjr"
                                                                    value="<?php echo $data['psrwjr']; ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Nilai Taksasi</label>
                                                                <input type="number" class="form-control" name="tak"
                                                                    value="<?php echo $data['tak']; ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Bahan Bakar</label>
                                                                <input type="text" class="form-control" name="bahbak"
                                                                    value="<?php echo $data['bahbak']; ?>" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php elseif ($jenis == 'shm'): ?>
                                            <!-- SHM Fields Group -->
                                            <div class="col-12">
                                                <div class="card border">
                                                    <div class="card-header bg-light">
                                                        <h5 class="mb-0">Informasi Sertifikat</h5>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">No Sertifikat</label>
                                                                <input type="text" class="form-control" name="bukkep"
                                                                    value="<?php echo $data['bukkep']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Surat Ukur</label>
                                                                <input type="text" class="form-control" name="suruk"
                                                                    value="<?php echo $data['suruk']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Luas Tanah (m²)</label>
                                                                <input type="number" class="form-control" name="lt"
                                                                    value="<?php echo $data['lt']; ?>" required>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Atas Nama</label>
                                                                <input type="text" class="form-control" name="an"
                                                                    value="<?php echo $data['an']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-12">
                                                                <label class="form-label">Alamat</label>
                                                                <input type="text" class="form-control" name="almt"
                                                                    value="<?php echo $data['almt']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-6">
                                                                <label class="form-label">Kabupaten</label>
                                                                <select type="text" class="form-control" name="kab"
                                                                    id="kabupaten" required>
                                                                    <option value="">Pilih Kabupaten</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <label class="form-label">Kecamatan</label>
                                                                <select type="text" class="form-control" name="kec"
                                                                    id="kecamatan" required>
                                                                    <option value="">Pilih Kecamatan</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Blok</label>
                                                                <input type="text" class="form-control" name="blok"
                                                                    value="<?php echo $data['blok']; ?>" required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">Tanggal Terbit</label>
                                                                <input type="text" class="form-control date-input"
                                                                    name="tglter" value="<?php echo $data['tglter']; ?>"
                                                                    required>
                                                            </div>
                                                            <div class="col-md-4">
                                                                <label class="form-label">NJOP</label>
                                                                <input type="number" class="form-control" name="njop"
                                                                    value="<?php echo $data['njop']; ?>" required>
                                                            </div>
                                                        </div>
                                                        <div class="row g-3">
                                                            <div class="col-md-4">
                                                                <label class="form-label">Nilai Taksasi</label>
                                                                <input type="number" class="form-control" name="tak"
                                                                    value="<?php echo $data['tak']; ?>" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        <?php endif; ?>

                                        <!-- Common Fields Group -->
                                        <div class="col-12 mt-3">
                                            <div class="card border">
                                                <div class="card-header bg-light">
                                                    <h5 class="mb-0">Informasi Tambahan</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label">Pengikatan</label>
                                                            <input type="text" class="form-control" name="pengikatan"
                                                                value="<?php echo $data['pengikatan']; ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="col-12 mt-3">
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="javascript:history.back()" class="btn btn-secondary">
                                                    <i class="bi bi-arrow-left me-1"></i>Kembali
                                                </a>
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="bi bi-save me-1"></i>Simpan
                                                </button>
                                            </div>
                                        </div>
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
    <script src="../assets/js/api-daerah.js"></script>
    <script>
        // Ambil value kabupaten dan kecamatan dari PHP
        const kabupatenDb = <?php echo json_encode($data['kab'] ?? ''); ?>;
        const kecamatanDb = <?php echo json_encode($data['kec'] ?? ''); ?>;

        // Prefill kabupaten dan kecamatan setelah data kabupaten selesai dimuat
        document.addEventListener("DOMContentLoaded", function() {
            // Tunggu kabupaten selesai di-load oleh api-daerah.js
            (async function prefillKabKec() {
                // Tunggu sampai option kabupaten sudah ada
                let kabupatenSelect = document.getElementById("kabupaten");
                let kecamatanSelect = document.getElementById("kecamatan");
                let maxWait = 20; // maksimal 2 detik
                while (kabupatenSelect.options.length <= 1 && maxWait > 0) {
                    await new Promise(r => setTimeout(r, 100));
                    maxWait--;
                }
                // Pilih kabupaten sesuai database
                if (kabupatenDb) {
                    for (let i = 0; i < kabupatenSelect.options.length; i++) {
                        if (kabupatenSelect.options[i].value === kabupatenDb) {
                            kabupatenSelect.selectedIndex = i;
                            // Trigger event change agar kecamatan ter-load
                            kabupatenSelect.dispatchEvent(new Event('change'));
                            break;
                        }
                    }
                }
                // Tunggu kecamatan selesai di-load
                maxWait = 20;
                while (kecamatanSelect.options.length <= 1 && maxWait > 0) {
                    await new Promise(r => setTimeout(r, 100));
                    maxWait--;
                }
                // Pilih kecamatan sesuai database
                if (kecamatanDb) {
                    for (let i = 0; i < kecamatanSelect.options.length; i++) {
                        if (kecamatanSelect.options[i].value === kecamatanDb) {
                            kecamatanSelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            })();
        });

        document.getElementById('editForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('edit-jaminan.php?jenis=<?php echo $jenis; ?>&id=<?php echo $id; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            showCancelButton: false,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.history.back();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat memproses permintaan.',
                        icon: 'error'
                    });
                });
        });
    </script>
</body>

</html>