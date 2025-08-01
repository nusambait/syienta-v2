<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

$message = '';
$message_type = '';
$search_results = array();

// Fungsi untuk mendapatkan ukuran file dalam format yang mudah dibaca
function formatFileSize($bytes)
{
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' bytes';
    }
}

// Fungsi untuk mendapatkan daftar file dari direktori
function getFilesFromDirectory($directory)
{
    $files = array();
    if (is_dir($directory)) {
        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item != "." && $item != ".." && is_file($directory . '/' . $item)) {
                $file_path = $directory . '/' . $item;
                $files[] = array(
                    'name' => $item,
                    'size' => formatFileSize(filesize($file_path)),
                    'path' => $file_path
                );
            }
        }
    }
    return $files;
}

// Fungsi untuk memformat nama file yang panjang
function formatFileName($filename, $maxLength = 30)
{
    if (strlen($filename) <= $maxLength) {
        return $filename;
    }

    $extension = pathinfo($filename, PATHINFO_EXTENSION);
    $nameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);

    $halfLength = floor(($maxLength - 3) / 2);
    $firstPart = substr($nameWithoutExt, 0, $halfLength);
    $lastPart = substr($nameWithoutExt, -$halfLength);

    return $firstPart . '...' . $lastPart . '.' . $extension;
}

// Fungsi untuk mendapatkan bulan dan tahun dari noreg
function getMonthYearFromNoreg($noreg)
{
    if (preg_match('/\.(\d{2})-(\d{2})$/', $noreg, $matches)) {
        $month = $matches[1];
        $year = '20' . $matches[2]; // Konversi 2 digit tahun ke 4 digit
        return array(
            'month' => (int)$month,
            'year' => (int)$year
        );
    }
    return array('month' => 0, 'year' => 0);
}

// Fungsi untuk mendapatkan daftar bulan
function getMonths()
{
    return array(
        '01' => 'Januari',
        '02' => 'Februari',
        '03' => 'Maret',
        '04' => 'April',
        '05' => 'Mei',
        '06' => 'Juni',
        '07' => 'Juli',
        '08' => 'Agustus',
        '09' => 'September',
        '10' => 'Oktober',
        '11' => 'November',
        '12' => 'Desember'
    );
}

// Proses pencarian
$items_per_page = 30;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Filter bulan dan tahun
$selected_month = isset($_GET['month']) ? $_GET['month'] : '';
$selected_year = isset($_GET['year']) ? $_GET['year'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date';

if (isset($_POST['search']) || isset($_GET['noreg'])) {
    $noreg = isset($_POST['search']) ? $_POST['noreg'] : $_GET['noreg'];
    $noreg = mysqli_real_escape_string($connect, $noreg);

    $query = mysqli_query($connect, "SELECT * FROM komite WHERE noreg LIKE '%$noreg%'");
    $total_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM komite WHERE noreg LIKE '%$noreg%'");
} else {
    // Query untuk mendapatkan semua data
    $query = mysqli_query($connect, "SELECT * FROM komite");
    $total_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM komite");
}

$search_results = array();
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        // Tambahkan informasi ukuran file
        $row['file_sizes'] = array(
            'analisa' => $row['analisa'] && file_exists('../../../nusamba2/assets/doc/analisa/' . $row['analisa']) ? filesize('../../../nusamba2/assets/doc/analisa/' . $row['analisa']) : 0,
            'slik' => $row['slik'] && file_exists('../../../nusamba2/assets/doc/slik/' . $row['slik']) ? filesize('../../../nusamba2/assets/doc/slik/' . $row['slik']) : 0,
            'lainnya' => $row['lainnya'] && file_exists('../../../nusamba2/assets/doc/lainnya/' . $row['lainnya']) ? filesize('../../../nusamba2/assets/doc/lainnya/' . $row['lainnya']) : 0
        );
        $search_results[] = $row;
    }
}

// Filter berdasarkan bulan dan tahun
if ($selected_month && $selected_year) {
    // Filter berdasarkan bulan dan tahun spesifik
    $search_results = array_filter($search_results, function ($row) use ($selected_month, $selected_year) {
        $date = getMonthYearFromNoreg($row['noreg']);
        return $date['month'] == $selected_month && $date['year'] == $selected_year;
    });
} elseif ($selected_year) {
    // Filter berdasarkan tahun saja
    $search_results = array_filter($search_results, function ($row) use ($selected_year) {
        $date = getMonthYearFromNoreg($row['noreg']);
        return $date['year'] == $selected_year;
    });
} elseif ($selected_month) {
    // Filter berdasarkan bulan saja
    $search_results = array_filter($search_results, function ($row) use ($selected_month) {
        $date = getMonthYearFromNoreg($row['noreg']);
        return $date['month'] == $selected_month;
    });
}

// Urutkan hasil berdasarkan kriteria yang dipilih
if ($sort_by === 'size') {
    usort($search_results, function ($a, $b) {
        // Hitung total ukuran file untuk setiap baris
        $sizeA = array_sum($a['file_sizes']);
        $sizeB = array_sum($b['file_sizes']);
        return $sizeB - $sizeA; // Urutkan dari terbesar ke terkecil
    });
} else {
    // Urutkan berdasarkan tanggal (default)
    usort($search_results, function ($a, $b) {
        $dateA = getMonthYearFromNoreg($a['noreg']);
        $dateB = getMonthYearFromNoreg($b['noreg']);

        if ($dateA['year'] !== $dateB['year']) {
            return $dateB['year'] - $dateA['year'];
        }
        return $dateB['month'] - $dateA['month'];
    });
}

// Hitung total data setelah filter
$total_rows = count($search_results);
$total_pages = ceil($total_rows / $items_per_page);

// Pastikan halaman yang diminta valid
if ($page > $total_pages) {
    $page = 1;
    $offset = 0;
}

// Ambil hanya data untuk halaman yang diminta
$search_results = array_slice($search_results, $offset, $items_per_page);

// Proses upload file
if (isset($_POST['upload'])) {
    try {
        $noreg = mysqli_real_escape_string($connect, $_POST['noreg']);
        $file_type = mysqli_real_escape_string($connect, $_POST['file_type']);
        $file = $_FILES['file'];

        // Validasi file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_message = "Error uploading file: ";
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $error_message .= "Ukuran file melebihi batas yang diizinkan. Silakan periksa konfigurasi PHP (upload_max_filesize dan post_max_size).";
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $error_message .= "File hanya terupload sebagian.";
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $error_message .= "Tidak ada file yang diupload.";
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $error_message .= "Folder temporary tidak ditemukan.";
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $error_message .= "Gagal menulis file ke disk.";
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $error_message .= "Upload dihentikan oleh ekstensi PHP.";
                    break;
                default:
                    $error_message .= "Unknown error code: " . $file['error'];
            }
            throw new Exception($error_message);
        }

        // Cek ukuran file
        $max_file_size = 500 * 1024 * 1024; // 500MB dalam bytes
        if ($file['size'] > $max_file_size) {
            throw new Exception("Ukuran file terlalu besar! Maksimal 500MB");
        }

        if ($file['type'] !== 'application/pdf') {
            throw new Exception("Hanya file PDF yang diperbolehkan!");
        }

        // Generate nama file
        $timestamp = date('Ymd-His');
        $unique_code = substr(md5(uniqid()), 0, 8);
        $new_filename = $noreg . '-' . $timestamp . '-' . $unique_code . '.pdf';

        // Tentukan direktori tujuan
        $upload_dir = '../../../nusamba2/assets/doc/' . $file_type . '/';
        if (!is_dir($upload_dir)) {
            if (!@mkdir($upload_dir, 0755, true)) {
                throw new Exception("Gagal membuat direktori upload. Silakan periksa permission folder.");
            }
        }

        // Pastikan direktori writable
        if (!is_writable($upload_dir)) {
            throw new Exception("Direktori upload tidak writable. Silakan periksa permission folder.");
        }

        // Hapus file lama jika ada
        $old_file_query = mysqli_query($connect, "SELECT $file_type FROM komite WHERE noreg = '$noreg'");
        if ($old_file_query && $row = mysqli_fetch_assoc($old_file_query)) {
            $old_file = $row[$file_type];
            if ($old_file && file_exists($upload_dir . $old_file)) {
                @unlink($upload_dir . $old_file);
            }
        }

        // Upload file baru dengan chunk
        $chunk_size = 1024 * 1024; // 1MB chunks
        $handle = fopen($file['tmp_name'], 'rb');
        $target = fopen($upload_dir . $new_filename, 'wb');

        if (!$handle || !$target) {
            throw new Exception("Gagal membuka file untuk upload");
        }

        while (!feof($handle)) {
            $buffer = fread($handle, $chunk_size);
            fwrite($target, $buffer);
        }

        fclose($handle);
        fclose($target);

        // Update database
        $update_query = mysqli_query($connect, "UPDATE komite SET $file_type = '$new_filename' WHERE noreg = '$noreg'");
        if ($update_query) {
            $_SESSION['flash_message'] = "File berhasil diupload!";
            $_SESSION['flash_type'] = "success";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            throw new Exception("Gagal mengupdate database: " . mysqli_error($connect));
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = $e->getMessage();
        $_SESSION['flash_type'] = "danger";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Proses hapus file
if (isset($_POST['delete'])) {
    try {
        $noreg = mysqli_real_escape_string($connect, $_POST['noreg']);
        $file_type = mysqli_real_escape_string($connect, $_POST['file_type']);
        $filename = mysqli_real_escape_string($connect, $_POST['filename']);

        $file_path = '../../../nusamba2/assets/doc/' . $file_type . '/' . $filename;

        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $update_query = mysqli_query($connect, "UPDATE komite SET $file_type = NULL WHERE noreg = '$noreg'");
                if ($update_query) {
                    $_SESSION['flash_message'] = "File berhasil dihapus!";
                    $_SESSION['flash_type'] = "success";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    throw new Exception("Gagal mengupdate database: " . mysqli_error($connect));
                }
            } else {
                throw new Exception("Gagal menghapus file!");
            }
        } else {
            throw new Exception("File tidak ditemukan!");
        }
    } catch (Exception $e) {
        $_SESSION['flash_message'] = $e->getMessage();
        $_SESSION['flash_type'] = "danger";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Proses hapus file berdasarkan filter
if (isset($_POST['delete_filtered'])) {
    try {
        $total_deleted = 0;
        $total_size = 0;

        foreach ($search_results as $row) {
            $file_types = array('analisa', 'slik', 'lainnya');
            foreach ($file_types as $type) {
                if ($row[$type]) {
                    $file_path = '../../../nusamba2/assets/doc/' . $type . '/' . $row[$type];
                    if (file_exists($file_path)) {
                        $file_size = filesize($file_path);
                        if (unlink($file_path)) {
                            $total_deleted++;
                            $total_size += $file_size;
                        }
                    }
                }
            }
        }

        if ($total_deleted > 0) {
            $_SESSION['flash_message'] = "Berhasil menghapus " . $total_deleted . " file dengan total ukuran " . formatFileSize($total_size);
            $_SESSION['flash_type'] = "success";
        } else {
            $_SESSION['flash_message'] = "Tidak ada file yang dihapus";
            $_SESSION['flash_type'] = "info";
        }

        header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_message'] = "Gagal menghapus file: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
        header("Location: " . $_SERVER['PHP_SELF'] . "?" . http_build_query($_GET));
        exit;
    }
}

// Proses pengecekan file orphan
$orphan_files = array();
if (isset($_POST['cek_orphan'])) {
    $folders = [
        'analisa' => '../../../nusamba2/assets/doc/analisa/',
        'slik' => '../../../nusamba2/assets/doc/slik/',
        'lainnya' => '../../../nusamba2/assets/doc/lainnya/'
    ];
    // Ambil semua nama file dari database
    $db_files = [
        'analisa' => array(),
        'slik' => array(),
        'lainnya' => array()
    ];
    $komite_query = mysqli_query($connect, "SELECT analisa, slik, lainnya FROM komite");
    if ($komite_query) {
        while ($row = mysqli_fetch_assoc($komite_query)) {
            if (!empty($row['analisa'])) $db_files['analisa'][] = $row['analisa'];
            if (!empty($row['slik'])) $db_files['slik'][] = $row['slik'];
            if (!empty($row['lainnya'])) $db_files['lainnya'][] = $row['lainnya'];
        }
    }
    // Scan folder dan cari file orphan
    foreach ($folders as $type => $dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file != '.' && $file != '..' && is_file($dir . $file)) {
                    if (!in_array($file, $db_files[$type])) {
                        $orphan_files[] = array(
                            'type' => $type,
                            'name' => $file,
                            'path' => $dir . $file,
                            'size' => formatFileSize(filesize($dir . $file))
                        );
                    }
                }
            }
        }
    }
}

// Proses hapus semua file orphan sekaligus
if (isset($_POST['delete_all_orphan'])) {
    $deleted = 0;
    $failed = 0;
    $failed_files = [];
    if (!empty($_POST['all_orphan_paths'])) {
        $paths = $_POST['all_orphan_paths'];
        foreach ($paths as $idx => $orphan_path) {
            $orphan_name = $_POST['all_orphan_names'][$idx] ?? basename($orphan_path);
            if ($orphan_path && file_exists($orphan_path)) {
                if (unlink($orphan_path)) {
                    $deleted++;
                } else {
                    $failed++;
                    $failed_files[] = $orphan_name;
                }
            } else {
                $failed++;
                $failed_files[] = $orphan_name;
            }
        }
    }
    if ($deleted > 0) {
        $message = "Berhasil menghapus $deleted file orphan.";
        if ($failed > 0) {
            $message .= "<br>Gagal menghapus $failed file: " . implode(', ', $failed_files);
        }
        $message_type = "success";
    } else {
        $message = "Tidak ada file orphan yang berhasil dihapus.";
        if ($failed > 0) {
            $message .= "<br>Gagal menghapus $failed file: " . implode(', ', $failed_files);
        }
        $message_type = "danger";
    }
}

// Ambil dan hapus flash message
$message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : '';
$message_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Komite</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .context-menu {
            display: none;
            position: fixed;
            z-index: 1000;
            background: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            padding: 5px 0;
        }

        .context-menu-item {
            padding: 5px 15px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .context-menu-item:hover {
            background-color: #f8f9fa;
        }

        .context-menu-item i {
            width: 16px;
        }

        /* Style untuk hover pada kolom file */
        .file-column {
            transition: background-color 0.2s ease;
        }

        .file-column:hover {
            background-color: #f5f5f5;
            cursor: context-menu;
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                    <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#panduanModal">
                        <i class="bi bi-info-circle"></i> Panduan Penggunaan
                    </button>
                    <button type="button" class="btn btn-warning" onclick="confirmGenerateFiles()">
                        <i class="bi bi-file-earmark-text"></i> Generate File
                    </button>
                    <form method="POST" class="d-inline">
                        <button type="submit" name="cek_orphan" class="btn btn-danger">
                            <i class="bi bi-search"></i> Cek File Orphan
                        </button>
                    </form>
                </div>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">File Komite</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <?php if (isset($_POST['delete_filtered'])): ?>
                            <script>
                                Swal.fire({
                                    icon: 'success',
                                    title: '<?php echo $message; ?>',
                                    confirmButtonText: 'OK'
                                });
                            </script>
                        <?php else: ?>
                            <script>
                                Swal.fire({
                                    icon: '<?php echo $message_type === 'success' ? 'success' : 'error'; ?>',
                                    title: '<?php echo $message_type === 'success' ? 'Berhasil!' : 'Gagal!'; ?>',
                                    text: '<?php echo $message; ?>',
                                    confirmButtonText: 'OK'
                                });
                            </script>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Form Pencarian -->
                    <form method="POST" class="mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label for="noreg" class="col-form-label">Cari Nomor Registrasi:</label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="noreg" name="noreg" class="form-control" value="<?php echo isset($_POST['noreg']) ? htmlspecialchars($_POST['noreg']) : ''; ?>" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" name="search" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                                <?php if (isset($_POST['search']) || isset($_GET['noreg'])): ?>
                                    <a href="file-komite.php" class="btn btn-secondary">
                                        <i class="bi bi-x-circle"></i> Reset
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </form>

                    <!-- Filter Bulan dan Tahun -->
                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0 small">Filter Data</h6>
                        </div>
                        <div class="card-body py-2">
                            <form method="GET">
                                <div class="row g-2 align-items-center">
                                    <div class="col-auto">
                                        <label for="month" class="col-form-label" style="font-size: 0.75rem;">Filter Bulan:</label>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="month" id="month" class="form-select form-select-sm">
                                            <option value="">Semua Bulan</option>
                                            <?php foreach (getMonths() as $value => $label): ?>
                                                <option value="<?php echo $value; ?>" <?php echo $selected_month == $value ? 'selected' : ''; ?>>
                                                    <?php echo $label; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <label for="year" class="col-form-label" style="font-size: 0.75rem;">Tahun:</label>
                                    </div>
                                    <div class="col-md-1">
                                        <select name="year" id="year" class="form-select form-select-sm">
                                            <option value="">Semua</option>
                                            <?php
                                            $current_year = date('Y');
                                            for ($year = $current_year; $year >= $current_year - 5; $year--):
                                            ?>
                                                <option value="<?php echo $year; ?>" <?php echo $selected_year == $year ? 'selected' : ''; ?>>
                                                    <?php echo $year; ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <label for="sort" class="col-form-label" style="font-size: 0.75rem;">Urutkan:</label>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="sort" id="sort" class="form-select form-select-sm">
                                            <option value="date" <?php echo $sort_by === 'date' ? 'selected' : ''; ?>>Berdasarkan Tanggal</option>
                                            <option value="size" <?php echo $sort_by === 'size' ? 'selected' : ''; ?>>Berdasarkan Ukuran File</option>
                                        </select>
                                    </div>
                                    <div class="col-auto">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="bi bi-filter"></i> Filter
                                        </button>
                                        <?php if ($selected_month || $selected_year || $sort_by !== 'date'): ?>
                                            <a href="file-komite.php" class="btn btn-secondary btn-sm">
                                                <i class="bi bi-x-circle"></i> Reset
                                            </a>
                                            <?php if ($selected_year && !$selected_month): ?>
                                                <a href="download_files.php?year=<?php echo $selected_year; ?>" class="btn btn-success btn-sm">
                                                    <i class="bi bi-download"></i> Download ZIP
                                                </a>
                                            <?php endif; ?>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteFiltered()">
                                                <i class="bi bi-trash"></i> Hapus File Terfilter
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Form untuk hapus file terfilter -->
                    <form id="deleteFilteredForm" method="POST" style="display: none;">
                        <input type="hidden" name="delete_filtered" value="1">
                    </form>

                    <?php if (!empty($search_results)): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-center" style="width: 15%">No. Registrasi</th>
                                        <th class="text-center" style="width: 28%">File Analisa</th>
                                        <th class="text-center" style="width: 28%">File SLIK</th>
                                        <th class="text-center" style="width: 28%">File Lainnya</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($search_results as $row): ?>
                                        <tr>
                                            <td class="align-middle text-center"><?php echo $row['noreg']; ?></td>
                                            <td class="file-column">
                                                <?php if ($row['analisa']): ?>
                                                    <div class="d-flex align-items-center" oncontextmenu="showContextMenu(event, 'analisa', '<?php echo $row['noreg']; ?>', '<?php echo $row['analisa']; ?>')">
                                                        <i class="bi bi-file-pdf text-danger me-2"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="small">
                                                                <span title="<?php echo $row['analisa']; ?>"><?php echo formatFileName($row['analisa']); ?></span>
                                                                <span class="text-muted ms-2">
                                                                    <?php
                                                                    $file_path = '../../../nusamba2/assets/doc/analisa/' . $row['analisa'];
                                                                    echo file_exists($file_path) ? formatFileSize(filesize($file_path)) : '0 bytes';
                                                                    ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-muted small">Belum ada file</span>
                                                        <form method="POST" enctype="multipart/form-data" class="ms-auto">
                                                            <input type="hidden" name="noreg" value="<?php echo $row['noreg']; ?>">
                                                            <input type="hidden" name="file_type" value="analisa">
                                                            <input type="hidden" name="upload" value="1">
                                                            <input type="file" name="file" class="d-none" id="analisa_<?php echo $row['noreg']; ?>" accept=".pdf" onchange="this.form.submit()">
                                                            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('analisa_<?php echo $row['noreg']; ?>').click()" title="Upload">
                                                                <i class="bi bi-upload"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="file-column">
                                                <?php if ($row['slik']): ?>
                                                    <div class="d-flex align-items-center" oncontextmenu="showContextMenu(event, 'slik', '<?php echo $row['noreg']; ?>', '<?php echo $row['slik']; ?>')">
                                                        <i class="bi bi-file-pdf text-danger me-2"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="small">
                                                                <span title="<?php echo $row['slik']; ?>"><?php echo formatFileName($row['slik']); ?></span>
                                                                <span class="text-muted ms-2">
                                                                    <?php
                                                                    $file_path = '../../../nusamba2/assets/doc/slik/' . $row['slik'];
                                                                    echo file_exists($file_path) ? formatFileSize(filesize($file_path)) : '0 bytes';
                                                                    ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-muted small">Belum ada file</span>
                                                        <form method="POST" enctype="multipart/form-data" class="ms-auto">
                                                            <input type="hidden" name="noreg" value="<?php echo $row['noreg']; ?>">
                                                            <input type="hidden" name="file_type" value="slik">
                                                            <input type="hidden" name="upload" value="1">
                                                            <input type="file" name="file" class="d-none" id="slik_<?php echo $row['noreg']; ?>" accept=".pdf" onchange="this.form.submit()">
                                                            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('slik_<?php echo $row['noreg']; ?>').click()" title="Upload">
                                                                <i class="bi bi-upload"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td class="file-column">
                                                <?php if ($row['lainnya']): ?>
                                                    <div class="d-flex align-items-center" oncontextmenu="showContextMenu(event, 'lainnya', '<?php echo $row['noreg']; ?>', '<?php echo $row['lainnya']; ?>')">
                                                        <i class="bi bi-file-pdf text-danger me-2"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="small">
                                                                <span title="<?php echo $row['lainnya']; ?>"><?php echo formatFileName($row['lainnya']); ?></span>
                                                                <span class="text-muted ms-2">
                                                                    <?php
                                                                    $file_path = '../../../nusamba2/assets/doc/lainnya/' . $row['lainnya'];
                                                                    echo file_exists($file_path) ? formatFileSize(filesize($file_path)) : '0 bytes';
                                                                    ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-muted small">Belum ada file</span>
                                                        <form method="POST" enctype="multipart/form-data" class="ms-auto">
                                                            <input type="hidden" name="noreg" value="<?php echo $row['noreg']; ?>">
                                                            <input type="hidden" name="file_type" value="lainnya">
                                                            <input type="hidden" name="upload" value="1">
                                                            <input type="file" name="file" class="d-none" id="lainnya_<?php echo $row['noreg']; ?>" accept=".pdf" onchange="this.form.submit()">
                                                            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('lainnya_<?php echo $row['noreg']; ?>').click()" title="Upload">
                                                                <i class="bi bi-upload"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Page navigation" class="mt-3">
                                <ul class="pagination justify-content-center">
                                    <?php
                                    $start_page = max(1, $page - 4);
                                    $end_page = min($total_pages, $start_page + 9);
                                    if ($end_page - $start_page < 9) {
                                        $start_page = max(1, $end_page - 9);
                                    }

                                    // Buat query string untuk filter
                                    $query_params = array();
                                    if ($selected_month) $query_params['month'] = $selected_month;
                                    if ($selected_year) $query_params['year'] = $selected_year;
                                    if ($sort_by !== 'date') $query_params['sort'] = $sort_by;
                                    $query_string = !empty($query_params) ? '&' . http_build_query($query_params) : '';
                                    ?>

                                    <?php if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1<?php echo $query_string; ?>" title="Halaman Pertama">
                                                <i class="bi bi-chevron-double-left"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>" title="Halaman Sebelumnya">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?><?php echo $query_string; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>" title="Halaman Selanjutnya">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $total_pages; ?><?php echo $query_string; ?>" title="Halaman Terakhir">
                                                <i class="bi bi-chevron-double-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Tidak ada data yang ditemukan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($_POST['cek_orphan'])): ?>
                <div class="card mb-3 mt-3">
                    <div class="card-header bg-danger text-white py-2 d-flex justify-content-between align-items-center">
                        <strong>Hasil Pengecekan File Orphan</strong>
                        <?php if (!empty($orphan_files)): ?>
                            <form method="POST" onsubmit="return confirm('Yakin hapus SEMUA file orphan yang ditemukan?')" class="mb-0">
                                <?php foreach ($orphan_files as $file): ?>
                                    <input type="hidden" name="all_orphan_paths[]" value="<?php echo htmlspecialchars($file['path']); ?>">
                                    <input type="hidden" name="all_orphan_names[]" value="<?php echo htmlspecialchars($file['name']); ?>">
                                <?php endforeach; ?>
                                <button type="submit" name="delete_all_orphan" class="btn btn-danger btn-sm">
                                    <i class="bi bi-trash"></i> Hapus Semua
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="card-body py-2">
                        <?php if (empty($orphan_files)): ?>
                            <div class="alert alert-success mb-0">Tidak ada file orphan, semua file sudah tercatat di database.</div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Jenis</th>
                                            <th>Nama File</th>
                                            <th>Lokasi</th>
                                            <th>Ukuran</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orphan_files as $file): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($file['type']); ?></td>
                                                <td><?php echo htmlspecialchars($file['name']); ?></td>
                                                <td><span class="small text-muted"><?php echo htmlspecialchars($file['path']); ?></span></td>
                                                <td><?php echo $file['size']; ?></td>
                                                <td>
                                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus file ini?')">
                                                        <input type="hidden" name="orphan_path" value="<?php echo htmlspecialchars($file['path']); ?>">
                                                        <input type="hidden" name="orphan_name" value="<?php echo htmlspecialchars($file['name']); ?>">
                                                        <button type="submit" name="delete_orphan_file" class="btn btn-danger btn-sm">
                                                            <i class="bi bi-trash"></i> Hapus
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Panduan -->
    <div class="modal fade" id="panduanModal" tabindex="-1" aria-labelledby="panduanModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="panduanModalLabel">
                        <i class="bi bi-info-circle"></i> Panduan Penggunaan
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="small">
                        <p class="mb-2"><strong>Fitur yang Tersedia:</strong></p>
                        <ol class="mb-2 ps-3">
                            <li><strong>Pencarian Nomor Registrasi</strong>
                                <ul>
                                    <li>Masukkan nomor registrasi untuk mencari file spesifik</li>
                                    <li>Hasil pencarian akan menampilkan file analisa, SLIK, dan lainnya</li>
                                </ul>
                            </li>
                            <li><strong>Filter Data</strong>
                                <ul>
                                    <li>Filter berdasarkan bulan dan tahun untuk melihat file dalam periode tertentu</li>
                                    <li>Pilih tahun saja untuk melihat semua file dalam tahun tersebut</li>
                                    <li>Pilih bulan dan tahun untuk melihat file dalam bulan dan tahun spesifik</li>
                                </ul>
                            </li>
                            <li><strong>Pengurutan Data</strong>
                                <ul>
                                    <li>Urutkan berdasarkan tanggal (default) - menampilkan file terbaru di atas</li>
                                    <li>Urutkan berdasarkan ukuran file - menampilkan file terbesar di atas</li>
                                </ul>
                            </li>
                            <li><strong>Manajemen File</strong>
                                <ul>
                                    <li>Klik kanan pada file untuk menampilkan menu aksi</li>
                                    <li>Lihat file - membuka file PDF di tab baru</li>
                                    <li>Copy Text - menyalin nama file</li>
                                    <li>Upload - mengganti file yang ada</li>
                                    <li>Hapus - menghapus file (data di database tidak terpengaruh)</li>
                                </ul>
                            </li>
                            <li><strong>Hapus File Terfilter</strong>
                                <ul>
                                    <li>Tombol "Hapus File Terfilter" muncul saat filter aktif</li>
                                    <li>Menghapus semua file yang sesuai dengan filter yang dipilih</li>
                                    <li>Hanya menghapus file, data di database tetap tersimpan</li>
                                </ul>
                            </li>
                        </ol>
                        <p class="mb-0"><strong>Catatan:</strong> File yang diupload harus dalam format PDF dengan ukuran maksimal 500MB.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Context Menu -->
    <div id="contextMenu" class="context-menu">
        <div class="context-menu-item" onclick="viewFile()">
            <i class="bi bi-eye"></i> Lihat
        </div>
        <div class="context-menu-item" onclick="copyText()">
            <i class="bi bi-clipboard"></i> Copy Text
        </div>
        <div class="context-menu-item" onclick="uploadFile()">
            <i class="bi bi-upload"></i> Upload
        </div>
        <div class="context-menu-item" onclick="deleteFile()">
            <i class="bi bi-trash"></i> Hapus
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let currentContext = {
            type: '',
            noreg: '',
            filename: ''
        };

        function showContextMenu(event, type, noreg, filename) {
            event.preventDefault();
            currentContext = {
                type,
                noreg,
                filename
            };

            const menu = document.getElementById('contextMenu');
            menu.style.display = 'block';

            // Posisi menu tepat di samping cursor
            menu.style.left = (event.clientX + 5) + 'px';
            menu.style.top = event.clientY + 'px';

            // Sembunyikan menu saat klik di luar
            document.addEventListener('click', hideContextMenu);
        }

        function hideContextMenu() {
            document.getElementById('contextMenu').style.display = 'none';
            document.removeEventListener('click', hideContextMenu);
        }

        function viewFile() {
            if (currentContext.filename) {
                window.open(`../../../nusamba2/assets/doc/${currentContext.type}/${currentContext.filename}`, '_blank');
            }
            hideContextMenu();
        }

        function copyText() {
            if (currentContext.filename) {
                navigator.clipboard.writeText(currentContext.filename).then(() => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Nama file berhasil disalin!',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }).catch(err => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Gagal menyalin teks',
                        timer: 2000,
                        showConfirmButton: false
                    });
                });
            }
            hideContextMenu();
        }

        function uploadLargeFile(input, noreg, fileType) {
            const file = input.files[0];
            if (!file) return;

            // Validasi tipe file
            if (file.type !== 'application/pdf') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Hanya file PDF yang diperbolehkan!'
                });
                return;
            }

            // Validasi ukuran file
            const maxSize = 600 * 1024 * 1024; // 600MB
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Ukuran file terlalu besar! Maksimal 600MB'
                });
                return;
            }

            // Tampilkan progress
            Swal.fire({
                title: 'Uploading...',
                html: `
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <div class="mt-2">Progress: <span id="upload-progress">0%</span></div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false
            });

            const formData = new FormData();
            formData.append('file', file);
            formData.append('noreg', noreg);
            formData.append('file_type', fileType);
            formData.append('upload', '1');

            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'upload_direct.php', true);

            xhr.upload.onprogress = function(e) {
                if (e.lengthComputable) {
                    const percentComplete = Math.round((e.loaded / e.total) * 100);
                    document.querySelector('.progress-bar').style.width = percentComplete + '%';
                    document.getElementById('upload-progress').textContent = percentComplete + '%';
                }
            };

            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: 'File berhasil diupload!',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            throw new Error(response.message || 'Upload gagal');
                        }
                    } catch (error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: error.message || 'Terjadi kesalahan saat upload'
                        });
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat upload'
                    });
                }
            };

            xhr.onerror = function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat upload'
                });
            };

            xhr.send(formData);
        }

        function uploadFile() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.pdf';
            input.onchange = function() {
                uploadLargeFile(this, currentContext.noreg, currentContext.type);
            };
            input.click();
            hideContextMenu();
        }

        function deleteFile() {
            if (currentContext.filename) {
                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    text: "File yang dihapus tidak dapat dikembalikan!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.createElement('form');
                        form.method = 'POST';

                        const noregInput = document.createElement('input');
                        noregInput.type = 'hidden';
                        noregInput.name = 'noreg';
                        noregInput.value = currentContext.noreg;

                        const typeInput = document.createElement('input');
                        typeInput.type = 'hidden';
                        typeInput.name = 'file_type';
                        typeInput.value = currentContext.type;

                        const filenameInput = document.createElement('input');
                        filenameInput.type = 'hidden';
                        filenameInput.name = 'filename';
                        filenameInput.value = currentContext.filename;

                        const deleteInput = document.createElement('input');
                        deleteInput.type = 'hidden';
                        deleteInput.name = 'delete';
                        deleteInput.value = '1';

                        form.appendChild(noregInput);
                        form.appendChild(typeInput);
                        form.appendChild(filenameInput);
                        form.appendChild(deleteInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            }
            hideContextMenu();
        }

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }

        function confirmDeleteFiltered() {
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Semua file yang sesuai dengan filter akan dihapus! Data di database tidak akan terpengaruh.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteFilteredForm').submit();
                }
            });
        }

        function confirmGenerateFiles() {
            Swal.fire({
                title: 'Peringatan!',
                html: `
                    <div class="text-start">
                        <p>Anda akan mengubah semua nama file dan database berdasarkan noreg masing-masing secara sistematis. Cara ini efektif agar nama file terorganisir lebih baik.</p>
                        <p class="text-danger"><strong>PERHATIAN:</strong> Setelah melakukan perubahan ini, maka tidak akan bisa dikembalikan. Sebaiknya backup terlebih dahulu database dan semua file.</p>
                    </div>
                `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Generate File',
                cancelButtonText: 'Tidak, Batalkan'
            }).then((result) => {
                if (result.isConfirmed) {
                    generateFiles();
                }
            });
        }

        function generateFiles() {
            // Tampilkan loading
            Swal.fire({
                title: 'Memproses...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Kirim request ke server
            fetch('generate_files.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload halaman setelah OK diklik
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message || 'Terjadi kesalahan saat memproses file.',
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Terjadi kesalahan saat memproses request.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        }
    </script>
</body>

</html>