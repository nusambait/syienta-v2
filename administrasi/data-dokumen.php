<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Ambil data droping berdasarkan noreg
$noreg = $_GET['noreg'];
$query = mysqli_query($connect, "SELECT d.*, p.niknas, n.nama as nama_nasabah 
    FROM droping d 
    LEFT JOIN pengajuan p ON p.noreg = d.noreg
    LEFT JOIN nasabah n ON n.nik = p.niknas
    WHERE d.noreg='$noreg'");
$data = mysqli_fetch_array($query);

// Perbaikan query untuk mengambil data height berdasarkan kantor user
$username = $_SESSION['username'];
$query_height = mysqli_query($connect, "SELECT h.value 
    FROM height h 
    JOIN account a ON a.kantor = h.id 
    WHERE a.username = '$username'");
$height_data = mysqli_fetch_array($query_height);
$current_height = $height_data ? $height_data['value'] : '';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dokumen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-gradient-blue {
            background: linear-gradient(to right, #1e88e5, #1565c0);
            color: white;
            text-transform: capitalize;
            border: none;
            transition: all 0.3s ease;
        }

        .btn-gradient-blue:hover {
            background: linear-gradient(to right, #1565c0, #0d47a1);
            color: white;
        }
    </style>
</head>

<body>
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="row">
                <div class="col-12">

                    <?php include '../includes/menu-droping.php'; ?>

                    <div class="col-12">
                        <div class="card mt-12 mt-4">
                            <div class="card-body">

                                <form id="heightForm">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="d-flex align-items-end gap-2">
                                                <div class="flex-grow-1">
                                                    <label class="form-label">Set Ukuran Dokumen</label>
                                                    <input type="text" class="form-control" id="heightValue"
                                                        value="<?= $current_height ?>" placeholder="Masukkan ukuran">
                                                </div>
                                                <div>
                                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Check status droping -->
                    <?php
                    $status = $data['status'];
                    $allowed_status = ['CAIR', 'SIAP CAIR'];

                    // Function to check if status starts with specific strings
                    function checkStatusPrefix($status, $prefix)
                    {
                        return strpos(strtoupper($status), strtoupper($prefix)) === 0;
                    }

                    $show_documents = false;
                    if (
                        in_array($status, $allowed_status) ||
                        checkStatusPrefix($status, 'MCC') ||
                        checkStatusPrefix($status, 'ACC')
                    ) {
                        $show_documents = true;
                    }
                    ?>

                    <!-- Replace the existing card content with this conditional rendering -->
                    <div class="card mt-3">
                        <div class="card-body">
                            <?php if (!$show_documents): ?>
                                <div class="text-center">
                                    <h1 class="text-danger mb-4">Pengajuan belum bisa melakukan pembuatan PK</h1>
                                    <p class="text-muted">Status pengajuan di izinkan adalah CAIR, SIAP, CAIR, ACC DIREKSI/KACAB</p>
                                </div>
                            <?php else: ?>
                                <h4 class="card-title mb-4">Data Dokumen</h4>

                                <div class="row">
                                    <!-- Kolom 1 -->
                                    <div class="col-md-3">
                                        <!-- Wajib -->
                                        <h5>Wajib</h5>
                                        <div class="mb-4">
                                            <a href="dokumen/surat-pengajuan-flat.php?noreg=<?= $noreg ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">Surat Pengajuan BWMK Direksi
                                                FLAT/ANUITAS</a>
                                            <a href="dokumen/surat-pengajuan-sliding.php?noreg=<?= $noreg ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Surat Pengajuan
                                                BWMK Direksi SLIDING</a>
                                            <a href="../../nusamba_api/production/system/dok_notaris_merry.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">Surat Order Notaris</a>
                                        </div>

                                        <!-- Perjanjian Kredit -->
                                        <h5>Perjanjian Kredit</h5>
                                        <div class="mb-4">
                                            <a href="../../nusamba_api/production/system/dok_pemberitahuan.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Pemberitahuan</a>
                                            <a href="../../nusamba_api/production/system/dok_autodebet.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Autodebet</a>
                                            <a href="../../nusamba_api/production/system/dok_ttj.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Tanda Terima
                                                Jaminan</a>
                                            <a href="../../nusamba_api/production/system/dok_perjanjiankredit.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Perjanjian Kredit
                                                FLAT/ANUITAS</a>
                                            <a href="../../nusamba_api/production/system/dok_perjanjiankredit_sliding.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Perjanjian Kredit
                                                SLIDING</a>
                                            <a href="../../nusamba_api/production/system/dok_perjanjiankredit_slidingblt.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Perjanjian Kredit
                                                SLIDING Bullet P</a>
                                            <a href="dokumen/perjanjian-kredit-sindikasi.php?noreg=<?= $noreg ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Perjanjian Kredit
                                                SLIDING SINDIKASI</a>
                                            <a href="../../nusamba_api/production/system/dok_perjanjiankredithaji.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Perjanjian Kredit
                                                (HAJI)</a>
                                        </div>

                                        <!-- SPPK -->
                                        <h5>SPPK</h5>
                                        <div class="mb-4">
                                            <a href="../../nusamba_api/production/system/dok_spp.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">SPPK
                                                FLAT/ANUITAS</a>
                                            <a href="../../nusamba_api/production/system/dok_sppreguler.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">SPPK Sliding</a>
                                        </div>
                                    </div>

                                    <!-- Kolom 2 -->
                                    <div class="col-md-3">
                                        <!-- SMTPK -->
                                        <h5>SMTPK</h5>
                                        <div class="mb-4">
                                            <a href="../../nusamba_api/production/system/dok_smtpk1.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">SMTPK INST FLAT</a>
                                            <a href="../../nusamba_api/production/system/dok_smtpk2.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">SMTPK INST
                                                ANUITAS</a>
                                            <a href="../../nusamba_api/production/system/dok_smtpk3.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">SMTPK REG
                                                SLIDING</a>
                                        </div>

                                        <!-- Jadwal Angsuran -->
                                        <h5>Jadwal Angsuran</h5>
                                        <div class="mb-4">
                                            <a href="../../nusamba_api/production/system/dok_jadwal.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">JADWAL Flat</a>
                                            <a href="../../nusamba_api/production/system/dok_jadwalreguler.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">JADWAL Sliding</a>
                                            <a href="../../nusamba_api/production/system/dok_jadwalanuitas.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">JADWAL Anuitas</a>
                                        </div>

                                        <!-- Paket Asuransi -->
                                        <h5>Paket Asuransi</h5>
                                        <div class="mb-4">
                                            <a href="dokumen/asuransi-a1.php?noreg=<?= $noreg ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">ASURANSI PAKET A1</a>
                                            <a href="dokumen/asuransi-a2.php?noreg=<?= $noreg ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">ASURANSI PAKET A2</a>
                                        </div>
                                    </div>

                                    <!-- Kolom 3 -->
                                    <div class="col-md-3">
                                        <!-- PORSI HAJI -->
                                        <h5>PORSI HAJI</h5>
                                        <div class="mb-4">
                                            <a href="../../nusamba_api/production/system/dok_spphaji.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">Persetujuan Fasilitas Kredit
                                                (HAJI)</a>
                                            <a href="../../nusamba_api/production/system/dok_smtpkhaji.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">SMTPK (HAJI)</a>
                                            <a href="../../nusamba_api/production/system/dok_sur_pernyataan_haji.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">Surat Pernyataan (HAJI)</a>
                                            <a href="../../nusamba_api/production/system/dok_adendum_haji.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">Addendum (HAJI)</a>
                                            <a href="../../nusamba_api/production/system/dok_suratkuasa_haji.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">Surat Kuasa (HAJI)</a>
                                            <a href="../../nusamba_api/production/system/dok_ttj_haji.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">Tanda Terima Jaminan (HAJI)</a>
                                        </div>

                                        <!-- SHM/AJB -->
                                        <h5>SHM/AJB</h5>
                                        <div class="mb-4">
                                            <a href="../../nusamba_api/production/system/dok_skmmh.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">SKMMH</a>
                                        </div>
                                    </div>

                                    <!-- Kolom 4 -->
                                    <div class="col-md-3">
                                        <!-- BPKB/KIOS -->
                                        <h5>BPKB/KIOS</h5>
                                        <div class="mb-4">
                                            <a href="../../nusamba_api/production/system/dok_feo.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Surat FEO</a>
                                            <a href="../../nusamba_api/production/system/dok_ppbj.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">PPBJ</a>
                                            <a href="../../nusamba_api/production/system/dok_surkuas.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">SURAT KUASA</a>
                                            <a href="../../nusamba_api/production/system/dok_diasuransikan.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Diasuransikan</a>
                                            <a href="../../nusamba_api/production/system/dok_tdkdiasuransikan.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Tidak
                                                Diasuransikan</a>
                                        </div>

                                        <!-- Lain-Lain -->
                                        <h5>Lain-Lain</h5>
                                        <div class="mb-4">
                                            <a href="../../nusamba_api/production/system/dok_pegadaian.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Surat Pegadaian</a>
                                            <a href="../../nusamba_api/production/system/dok_spdkb.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">SPKKB</a>
                                            <a href="../../nusamba_api/production/system/dok_surblok.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Surat Blokir</a>
                                            <a href="../../nusamba_api/production/system/dok_penolakan.php?noreg=<?= $noreg ?>&nik=<?= $data['niknas'] ?>&noregis=<?= $data['noreg'] ?>"
                                                target="_blank" class="btn btn-gradient-blue w-100 mb-2">Surat Penolakan
                                                Kredit</a>
                                            <a href="dokumen/qr-alamat.php?noreg=<?= $noreg ?>" target="_blank"
                                                class="btn btn-gradient-blue w-100 mb-2">QR CODE ALAMAT</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        document.getElementById('heightForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const heightValue = document.getElementById('heightValue').value;

            fetch('update-height.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'height=' + encodeURIComponent(heightValue)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: 'Ukuran dokumen berhasil diperbarui'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat memperbarui ukuran dokumen'
                        });
                    }
                });
        });
    </script>
</body>

</html>