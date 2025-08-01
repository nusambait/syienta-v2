<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Tambahkan query untuk mengambil data AO di bagian atas file setelah koneksi database
$query_ao = mysqli_query($connect, "SELECT kd_ao, nama FROM account WHERE jabatan = 'AO' ORDER BY nama ASC");

// Ubah query untuk mengambil data FO dengan urutan jabatan yang ditentukan
$query_fo = mysqli_query($connect, "SELECT a.id as FO, a.nama, a.jabatan 
    FROM account a 
    WHERE a.status = 'AKTIF' 
    ORDER BY 
        CASE a.jabatan
            WHEN 'FO' THEN 1
            WHEN 'AO' THEN 2
            WHEN 'ADM' THEN 3
            WHEN 'CS' THEN 4
            WHEN 'KABID' THEN 5
            WHEN 'KACAB' THEN 6
            WHEN 'PPK' THEN 7
            WHEN 'SEKPER' THEN 8
            WHEN 'DIREKSI' THEN 9
            ELSE 10
        END,
        a.nama ASC");

// Tambahkan ini di bagian atas file, setelah session_start()
if (isset($_SESSION['success_message'])) {
    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Sukses',
                text: '" . $_SESSION['success_message'] . "',
                showConfirmButton: false,
                timer: 1500
            });
          </script>";
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . $_SESSION['error_message'] . "'
            });
          </script>";
    unset($_SESSION['error_message']);
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $noreg = mysqli_real_escape_string($connect, $_POST['noreg']);
    $nik = mysqli_real_escape_string($connect, $_POST['nik']);
    $tglPengajuan = mysqli_real_escape_string($connect, $_POST['tglPengajuan']);

    // Modifikasi pengolahan plafon
    $plafon_input = $_POST['jumlahPengajuan'];
    // Hapus Rp, spasi dan koma
    $plafon_clean = str_replace(['Rp', ' ', ','], '', $plafon_input);
    // Konversi ke float untuk memastikan format desimal yang benar
    $plaf = (float)$plafon_clean;

    $pengajuan = mysqli_real_escape_string($connect, $_POST['pengajuan']);

    // Cek apakah sudah ada pengajuan sebelumnya untuk nasabah ini
    $check_pengajuan = mysqli_query($connect, "SELECT COUNT(*) as total FROM pengajuan WHERE niknas = '$nik'");
    $row = mysqli_fetch_assoc($check_pengajuan);
    $pengajuan = strval($row['total'] + 1); // Konversi ke string karena kolom pengajuan bertipe varchar

    $tujuanPengajuan = mysqli_real_escape_string($connect, $_POST['tujpeng'] ?? '');

    // Tambahan field baru
    $norek = mysqli_real_escape_string($connect, $_POST['norek']);
    $jns_kredit = mysqli_real_escape_string($connect, $_POST['jns_kredit']);
    $penggunaan = mysqli_real_escape_string($connect, $_POST['penggunaan']);
    $prodkre = mysqli_real_escape_string($connect, $_POST['prodkre']);
    $jw = (int)$_POST['jw'];
    $sukbung = (float)$_POST['sukbung'];
    $angpok = (int)str_replace(['.', ','], '', $_POST['angpok']);
    $angbung = (int)str_replace(['.', ','], '', $_POST['angbung']);
    $totang = (int)str_replace(['.', ','], '', $_POST['totang']);
    $biaprov = (float)$_POST['biaprov'];
    $nomprov = (int)str_replace(['.', ','], '', $_POST['nomprov']);
    $adm = (int)str_replace(['.', ','], '', $_POST['adm']);
    $jaminan = mysqli_real_escape_string($connect, $_POST['jaminan']);
    $agen = mysqli_real_escape_string($connect, $_POST['agen']);
    $ket_peng = mysqli_real_escape_string($connect, $_POST['ket_peng']);
    $sumber = mysqli_real_escape_string($connect, $_POST['sumber']);
    $fo = mysqli_real_escape_string($connect, $_POST['fo']);
    $ao = mysqli_real_escape_string($connect, $_POST['ao'] ?? '');

    // Query insert dengan nilai plafon yang sudah diformat
    $query = "INSERT INTO pengajuan (noreg, niknas, norek, tglpeng, pengajuan, jns_kredit, 
              penggunaan, prodkre, plaf, jw, sukbung, angpok, angbung, totang, biaprov, 
              nomprov, adm, tujpeng, jaminan, ao, agen, status, ket_peng, sumber, FO) 
              VALUES ('$noreg', '$nik', '$norek', '$tglPengajuan', '$pengajuan', 
              '$jns_kredit', '$penggunaan', '$prodkre', $plaf, $jw, $sukbung, 
              $angpok, $angbung, $totang, $biaprov, $nomprov, $adm, '$tujuanPengajuan', 
              '$jaminan', '$ao', '$agen', 'SURVEI', '$ket_peng', '$sumber', '$fo')";

    if (mysqli_query($connect, $query)) {
        $query_droping = "INSERT INTO droping (noreg, status) VALUES ('$noreg', 'SURVEI')";

        if (mysqli_query($connect, $query_droping)) {
            $_SESSION['success_message'] = "Data pengajuan berhasil disimpan";
            header("Location: input-pengajuan.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Terjadi kesalahan saat menyimpan data droping";
            header("Location: input-pengajuan.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Terjadi kesalahan saat menyimpan data";
        header("Location: input-pengajuan.php");
        exit();
    }
}

// Ambil data nasabah jika ada parameter NIK
if (isset($_GET['nik'])) {
    $nik = mysqli_real_escape_string($connect, $_GET['nik']);
    $query = mysqli_query($connect, "SELECT * FROM nasabah WHERE nik = '$nik'");
    $nasabah = mysqli_fetch_assoc($query);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengajuan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    // Pindahkan ke bagian atas sebelum unset session
    window.onload = function() {
        <?php if (isset($_SESSION['success_message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Sukses',
            text: '<?php echo $_SESSION['success_message']; ?>',
            showConfirmButton: false,
            timer: 1500
        });
        <?php unset($_SESSION['success_message']);
            endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo $_SESSION['error_message']; ?>'
        });
        <?php unset($_SESSION['error_message']);
            endif; ?>
    }
    </script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Form Pengajuan</h2>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='input-pengajuan.php'">
                    <i class="bi bi-arrow-left"></i> Kembali
                </button>
            </div>

            <div class="card">
                <div class="card-body">
                    <form id="formPengajuan" method="POST">
                        <!-- Data Identitas -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Identitas</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="noreg" class="form-label">Nomor Registrasi</label>
                                            <input type="text" class="form-control" id="noreg" name="noreg"
                                                placeholder="100.12345.12-25" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nik" class="form-label">NIK Nasabah</label>
                                            <input type="text" class="form-control" id="nik" name="nik"
                                                value="<?php echo isset($_GET['nik']) ? $_GET['nik'] : ''; ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nama" class="form-label">Nama Nasabah</label>
                                            <input type="text" class="form-control" id="nama" name="nama"
                                                value="<?php echo isset($nasabah) ? $nasabah['nama'] : ''; ?>" readonly>
                                        </div>
                                        <div class="mb-3">
                                            <label for="norek" class="form-label">Nomor Rekening</label>
                                            <input type="text" class="form-control" id="norek" name="norek"
                                                maxlength="10" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Pengajuan -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Pengajuan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tglPengajuan" class="form-label">Tanggal Pengajuan</label>
                                            <input type="text" class="form-control date-input" id="tglPengajuan"
                                                name="tglPengajuan" value="<?php echo date('d-m-Y'); ?>"
                                                placeholder="dd-mm-yyyy" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="pengajuan" class="form-label">Pengajuan Ke-</label>
                                            <input type="number" class="form-control" id="pengajuan" name="pengajuan"
                                                min="1" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="jumlahPengajuan" class="form-label">Jumlah Pengajuan
                                                (Plafon)</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control" id="jumlahPengajuan"
                                                    name="jumlahPengajuan" required>
                                                <div class="input-group-text">
                                                    <input type="checkbox" name="enable_decimal" id="enable_decimal"
                                                        class="form-check-input mt-0" aria-label="Aktifkan desimal"
                                                        style="margin-right: 5px;">
                                                    <small>Desimal</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="jw" class="form-label">Jangka Waktu (Bulan)</label>
                                            <input type="number" class="form-control" id="jw" name="jw" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="sukbung" class="form-label">Suku Bunga (%)</label>
                                            <input type="number" class="form-control" id="sukbung" name="sukbung"
                                                required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jns_kredit" class="form-label">Jenis Kredit</label>
                                            <select class="form-control" id="jns_kredit" name="jns_kredit" required>
                                                <option value="">Pilih Jenis Kredit</option>
                                                <option value="Installment">Installment</option>
                                                <option value="Konsumtif">Konsumtif</option>
                                                <option value="KRY">Kredit Karyawan</option>
                                                <option value="Mikro">Mikro</option>
                                                <option value="Modal Kerja">Modal Kerja</option>
                                                <option value="Reguler">Reguler</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="penggunaan" class="form-label">Penggunaan</label>
                                            <select class="form-control" id="penggunaan" name="penggunaan" required>
                                                <option value="">Pilih Penggunaan</option>
                                                <option value="Modal Kerja">Modal Kerja</option>
                                                <option value="Investasi">Investasi</option>
                                                <option value="Konsumtif">Konsumtif</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="prodkre" class="form-label">Produk Kredit</label>
                                            <input type="text" class="form-control" id="prodkre" name="prodkre"
                                                required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tujuanPengajuan" class="form-label">Tujuan Pengajuan</label>
                                            <textarea class="form-control" id="tujpeng" name="tujpeng" rows="3"
                                                required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Angsuran -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Angsuran</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="angpok" class="form-label">Angsuran Pokok</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control readonly-blue" id="angpok"
                                                    name="angpok" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="angbung" class="form-label">Angsuran Bunga</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control readonly-blue" id="angbung"
                                                    name="angbung" readonly>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="totang" class="form-label">Total Angsuran</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control readonly-blue" id="totang"
                                                    name="totang" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Biaya -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Biaya & Jaminan</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="biaprov" class="form-label">Biaya Provisi (%)</label>
                                            <input type="number" step="0.15" class="form-control" id="biaprov"
                                                name="biaprov" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="nomprov" class="form-label">Nominal Provisi</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control" id="nomprov" name="nomprov"
                                                    readonly>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="adm" class="form-label">Biaya Administrasi</label>
                                            <div class="input-group">
                                                <span class="input-group-text">Rp</span>
                                                <input type="text" class="form-control" id="adm" name="adm" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="jaminan" class="form-label">Jaminan</label>
                                            <textarea class="form-control" id="jaminan" name="jaminan" rows="2"
                                                required></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Petugas -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <h5 class="mb-0">Data Petugas & Sumber</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="ao" class="form-label">Account Officer</label>
                                            <select class="form-control" id="ao" name="ao" required>
                                                <option value="">Pilih Account Officer</option>
                                                <?php while ($row = mysqli_fetch_assoc($query_ao)): ?>
                                                <option value="<?php echo htmlspecialchars($row['kd_ao']); ?>">
                                                    <?php echo htmlspecialchars($row['nama']); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fo" class="form-label">Pembawa Berkas</label>
                                            <select class="form-control" id="fo" name="fo" required>
                                                <option value="">Pilih Pembawa Berkas</option>
                                                <?php while ($row = mysqli_fetch_assoc($query_fo)): ?>
                                                <option value="<?php echo htmlspecialchars($row['FO']); ?>">
                                                    <?php echo htmlspecialchars($row['nama']); ?>
                                                </option>
                                                <?php endwhile; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="agen" class="form-label">Agen</label>
                                            <input type="text" class="form-control" id="agen" name="agen"
                                                value="Tanpa Agen">
                                        </div>
                                        <div class="mb-3">
                                            <label for="sumber" class="form-label">Sumber Pengajuan</label>
                                            <select class="form-control" id="sumber" name="sumber" required>
                                                <option value="">Pilih Sumber Pengajuan</option>
                                                <option value="50%">50%</option>
                                                <option value="Promosi">Promosi</option>
                                                <option value="Radio">Radio</option>
                                                <option value="Spanduk">Spanduk</option>
                                                <option value="Baligho">Baligho</option>
                                                <option value="Brosur">Brosur</option>
                                                <option value="CGC">CGC</option>
                                                <option value="Instagram">Instagram</option>
                                                <option value="Facebook">Facebook</option>
                                                <option value="Website">Website</option>
                                                <option value="Google">Google</option>
                                                <option value="X">X (Twitter)</option>
                                                <option value="Whatsapp">Whatsapp</option>
                                                <option value="Komunal">Komunal</option>
                                                <option value="X-Debitur">X-Debitur</option>
                                                <option value="Restrukturisasi">Restrukturisasi</option>
                                                <option value="Lainnya">Lainnya</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="ket_peng" class="form-label">Keterangan Pengajuan</label>
                                            <textarea class="form-control" id="ket_peng" name="ket_peng"
                                                rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/api-daerah.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Tampilkan notifikasi sukses jika ada
        <?php if (isset($_SESSION['success_message'])): ?>
        Swal.fire({
            icon: 'success',
            title: 'Sukses',
            text: '<?php echo $_SESSION['success_message']; ?>',
            showConfirmButton: false,
            timer: 1500
        });
        <?php endif; ?>

        // Tampilkan notifikasi error jika ada
        <?php if (isset($_SESSION['error_message'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo $_SESSION['error_message']; ?>'
        });
        <?php endif; ?>
    });

    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // Update fungsi format input jumlah
    document.getElementById('jumlahPengajuan').addEventListener('input', function(e) {
        const enableDecimal = document.getElementById('enable_decimal').checked;

        if (enableDecimal) {
            // Format dengan desimal
            let value = this.value.replace(/[^\d.]/g, '');
            if (value) {
                // Simpan posisi kursor
                const cursorPos = this.selectionStart;
                const prevLength = this.value.length;

                // Format angka
                let parts = value.split('.');
                if (parts.length > 2) parts = [parts[0], parts.slice(1).join('')];
                if (parts[1]) parts[1] = parts[1].slice(0, 2);

                // Format ribuan hanya untuk bagian bilangan bulat
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                value = parts.join('.');
                this.value = value;

                // Kembalikan posisi kursor dengan memperhitungkan perubahan panjang
                const newPos = cursorPos + (this.value.length - prevLength);
                this.setSelectionRange(newPos, newPos);
            } else {
                this.value = '';
            }
        } else {
            // Format bilangan bulat
            let value = this.value.replace(/\D/g, '');
            this.value = value ? value.replace(/\B(?=(\d{3})+(?!\d))/g, ",") : '';
        }
        hitungAngsuran();
    });

    // Tambahkan event listener untuk checkbox desimal
    document.getElementById('enable_decimal').addEventListener('change', function() {
        const input = document.getElementById('jumlahPengajuan');
        const currentValue = input.value.replace(/,/g, '');

        if (this.checked) {
            // Jika checkbox dicentang, izinkan 2 desimal
            if (currentValue) {
                const numValue = parseFloat(currentValue);
                input.value = formatDecimal(numValue);
            }
        } else {
            // Jika checkbox tidak dicentang, bulatkan ke bilangan bulat
            if (currentValue) {
                const numValue = Math.round(parseFloat(currentValue));
                input.value = numValue.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        }
        hitungAngsuran();
    });

    // Fungsi untuk format desimal
    function formatDecimal(num) {
        let parts = parseFloat(num).toFixed(2).toString().split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join('.');
    }

    // Fungsi untuk format number dengan pemisah ribuan
    function formatNumber(num) {
        // Format tanpa desimal untuk semua angka kecuali plafond
        return Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    }

    // Fungsi untuk menghitung angsuran
    function hitungAngsuran() {
        // Ambil nilai input
        const plafonStr = document.getElementById('jumlahPengajuan').value.replace(/,/g, '');
        const jw = parseInt(document.getElementById('jw').value) || 0;
        const sukbung = parseFloat(document.getElementById('sukbung').value) || 0;
        const biaprov = parseFloat(document.getElementById('biaprov').value) || 0;

        // Konversi plafon ke float
        const plafon = parseFloat(plafonStr);

        if (plafon && jw && sukbung) {
            // Hitung angsuran pokok per bulan
            const angsuranPokok = plafon / jw;

            // Hitung angsuran bunga per bulan
            const angsuranBunga = (plafon * (sukbung / 100)) / 12;

            // Hitung total angsuran
            const totalAngsuran = angsuranPokok + angsuranBunga;

            // Update nilai input dengan format angka tanpa desimal
            document.getElementById('angpok').value = formatNumber(angsuranPokok);
            document.getElementById('angbung').value = formatNumber(angsuranBunga);
            document.getElementById('totang').value = formatNumber(totalAngsuran);
        }

        // Hitung nominal provisi terpisah
        if (plafon && biaprov) {
            const nominalProvisi = plafon * (biaprov / 100);
            document.getElementById('nomprov').value = formatNumber(nominalProvisi);
        } else {
            document.getElementById('nomprov').value = '';
        }
    }

    // Event listeners untuk input yang mempengaruhi perhitungan
    document.getElementById('jumlahPengajuan').addEventListener('input', hitungAngsuran);
    document.getElementById('jw').addEventListener('input', hitungAngsuran);
    document.getElementById('sukbung').addEventListener('input', hitungAngsuran);
    document.getElementById('biaprov').addEventListener('input', hitungAngsuran);

    // Event listener untuk checkbox desimal
    document.getElementById('enable_decimal').addEventListener('change', function() {
        hitungAngsuran(); // Hitung ulang saat checkbox berubah
    });

    // Panggil hitungAngsuran saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        hitungAngsuran();
    });

    // Auto-fill nama nasabah ketika NIK diisi
    document.getElementById('nik').addEventListener('blur', function() {
        const nik = this.value;
        if (nik) {
            fetch(`get_nasabah.php?nik=${nik}`)
                .then(response => response.json())
                .then(data => {
                    if (data.nama) {
                        document.getElementById('nama').value = data.nama;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'NIK tidak ditemukan!'
                        });
                        document.getElementById('nama').value = '';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    });

    // Validasi noreg saat input selesai
    document.getElementById('noreg').addEventListener('blur', function() {
        const noreg = this.value;
        if (noreg) {
            // Kirim request AJAX untuk cek noreg
            fetch('check-noreg.php?noreg=' + encodeURIComponent(noreg))
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Nomor Registrasi sudah ada!'
                        });
                        this.value = ''; // Kosongkan input
                        this.focus(); // Fokus kembali ke input
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
        }
    });

    // Update validasi form submission
    document.getElementById('formPengajuan').addEventListener('submit', function(e) {
        e.preventDefault();

        const noreg = document.getElementById('noreg').value;
        const nik = document.getElementById('nik').value;
        const nama = document.getElementById('nama').value;

        // Cek noreg terlebih dahulu
        fetch('check-noreg.php?noreg=' + encodeURIComponent(noreg))
            .then(response => response.json())
            .then(data => {
                if (data.exists) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Nomor Registrasi sudah ada!'
                    });
                    return;
                }

                if (!nama) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'NIK tidak valid!'
                    });
                    return;
                }

                // Jika semua validasi berhasil, submit form
                this.submit();
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

        if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 767) {
            document.getElementById('sidebar').classList.remove('show');
        }
    });

    // Format nomor registrasi otomatis
    document.getElementById('noreg').addEventListener('input', function(e) {
        let value = this.value.replace(/\D/g, ''); // Hapus semua karakter non-digit

        if (value.length > 12) {
            value = value.substr(0, 12); // Batasi maksimal 12 digit
        }

        // Format sesuai pola xxx.xxxxx.xx-xx
        if (value.length > 0) {
            let formatted = '';
            if (value.length > 0) {
                formatted += value.substr(0, Math.min(3, value.length));
            }
            if (value.length > 3) {
                formatted += '.' + value.substr(3, Math.min(5, value.length - 3));
            }
            if (value.length > 8) {
                formatted += '.' + value.substr(8, Math.min(2, value.length - 8));
            }
            if (value.length > 10) {
                formatted += '-' + value.substr(10, Math.min(2, value.length - 10));
            }

            this.value = formatted;
        }
    });
    </script>
</body>

</html>