<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inisialisasi variabel pencarian dan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status = isset($_GET['status']) ? $_GET['status'] : '';
$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Pagination setup
$limit = 30; // Data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Query dasar untuk menghitung total data
$count_query = "SELECT COUNT(*) as total FROM droping d 
                INNER JOIN pengajuan p ON d.noreg = p.noreg
                INNER JOIN account a ON p.ao = a.kd_ao 
                LEFT JOIN nasabah n ON p.niknas = n.nik 
                WHERE 1=1";

// Tambahkan kondisi pencarian ke query count
if (!empty($search)) {
    $search = mysqli_real_escape_string($connect, $search);
    $count_query .= " AND (d.noreg LIKE '%$search%' OR n.nama LIKE '%$search%')";
}

if (!empty($status)) {
    $status = mysqli_real_escape_string($connect, $status);
    $count_query .= " AND d.status LIKE '$status%'";
}

// Filter berdasarkan tahun dan bulan dari tgl_droping
if (!empty($year)) {
    $year = mysqli_real_escape_string($connect, $year);
    $count_query .= " AND d.tgl_droping LIKE '%-$year'";

    if (!empty($month)) {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $month = mysqli_real_escape_string($connect, $month);
        $count_query .= " AND d.tgl_droping LIKE '%-$month-%'";
    }
}

// Eksekusi query count
$count_result = mysqli_query($connect, $count_query);
$total_rows = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_rows / $limit);

// Query untuk data dengan pagination
$query = "SELECT d.noreg, d.nobwmk, d.nospp, d.nostpk, d.nocif, d.noloan,
          d.tgl_droping, d.plafond, d.sukbung, d.jw, d.angpok, d.angbung, d.totang,
          d.prov as biaprov, d.nomprov, d.adm, d.status,
          a.nama as nama_ao, n.nama as nama_nasabah
          FROM droping d 
          INNER JOIN pengajuan p ON d.noreg = p.noreg
          INNER JOIN account a ON p.ao = a.kd_ao 
          LEFT JOIN nasabah n ON p.niknas = n.nik 
          WHERE 1=1";

if (!empty($search)) {
    $query .= " AND (d.noreg LIKE '%$search%' OR n.nama LIKE '%$search%')";
}

if (!empty($status)) {
    $query .= " AND d.status LIKE '$status%'";
}

// Filter berdasarkan tahun dan bulan dari tgl_droping
if (!empty($year)) {
    $year = mysqli_real_escape_string($connect, $year);
    $query .= " AND d.tgl_droping LIKE '%-$year'";

    if (!empty($month)) {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $query .= " AND d.tgl_droping LIKE '%-$month-%'";
    }
}

$query .= " ORDER BY d.noreg DESC LIMIT $start, $limit";

// Eksekusi query dengan error handling
$result = mysqli_query($connect, $query);
if (!$result) {
    die("Error dalam query: " . mysqli_error($connect));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Dropping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <style>
        .table th,
        .table td {
            font-size: 0.9rem;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.5rem;
        }
    </style>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../../includes/navbar.php'; ?>
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h2>Data Dropping</h2>
                <a href="../index.php" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali
                </a>
            </div>

            <!-- Form Filter dan Pencarian -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="form-label">Cari</label>
                            <input type="text" class="form-control" id="search" name="search"
                                value="<?php echo htmlspecialchars($search); ?>"
                                placeholder="Noreg atau Nama Nasabah">
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status">
                                <option value="">Semua</option>
                                <option value="SIAP CAIR" <?php echo $status === 'SIAP CAIR' ? 'selected' : ''; ?>>SIAP CAIR</option>
                                <option value="CAIR" <?php echo $status === 'CAIR' ? 'selected' : ''; ?>>CAIR</option>
                                <option value="TOLAK" <?php echo $status === 'TOLAK' ? 'selected' : ''; ?>>TOLAK</option>
                                <option value="BATAL" <?php echo $status === 'BATAL' ? 'selected' : ''; ?>>BATAL</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="month" class="form-label">Bulan</label>
                            <select class="form-select" id="month" name="month">
                                <option value="">Semua</option>
                                <?php
                                for ($i = 1; $i <= 12; $i++) {
                                    $m = str_pad($i, 2, '0', STR_PAD_LEFT);
                                    $selected = $month === $m ? 'selected' : '';
                                    echo "<option value=\"$m\" $selected>" . date('F', mktime(0, 0, 0, $i, 1)) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="year" class="form-label">Tahun</label>
                            <select class="form-select" id="year" name="year">
                                <option value="">Semua</option>
                                <?php
                                $current_year = date('Y');
                                for ($y = $current_year; $y >= $current_year - 5; $y--) {
                                    $selected = $year === (string)$y ? 'selected' : '';
                                    echo "<option value=\"$y\" $selected>$y</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary me-2">
                                <i class="bi bi-search"></i> Cari
                            </button>
                            <a href="index.php" class="btn btn-secondary me-2">
                                <i class="bi bi-x-circle"></i> Reset
                            </a>
                            <a href="export-excel.php?<?php echo http_build_query([
                                                            'search' => $search,
                                                            'status' => $status,
                                                            'month' => $month,
                                                            'year' => $year
                                                        ]); ?>" class="btn btn-success">
                                <i class="bi bi-file-excel"></i> Export
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No. Registrasi</th>
                                    <th>No. BWM</th>
                                    <th>No. SPP</th>
                                    <th>No. STPK</th>
                                    <th>No. CIF</th>
                                    <th>No. Loan</th>
                                    <th>Tanggal Dropping</th>
                                    <th>Nama Nasabah</th>
                                    <th>AO</th>
                                    <th>Plafond</th>
                                    <th>Suku Bunga</th>
                                    <th>JW</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['noreg'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['nobwmk'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['nospp'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['nostpk'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['nocif'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['noloan'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['tgl_droping'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_nasabah'] ?? ''); ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_ao'] ?? ''); ?></td>
                                            <td>Rp <?php
                                                    $plafond = $row['plafond'] ?? 0;
                                                    echo number_format((float)$plafond, 0, ',', '.');
                                                    ?></td>
                                            <td><?php echo $row['sukbung'] ?? ''; ?>%</td>
                                            <td><?php echo $row['jw'] ?? ''; ?></td>
                                            <td>
                                                <?php
                                                $status_class = '';
                                                $status = strtoupper($row['status'] ?? '');

                                                if (strpos($status, 'TOLAK') !== false) {
                                                    $status_class = 'bg-danger text-white';
                                                } else if (strpos($status, 'BATAL') !== false) {
                                                    $status_class = 'bg-warning text-dark';
                                                } else if (strpos($status, 'DISETUJUI') !== false || strpos($status, 'ACC') !== false) {
                                                    $status_class = 'bg-success text-white';
                                                } else if (strpos($status, 'PROSES') !== false || strpos($status, 'SURVEY') !== false) {
                                                    $status_class = 'bg-info text-white';
                                                } else if (strpos($status, 'PENDING') !== false || strpos($status, 'TUNDA') !== false) {
                                                    $status_class = 'bg-secondary text-white';
                                                } else if (strpos($status, 'BARU') !== false || strpos($status, 'DAFTAR') !== false) {
                                                    $status_class = 'bg-primary text-white';
                                                } else {
                                                    $status_class = 'bg-light text-dark';
                                                }
                                                ?>
                                                <span class="badge <?php echo $status_class; ?> status-badge">
                                                    <?php echo htmlspecialchars($status); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="13" class="text-center">Tidak ada data yang ditemukan</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="d-flex justify-content-center mt-4">
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    <?php
                                    // Previous button
                                    if ($page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&month=<?php echo urlencode($month); ?>&year=<?php echo urlencode($year); ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif;

                                    // Calculate range for pagination buttons
                                    $start_page = max(1, min($page - 4, $total_pages - 9));
                                    $end_page = min($total_pages, $start_page + 9);

                                    // First page button if not in range
                                    if ($start_page > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=1&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&month=<?php echo urlencode($month); ?>&year=<?php echo urlencode($year); ?>">1</a>
                                        </li>
                                        <?php if ($start_page > 2): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif;
                                    endif;

                                    // Page numbers
                                    for ($i = $start_page; $i <= $end_page; $i++): ?>
                                        <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&month=<?php echo urlencode($month); ?>&year=<?php echo urlencode($year); ?>"><?php echo $i; ?></a>
                                        </li>
                                        <?php endfor;

                                    // Last page button if not in range
                                    if ($end_page < $total_pages):
                                        if ($end_page < $total_pages - 1): ?>
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        <?php endif; ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo $total_pages; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&month=<?php echo urlencode($month); ?>&year=<?php echo urlencode($year); ?>"><?php echo $total_pages; ?></a>
                                        </li>
                                    <?php endif;

                                    // Next button
                                    if ($page < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status); ?>&month=<?php echo urlencode($month); ?>&year=<?php echo urlencode($year); ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
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