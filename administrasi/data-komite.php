<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Get noreg from URL
$noreg = $_GET['noreg'];

// Query to get komite data
$query = mysqli_query($connect, "SELECT k.*, p.niknas, n.nama as nama_nasabah, p.status as status_pengajuan, d.status as status_droping
    FROM komite k
    LEFT JOIN pengajuan p ON p.noreg = k.noreg
    LEFT JOIN nasabah n ON n.nik = p.niknas
    LEFT JOIN droping d ON d.noreg = k.noreg
    WHERE k.noreg='$noreg'");

if (!$query) {
    die("Error: " . mysqli_error($connect));
}

$data = mysqli_fetch_array($query);

// Get NIK from data
$nik = $data['niknas'] ?? '';

// Query untuk mengambil data dari semua tabel jaminan berdasarkan NIK
$jaminan_data = [];

if (!empty($nik)) {
    // BPKB
    $query_bpkb = mysqli_query($connect, "SELECT *, 'BPKB' as jenis_jaminan FROM bpkb WHERE nik='$nik'");
    if ($query_bpkb) {
        while ($row = mysqli_fetch_array($query_bpkb)) {
            // Cari nilai taksasi dari tabel res_jam
            $atas_nama = $row['an'] ?? '';
            $taksasi = '-';
            if (!empty($atas_nama)) {
                $query_taksasi = mysqli_query($connect, "SELECT taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
                if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
                    $taksasi_row = mysqli_fetch_array($query_taksasi);
                    $taksasi = $taksasi_row['taksasi'] ?? '-';
                }
            }

            $jaminan_data[] = [
                'jenis' => 'BPKB',
                'atas_nama' => $atas_nama,
                'status' => $row['status'] ?? '-',
                'norut' => $row['norut'] ?? '',
                'table' => 'bpkb',
                'taksasi' => $taksasi
            ];
        }
    }

    // SHM
    $query_shm = mysqli_query($connect, "SELECT *, 'Sertifikat' as jenis_jaminan FROM shm WHERE nik='$nik'");
    if ($query_shm) {
        while ($row = mysqli_fetch_array($query_shm)) {
            // Cari nilai taksasi dari tabel res_jam
            $atas_nama = $row['an'] ?? '';
            $taksasi = '-';
            if (!empty($atas_nama)) {
                $query_taksasi = mysqli_query($connect, "SELECT taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
                if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
                    $taksasi_row = mysqli_fetch_array($query_taksasi);
                    $taksasi = $taksasi_row['taksasi'] ?? '-';
                }
            }

            $jaminan_data[] = [
                'jenis' => 'Sertifikat',
                'atas_nama' => $atas_nama,
                'status' => $row['status'] ?? '-',
                'norut' => $row['norut'] ?? '',
                'table' => 'shm',
                'taksasi' => $taksasi
            ];
        }
    }

    // AJB
    $query_ajb = mysqli_query($connect, "SELECT *, 'AKTA' as jenis_jaminan FROM ajb WHERE nik='$nik'");
    if ($query_ajb) {
        while ($row = mysqli_fetch_array($query_ajb)) {
            // Cari nilai taksasi dari tabel res_jam
            $atas_nama = $row['an'] ?? '';
            $taksasi = '-';
            if (!empty($atas_nama)) {
                $query_taksasi = mysqli_query($connect, "SELECT taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
                if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
                    $taksasi_row = mysqli_fetch_array($query_taksasi);
                    $taksasi = $taksasi_row['taksasi'] ?? '-';
                }
            }

            $jaminan_data[] = [
                'jenis' => 'AKTA',
                'atas_nama' => $atas_nama,
                'status' => $row['status'] ?? '-',
                'norut' => $row['norut'] ?? '',
                'table' => 'ajb',
                'taksasi' => $taksasi
            ];
        }
    }

    // Kios
    $query_kios = mysqli_query($connect, "SELECT *, 'Kios' as jenis_jaminan FROM kios WHERE nik='$nik'");
    if ($query_kios) {
        while ($row = mysqli_fetch_array($query_kios)) {
            // Cari nilai taksasi dari tabel res_jam
            $atas_nama = $row['an'] ?? '';
            $taksasi = '-';
            if (!empty($atas_nama)) {
                $query_taksasi = mysqli_query($connect, "SELECT taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
                if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
                    $taksasi_row = mysqli_fetch_array($query_taksasi);
                    $taksasi = $taksasi_row['taksasi'] ?? '-';
                }
            }

            $jaminan_data[] = [
                'jenis' => 'Kios',
                'atas_nama' => $atas_nama,
                'status' => $row['status'] ?? '-',
                'norut' => $row['norut'] ?? '',
                'table' => 'kios',
                'taksasi' => $taksasi
            ];
        }
    }

    // Bilyet
    $query_bilyet = mysqli_query($connect, "SELECT *, 'Bilyet' as jenis_jaminan FROM bilyet WHERE nik='$nik'");
    if ($query_bilyet) {
        while ($row = mysqli_fetch_array($query_bilyet)) {
            // Cari nilai taksasi dari tabel res_jam
            $atas_nama = $row['an'] ?? '';
            $taksasi = '-';
            if (!empty($atas_nama)) {
                $query_taksasi = mysqli_query($connect, "SELECT taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
                if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
                    $taksasi_row = mysqli_fetch_array($query_taksasi);
                    $taksasi = $taksasi_row['taksasi'] ?? '-';
                }
            }

            $jaminan_data[] = [
                'jenis' => 'Bilyet',
                'atas_nama' => $atas_nama,
                'status' => $row['status'] ?? '-',
                'norut' => $row['norut'] ?? '',
                'table' => 'bilyet',
                'taksasi' => $taksasi
            ];
        }
    }

    // Manulife
    $query_manulife = mysqli_query($connect, "SELECT *, 'Manulife' as jenis_jaminan FROM manulife WHERE nik='$nik'");
    if ($query_manulife) {
        while ($row = mysqli_fetch_array($query_manulife)) {
            // Cari nilai taksasi dari tabel res_jam
            $atas_nama = $row['an'] ?? '';
            $taksasi = '-';
            if (!empty($atas_nama)) {
                $query_taksasi = mysqli_query($connect, "SELECT taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
                if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
                    $taksasi_row = mysqli_fetch_array($query_taksasi);
                    $taksasi = $taksasi_row['taksasi'] ?? '-';
                }
            }

            $jaminan_data[] = [
                'jenis' => 'Manulife',
                'atas_nama' => $atas_nama,
                'status' => $row['status'] ?? '-',
                'norut' => $row['norut'] ?? '',
                'table' => 'manulife',
                'taksasi' => $taksasi
            ];
        }
    }

    // BPIH
    $query_bpih = mysqli_query($connect, "SELECT *, 'Haji BPIH' as jenis_jaminan FROM bpih WHERE nik='$nik'");
    if ($query_bpih) {
        while ($row = mysqli_fetch_array($query_bpih)) {
            // Cari nilai taksasi dari tabel res_jam
            $atas_nama = $row['an'] ?? '';
            $taksasi = '-';
            if (!empty($atas_nama)) {
                $query_taksasi = mysqli_query($connect, "SELECT taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
                if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
                    $taksasi_row = mysqli_fetch_array($query_taksasi);
                    $taksasi = $taksasi_row['taksasi'] ?? '-';
                }
            }

            $jaminan_data[] = [
                'jenis' => 'Haji BPIH',
                'atas_nama' => $atas_nama,
                'status' => $row['status'] ?? '-',
                'norut' => $row['id'] ?? '',
                'table' => 'bpih',
                'taksasi' => $taksasi
            ];
        }
    }

    // SPPH
    $query_spph = mysqli_query($connect, "SELECT *, 'Haji SPPH' as jenis_jaminan FROM spph WHERE nik='$nik'");
    if ($query_spph) {
        while ($row = mysqli_fetch_array($query_spph)) {
            // Cari nilai taksasi dari tabel res_jam
            $atas_nama = $row['an'] ?? '';
            $taksasi = '-';
            if (!empty($atas_nama)) {
                $query_taksasi = mysqli_query($connect, "SELECT taksasi FROM res_jam WHERE jaminan LIKE '%" . mysqli_real_escape_string($connect, $atas_nama) . "%' LIMIT 1");
                if ($query_taksasi && mysqli_num_rows($query_taksasi) > 0) {
                    $taksasi_row = mysqli_fetch_array($query_taksasi);
                    $taksasi = $taksasi_row['taksasi'] ?? '-';
                }
            }

            $jaminan_data[] = [
                'jenis' => 'Haji SPPH',
                'atas_nama' => $atas_nama,
                'status' => $row['status'] ?? '-',
                'norut' => $row['id'] ?? '',
                'table' => 'spph',
                'taksasi' => $taksasi
            ];
        }
    }
}

function formatLabel($key)
{
    // Format label mapping
    $labels = [
        'tgl_peng_ao' => 'Tgl Pengajuan AO',
        'nama_ao' => 'Nama AO',
        'tgl_peng_analis' => 'Tgl Pengajuan Analis',
        'nama_analis' => 'Nama Analis',
        'tgl_peng_mkt' => 'Tgl Pengajuan Marketing',
        'nama_mkt' => 'Nama Marketing',
        // Add more mappings as needed
    ];

    return isset($labels[$key]) ? $labels[$key] : ucwords(str_replace('_', ' ', $key));
}

function formatValue($value)
{
    // Remove JSON characters
    $value = strip_tags(str_replace(['{', '}', '"'], '', $value));

    // Format dates if the value matches date pattern
    if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $value)) {
        return date('d-m-Y', strtotime($value));
    }

    return $value;
}

// Fungsi untuk memformat nilai taksasi
function formatTaksasi($taksasi)
{
    if (empty($taksasi) || $taksasi === '-' || $taksasi === '0') {
        return '<span class="text-danger">-</span>';
    }
    // Coba konversi ke angka
    $numeric_value = preg_replace('/[^0-9]/', '', $taksasi);
    if (is_numeric($numeric_value) && $numeric_value > 0) {
        return 'Rp ' . number_format((float)$numeric_value, 0, ',', '.');
    }
    return $taksasi;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Komite - <?php echo $noreg; ?></title>
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
            <div class="row">
                <div class="col-12">

                    <?php include '../includes/menu-droping.php'; ?>

                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="card-title mb-3">Data Komite Kredit</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($data): ?>
                                <div class="row mb-4">
                                    <div class="col-md-8">
                                        <div class="table-responsive">
                                            <table class="table table-sm mb-0">
                                                <tr class="py-1">
                                                    <td class="py-1" style="width: 120px;">No Registrasi</td>
                                                    <td class="py-1" style="width: 10px;">:</td>
                                                    <td class="py-1"><strong><?php echo $data['noreg']; ?></strong></td>
                                                </tr>
                                                <tr class="py-1">
                                                    <td class="py-1">Nama Nasabah</td>
                                                    <td class="py-1">:</td>
                                                    <td class="py-1"><?php echo $data['nama_nasabah']; ?></td>
                                                </tr>
                                                <tr class="py-1">
                                                    <td class="py-1">Status Pengajuan</td>
                                                    <td class="py-1">:</td>
                                                    <td class="py-1"><span class="badge bg-<?php echo !empty($data['status_pengajuan']) ? ($data['status_pengajuan'] == 'APPROVED' ? 'success' : 'warning') : 'secondary'; ?>"><?php echo !empty($data['status_pengajuan']) ? $data['status_pengajuan'] : 'NULL'; ?></span></td>
                                                </tr>
                                                <tr class="py-1">
                                                    <td class="py-1">Status Komite</td>
                                                    <td class="py-1">:</td>
                                                    <td class="py-1"><span class="badge bg-<?php echo !empty($data['status']) ? ($data['status'] == 'APPROVED' ? 'success' : 'warning') : 'secondary'; ?>"><?php echo !empty($data['status']) ? $data['status'] : 'NULL'; ?></span></td>
                                                </tr>
                                                <tr class="py-1">
                                                    <td class="py-1">Status Droping</td>
                                                    <td class="py-1">:</td>
                                                    <td class="py-1"><span class="badge bg-<?php echo !empty($data['status_droping']) ? ($data['status_droping'] == 'APPROVED' ? 'success' : 'warning') : 'secondary'; ?>"><?php echo !empty($data['status_droping']) ? $data['status_droping'] : 'NULL'; ?></span></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="btn-group-vertical gap-2 w-100">
                                            <?php if (!empty($data['analisa'])): ?>
                                                <a href="download-file-komite.php?type=analisa&noreg=<?php echo $noreg; ?>" class="btn btn-success">
                                                    <i class="bi bi-download me-2"></i>Download Analisa
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!empty($data['slik'])): ?>
                                                <a href="download-file-komite.php?type=slik&noreg=<?php echo $noreg; ?>" class="btn btn-success">
                                                    <i class="bi bi-download me-2"></i>Download SLIK
                                                </a>
                                            <?php endif; ?>

                                            <?php if (!empty($data['lainnya'])): ?>
                                                <a href="download-file-komite.php?type=lainnya&noreg=<?php echo $noreg; ?>" class="btn btn-success">
                                                    <i class="bi bi-download me-2"></i>Download Doc Lainnya
                                                </a>
                                            <?php endif; ?>

                                            <?php if (isset($_SESSION['key_app']) && $_SESSION['key_app'] === 'ADMIN'): ?>
                                                <a href="../komite/edit-data-komite.php?noreg=<?php echo urlencode($data['noreg']); ?>" class="btn btn-danger">
                                                    <i class="bi bi-gear me-2"></i>Ubah Data
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Data Jaminan Section -->
                                    <?php if ($data): ?>
                                        <div class="col-md-12">
                                            <div class="card border mb-3">
                                                <div class="card-header bg-light">
                                                    <h5 class="mb-0 py-1">Data Jaminan & Nilai Taksasi (Restruk)</h5>
                                                </div>
                                                <div class="card-body">
                                                    <?php if (!empty($jaminan_data)): ?>
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-bordered table-hover align-middle" style="font-size:0.92em;">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th style="width:36px;">No</th>
                                                                        <th style="min-width:90px;">Jenis Jaminan</th>
                                                                        <th style="min-width:120px;">Atas Nama</th>
                                                                        <th style="min-width:90px;">Nilai Taksasi</th>
                                                                        <th style="width:190px;">Aksi</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <?php
                                                                    $no = 1;
                                                                    foreach ($jaminan_data as $jaminan):
                                                                    ?>
                                                                        <tr>
                                                                            <td><?php echo $no; ?></td>
                                                                            <td style="white-space:normal;word-break:break-word;"><?php echo htmlspecialchars($jaminan['jenis']); ?></td>
                                                                            <td style="white-space:normal;word-break:break-word;"><?php echo htmlspecialchars($jaminan['atas_nama']); ?></td>
                                                                            <td style="white-space:normal;word-break:break-word;">
                                                                                <?php echo formatTaksasi($jaminan['taksasi']); ?>
                                                                            </td>
                                                                            <td>
                                                                                <div class="btn-group btn-group-xs d-flex flex-nowrap gap-1" role="group">
                                                                                    <a href="edit-jaminan.php?jenis=<?php echo $jaminan['table']; ?>&id=<?php echo $jaminan['norut']; ?>"
                                                                                        class="btn btn-warning btn-sm px-1 py-0" title="Edit Jaminan">
                                                                                        <i class="bi bi-pencil"></i>
                                                                                    </a>
                                                                                    <?php
                                                                                    $taksasi_raw = $jaminan['taksasi'];
                                                                                    $is_restruk = !empty($taksasi_raw) && $taksasi_raw !== '-' && $taksasi_raw !== '0';
                                                                                    ?>
                                                                                    <a href="edit-taksasi.php?jenis=<?php echo $jaminan['table']; ?>&id=<?php echo $jaminan['norut']; ?>&atas_nama=<?php echo urlencode($jaminan['atas_nama']); ?>"
                                                                                        class="btn btn-info btn-sm px-1 py-0<?php echo $is_restruk ? '' : ' disabled'; ?>"
                                                                                        title="Edit Nilai Taksasi" <?php echo $is_restruk ? '' : 'tabindex="-1" aria-disabled="true"'; ?>>
                                                                                        Ubah Nilai Taksasi
                                                                                    </a>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    <?php
                                                                        $no++;
                                                                    endforeach;
                                                                    ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    <?php else: ?>
                                                        <div class="text-center text-muted py-4">
                                                            <i class="bi bi-inbox display-4"></i>
                                                            <p class="mt-2">Tidak ada data jaminan untuk nasabah ini</p>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Komite Section -->
                                    <div class="col-md-12">
                                        <div class="card border mb-3">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0 py-1">Informasi Komite</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-sm">
                                                        <thead>
                                                            <tr>
                                                                <th width="200">INFO</th>
                                                                <th>KET</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            foreach (['pengaju', 'ket', 'sandi'] as $field) {
                                                                if (!empty($data[$field])) {
                                                                    $json_data = json_decode($data[$field], true);
                                                                    if (is_array($json_data)) {
                                                                        foreach ($json_data as $key => $value) {
                                                                            printf(
                                                                                "<tr><th>%s</th><td>%s</td></tr>",
                                                                                formatLabel($key),
                                                                                formatValue($value)
                                                                            );
                                                                        }
                                                                    } else {
                                                                        printf(
                                                                            "<tr><th>%s</th><td>%s</td></tr>",
                                                                            formatLabel($field),
                                                                            formatValue($data[$field])
                                                                        );
                                                                    }
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Keputusan Komite -->
                                    <div class="col-md-12">
                                        <div class="card border">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">Keputusan Komite</h5>
                                                <button class="btn btn-sm btn-primary" onclick="toggleKeputusan()">
                                                    <i class="bi bi-chevron-down" id="toggleIcon"></i> Show All
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead>
                                                            <tr>
                                                                <th>Komite</th>
                                                                <th>Nama</th>
                                                                <th>Keterangan</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="keputusanBody" class="d-none">
                                                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                                                <?php if (!empty($data['komite' . $i])): ?>
                                                                    <?php
                                                                    $komite_data = json_decode($data['komite' . $i], true);
                                                                    $ket_data = json_decode($data['ket' . $i], true);
                                                                    ?>
                                                                    <tr>
                                                                        <td>Komite <?php echo $i; ?></td>
                                                                        <td>
                                                                            <?php
                                                                            if (is_array($komite_data)) {
                                                                                foreach ($komite_data as $key => $value) {
                                                                                    echo formatLabel($key) . ": " . formatValue($value) . "<br>";
                                                                                }
                                                                            } else {
                                                                                echo formatValue($data['komite' . $i]);
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                        <td>
                                                                            <?php
                                                                            if (is_array($ket_data)) {
                                                                                foreach ($ket_data as $key => $value) {
                                                                                    echo formatLabel($key) . ": " . formatValue($value) . "<br>";
                                                                                }
                                                                            } else {
                                                                                echo formatValue($data['ket' . $i]);
                                                                            }
                                                                            ?>
                                                                        </td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            <?php endfor; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional Information -->
                                    <div class="col-md-12 mt-3">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h5 class="mb-0">Informasi Tambahan</h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <h6>Kepatuhan</h6>
                                                        <p><?php echo nl2br(strip_tags(str_replace(['{', '}', '"'], '', $data['kepatuhan']))); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Opini</h6>
                                                        <p><?php echo nl2br(strip_tags(str_replace(['{', '}', '"'], '', $data['opini']))); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>Analisa</h6>
                                                        <p><?php echo nl2br(strip_tags(str_replace(['{', '}', '"'], '', $data['analisa']))); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <h6>SLIK</h6>
                                                        <p><?php echo nl2br(strip_tags(str_replace(['{', '}', '"'], '', $data['slik']))); ?></p>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <h6>Informasi Lainnya</h6>
                                                        <p><?php echo nl2br(strip_tags(str_replace(['{', '}', '"'], '', $data['lainnya']))); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle me-2"></i>
                                    Data komite tidak ditemukan untuk No Registrasi: <?php echo $noreg; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function toggleKeputusan() {
            const tbody = document.getElementById('keputusanBody');
            const icon = document.getElementById('toggleIcon');
            const button = icon.parentElement;

            if (tbody.classList.contains('d-none')) {
                tbody.classList.remove('d-none');
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
                button.innerHTML = '<i class="bi bi-chevron-up" id="toggleIcon"></i> Hide All';
            } else {
                tbody.classList.add('d-none');
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
                button.innerHTML = '<i class="bi bi-chevron-down" id="toggleIcon"></i> Show All';
            }
        }
    </script>
</body>

</html>