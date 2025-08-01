<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Proses update data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Tambahkan ini di awal untuk mendapatkan NIK
    $nik = mysqli_real_escape_string($connect, $_POST['nik']);

    // Validasi NIK harus tepat 16 digit
    if (strlen($nik) !== 16 || !preg_match('/^[0-9]{16}$/', $nik)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'NIK harus tepat 16 digit angka',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
        exit;
    }

    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $tmpt = mysqli_real_escape_string($connect, $_POST['tmpt']);
    $tgl = mysqli_real_escape_string($connect, $_POST['tgl']);
    $usia = mysqli_real_escape_string($connect, $_POST['usia']);
    $jk = mysqli_real_escape_string($connect, $_POST['jk']);
    $almt = mysqli_real_escape_string($connect, $_POST['almt']);
    $kec = mysqli_real_escape_string($connect, $_POST['kec']);
    $kab = mysqli_real_escape_string($connect, $_POST['kab']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $pend = mysqli_real_escape_string($connect, $_POST['pend']);
    $pek = mysqli_real_escape_string($connect, $_POST['pek']);
    $ttktp = mysqli_real_escape_string($connect, $_POST['ttktp_formatted']);
    $mb = mysqli_real_escape_string($connect, $_POST['mb']);
    $ibu = mysqli_real_escape_string($connect, $_POST['ibu']);
    $tlfn1 = mysqli_real_escape_string($connect, $_POST['tlfn1']);
    $tlfn2 = mysqli_real_escape_string($connect, $_POST['tlfn2']);
    $jns = mysqli_real_escape_string($connect, $_POST['jns']);
    $jmlh = mysqli_real_escape_string($connect, $_POST['jmlh']);
    $anak = mysqli_real_escape_string($connect, $_POST['anak']);

    // Proses pekerjaan - jika memilih Lainnya, gunakan input manual
    if ($pek == 'Lainnya') {
        if (isset($_POST['pek_lainnya']) && !empty($_POST['pek_lainnya'])) {
            $pek_lainnya = mysqli_real_escape_string($connect, $_POST['pek_lainnya']);

            // Validasi format input (hanya huruf, spasi, titik, dan koma)
            if (!preg_match('/^[A-Za-z\s\.\,]+$/', $pek_lainnya)) {
                echo "
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Pekerjaan hanya boleh berisi huruf, spasi, titik, dan koma',
                            confirmButtonText: 'OK'
                        });
                    });
                </script>";
                exit;
            }

            $pek = $pek_lainnya;
        } else {
            // Jika memilih Lainnya tapi tidak mengisi input, kembalikan error
            echo "
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Harap isi pekerjaan Anda jika memilih Lainnya',
                        confirmButtonText: 'OK'
                    });
                });
            </script>";
            exit;
        }
    }

    // Validasi nomor telepon
    $tlfn1 = empty($tlfn1) ? '+62' : $tlfn1;
    $tlfn2 = empty($tlfn2) ? '+62' : $tlfn2;

    $query = "UPDATE nasabah SET 
              nama='$nama', tmpt='$tmpt', tgl='$tgl', usia='$usia', 
              jk='$jk', almt='$almt', kec='$kec', kab='$kab', 
              status='$status', pend='$pend', pek='$pek', ttktp='$ttktp', 
              mb='$mb', ibu='$ibu', tlfn1='$tlfn1', tlfn2='$tlfn2', 
              jns='$jns', jmlh='$jmlh', anak='$anak'
              WHERE nik='$nik'";

    if (!mysqli_query($connect, $query)) {
        $error_message = mysqli_error($connect);
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Error: " . $error_message . "',
                    confirmButtonText: 'OK'
                });
            });
        </script>";
    } else {
        echo "
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data berhasil diupdate',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href='input-nasabah.php';
                    }
                });
            });
        </script>";
    }
}

// Pindahkan pengecekan NIK dan data ke dalam DOMContentLoaded
if (!isset($_GET['nik'])) {
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'NIK tidak ditemukan!',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href='input-nasabah.php';
                }
            });
        });
    </script>";
    exit;
}

$nik = mysqli_real_escape_string($connect, $_GET['nik']);

// Ambil data nasabah berdasarkan NIK
$query = mysqli_query($connect, "SELECT * FROM nasabah WHERE nik='$nik'");
$data = mysqli_fetch_array($query);

if (!$data) {
    echo "
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: 'Data nasabah tidak ditemukan!',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href='input-nasabah.php';
                }
            });
        });
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Nasabah</title>
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
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Edit Data Nasabah</h2>
                <a href="input-nasabah.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIK/NPWP Perusahaan</label>
                                    <input type="text" class="form-control" value="<?php echo $data['nik']; ?>"
                                        readonly>
                                    <input type="hidden" name="nik" value="<?php echo $data['nik']; ?>">
                                    <div class="form-text">NIK harus tepat 16 digit angka</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama"
                                        value="<?php echo $data['nama']; ?>" pattern="[A-Za-z\s\.\,]+"
                                        title="Hanya huruf titik dan koma yang diperbolehkan"
                                        onkeypress="return /[A-Za-z\s\.\,]/.test(event.key)"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" class="form-control" name="tmpt"
                                        value="<?php echo $data['tmpt']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="text" class="form-control date-input" name="tgl"
                                        value="<?php echo $data['tgl']; ?>" onchange="hitungUsia(this.value)" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usia</label>
                                    <input type="number" class="form-control" name="usia"
                                        value="<?php echo $data['usia']; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" name="jk" required>
                                        <option value="L"
                                            <?php echo ($data['jk'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki
                                        </option>
                                        <option value="P"
                                            <?php echo ($data['jk'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" name="almt"
                                        required><?php echo $data['almt']; ?></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kabupaten</label>
                                    <select class="form-select" name="kab" id="kabupaten" required>
                                        <option value="">Pilih Kabupaten</option>
                                        <!-- Akan diisi oleh JavaScript -->
                                        <option value="<?php echo $data['kab']; ?>" selected><?php echo $data['kab']; ?></option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <select class="form-select" name="kec" id="kecamatan" required>
                                        <option value="">Pilih Kecamatan</option>
                                        <!-- Akan diisi oleh JavaScript -->
                                        <option value="<?php echo $data['kec']; ?>" selected><?php echo $data['kec']; ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Status Pernikahan</label>
                                    <select class="form-select" name="status" required>
                                        <option value="Belum Menikah"
                                            <?php echo ($data['status'] == 'Belum Menikah') ? 'selected' : ''; ?>>Belum
                                            Menikah</option>
                                        <option value="Menikah"
                                            <?php echo ($data['status'] == 'Menikah') ? 'selected' : ''; ?>>Menikah
                                        </option>
                                        <option value="Cerai"
                                            <?php echo ($data['status'] == 'Cerai') ? 'selected' : ''; ?>>Cerai</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pendidikan Terakhir</label>
                                    <select class="form-select" name="pend" required>
                                        <option value="SD" <?php echo ($data['pend'] == 'SD') ? 'selected' : ''; ?>>SD
                                        </option>
                                        <option value="SMP" <?php echo ($data['pend'] == 'SMP') ? 'selected' : ''; ?>>
                                            SMP</option>
                                        <option value="SMA" <?php echo ($data['pend'] == 'SMA') ? 'selected' : ''; ?>>
                                            SMA</option>
                                        <option value="D3" <?php echo ($data['pend'] == 'D3') ? 'selected' : ''; ?>>D3
                                        </option>
                                        <option value="S1" <?php echo ($data['pend'] == 'S1') ? 'selected' : ''; ?>>S1
                                        </option>
                                        <option value="S2" <?php echo ($data['pend'] == 'S2') ? 'selected' : ''; ?>>S2
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pekerjaan</label>
                                    <div class="alert alert-info" role="alert">
                                        <!-- <i class="bi bi-info-circle"></i> -->
                                        <strong>Petunjuk:</strong> Pilih pekerjaan dari daftar di bawah. Jika pekerjaan Anda tidak ada dalam daftar, pilih "Lainnya" dan ketik pekerjaan Anda secara manual.
                                    </div>
                                    <select class="form-select" name="pek" id="pekerjaanSelect" onchange="togglePekerjaanLainnya()" required>
                                        <option value="">Pilih Pekerjaan</option>
                                        <option value="PNS" <?php echo ($data['pek'] == 'PNS') ? 'selected' : ''; ?>>PNS</option>
                                        <option value="TNI/POLRI" <?php echo ($data['pek'] == 'TNI/POLRI') ? 'selected' : ''; ?>>TNI/POLRI</option>
                                        <option value="Karyawan Swasta" <?php echo ($data['pek'] == 'Karyawan Swasta') ? 'selected' : ''; ?>>Karyawan Swasta</option>
                                        <option value="Karyawan Honorer" <?php echo ($data['pek'] == 'Karyawan Honorer') ? 'selected' : ''; ?>>Karyawan Honorer</option>
                                        <option value="Wiraswasta" <?php echo ($data['pek'] == 'Wiraswasta') ? 'selected' : ''; ?>>Wiraswasta</option>
                                        <option value="Petani/Pekebun" <?php echo ($data['pek'] == 'Petani/Pekebun') ? 'selected' : ''; ?>>Petani/Pekebun</option>
                                        <option value="Nelayan" <?php echo ($data['pek'] == 'Nelayan') ? 'selected' : ''; ?>>Nelayan</option>
                                        <option value="Buruh" <?php echo ($data['pek'] == 'Buruh') ? 'selected' : ''; ?>>Buruh</option>
                                        <option value="Guru" <?php echo ($data['pek'] == 'Guru') ? 'selected' : ''; ?>>Guru</option>
                                        <option value="Dosen" <?php echo ($data['pek'] == 'Dosen') ? 'selected' : ''; ?>>Dosen</option>
                                        <option value="Dokter" <?php echo ($data['pek'] == 'Dokter') ? 'selected' : ''; ?>>Dokter</option>
                                        <option value="Perawat" <?php echo ($data['pek'] == 'Perawat') ? 'selected' : ''; ?>>Perawat</option>
                                        <option value="Pedagang" <?php echo ($data['pek'] == 'Pedagang') ? 'selected' : ''; ?>>Pedagang</option>
                                        <option value="Pengacara" <?php echo ($data['pek'] == 'Pengacara') ? 'selected' : ''; ?>>Pengacara</option>
                                        <option value="Notaris" <?php echo ($data['pek'] == 'Notaris') ? 'selected' : ''; ?>>Notaris</option>
                                        <option value="Arsitek" <?php echo ($data['pek'] == 'Arsitek') ? 'selected' : ''; ?>>Arsitek</option>
                                        <option value="Akuntan" <?php echo ($data['pek'] == 'Akuntan') ? 'selected' : ''; ?>>Akuntan</option>
                                        <option value="Konsultan" <?php echo ($data['pek'] == 'Konsultan') ? 'selected' : ''; ?>>Konsultan</option>
                                        <option value="Freelancer" <?php echo ($data['pek'] == 'Freelancer') ? 'selected' : ''; ?>>Freelancer</option>
                                        <option value="Ibu Rumah Tangga" <?php echo ($data['pek'] == 'Ibu Rumah Tangga') ? 'selected' : ''; ?>>Ibu Rumah Tangga</option>
                                        <option value="Pelajar/Mahasiswa" <?php echo ($data['pek'] == 'Pelajar/Mahasiswa') ? 'selected' : ''; ?>>Pelajar/Mahasiswa</option>
                                        <option value="Pensiunan" <?php echo ($data['pek'] == 'Pensiunan') ? 'selected' : ''; ?>>Pensiunan</option>
                                        <option value="Lainnya" <?php echo ($data['pek'] == 'Lainnya' || !in_array($data['pek'], ['PNS', 'TNI/POLRI', 'Karyawan Swasta', 'Karyawan Honorer', 'Wiraswasta', 'Petani/Pekebun', 'Nelayan', 'Buruh', 'Guru', 'Dosen', 'Dokter', 'Perawat', 'Pedagang', 'Pengacara', 'Notaris', 'Arsitek', 'Akuntan', 'Konsultan', 'Freelancer', 'Ibu Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan'])) ? 'selected' : ''; ?>>Lainnya</option>
                                    </select>
                                    <div class="mb-3" id="pekerjaanLainnyaDiv" style="display: <?php echo (!in_array($data['pek'], ['PNS', 'TNI/POLRI', 'Karyawan Swasta', 'Karyawan Honorer', 'Wiraswasta', 'Petani/Pekebun', 'Nelayan', 'Buruh', 'Guru', 'Dosen', 'Dokter', 'Perawat', 'Pedagang', 'Pengacara', 'Notaris', 'Arsitek', 'Akuntan', 'Konsultan', 'Freelancer', 'Ibu Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan'])) ? 'block' : 'none'; ?>;">
                                        <label class="form-label">Sebutkan Pekerjaan</label>
                                        <input type="text" class="form-control" name="pek_lainnya" id="pekerjaanLainnya"
                                            placeholder="Masukkan pekerjaan Anda"
                                            pattern="[A-Za-z\s\.\,]+"
                                            title="Hanya huruf titik dan koma yang diperbolehkan"
                                            onkeypress="return /[A-Za-z\s\.\,]/.test(event.key)"
                                            value="<?php echo (!in_array($data['pek'], ['PNS', 'TNI/POLRI', 'Karyawan Swasta', 'Karyawan Honorer', 'Wiraswasta', 'Petani/Pekebun', 'Nelayan', 'Buruh', 'Guru', 'Dosen', 'Dokter', 'Perawat', 'Pedagang', 'Pengacara', 'Notaris', 'Arsitek', 'Akuntan', 'Konsultan', 'Freelancer', 'Ibu Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan'])) ? $data['pek'] : ''; ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal KTP</label>
                                    <input type="text" class="form-control date-input" name="ttktp"
                                        onchange="formatTanggalKTP(this.value)" value="<?php echo $data['ttktp']; ?>"
                                        required>
                                    <input type="hidden" name="ttktp_formatted" id="ttktp_formatted"
                                        value="<?php echo $data['ttktp']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Masa Berlaku KTP</label>
                                    <input type="text" class="form-control" name="mb"
                                        value="<?php echo $data['mb']; ?>" pattern="[A-Za-z\s\.\,]+"
                                        title="Hanya huruf titik dan koma yang diperbolehkan"
                                        onkeypress="return /[A-Za-z\s\.\,]/.test(event.key)"
                                        required readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Ibu Kandung</label>
                                    <input type="text" class="form-control" name="ibu"
                                        value="<?php echo $data['ibu']; ?>" pattern="[A-Za-z\s\.\,\-]+"
                                        title="Hanya huruf titik, koma, dan garis yang diperbolehkan"
                                        onkeypress="return /[A-Za-z\s\.\,\-]/.test(event.key)"
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon 1</label>
                                    <input type="text" class="form-control" name="tlfn1"
                                        value="<?php echo $data['tlfn1']; ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon 2</label>
                                    <input type="text" class="form-control" name="tlfn2"
                                        value="<?php echo $data['tlfn2']; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Tanggungan</label>
                                    <select class="form-select" name="jns" id="jenisTanggungan"
                                        onchange="toggleTanggungan()" required>
                                        <option value="Tidak Ada"
                                            <?php echo ($data['jns'] == 'Tidak Ada') ? 'selected' : ''; ?>>Tidak Ada
                                        </option>
                                        <option value="Ada" <?php echo ($data['jns'] == 'Ada') ? 'selected' : ''; ?>>Ada
                                        </option>
                                    </select>
                                </div>
                                <div class="mb-3" id="jmlhDiv"
                                    style="display: <?php echo ($data['jns'] == 'Ada') ? 'block' : 'none'; ?>">
                                    <label class="form-label">Jumlah Tanggungan</label>
                                    <input type="number" class="form-control" name="jmlh" id="jmlh"
                                        value="<?php echo $data['jmlh']; ?>">
                                </div>
                                <div class="mb-3" id="anakDiv"
                                    style="display: <?php echo ($data['jns'] == 'Ada') ? 'block' : 'none'; ?>">
                                    <label class="form-label">Jumlah Anak</label>
                                    <input type="number" class="form-control" name="anak" id="anak"
                                        value="<?php echo $data['anak']; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary" onclick="return validateForm()">Update Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/api-daerah.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        // Fungsi-fungsi JavaScript yang sama seperti di input-nasabah.php
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        // Fungsi untuk menghitung usia
        function hitungUsia(tglLahir) {
            // Memisahkan tanggal, bulan, dan tahun
            const [day, month, year] = tglLahir.split('-');

            const today = new Date();
            // Format tanggal ke format yang benar (yyyy-mm-dd)
            const birthDate = new Date(`${year}-${month}-${day}`);

            let usia = today.getFullYear() - birthDate.getFullYear();
            const m = today.getMonth() - birthDate.getMonth();

            if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
                usia--;
            }

            const usiaInput = document.querySelector('input[name="usia"]');
            if (usiaInput && !isNaN(usia)) {
                usiaInput.value = usia;
            }
        }

        // Fungsi untuk toggle tanggungan
        function toggleTanggungan() {
            const jenisTanggungan = document.getElementById('jenisTanggungan').value;
            const jmlhDiv = document.getElementById('jmlhDiv');
            const anakDiv = document.getElementById('anakDiv');
            const jmlhInput = document.getElementById('jmlh');
            const anakInput = document.getElementById('anak');

            if (jenisTanggungan === 'Tidak Ada') {
                jmlhDiv.style.display = 'none';
                anakDiv.style.display = 'none';
                jmlhInput.value = '0';
                anakInput.value = '0';
            } else {
                jmlhDiv.style.display = 'block';
                anakDiv.style.display = 'block';
                jmlhInput.required = true;
                anakInput.required = true;
            }
        }

        // Fungsi untuk toggle input pekerjaan lainnya
        function togglePekerjaanLainnya() {
            const pekerjaanSelect = document.getElementById('pekerjaanSelect');
            const pekerjaanLainnyaDiv = document.getElementById('pekerjaanLainnyaDiv');
            const pekerjaanLainnyaInput = document.getElementById('pekerjaanLainnya');

            if (pekerjaanSelect.value === 'Lainnya') {
                pekerjaanLainnyaDiv.style.display = 'block';
                pekerjaanLainnyaInput.required = true;
            } else {
                pekerjaanLainnyaDiv.style.display = 'none';
                pekerjaanLainnyaInput.required = false;
                pekerjaanLainnyaInput.value = '';
            }
        }

        // Fungsi validasi form
        function validateForm() {
            // Validasi pekerjaan
            const pekerjaanSelect = document.getElementById('pekerjaanSelect');
            const pekerjaanLainnyaInput = document.getElementById('pekerjaanLainnya');

            if (pekerjaanSelect.value === 'Lainnya') {
                if (!pekerjaanLainnyaInput.value.trim()) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Harap isi pekerjaan Anda jika memilih Lainnya',
                        confirmButtonText: 'OK'
                    });
                    pekerjaanLainnyaInput.focus();
                    return false;
                }

                // Validasi format input (hanya huruf, spasi, titik, dan koma)
                const regex = /^[A-Za-z\s\.\,]+$/;
                if (!regex.test(pekerjaanLainnyaInput.value.trim())) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Pekerjaan hanya boleh berisi huruf, spasi, titik, dan koma',
                        confirmButtonText: 'OK'
                    });
                    pekerjaanLainnyaInput.focus();
                    return false;
                }
            }
            return true;
        }
    </script>

    <script>
        function formatTanggalKTP(value) {
            // Memastikan format tanggal KTP konsisten
            let [day, month, year] = value.split('-');
            document.getElementById('ttktp_formatted').value = `${day}-${month}-${year}`;
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', async function() {
            // Simpan nilai yang ada di database
            const savedKabupaten = '<?php echo $data['kab']; ?>';
            const savedKecamatan = '<?php echo $data['kec']; ?>';
            const savedPekerjaan = '<?php echo $data['pek']; ?>';

            // Cek apakah pekerjaan yang tersimpan adalah custom (bukan dari dropdown)
            const pekerjaanOptions = ['PNS', 'TNI/POLRI', 'Karyawan Swasta', 'Karyawan Honorer', 'Wiraswasta', 'Petani/Pekebun', 'Nelayan', 'Buruh', 'Guru', 'Dosen', 'Dokter', 'Perawat', 'Pedagang', 'Pengacara', 'Notaris', 'Arsitek', 'Akuntan', 'Konsultan', 'Freelancer', 'Ibu Rumah Tangga', 'Pelajar/Mahasiswa', 'Pensiunan', 'Lainnya'];
            const isCustomPekerjaan = !pekerjaanOptions.includes(savedPekerjaan) && savedPekerjaan !== '';

            // Jika pekerjaan custom, set dropdown ke "Lainnya" dan tampilkan input
            if (isCustomPekerjaan) {
                const pekerjaanSelect = document.getElementById('pekerjaanSelect');
                pekerjaanSelect.value = 'Lainnya';
                // Tampilkan input dan set nilainya
                const pekerjaanLainnyaDiv = document.getElementById('pekerjaanLainnyaDiv');
                const pekerjaanLainnyaInput = document.getElementById('pekerjaanLainnya');
                pekerjaanLainnyaDiv.style.display = 'block';
                pekerjaanLainnyaInput.value = savedPekerjaan;
                pekerjaanLainnyaInput.required = true;
            }

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
</body>

</html>