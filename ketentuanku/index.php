<?php
session_start();
require_once __DIR__ . '/../config/init.php';
include '../../config.php';
include '../config/config.php';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ketentuanku</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <?php
    if (isset($_SESSION['success'])) {
        echo "<script>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: '" . $_SESSION['success'] . "',
                    showConfirmButton: false,
                    timer: 1500
                });
              </script>";
        unset($_SESSION['success']);
    }

    if (isset($_SESSION['error'])) {
        echo "<script>
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: '" . $_SESSION['error'] . "'
                });
              </script>";
        unset($_SESSION['error']);
    }
    ?>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Ketentuanku</h2>
                <?php if (isset($_SESSION['key_app']) && ($_SESSION['key_app'] == 'SKAI' || $_SESSION['key_app'] == 'ADMIN')) { ?>
                <button type="button" class="btn btn-primary" onclick="window.location.href='upload-ketentuan.php'">
                    <i class="bi bi-upload"></i> Upload Ketentuan
                </button>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Data Ketentuan</h5>
                                <div class="col-md-4 d-flex gap-2">
                                    <input type="text" id="searchInput" class="form-control"
                                        placeholder="Cari nomor atau judul...">
                                    <button class="btn btn-primary" id="searchButton">
                                        <i class="bi bi-search"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-compact">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Nomor Ketentuan</th>
                                            <th>Judul</th>
                                            <th>Tanggal Terbit</th>
                                            <th>File</th>
                                            <th>Pengupload</th>
                                            <th>Tanggal Upload</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                        $start = ($page - 1) * $limit;

                                        $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                        $where = "";

                                        if (!empty($search)) {
                                            $where = "WHERE nomor_ket LIKE '%$search%' OR judul_ket LIKE '%$search%'";
                                        }

                                        $query = "SELECT * FROM ketentuanku_surat $where ORDER BY ttl_terbit DESC LIMIT $start, $limit";
                                        $result = mysqli_query($connect, $query);

                                        $total_query = mysqli_query($connect, "SELECT COUNT(*) FROM ketentuanku_surat $where");
                                        $total_records = mysqli_fetch_array($total_query)[0];
                                        $total_pages = ceil($total_records / $limit);

                                        $no = $start + 1;
                                        while ($row = mysqli_fetch_array($result)) {
                                            echo "<tr>
                                                <td>$no</td>
                                                <td>{$row['nomor_ket']}</td>
                                                <td>{$row['judul_ket']}</td>
                                                <td>" . date('d-m-Y', strtotime($row['ttl_terbit'])) . "</td>
                                                <td><a href= '{$base_url}ketentuanku/uploads/{$row['file_surat']}' target='_blank' class='btn btn-sm btn-info'><i class='bi bi-file-earmark-text'></i></a></td>
                                                <td>{$row['nama_pengupload']}</td>
                                                <td>" . date('d-m-Y', strtotime($row['tgl_upload'])) . "</td>
                                                <td>";
                                            if (isset($_SESSION['key_app']) && ($_SESSION['key_app'] == 'SKAI' || $_SESSION['key_app'] == 'ADMIN')) {
                                                echo "<div class='d-flex gap-1'>
                                                        <a href='edit-ketentuan.php?id={$row['id']}' class='btn btn-sm btn-warning'>
                                                            <i class='bi bi-pencil'></i>
                                                        </a>
                                                        <button class='btn btn-sm btn-danger' onclick='confirmDelete({$row['id']}, \"{$row['file_surat']}\")'>
                                                            <i class='bi bi-trash'></i>
                                                        </button>
                                                      </div>";
                                            } else {
                                                echo "<div class='d-flex gap-1'>
                                                        <button class='btn btn-sm btn-warning' disabled>
                                                            <i class='bi bi-pencil'></i>
                                                        </button>
                                                        <button class='btn btn-sm btn-danger' disabled>
                                                            <i class='bi bi-trash'></i>
                                                        </button>
                                                      </div>";
                                            }
                                            echo "</td>
                                            </tr>";
                                            $no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>

                                <!-- Pagination -->
                                <nav aria-label="Page navigation">
                                    <ul class="pagination justify-content-center">
                                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="?page=<?php echo $page - 1; ?>&search=<?php echo $search; ?>"
                                                tabindex="-1">Previous</a>
                                        </li>

                                        <?php
                                        $start_number = max(1, min($page - 4, $total_pages - 9));
                                        $end_number = min($total_pages, $start_number + 9);

                                        if ($start_number > 1) {
                                            echo '<li class="page-item"><a class="page-link" href="?page=1&search=' . $search . '">1</a></li>';
                                            if ($start_number > 2) {
                                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                            }
                                        }

                                        for ($i = $start_number; $i <= $end_number; $i++) {
                                            echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                                    <a class="page-link" href="?page=' . $i . '&search=' . $search . '">' . $i . '</a>
                                                  </li>';
                                        }

                                        if ($end_number < $total_pages) {
                                            if ($end_number < $total_pages - 1) {
                                                echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                            }
                                            echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '&search=' . $search . '">' . $total_pages . '</a></li>';
                                        }
                                        ?>

                                        <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                            <a class="page-link"
                                                href="?page=<?php echo $page + 1; ?>&search=<?php echo $search; ?>">Next</a>
                                        </li>
                                    </ul>
                                </nav>
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
    document.addEventListener('DOMContentLoaded', function() {
        const searchButton = document.getElementById('searchButton');
        const searchInput = document.getElementById('searchInput');

        if (searchButton && searchInput) {
            function performSearch() {
                let searchValue = searchInput.value;
                window.location.href = 'index.php?search=' + encodeURIComponent(searchValue);
            }

            searchButton.addEventListener('click', performSearch);

            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            let urlParams = new URLSearchParams(window.location.search);
            let searchValue = urlParams.get('search');
            if (searchValue) {
                searchInput.value = searchValue;
            }
        }
    });

    function confirmDelete(id, fileName) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data yang dihapus tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete-ketentuan.php?id=${id}&file=${fileName}`;
            }
        });
    }
    </script>

</body>

</html>