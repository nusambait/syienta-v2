<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Fungsi helper untuk memformat angka dengan aman
function formatNumber($value, $decimals = 0)
{
    if ($value === null || $value === '' || $value === '-') {
        return '0';
    }
    return number_format((float)$value, $decimals, ',', '.');
}

// Proses Update Status Jaminan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $response = ['success' => false, 'message' => ''];

    // Validasi input
    if (!isset($_POST['jenis']) || !isset($_POST['id']) || !isset($_POST['action']) || !isset($_POST['noreg'])) {
        $response['message'] = "Parameter yang diperlukan tidak lengkap";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $jenis = $_POST['jenis'];
    $id = $_POST['id'];
    $action = $_POST['action'];
    $noreg = $_POST['noreg'];

    // Validasi action
    if (!in_array($action, ['pasang', 'lepas'])) {
        $response['message'] = "Action tidak valid";
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $status = ($action == 'pasang') ? $noreg : 'Tersedia';

    // Tentukan tabel berdasarkan jenis jaminan
    $table = '';
    switch ($jenis) {
        case 'bpkb':
            $table = 'bpkb';
            break;
        case 'shm':
            $table = 'shm';
            break;
        case 'ajb':
            $table = 'ajb';
            break;
        case 'kios':
            $table = 'kios';
            break;
        case 'bilyet':
            $table = 'bilyet';
            break;
        case 'manulife':
            $table = 'manulife';
            break;
        case 'bpih':
            $table = 'bpih';
            break;
        case 'spph':
            $table = 'spph';
            break;
    }

    if ($table) {
        // Tentukan field ID berdasarkan jenis jaminan
        $idField = 'norut'; // default
        if ($jenis == 'bpih' || $jenis == 'spph') {
            $idField = 'id';
        }

        // Log untuk debugging
        error_log("Updating table: $table, field: $idField, id: $id, status: $status");

        // Validasi koneksi database
        if (!$connect) {
            $response['message'] = "Koneksi database tidak tersedia";
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }

        $query = mysqli_query($connect, "UPDATE $table SET status='$status' WHERE $idField=$id");

        if ($query) {
            $response['success'] = true;
            $response['message'] = "Berhasil " . ($action == 'pasang' ? 'memasang' : 'melepas') . " jaminan";
        } else {
            $error_msg = mysqli_error($connect);
            error_log("Database error: " . $error_msg);
            $response['message'] = "Gagal mengubah status jaminan: " . $error_msg;
        }
    } else {
        $response['message'] = "Jenis jaminan tidak valid";
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Ambil data droping dan NIK nasabah berdasarkan noreg
$noreg = $_GET['noreg'];

// Validasi noreg
if (empty($noreg)) {
    echo '<div class="alert alert-danger">No. Registrasi tidak valid!</div>';
    exit;
}

$query = mysqli_query($connect, "SELECT d.*, p.niknas, n.nama as nama_nasabah, n.nik 
    FROM droping d 
    LEFT JOIN pengajuan p ON p.noreg = d.noreg
    LEFT JOIN nasabah n ON n.nik = p.niknas
    WHERE d.noreg='$noreg'");

if (!$query) {
    echo '<div class="alert alert-danger">Error: ' . mysqli_error($connect) . '</div>';
    exit;
}

$data = mysqli_fetch_array($query);

if (!$data) {
    echo '<div class="alert alert-warning">Data tidak ditemukan untuk No. Registrasi: ' . htmlspecialchars($noreg) . '</div>';
    exit;
}

// Query untuk mengambil data dari semua tabel jaminan berdasarkan NIK
$nik = $data['nik'];

if (empty($nik)) {
    echo '<div class="alert alert-warning">NIK nasabah tidak ditemukan!</div>';
    exit;
}

// Query untuk semua jenis jaminan dengan error handling
$query_bpkb = mysqli_query($connect, "SELECT *, 'BPKB' as jenis_jaminan FROM bpkb WHERE nik='$nik'");
if (!$query_bpkb) {
    error_log("Error query BPKB: " . mysqli_error($connect));
}

$query_shm = mysqli_query($connect, "SELECT *, 'Sertifikat' as jenis_jaminan FROM shm WHERE nik='$nik'");
if (!$query_shm) {
    error_log("Error query SHM: " . mysqli_error($connect));
}

$query_ajb = mysqli_query($connect, "SELECT *, 'AKTA' as jenis_jaminan FROM ajb WHERE nik='$nik'");
if (!$query_ajb) {
    error_log("Error query AJB: " . mysqli_error($connect));
}

$query_kios = mysqli_query($connect, "SELECT *, 'Kios' as jenis_jaminan FROM kios WHERE nik='$nik'");
if (!$query_kios) {
    error_log("Error query Kios: " . mysqli_error($connect));
}

$query_bilyet = mysqli_query($connect, "SELECT *, 'Bilyet' as jenis_jaminan FROM bilyet WHERE nik='$nik'");
if (!$query_bilyet) {
    error_log("Error query Bilyet: " . mysqli_error($connect));
}

$query_manulife = mysqli_query($connect, "SELECT *, 'Manulife' as jenis_jaminan FROM manulife WHERE nik='$nik'");
if (!$query_manulife) {
    error_log("Error query Manulife: " . mysqli_error($connect));
}

$query_bpih = mysqli_query($connect, "SELECT *, 'Haji BPIH' as jenis_jaminan FROM bpih WHERE nik='$nik'");
if (!$query_bpih) {
    error_log("Error query BPIH: " . mysqli_error($connect));
}

$query_spph = mysqli_query($connect, "SELECT *, 'Haji SPPH' as jenis_jaminan FROM spph WHERE nik='$nik'");
if (!$query_spph) {
    error_log("Error query SPPH: " . mysqli_error($connect));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Jaminan - <?php echo $data['noreg']; ?></title>
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

                    <?php include '../includes/menu-droping.php'; ?>

                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="card-title mb-3">Data Jaminan</h4>

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Jenis Jaminan</th>
                                            <th>Detail Jaminan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;

                                        // BPKB
                                        if ($query_bpkb && mysqli_num_rows($query_bpkb) > 0) {
                                            while ($bpkb = mysqli_fetch_array($query_bpkb)) {
                                                $isLocked = $bpkb['status'] == $noreg;
                                                echo "<tr>
                                                    <td>$no</td>
                                                    <td>{$bpkb['jenis_jaminan']}</td>
                                                    <td>
                                                        No BPKB: " . ($bpkb['bpkb'] ?? '-') . "<br>
                                                        No Polisi: " . ($bpkb['nopol'] ?? '-') . "<br>
                                                        Merk: " . ($bpkb['merk'] ?? '-') . "<br>
                                                        No Rangka: " . ($bpkb['norang'] ?? '-') . "<br>
                                                        No Mesin: " . ($bpkb['nomes'] ?? '-') . "<br>
                                                        Warna: " . ($bpkb['war'] ?? '-') . "<br>
                                                        Tahun: " . ($bpkb['thnpem'] ?? '-') . "<br>
                                                        Atas Nama: " . ($bpkb['an'] ?? '-') . "<br>
                                                        Alamat: " . ($bpkb['almt'] ?? '-') . "<br>
                                                        Nilai Pasar Wajar: Rp " . formatNumber($bpkb['psrwjr'] ?? 0) . "<br>
                                                        Nilai Taksasi: Rp " . formatNumber($bpkb['tak'] ?? 0) . "<br>
                                                        Pengikatan: " . ($bpkb['pengikatan'] ?? '-') . "
                                                    </td>
                                                    <td>" . ($bpkb['status'] ?? '-') . "</td>
                                                    <td class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " toggle-status' 
                                                            data-jenis='bpkb' 
                                                            data-id='{$bpkb['norut']}' 
                                                            data-action='" . ($isLocked ? "lepas" : "pasang") . "'>
                                                            <i class='bi bi-" . ($isLocked ? "unlock" : "lock") . "'></i>
                                                        </button>
                                                        <a href='edit-jaminan.php?jenis=bpkb&id={$bpkb['norut']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        }

                                        // SHM
                                        if ($query_shm && mysqli_num_rows($query_shm) > 0) {
                                            while ($shm = mysqli_fetch_array($query_shm)) {
                                                $isLocked = $shm['status'] == $noreg;
                                                echo "<tr>
                                                    <td>$no</td>
                                                    <td>{$shm['jenis_jaminan']}</td>
                                                    <td>
                                                        No Sertifikat: " . ($shm['bukkep'] ?? '-') . "<br>
                                                        Surat Ukur: " . ($shm['suruk'] ?? '-') . "<br>
                                                        Luas Tanah: " . ($shm['lt'] ?? '0') . " m²<br>
                                                        Atas Nama: " . ($shm['an'] ?? '-') . "<br>
                                                        Alamat: " . ($shm['almt'] ?? '-') . "<br>
                                                        Kecamatan: " . ($shm['kec'] ?? '-') . "<br>
                                                        Kabupaten: " . ($shm['kab'] ?? '-') . "<br>
                                                        Blok: " . ($shm['blok'] ?? '-') . "<br>
                                                        Tanggal Terbit: " . ($shm['tglter'] ?? '-') . "<br>
                                                        NJOP: Rp " . formatNumber($shm['njop'] ?? 0) . "<br>
                                                        Nilai Taksasi: Rp " . formatNumber($shm['tak'] ?? 0) . "<br>
                                                        Pengikatan: " . ($shm['pengikatan'] ?? '-') . "
                                                    </td>
                                                    <td>" . ($shm['status'] ?? '-') . "</td>
                                                    <td class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " toggle-status' 
                                                            data-jenis='shm' 
                                                            data-id='{$shm['norut']}' 
                                                            data-action='" . ($isLocked ? "lepas" : "pasang") . "'>
                                                            <i class='bi bi-" . ($isLocked ? "unlock" : "lock") . "'></i>
                                                        </button>
                                                        <a href='edit-jaminan.php?jenis=shm&id={$shm['norut']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        }

                                        // AJB
                                        if ($query_ajb && mysqli_num_rows($query_ajb) > 0) {
                                            while ($ajb = mysqli_fetch_array($query_ajb)) {
                                                $isLocked = $ajb['status'] == $noreg;
                                                echo "<tr>
                                                    <td>$no</td>
                                                    <td>{$ajb['jenis_jaminan']}</td>
                                                    <td>
                                                        No Bukti: " . ($ajb['bukkep'] ?? '-') . "<br>
                                                        Persil: " . ($ajb['persil'] ?? '-') . "<br>
                                                        Kohir: " . ($ajb['kohir'] ?? '-') . "<br>
                                                        Luas Tanah: " . ($ajb['lt'] ?? '0') . " m²<br>
                                                        Atas Nama: " . ($ajb['an'] ?? '-') . "<br>
                                                        Alamat: " . ($ajb['almt'] ?? '-') . "<br>
                                                        Kecamatan: " . ($ajb['kec'] ?? '-') . "<br>
                                                        Kabupaten: " . ($ajb['kab'] ?? '-') . "<br>
                                                        Blok: " . ($ajb['blok'] ?? '-') . "<br>
                                                        Tanggal Terbit: " . ($ajb['tglter'] ?? '-') . "<br>
                                                        NJOP: Rp " . formatNumber($ajb['njop'] ?? 0) . "<br>
                                                        Nilai Taksasi: Rp " . formatNumber($ajb['tak'] ?? 0) . "<br>
                                                        Pengikatan: " . ($ajb['pengikatan'] ?? '-') . "
                                                    </td>
                                                    <td>" . ($ajb['status'] ?? '-') . "</td>
                                                    <td class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " toggle-status' 
                                                            data-jenis='ajb' 
                                                            data-id='{$ajb['norut']}' 
                                                            data-action='" . ($isLocked ? "lepas" : "pasang") . "'>
                                                            <i class='bi bi-" . ($isLocked ? "unlock" : "lock") . "'></i>
                                                        </button>
                                                        <a href='edit-jaminan.php?jenis=ajb&id={$ajb['norut']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        }

                                        // Kios
                                        if ($query_kios && mysqli_num_rows($query_kios) > 0) {
                                            while ($kios = mysqli_fetch_array($query_kios)) {
                                                $isLocked = $kios['status'] == $noreg;
                                                echo "<tr>
                                                    <td>$no</td>
                                                    <td>{$kios['jenis_jaminan']}</td>
                                                    <td>
                                                        No Bukti: " . ($kios['bukkep'] ?? '-') . "<br>
                                                        Ukuran: " . ($kios['ukuran'] ?? '-') . "<br>
                                                        Atas Nama: " . ($kios['an'] ?? '-') . "<br>
                                                        Blok: " . ($kios['blok'] ?? '-') . "<br>
                                                        Alamat: " . ($kios['almt'] ?? '-') . "<br>
                                                        Tanggal Terbit: " . ($kios['tgltrbt'] ?? '-') . "<br>
                                                        Jenis Usaha: " . ($kios['jenus'] ?? '-') . "<br>
                                                        Nilai Pasar Wajar: Rp " . formatNumber($kios['psrwjr'] ?? 0) . "<br>
                                                        Nilai Taksasi: Rp " . formatNumber($kios['tak'] ?? 0) . "<br>
                                                        Pengikatan: " . ($kios['pengikatan'] ?? '-') . "
                                                    </td>
                                                    <td>" . ($kios['status'] ?? '-') . "</td>
                                                    <td class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " toggle-status' 
                                                            data-jenis='kios' 
                                                            data-id='{$kios['norut']}' 
                                                            data-action='" . ($isLocked ? "lepas" : "pasang") . "'>
                                                            <i class='bi bi-" . ($isLocked ? "unlock" : "lock") . "'></i>
                                                        </button>
                                                        <a href='edit-jaminan.php?jenis=kios&id={$kios['norut']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        }

                                        // Bilyet
                                        if ($query_bilyet && mysqli_num_rows($query_bilyet) > 0) {
                                            while ($bilyet = mysqli_fetch_array($query_bilyet)) {
                                                $isLocked = $bilyet['status'] == $noreg;
                                                echo "<tr>
                                                    <td>$no</td>
                                                    <td>{$bilyet['jenis_jaminan']}</td>
                                                    <td>
                                                        No Rekening: " . ($bilyet['norek'] ?? '-') . "<br>
                                                        No Bilyet: " . ($bilyet['nobuk'] ?? '-') . "<br>
                                                        Nominal: Rp " . formatNumber($bilyet['nom'] ?? 0) . "<br>
                                                        Tanggal Buka: " . ($bilyet['tglbuka'] ?? '-') . "<br>
                                                        Tanggal Jatuh Tempo: " . ($bilyet['tgljthtempo'] ?? '-') . "<br>
                                                        Atas Nama: " . ($bilyet['an'] ?? '-') . "<br>
                                                        Jenis Tabungan/Deposito: " . ($bilyet['jentabdep'] ?? '-') . "<br>
                                                        Pengikatan: " . ($bilyet['pengikatan'] ?? '-') . "
                                                    </td>
                                                    <td>" . ($bilyet['status'] ?? '-') . "</td>
                                                    <td class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " toggle-status' 
                                                            data-jenis='bilyet' 
                                                            data-id='{$bilyet['norut']}' 
                                                            data-action='" . ($isLocked ? "lepas" : "pasang") . "'>
                                                            <i class='bi bi-" . ($isLocked ? "unlock" : "lock") . "'></i>
                                                        </button>
                                                        <a href='edit-jaminan.php?jenis=bilyet&id={$bilyet['norut']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        }

                                        // Manulife
                                        if ($query_manulife && mysqli_num_rows($query_manulife) > 0) {
                                            while ($manulife = mysqli_fetch_array($query_manulife)) {
                                                $isLocked = $manulife['status'] == $noreg;
                                                echo "<tr>
                                                    <td>$no</td>
                                                    <td>{$manulife['jenis_jaminan']}</td>
                                                    <td>
                                                        No Jaminan: " . ($manulife['nojam'] ?? '-') . "<br>
                                                        Jenis Dokumen: " . ($manulife['jendok'] ?? '-') . "<br>
                                                        Nama Dokumen: " . ($manulife['nadok'] ?? '-') . "<br>
                                                        Tanggal Dokumen: " . ($manulife['tgldok'] ?? '-') . "<br>
                                                        Pengikatan: " . ($manulife['pengikatan'] ?? '-') . "
                                                    </td>
                                                    <td>" . ($manulife['status'] ?? '-') . "</td>
                                                    <td class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " toggle-status' 
                                                            data-jenis='manulife' 
                                                            data-id='{$manulife['norut']}' 
                                                            data-action='" . ($isLocked ? "lepas" : "pasang") . "'>
                                                            <i class='bi bi-" . ($isLocked ? "unlock" : "lock") . "'></i>
                                                        </button>
                                                        <a href='edit-jaminan.php?jenis=manulife&id={$manulife['norut']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        }

                                        // BPIH
                                        if ($query_bpih && mysqli_num_rows($query_bpih) > 0) {
                                            while ($bpih = mysqli_fetch_array($query_bpih)) {
                                                $isLocked = $bpih['status'] == $noreg;
                                                echo "<tr>
                                                    <td>$no</td>
                                                    <td>{$bpih['jenis_jaminan']}</td>
                                                    <td>
                                                        No Validasi: " . ($bpih['noval'] ?? '-') . "<br>
                                                        No Rekening: " . ($bpih['norek'] ?? '-') . "<br>
                                                        Tanggal Surat: " . ($bpih['tgl_surat'] ?? '-') . "<br>
                                                        Atas Nama: " . ($bpih['an'] ?? '-') . "
                                                    </td>
                                                    <td>" . ($bpih['status'] ?? '-') . "</td>
                                                    <td class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " toggle-status' 
                                                            data-jenis='bpih' 
                                                            data-id='{$bpih['id']}' 
                                                            data-action='" . ($isLocked ? "lepas" : "pasang") . "'>
                                                            <i class='bi bi-" . ($isLocked ? "unlock" : "lock") . "'></i>
                                                        </button>
                                                        <a href='edit-jaminan.php?jenis=bpih&id={$bpih['id']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        }

                                        // SPPH
                                        if ($query_spph && mysqli_num_rows($query_spph) > 0) {
                                            while ($spph = mysqli_fetch_array($query_spph)) {
                                                $isLocked = $spph['status'] == $noreg;
                                                echo "<tr>
                                                    <td>$no</td>
                                                    <td>{$spph['jenis_jaminan']}</td>
                                                    <td>
                                                        No Porsi: " . ($spph['nopor'] ?? '-') . "<br>
                                                        No Validasi: " . ($spph['noval'] ?? '-') . "<br>
                                                        Tanggal Surat: " . ($spph['tgl_surat'] ?? '-') . "<br>
                                                        Tanggal STT: " . ($spph['tgl_stt'] ?? '-') . "<br>
                                                        Kemenag: " . ($spph['kemenag'] ?? '-') . "<br>
                                                        Atas Nama: " . ($spph['an'] ?? '-') . "
                                                    </td>
                                                    <td>" . ($spph['status'] ?? '-') . "</td>
                                                    <td class='d-flex justify-content-center gap-1'>
                                                        <button class='btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " toggle-status' 
                                                            data-jenis='spph' 
                                                            data-id='{$spph['id']}' 
                                                            data-action='" . ($isLocked ? "lepas" : "pasang") . "'>
                                                            <i class='bi bi-" . ($isLocked ? "unlock" : "lock") . "'></i>
                                                        </button>
                                                        <a href='edit-jaminan.php?jenis=spph&id={$spph['id']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                    </td>
                                                </tr>";
                                                $no++;
                                            }
                                        }

                                        // Jika tidak ada data jaminan
                                        if ($no == 1) {
                                            echo "<tr><td colspan='5' class='text-center text-muted'>Tidak ada data jaminan untuk nasabah ini</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        document.querySelectorAll('.toggle-status').forEach(button => {
            button.addEventListener('click', function() {
                const jenis = this.dataset.jenis;
                const id = this.dataset.id;
                const action = this.dataset.action;
                const noreg = '<?php echo $noreg; ?>';

                Swal.fire({
                    title: `Konfirmasi ${action} jaminan`,
                    text: `Apakah Anda yakin ingin ${action} jaminan ini?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Kirim request AJAX ke file yang sama
                        fetch('data-jaminan.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `jenis=${jenis}&id=${id}&action=${action}&noreg=${noreg}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire(
                                        'Berhasil!',
                                        data.message,
                                        'success'
                                    ).then(() => {
                                        // Reload halaman setelah berhasil
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Gagal!',
                                        data.message,
                                        'error'
                                    );
                                }
                            })
                            .catch(error => {
                                Swal.fire(
                                    'Error!',
                                    'Terjadi kesalahan saat memproses permintaan.',
                                    'error'
                                );
                            });
                    }
                });
            });
        });
    </script>

</body>

</html>