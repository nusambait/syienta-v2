<?php
session_start();
include '../../config.php';
include '../config/config.php';
include '../includes/check-admin.php';

$message = '';
$message_type = '';
$data_pengajuan = null;
$search_results = array();
$found_value = null;
$found_table = null;
$found_column = null;

// Konfigurasi pagination
$items_per_page = 20; // Ubah ke 20 data per halaman
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $items_per_page;

// Fungsi untuk mendapatkan semua hasil pencarian
function getAllSearchResults($connect, $keyword)
{
    try {
        $all_results = array();
        $tables_query = mysqli_query($connect, "SHOW TABLES");
        if (!$tables_query) {
            throw new Exception("Error getting tables: " . mysqli_error($connect));
        }

        while ($table = mysqli_fetch_row($tables_query)) {
            $table_name = $table[0];
            $columns_query = mysqli_query($connect, "SHOW COLUMNS FROM `$table_name`");
            if (!$columns_query) {
                continue; // Skip table if can't get columns
            }

            $conditions = array();
            while ($column = mysqli_fetch_assoc($columns_query)) {
                $column_name = $column['Field'];
                // Hanya tambahkan kolom yang bertipe string atau numerik
                if (
                    strpos($column['Type'], 'char') !== false ||
                    strpos($column['Type'], 'text') !== false ||
                    strpos($column['Type'], 'int') !== false ||
                    strpos($column['Type'], 'decimal') !== false ||
                    strpos($column['Type'], 'float') !== false
                ) {
                    $conditions[] = "`$column_name` = '$keyword'";
                }
            }

            if (!empty($conditions)) {
                $where_clause = implode(' OR ', $conditions);
                $query = mysqli_query($connect, "SELECT * FROM `$table_name` WHERE $where_clause");
                if (!$query) {
                    continue; // Skip if query fails
                }

                while ($row = mysqli_fetch_assoc($query)) {
                    foreach ($row as $column => $value) {
                        if ($value === $keyword) {
                            $all_results[] = array(
                                'table' => $table_name,
                                'column' => $column,
                                'value' => $value
                            );
                        }
                    }
                }
            }
        }

        return $all_results;
    } catch (Exception $e) {
        error_log("Search error: " . $e->getMessage());
        return array();
    }
}

// Proses pencarian
if (isset($_POST['search']) || isset($_GET['keyword'])) {
    try {
        $keyword = isset($_POST['search']) ? $_POST['noreg'] : $_GET['keyword'];
        $keyword = mysqli_real_escape_string($connect, $keyword);

        // Dapatkan semua hasil pencarian
        $all_results = getAllSearchResults($connect, $keyword);
        $total_results = count($all_results);
        $total_pages = ceil($total_results / $items_per_page);

        // Ambil data untuk halaman saat ini
        $search_results = array_slice($all_results, $offset, $items_per_page);

        // Cari di semua tabel untuk form update
        $found_value = null;
        $found_table = null;
        $found_column = null;

        if (!empty($all_results)) {
            // Ambil hasil pertama yang ditemukan
            $first_result = $all_results[0];
            $found_value = $first_result['value'];
            $found_table = $first_result['table'];
            $found_column = $first_result['column'];
        }

        if (empty($search_results) && !$found_value) {
            $message = "Data tidak ditemukan di database!";
            $message_type = "danger";
        }
    } catch (Exception $e) {
        $message = "Terjadi kesalahan saat pencarian: " . $e->getMessage();
        $message_type = "danger";
        $search_results = array();
        $found_value = null;
    }
}

// Proses update noreg
if (isset($_POST['update'])) {
    try {
        $noreg_lama = mysqli_real_escape_string($connect, $_POST['noreg_lama']);
        $noreg_baru = mysqli_real_escape_string($connect, $_POST['noreg_baru']);
        $table_lama = mysqli_real_escape_string($connect, $_POST['table_lama']);
        $column_lama = mysqli_real_escape_string($connect, $_POST['column_lama']);

        // Dapatkan semua hasil pencarian untuk noreg_lama
        $all_results = getAllSearchResults($connect, $noreg_lama);

        // Kelompokkan hasil berdasarkan tabel
        $tables_to_update = array();
        foreach ($all_results as $result) {
            if (!isset($tables_to_update[$result['table']])) {
                $tables_to_update[$result['table']] = array();
            }
            $tables_to_update[$result['table']][] = $result['column'];
        }

        // Cek duplikasi hanya di tabel yang akan diupdate
        $duplicate_found = false;
        $duplicate_tables = array();

        foreach ($tables_to_update as $table => $columns) {
            // Cek duplikasi hanya jika tabel tersebut adalah pengajuan, komite, atau droping
            if (in_array($table, array('pengajuan', 'komite', 'droping'))) {
                $check_query = mysqli_query($connect, "SELECT noreg FROM `$table` WHERE noreg = '$noreg_baru'");
                if ($check_query && mysqli_num_rows($check_query) > 0) {
                    $duplicate_found = true;
                    $duplicate_tables[] = $table;
                }
            }
        }

        if ($duplicate_found) {
            $message = "Nilai baru sudah digunakan di tabel: " . implode(', ', $duplicate_tables);
            $message_type = "danger";
        } else {
            $updated_tables = array();
            $errors = array();

            // Update hanya di tabel yang ditemukan di hasil pencarian
            foreach ($tables_to_update as $table_name => $columns) {
                $updated = false;

                foreach ($columns as $column_name) {
                    // Dapatkan tipe kolom
                    $column_query = mysqli_query($connect, "SHOW COLUMNS FROM `$table_name` WHERE Field = '$column_name'");
                    if (!$column_query) {
                        $errors[] = "Error getting column info for $table_name.$column_name: " . mysqli_error($connect);
                        continue;
                    }

                    $column = mysqli_fetch_assoc($column_query);
                    $column_type = strtolower($column['Type']);

                    // Skip kolom yang tidak sesuai untuk diupdate
                    if (
                        strpos($column_type, 'date') !== false ||
                        strpos($column_type, 'time') !== false ||
                        strpos($column_type, 'year') !== false ||
                        strpos($column_type, 'blob') !== false ||
                        strpos($column_type, 'binary') !== false ||
                        strpos($column_type, 'int') !== false ||
                        strpos($column_type, 'decimal') !== false ||
                        strpos($column_type, 'float') !== false ||
                        strpos($column_type, 'double') !== false
                    ) {
                        continue;
                    }

                    // Hanya update kolom yang bertipe string
                    if (
                        strpos($column_type, 'char') !== false ||
                        strpos($column_type, 'text') !== false ||
                        strpos($column_type, 'varchar') !== false
                    ) {

                        // Update jika nilai kolom persis sama dengan noreg_lama
                        $update_query = mysqli_query($connect, "UPDATE `$table_name` SET `$column_name` = '$noreg_baru' WHERE `$column_name` = '$noreg_lama'");
                        if ($update_query === false) {
                            $errors[] = "Error updating $table_name.$column_name: " . mysqli_error($connect);
                        } else if (mysqli_affected_rows($connect) > 0) {
                            $updated = true;
                        }
                    }
                }

                if ($updated) {
                    $updated_tables[] = $table_name;
                }
            }

            if (!empty($updated_tables)) {
                $tables_str = implode(', ', $updated_tables);
                $message = "Nilai berhasil diubah di " . count($updated_tables) . " tabel: " . $tables_str;
                if (!empty($errors)) {
                    $message .= "<br>Beberapa error terjadi: " . implode("<br>", $errors);
                }
                $message_type = "success";
                $data_pengajuan = null;
                $search_results = array();
            } else {
                $message = "Tidak ada data yang diubah!";
                if (!empty($errors)) {
                    $message .= "<br>Error yang terjadi: " . implode("<br>", $errors);
                }
                $message_type = "warning";
            }
        }
    } catch (Exception $e) {
        $message = "Terjadi kesalahan: " . $e->getMessage();
        $message_type = "danger";
    }
}

// Proses hapus data berdasarkan noreg
if (isset($_POST['delete'])) {
    try {
        $noreg_hapus = mysqli_real_escape_string($connect, $_POST['noreg_hapus']);
        $all_results = getAllSearchResults($connect, $noreg_hapus);
        $tables_to_delete = array();
        foreach ($all_results as $result) {
            if (!isset($tables_to_delete[$result['table']])) {
                $tables_to_delete[$result['table']] = array();
            }
            $tables_to_delete[$result['table']][] = $result['column'];
        }
        $deleted_tables = array();
        $errors = array();
        $deleted_files = array();

        // Hapus file fisik dari tabel komite terlebih dahulu
        if (isset($tables_to_delete['komite'])) {
            $komite_query = mysqli_query($connect, "SELECT * FROM komite WHERE noreg = '$noreg_hapus'");
            if ($komite_query) {
                while ($komite_data = mysqli_fetch_assoc($komite_query)) {
                    // Hapus file analisa
                    if (!empty($komite_data['analisa'])) {
                        $analisa_path = "../../nusamba2/assets/doc/analisa/" . $komite_data['analisa'];
                        if (file_exists($analisa_path)) {
                            if (unlink($analisa_path)) {
                                $deleted_files[] = "analisa/" . $komite_data['analisa'];
                            } else {
                                $errors[] = "Gagal menghapus file analisa: " . $komite_data['analisa'];
                            }
                        }
                    }

                    // Hapus file slik
                    if (!empty($komite_data['slik'])) {
                        $slik_path = "../../nusamba2/assets/doc/slik/" . $komite_data['slik'];
                        if (file_exists($slik_path)) {
                            if (unlink($slik_path)) {
                                $deleted_files[] = "slik/" . $komite_data['slik'];
                            } else {
                                $errors[] = "Gagal menghapus file slik: " . $komite_data['slik'];
                            }
                        }
                    }

                    // Hapus file lainnya
                    if (!empty($komite_data['lainnya'])) {
                        $lainnya_path = "../../nusamba2/assets/doc/lainnya/" . $komite_data['lainnya'];
                        if (file_exists($lainnya_path)) {
                            if (unlink($lainnya_path)) {
                                $deleted_files[] = "lainnya/" . $komite_data['lainnya'];
                            } else {
                                $errors[] = "Gagal menghapus file lainnya: " . $komite_data['lainnya'];
                            }
                        }
                    }
                }
            }
        }

        // Hapus data dari database
        foreach ($tables_to_delete as $table_name => $columns) {
            foreach ($columns as $column_name) {
                $delete_query = mysqli_query($connect, "DELETE FROM `$table_name` WHERE `$column_name` = '$noreg_hapus'");
                if ($delete_query === false) {
                    $errors[] = "Error deleting from $table_name.$column_name: " . mysqli_error($connect);
                } else if (mysqli_affected_rows($connect) > 0) {
                    $deleted_tables[] = $table_name;
                }
            }
        }
        if (!empty($deleted_tables)) {
            $tables_str = implode(', ', array_unique($deleted_tables));
            $message = "Data berhasil dihapus di tabel: " . $tables_str;

            // Tambahkan informasi file yang dihapus
            if (!empty($deleted_files)) {
                $message .= "<br>File yang dihapus: " . implode(', ', $deleted_files);
            }

            if (!empty($errors)) {
                $message .= "<br>Beberapa error terjadi: " . implode("<br>", $errors);
            }
            $message_type = "success";
            $data_pengajuan = null;
            $search_results = array();
        } else {
            $message = "Tidak ada data yang dihapus!";
            if (!empty($errors)) {
                $message .= "<br>Error yang terjadi: " . implode("<br>", $errors);
            }
            $message_type = "warning";
        }
    } catch (Exception $e) {
        $message = "Terjadi kesalahan saat menghapus: " . $e->getMessage();
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Nomor Registrasi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="mb-3 text-end">
                <a href="index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>
            <div class="card">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">Ubah Nomor Registrasi</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Pencarian -->
                    <form method="POST" class="mb-4">
                        <div class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label for="noreg" class="col-form-label">Cari Nomor Registrasi:</label>
                            </div>
                            <div class="col-md-6">
                                <input type="text" id="noreg" name="noreg" class="form-control"
                                    value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>"
                                    required
                                    style="width: 100%;">
                            </div>
                            <div class="col-auto">
                                <button type="submit" name="search" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </div>
                    </form>

                    <script>
                    </script>

                    <!-- Hasil Pencarian -->
                    <?php if (!empty($search_results)): ?>
                        <div class="alert alert-info">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="mb-0">Data ditemukan di tabel-tabel berikut (Menampilkan <?php echo count($search_results); ?> dari <?php echo $total_results; ?> hasil):</h6>
                                <?php if ($found_value): ?>
                                    <form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus SEMUA data yang mengandung noreg ini di seluruh tabel? Tindakan ini tidak dapat dibatalkan!')">
                                        <input type="hidden" name="noreg_hapus" value="<?php echo $found_value; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i> Hapus Data
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Tabel</th>
                                            <th>Kolom</th>
                                            <th>Nilai</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($search_results as $result): ?>
                                            <tr>
                                                <td><?php echo $result['table']; ?></td>
                                                <td><?php echo $result['column']; ?></td>
                                                <td><?php echo $result['value']; ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-3">
                                    <ul class="pagination justify-content-center">
                                        <?php if ($current_page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?keyword=<?php echo urlencode($keyword); ?>&page=<?php echo $current_page - 1; ?>">
                                                    <i class="bi bi-chevron-left"></i> Sebelumnya
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php
                                        // Hitung range halaman yang akan ditampilkan
                                        $start_page = max(1, $current_page - 4);
                                        $end_page = min($total_pages, $start_page + 9);

                                        // Sesuaikan start_page jika end_page terlalu dekat dengan total_pages
                                        if ($end_page - $start_page < 9) {
                                            $start_page = max(1, $end_page - 9);
                                        }

                                        // Tampilkan tombol halaman pertama jika start_page > 1
                                        if ($start_page > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?keyword=<?php echo urlencode($keyword); ?>&page=1">1</a>
                                            </li>
                                            <?php if ($start_page > 2): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                                            <li class="page-item <?php echo $i === $current_page ? 'active' : ''; ?>">
                                                <a class="page-link" href="?keyword=<?php echo urlencode($keyword); ?>&page=<?php echo $i; ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <?php if ($end_page < $total_pages): ?>
                                            <?php if ($end_page < $total_pages - 1): ?>
                                                <li class="page-item disabled">
                                                    <span class="page-link">...</span>
                                                </li>
                                            <?php endif; ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?keyword=<?php echo urlencode($keyword); ?>&page=<?php echo $total_pages; ?>">
                                                    <?php echo $total_pages; ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>

                                        <?php if ($current_page < $total_pages): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?keyword=<?php echo urlencode($keyword); ?>&page=<?php echo $current_page + 1; ?>">
                                                    Selanjutnya <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Form Update Noreg -->
                    <?php if ($found_value): ?>
                        <form method="POST" class="mt-4">
                            <input type="hidden" name="noreg_lama" value="<?php echo $found_value; ?>">
                            <input type="hidden" name="table_lama" value="<?php echo $found_table; ?>">
                            <input type="hidden" name="column_lama" value="<?php echo $found_column; ?>">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Nilai Sekarang</label>
                                    <input type="text" class="form-control" value="<?php echo $found_value; ?>" readonly>
                                    <small class="text-muted">Ditemukan di tabel <?php echo $found_table; ?> kolom <?php echo $found_column; ?></small>
                                </div>
                                <div class="col-md-6">
                                    <label for="noreg_baru" class="form-label">Nilai Baru</label>
                                    <input type="text" id="noreg_baru" name="noreg_baru" class="form-control" required>
                                </div>
                            </div>
                            <div class="mt-3 d-flex gap-2">
                                <button type="submit" name="update" class="btn btn-warning" onclick="return confirm('Apakah Anda yakin ingin mengubah nilai ini? Perubahan akan dilakukan di semua tabel yang memiliki nilai yang sama.')">
                                    <i class="bi bi-pencil-square"></i> Ubah Nilai
                                </button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('show');
        }
    </script>
</body>

</html>