<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ambil parameter pencarian
$search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
$where = '';
if (!empty($search)) {
    $where = "WHERE n.nik LIKE '%$search%' OR n.nama LIKE '%$search%'";
}

// Konfigurasi pagination
$limit = 100; // jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Query untuk total data
$total_query = mysqli_query($connect, "
    SELECT COUNT(DISTINCT n.nik) as total 
    FROM nasabah n 
    LEFT JOIN bpkb b ON b.nik = n.nik
    LEFT JOIN shm s ON s.nik = n.nik
    LEFT JOIN ajb a ON a.nik = n.nik
    LEFT JOIN kios k ON k.nik = n.nik
    LEFT JOIN bilyet bl ON bl.nik = n.nik
    LEFT JOIN manulife m ON m.nik = n.nik
    LEFT JOIN bpih bp ON bp.nik = n.nik
    LEFT JOIN spph sp ON sp.nik = n.nik
    $where
");
$total_data = mysqli_fetch_assoc($total_query)['total'];
$total_pages = ceil($total_data / $limit);

// Modify main query to include LIMIT
$query = mysqli_query($connect, "
    SELECT 
        n.nik,
        n.nama,
        n.almt,
        GROUP_CONCAT(
            DISTINCT 
            CASE 
                WHEN b.merk IS NOT NULL THEN CONCAT('BPKB:', b.merk)
                WHEN s.bukkep IS NOT NULL THEN CONCAT('SHM:', s.bukkep)
                WHEN a.bukkep IS NOT NULL THEN CONCAT('AJB:', a.bukkep)
                WHEN k.bukkep IS NOT NULL THEN CONCAT('KIOS:', k.bukkep)
                WHEN bl.jentabdep IS NOT NULL THEN CONCAT('BILYET:', bl.jentabdep)
                WHEN m.nojam IS NOT NULL THEN CONCAT('MANULIFE:', m.nojam)
                WHEN bp.noval IS NOT NULL THEN CONCAT('BPIH:', bp.noval)
                WHEN sp.nopor IS NOT NULL THEN CONCAT('SPPH:', sp.nopor)
            END
            SEPARATOR ','
        ) as jaminan_list
    FROM (
        SELECT nik, nama, almt 
        FROM nasabah 
        WHERE " . (!empty($search) ? "(nik LIKE '%$search%' OR nama LIKE '%$search%')" : "1=1") . "
        ORDER BY nama ASC 
        LIMIT $start, $limit
    ) n
    LEFT JOIN (SELECT nik, merk FROM bpkb) b ON b.nik = n.nik
    LEFT JOIN (SELECT nik, bukkep FROM shm) s ON s.nik = n.nik
    LEFT JOIN (SELECT nik, bukkep FROM ajb) a ON a.nik = n.nik
    LEFT JOIN (SELECT nik, bukkep FROM kios) k ON k.nik = n.nik
    LEFT JOIN (SELECT nik, jentabdep FROM bilyet) bl ON bl.nik = n.nik
    LEFT JOIN (SELECT nik, nojam FROM manulife) m ON m.nik = n.nik
    LEFT JOIN (SELECT nik, noval FROM bpih) bp ON bp.nik = n.nik
    LEFT JOIN (SELECT nik, nopor FROM spph) sp ON sp.nik = n.nik
    GROUP BY n.nik, n.nama, n.almt
");

// Check for query errors
if (!$query) {
    die("Error in query: " . mysqli_error($connect));
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Data Jaminan</title> <!-- Changed title -->
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
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Cek Jaminan Nasabah</h2>
                <a href="input-jaminan.php" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Tambah Jaminan
                </a>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4 class="card-title mb-0">Cek Jaminan Nasabah</h4>
                                <div class="col-md-4 d-flex gap-2">
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Cari NIK atau Nama..."
                                        value="<?php echo htmlspecialchars($search); ?>">
                                    <button class="btn btn-primary" id="searchButton">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Display section -->
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIK</th>
                                            <th>Nama</th>
                                            <th>Alamat</th>
                                            <th>Jaminan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        while ($row = mysqli_fetch_array($query)):
                                        ?>
                                            <tr>
                                                <td><?php echo $no++; ?></td>
                                                <td><?php echo htmlspecialchars($row['nik']); ?></td>
                                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                                <td><?php echo htmlspecialchars($row['almt']); ?></td>
                                                <td>
                                                    <?php
                                                    if (!empty($row['jaminan_list'])) {
                                                        $jaminan_array = explode(',', $row['jaminan_list']);
                                                        foreach ($jaminan_array as $jaminan) {
                                                            if (!empty($jaminan)) {
                                                                list($type, $value) = explode(':', $jaminan);
                                                                echo "<div class='d-inline-flex align-items-center me-2 mb-1'>";
                                                                echo "<span class='badge bg-success'>" . htmlspecialchars($jaminan) . "</span>";
                                                                // Modified button to match badge size
                                                                echo "<button class='badge bg-danger border-0 ms-1' style='cursor:pointer' onclick='deleteJaminan(\"{$row['nik']}\", \"$type\", \"$value\")'>";
                                                                echo "<i class='bi bi-trash'></i>";
                                                                echo "</button>";
                                                                echo "</div>";
                                                            }
                                                        }
                                                    } else {
                                                        echo "<span class='text-muted'>Tidak ada jaminan</span>";
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <?php if ($total_pages > 1): ?>
                                <nav aria-label="Page navigation" class="mt-4">
                                    <ul class="pagination justify-content-center">
                                        <!-- Previous Button -->
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>">Previous</a>
                                        </li>

                                        <?php
                                        // Calculate page range
                                        $start_number = max(1, min($page - 4, $total_pages - 9));
                                        $end_number = min($total_pages, $start_number + 9);

                                        // First page
                                        if ($start_number > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . urlencode($search) . '">1</a></li>';
                                            if ($start_number > 2) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                        }

                                        // Page numbers
                                        for ($i = $start_number; $i <= $end_number; $i++) {
                                            echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                            <a class="page-link" href="?page=' . $i . '&search=' . urlencode($search) . '">' . $i . '</a>
                                        </li>';
                                        }

                                        // Last page
                                        if ($end_number < $total_pages) {
                                            if ($end_number < $total_pages - 1) {
                                                echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . urlencode($search) . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>

                                        <!-- Next Button -->
                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
                            <?php endif; ?>

                            <!-- Data Info -->
                            <div class="text-center mt-2">
                                <small class="text-muted">
                                    Showing <?php echo $start + 1; ?> to <?php echo min($start + $limit, $total_data); ?>
                                    of <?php echo $total_data; ?> entries
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/script.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchButton = document.getElementById('searchButton');
            const searchInput = document.getElementById('searchInput');

            function performSearch() {
                const searchValue = searchInput.value;
                window.location.href = 'data-jaminan.php?search=' + encodeURIComponent(searchValue);
            }

            searchButton.addEventListener('click', performSearch);
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });
        });

        function deleteJaminan(nik, type, value) {
            Swal.fire({
                title: 'Konfirmasi Hapus',
                text: `Apakah Anda yakin ingin menghapus jaminan ${type}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Send delete request
                    fetch('delete-jaminan.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `nik=${encodeURIComponent(nik)}&type=${encodeURIComponent(type)}&value=${encodeURIComponent(value)}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire(
                                    'Terhapus!',
                                    'Jaminan berhasil dihapus.',
                                    'success'
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    data.message || 'Gagal menghapus jaminan.',
                                    'error'
                                );
                            }
                        })
                        .catch(error => {
                            Swal.fire(
                                'Error!',
                                'Terjadi kesalahan saat menghapus jaminan.',
                                'error'
                            );
                        });
                }
            });
        }
    </script>

</body>

</html>