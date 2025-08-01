<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Cek apakah user sudah login
if (!isset($_SESSION['username'])) {
    header("Location: ../../login.php");
    exit();
}

// Ambil parameter dari URL
$jenis = $_GET['jenis'] ?? '';
$id = $_GET['id'] ?? '';
$atas_nama = $_GET['atas_nama'] ?? '';

// Validasi parameter
if (empty($jenis) || empty($id) || empty($atas_nama)) {
    echo '<div class="alert alert-danger">Parameter tidak valid!</div>';
    exit();
}

// Ambil noreg dari kolom status pada tabel jaminan
$noreg_jaminan = '-';
if (!empty($jenis) && !empty($id)) {
    $table = mysqli_real_escape_string($connect, $jenis);
    $id_field = ($jenis === 'bpih' || $jenis === 'spph') ? 'id' : 'norut';
    $id_val = mysqli_real_escape_string($connect, $id);
    $q = mysqli_query($connect, "SELECT status FROM $table WHERE $id_field='$id_val' LIMIT 1");
    if ($q && $row = mysqli_fetch_assoc($q)) {
        $noreg_jaminan = $row['status'] ?? '-';
    }
}

// Cari data taksasi yang sudah ada
$current_taksasi = '-';
$id_taksasi = '-';
$query_taksasi = mysqli_query($connect, "SELECT id, taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
    $taksasi_row = mysqli_fetch_array($query_taksasi);
    $current_taksasi = $taksasi_row['taksasi'] ?? '-';
    $id_taksasi = $taksasi_row['id'] ?? '-';
}

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_taksasi = $_POST['taksasi'] ?? '';

    // Bersihkan input taksasi: hilangkan Rp, titik, spasi, dan karakter non-numerik kecuali koma
    $clean_taksasi = preg_replace('/[^0-9,]/', '', str_replace(['Rp', ' ', '.'], '', $new_taksasi));
    // Konversi koma ke titik jika ada (opsional, jika ingin simpan desimal)
    $clean_taksasi = str_replace(',', '.', $clean_taksasi);

    // Jika ada data taksasi yang sudah ada, update
    if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
        $update_query = "UPDATE res_jam SET taksasi = ? WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%'";
        $stmt = $connect->prepare($update_query);
        $stmt->bind_param('s', $clean_taksasi);

        if ($stmt->execute()) {
            $success_message = "Nilai taksasi berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui nilai taksasi: " . $stmt->error;
        }
    } else {
        // Jika belum ada data, insert baru
        $insert_query = "INSERT INTO res_jam (jaminan, taksasi) VALUES (?, ?)";
        $stmt = $connect->prepare($insert_query);
        $stmt->bind_param('ss', $atas_nama, $clean_taksasi);

        if ($stmt->execute()) {
            $success_message = "Nilai taksasi berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan nilai taksasi: " . $stmt->error;
        }
    }

    // Update current_taksasi untuk ditampilkan
    $current_taksasi = $clean_taksasi;
}

// Fungsi untuk memformat nilai taksasi
function formatTaksasi($taksasi)
{
    if (empty($taksasi) || $taksasi === '-') {
        return '';
    }

    // Coba konversi ke angka
    $numeric_value = preg_replace('/[^0-9]/', '', $taksasi);
    if (is_numeric($numeric_value) && $numeric_value > 0) {
        return number_format((float)$numeric_value, 0, ',', '.');
    }

    return $taksasi;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Nilai Taksasi - <?php echo htmlspecialchars($atas_nama); ?></title>
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
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title mb-0">Edit Nilai Taksasi</h4>
                                <a href="data-komite.php?noreg=<?php echo urlencode($noreg_jaminan); ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Kembali
                                </a>
                            </div>

                            <?php if (isset($success_message)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <i class="bi bi-check-circle me-2"></i>
                                    <?php echo $success_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($error_message)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    <?php echo $error_message; ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Informasi Jaminan</h5>
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td width="150"><strong>Jenis Jaminan:</strong></td>
                                                    <td><?php echo strtoupper(htmlspecialchars($jenis)); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Atas Nama:</strong></td>
                                                    <td><?php echo htmlspecialchars($atas_nama); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>ID:</strong></td>
                                                    <td><?php echo htmlspecialchars($id); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>ID Taksasi:</strong></td>
                                                    <td><?php echo htmlspecialchars($id_taksasi); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><strong>No. Registrasi:</strong></td>
                                                    <td><?php echo htmlspecialchars($noreg_jaminan); ?></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-8">
                                    <div class="card">
                                        <div class="card-header">
                                            <h5 class="mb-0">Edit Nilai Taksasi</h5>
                                        </div>
                                        <div class="card-body">
                                            <form method="POST" action="">
                                                <div class="mb-3">
                                                    <label for="taksasi" class="form-label">Nilai Taksasi</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text">Rp</span>
                                                        <input type="text"
                                                            class="form-control"
                                                            id="taksasi"
                                                            name="taksasi"
                                                            value="<?php echo htmlspecialchars(formatTaksasi($current_taksasi)); ?>"
                                                            placeholder="Masukkan nilai taksasi"
                                                            oninput="formatNumber(this)">
                                                    </div>
                                                    <div class="form-text">
                                                        Masukkan nilai taksasi dalam format angka (contoh: 100000000)
                                                    </div>
                                                </div>

                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="bi bi-save me-2"></i>Simpan Perubahan
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Fungsi untuk memformat angka dengan separator ribuan
        function formatNumber(input) {
            // Hapus semua karakter non-digit
            let value = input.value.replace(/\D/g, '');

            // Format dengan separator ribuan
            if (value.length > 0) {
                value = parseInt(value).toLocaleString('id-ID');
            }

            input.value = value;
        }

        // Konfirmasi sebelum submit
        document.querySelector('form').addEventListener('submit', function(e) {
            const taksasi = document.getElementById('taksasi').value;
            if (!taksasi.trim()) {
                e.preventDefault();
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Nilai taksasi tidak boleh kosong!'
                });
                return;
            }

            Swal.fire({
                title: 'Konfirmasi',
                text: 'Apakah Anda yakin ingin menyimpan perubahan nilai taksasi?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Simpan',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) {
                    e.preventDefault();
                }
            });
        });

        // Auto format saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const taksasiInput = document.getElementById('taksasi');
            if (taksasiInput.value) {
                formatNumber(taksasiInput);
            }
        });
    </script>
</body>

</html>