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

if (isset($_POST['search']) || isset($_GET['nomor_ket'])) {
    $nomor_ket = isset($_POST['search']) ? $_POST['nomor_ket'] : $_GET['nomor_ket'];
    $nomor_ket = mysqli_real_escape_string($connect, $nomor_ket);

    $query = mysqli_query($connect, "SELECT * FROM ketentuanku_surat WHERE nomor_ket LIKE '%$nomor_ket%'");
    $total_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM ketentuanku_surat WHERE nomor_ket LIKE '%$nomor_ket%'");
} else {
    // Query untuk mendapatkan semua data
    $query = mysqli_query($connect, "SELECT * FROM ketentuanku_surat");
    $total_query = mysqli_query($connect, "SELECT COUNT(*) as total FROM ketentuanku_surat");
}

$search_results = array();
if ($query) {
    while ($row = mysqli_fetch_assoc($query)) {
        // Tambahkan informasi ukuran file
        $file_path = '../../ketentuanku/uploads/' . $row['file_surat'];

        // Cek apakah file ada dan bisa diakses
        if ($row['file_surat'] && file_exists($file_path) && is_readable($file_path)) {
            $row['file_size'] = filesize($file_path);
        } else {
            $row['file_size'] = 0;
            error_log("File tidak ditemukan atau tidak bisa diakses: " . $file_path);
        }

        $search_results[] = $row;
    }
}

// Filter berdasarkan bulan dan tahun
if ($selected_month && $selected_year) {
    // Filter berdasarkan bulan dan tahun spesifik
    $search_results = array_filter($search_results, function ($row) use ($selected_month, $selected_year) {
        $date = date('m-Y', strtotime($row['ttl_terbit']));
        return $date == $selected_month . '-' . $selected_year;
    });
} elseif ($selected_year) {
    // Filter berdasarkan tahun saja
    $search_results = array_filter($search_results, function ($row) use ($selected_year) {
        return date('Y', strtotime($row['ttl_terbit'])) == $selected_year;
    });
} elseif ($selected_month) {
    // Filter berdasarkan bulan saja
    $search_results = array_filter($search_results, function ($row) use ($selected_month) {
        return date('m', strtotime($row['ttl_terbit'])) == $selected_month;
    });
}

// Urutkan hasil berdasarkan kriteria yang dipilih
if ($sort_by === 'size') {
    usort($search_results, function ($a, $b) {
        return $b['file_size'] - $a['file_size']; // Urutkan dari terbesar ke terkecil
    });
} else {
    // Urutkan berdasarkan tanggal (default)
    usort($search_results, function ($a, $b) {
        return strtotime($b['ttl_terbit']) - strtotime($a['ttl_terbit']);
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
        $nomor_ket = mysqli_real_escape_string($connect, $_POST['nomor_ket']);
        $judul_ket = mysqli_real_escape_string($connect, $_POST['judul_ket']);
        $ttl_terbit = mysqli_real_escape_string($connect, $_POST['ttl_terbit']);
        $file = $_FILES['file'];

        // Debug informasi
        error_log("Upload attempt - File info: " . print_r($file, true));
        error_log("POST data: " . print_r($_POST, true));

        // Validasi file
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Error uploading file: " . $file['error']);
        }

        if ($file['size'] > 500 * 1024 * 1024) { // 500MB
            throw new Exception("Ukuran file terlalu besar! Maksimal 500MB");
        }

        if ($file['type'] !== 'application/pdf') {
            throw new Exception("Hanya file PDF yang diperbolehkan!");
        }

        // Generate nama file
        $timestamp = date('Ymd-His');
        $unique_code = substr(md5(uniqid()), 0, 8);
        $new_filename = $nomor_ket . '-' . $timestamp . '-' . $unique_code . '.pdf';

        // Tentukan direktori tujuan
        $upload_dir = '../../ketentuanku/uploads/';
        error_log("Upload directory: " . $upload_dir);

        // Buat direktori jika belum ada
        if (!is_dir($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Gagal membuat direktori upload: " . $upload_dir);
            }
        }

        // Pastikan direktori writable
        if (!is_writable($upload_dir)) {
            throw new Exception("Direktori upload tidak writable: " . $upload_dir);
        }

        // Hapus file lama jika ada
        $old_file_query = mysqli_query($connect, "SELECT file_surat FROM ketentuanku_surat WHERE nomor_ket = '$nomor_ket'");
        if ($old_file_query && $row = mysqli_fetch_assoc($old_file_query)) {
            $old_file = $row['file_surat'];
            if ($old_file && file_exists($upload_dir . $old_file)) {
                if (!unlink($upload_dir . $old_file)) {
                    error_log("Warning: Gagal menghapus file lama: " . $upload_dir . $old_file);
                }
            }
        }

        // Upload file baru
        $target_path = $upload_dir . $new_filename;
        error_log("Target path: " . $target_path);
        error_log("Temp file path: " . $file['tmp_name']);

        // Coba copy file dulu
        if (copy($file['tmp_name'], $target_path)) {
            // Update database
            $update_query = mysqli_query($connect, "UPDATE ketentuanku_surat SET 
                file_surat = '$new_filename',
                judul_ket = '$judul_ket',
                ttl_terbit = '$ttl_terbit',
                nama_pengupload = '{$_SESSION['nama']}',
                tgl_upload = NOW()
                WHERE nomor_ket = '$nomor_ket'");

            if ($update_query) {
                $_SESSION['flash_message'] = "File berhasil diupload!";
                $_SESSION['flash_type'] = "success";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            } else {
                // Hapus file jika update database gagal
                unlink($target_path);
                throw new Exception("Gagal mengupdate database: " . mysqli_error($connect));
            }
        } else {
            $error = error_get_last();
            throw new Exception("Gagal mengupload file! Error: " . ($error ? $error['message'] : 'Unknown error'));
        }
    } catch (Exception $e) {
        error_log("Upload error: " . $e->getMessage());
        $_SESSION['flash_message'] = $e->getMessage();
        $_SESSION['flash_type'] = "danger";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Proses hapus file
if (isset($_POST['delete'])) {
    try {
        $nomor_ket = mysqli_real_escape_string($connect, $_POST['nomor_ket']);
        $filename = mysqli_real_escape_string($connect, $_POST['filename']);

        $file_path = '../../ketentuanku/uploads/' . $filename;

        if (file_exists($file_path)) {
            if (unlink($file_path)) {
                $update_query = mysqli_query($connect, "UPDATE ketentuanku_surat SET file_surat = NULL WHERE nomor_ket = '$nomor_ket'");
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
            if ($row['file_surat']) {
                $file_path = '../../ketentuanku/uploads/' . $row['file_surat'];
                if (file_exists($file_path)) {
                    $file_size = filesize($file_path);
                    if (unlink($file_path)) {
                        $total_deleted++;
                        $total_size += $file_size;
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

// Ambil dan hapus flash message
$message = isset($_SESSION['flash_message']) ? $_SESSION['flash_message'] : '';
$message_type = isset($_SESSION['flash_type']) ? $_SESSION['flash_type'] : '';
unset($_SESSION['flash_message'], $_SESSION['flash_type']);

// Tambahkan fungsi untuk mendapatkan URL file
function getFileUrl($filename)
{
    global $base_url;
    return $base_url . 'ketentuanku/uploads/' . $filename;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Ketentuan</title>
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
                </div>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">File Ketentuan</h5>
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
                                <label for="nomor_ket" class="col-form-label">Cari Nomor Ketentuan:</label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="nomor_ket" name="nomor_ket" class="form-control" value="<?php echo isset($_POST['nomor_ket']) ? htmlspecialchars($_POST['nomor_ket']) : ''; ?>" required>
                            </div>
                            <div class="col-auto">
                                <button type="submit" name="search" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                                <?php if (isset($_POST['search']) || isset($_GET['nomor_ket'])): ?>
                                    <a href="file-ketentuanku.php" class="btn btn-secondary">
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
                                            <?php
                                            $months = array(
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
                                            foreach ($months as $value => $label): ?>
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
                                            <a href="file-ketentuanku.php" class="btn btn-secondary btn-sm">
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
                                        <th class="text-center" style="width: 15%">No. Ketentuan</th>
                                        <th class="text-center" style="width: 25%">Judul</th>
                                        <th class="text-center" style="width: 15%">Tanggal Terbit</th>
                                        <th class="text-center" style="width: 15%">Pengupload</th>
                                        <th class="text-center" style="width: 15%">Tanggal Upload</th>
                                        <th class="text-center" style="width: 15%">File</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($search_results as $row): ?>
                                        <tr>
                                            <td class="align-middle text-center"><?php echo $row['nomor_ket']; ?></td>
                                            <td class="align-middle"><?php echo $row['judul_ket']; ?></td>
                                            <td class="align-middle text-center"><?php echo date('d/m/Y', strtotime($row['ttl_terbit'])); ?></td>
                                            <td class="align-middle text-center"><?php echo $row['nama_pengupload']; ?></td>
                                            <td class="align-middle text-center"><?php echo date('d/m/Y', strtotime($row['tgl_upload'])); ?></td>
                                            <td class="file-column">
                                                <?php if ($row['file_surat']): ?>
                                                    <div class="d-flex align-items-center" oncontextmenu="showContextMenu(event, '<?php echo $row['nomor_ket']; ?>', '<?php echo $row['file_surat']; ?>')">
                                                        <i class="bi bi-file-pdf text-danger me-2"></i>
                                                        <div class="flex-grow-1">
                                                            <div class="small">
                                                                <span title="<?php echo $row['file_surat']; ?>"><?php echo formatFileName($row['file_surat']); ?></span>
                                                                <span class="text-muted ms-2">
                                                                    <?php echo formatFileSize($row['file_size']); ?>
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="text-muted small">Belum ada file</span>
                                                        <form method="POST" enctype="multipart/form-data" class="ms-auto">
                                                            <input type="hidden" name="nomor_ket" value="<?php echo $row['nomor_ket']; ?>">
                                                            <input type="hidden" name="judul_ket" value="<?php echo $row['judul_ket']; ?>">
                                                            <input type="hidden" name="ttl_terbit" value="<?php echo $row['ttl_terbit']; ?>">
                                                            <input type="hidden" name="upload" value="1">
                                                            <input type="file" name="file" class="d-none" id="file_<?php echo $row['nomor_ket']; ?>" accept=".pdf" onchange="this.form.submit()">
                                                            <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('file_<?php echo $row['nomor_ket']; ?>').click()" title="Upload">
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
                            <li><strong>Pencarian Nomor Ketentuan</strong>
                                <ul>
                                    <li>Masukkan nomor ketentuan untuk mencari file spesifik</li>
                                    <li>Hasil pencarian akan menampilkan file ketentuan</li>
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
            nomor_ket: '',
            filename: ''
        };

        function showContextMenu(event, nomor_ket, filename) {
            event.preventDefault();
            currentContext = {
                nomor_ket,
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
                window.open(`<?php echo $base_url; ?>ketentuanku/uploads/${currentContext.filename}`, '_blank');
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

        function uploadFile() {
            const input = document.createElement('input');
            input.type = 'file';
            input.accept = '.pdf';
            input.onchange = function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.enctype = 'multipart/form-data';

                const nomorKetInput = document.createElement('input');
                nomorKetInput.type = 'hidden';
                nomorKetInput.name = 'nomor_ket';
                nomorKetInput.value = currentContext.nomor_ket;

                const uploadInput = document.createElement('input');
                uploadInput.type = 'hidden';
                uploadInput.name = 'upload';
                uploadInput.value = '1';

                const fileInput = document.createElement('input');
                fileInput.type = 'file';
                fileInput.name = 'file';
                fileInput.files = input.files;

                form.appendChild(nomorKetInput);
                form.appendChild(uploadInput);
                form.appendChild(fileInput);
                document.body.appendChild(form);
                form.submit();
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

                        const nomorKetInput = document.createElement('input');
                        nomorKetInput.type = 'hidden';
                        nomorKetInput.name = 'nomor_ket';
                        nomorKetInput.value = currentContext.nomor_ket;

                        const filenameInput = document.createElement('input');
                        filenameInput.type = 'hidden';
                        filenameInput.name = 'filename';
                        filenameInput.value = currentContext.filename;

                        const deleteInput = document.createElement('input');
                        deleteInput.type = 'hidden';
                        deleteInput.name = 'delete';
                        deleteInput.value = '1';

                        form.appendChild(nomorKetInput);
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
    </script>
</body>

</html>