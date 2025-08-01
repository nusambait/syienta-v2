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
    <title>Monitoring Teller</title>
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
                <h2 class="mb-0">Monitoring Teller</h2>
                <?php if (isset($_SESSION['key_app']) && ($_SESSION['key_app'] == 'TELLER' || $_SESSION['key_app'] == 'CS' || $_SESSION['key_app'] == 'ADMIN' || $_SESSION['key_app'] == 'ADM')) { ?>
                    <button type="button" class="btn btn-primary" onclick="window.location.href='upload-m-teller.php'">
                        <i class="bi bi-plus-circle"></i> Tambah Data
                    </button>
                <?php } ?>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="card-title mb-0">Data Monitoring Teller</h5>
                                <div class="col-md-9 d-flex gap-2">
                                    <select id="kantorFilter" class="form-control">
                                        <option value="">Semua Kantor</option>
                                        <?php
                                        $query_kantor = "SELECT DISTINCT kantor FROM m_teller ORDER BY kantor";
                                        $result_kantor = mysqli_query($connect, $query_kantor);
                                        while ($row_kantor = mysqli_fetch_array($result_kantor)) {
                                            $selected = (isset($_GET['kantor']) && $_GET['kantor'] == $row_kantor['kantor']) ? 'selected' : '';
                                            echo "<option value='" . $row_kantor['kantor'] . "' $selected>" . $row_kantor['kantor'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <select id="penguploadFilter" class="form-control">
                                        <option value="">Semua Penginput</option>
                                        <?php
                                        $query_uploader = "SELECT DISTINCT nama_pengupload FROM m_teller ORDER BY nama_pengupload";
                                        $result_uploader = mysqli_query($connect, $query_uploader);
                                        while ($row_uploader = mysqli_fetch_array($result_uploader)) {
                                            $selected = (isset($_GET['pengupload']) && $_GET['pengupload'] == $row_uploader['nama_pengupload']) ? 'selected' : '';
                                            echo "<option value='" . $row_uploader['nama_pengupload'] . "' $selected>" . $row_uploader['nama_pengupload'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                    <input type="date" id="startDate" class="form-control"
                                        value="<?php echo isset($_GET['start_date']) ? $_GET['start_date'] : ''; ?>"
                                        placeholder="Tanggal Mulai">
                                    <input type="date" id="endDate" class="form-control"
                                        value="<?php echo isset($_GET['end_date']) ? $_GET['end_date'] : ''; ?>"
                                        placeholder="Tanggal Akhir">
                                    <div class="input-group">
                                        <input type="text" id="searchInput" class="form-control"
                                            placeholder="Cari nomor loan atau nama..."
                                            value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                                        <button class="btn btn-primary" id="searchButton">
                                            <i class="bi bi-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered table-compact">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>No Loan</th>
                                            <th>Nama Nasabah</th>
                                            <th>File Ke</th>
                                            <th>Kategori</th>
                                            <th>Tanggal Upload</th>
                                            <th>Pengupload</th>
                                            <th>Kantor</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $limit = 10;
                                        $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                        $start = ($page - 1) * $limit;

                                        $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                        $kantor = isset($_GET['kantor']) ? mysqli_real_escape_string($connect, $_GET['kantor']) : '';
                                        $pengupload = isset($_GET['pengupload']) ? mysqli_real_escape_string($connect, $_GET['pengupload']) : '';
                                        $start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($connect, $_GET['start_date']) : '';
                                        $end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($connect, $_GET['end_date']) : '';

                                        $where = [];

                                        if (!empty($search)) {
                                            $where[] = "(no_loan LIKE '%$search%' OR nama_nasabah LIKE '%$search%')";
                                        }
                                        if (!empty($kantor)) {
                                            $where[] = "kantor = '$kantor'";
                                        }
                                        if (!empty($pengupload)) {
                                            $where[] = "nama_pengupload = '$pengupload'";
                                        }
                                        if (!empty($start_date) && !empty($end_date)) {
                                            $where[] = "DATE(tgl_upload) BETWEEN '$start_date' AND '$end_date'";
                                        } else if (!empty($start_date)) {
                                            $where[] = "DATE(tgl_upload) >= '$start_date'";
                                        } else if (!empty($end_date)) {
                                            $where[] = "DATE(tgl_upload) <= '$end_date'";
                                        }

                                        $where_clause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

                                        $query = "SELECT * FROM m_teller $where_clause ORDER BY tgl_upload DESC LIMIT $start, $limit";
                                        $result = mysqli_query($connect, $query);
                                        if (!$result) {
                                            die("Error dalam query: " . mysqli_error($connect));
                                        }

                                        $total_query = "SELECT COUNT(*) as total FROM m_teller $where_clause";
                                        $total_result = mysqli_query($connect, $total_query);
                                        if (!$total_result) {
                                            die("Error dalam query total: " . mysqli_error($connect));
                                        }
                                        $total_records = mysqli_fetch_assoc($total_result)['total'];
                                        $total_pages = ceil($total_records / $limit);

                                        $no = $start + 1;
                                        while ($row = mysqli_fetch_array($result)) {
                                            echo "<tr>
                                                <td>$no</td>
                                                <td>{$row['no_loan']}</td>
                                                <td>{$row['nama_nasabah']}</td>
                                                <td>{$row['file_ke']}</td>
                                                <td>{$row['kategori']}</td>
                                                <td>" . date('d-m-Y', strtotime($row['tgl_upload'])) . "</td>
                                                <td>{$row['nama_pengupload']}</td>
                                                <td>{$row['kantor']}</td>
                                                <td>
                                                    <div class='d-flex gap-1'>
                                                        <a href='uploads/{$row['file']}' 
                                                            class='btn btn-info btn-sm'
                                                            target='_blank'>
                                                            <i class='bi bi-file-earmark-text'></i>
                                                        </a>";
                                            if ($_SESSION['key_app'] == 'ADMIN' || $row['nama_pengupload'] == $_SESSION['nama']) {
                                                echo "<a href='#' 
                                                    class='btn btn-danger btn-sm'
                                                    onclick='confirmDelete({$row['id']})'>
                                                    <i class='bi bi-trash'></i> 
                                                    </a>";
                                            }
                                            echo "</div></td></tr>";
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
            const kantorFilter = document.getElementById('kantorFilter');
            const penguploadFilter = document.getElementById('penguploadFilter');
            const startDate = document.getElementById('startDate');
            const endDate = document.getElementById('endDate');

            function performSearch() {
                let searchValue = searchInput.value;
                let kantorValue = kantorFilter.value;
                let penguploadValue = penguploadFilter.value;
                let startDateValue = startDate.value;
                let endDateValue = endDate.value;

                let url = 'index.php?';
                let params = [];

                if (searchValue) params.push('search=' + encodeURIComponent(searchValue));
                if (kantorValue) params.push('kantor=' + encodeURIComponent(kantorValue));
                if (penguploadValue) params.push('pengupload=' + encodeURIComponent(penguploadValue));
                if (startDateValue) params.push('start_date=' + encodeURIComponent(startDateValue));
                if (endDateValue) params.push('end_date=' + encodeURIComponent(endDateValue));

                window.location.href = url + params.join('&');
            }

            // Event listeners untuk semua filter
            [searchButton, kantorFilter, penguploadFilter, startDate, endDate].forEach(element => {
                if (element) {
                    if (element === searchButton) {
                        element.addEventListener('click', performSearch);
                    } else {
                        element.addEventListener('change', performSearch);
                    }
                }
            });

            // Enter key pada search input
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        performSearch();
                    }
                });
            }

            // Set nilai filter dari URL
            let urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('search')) searchInput.value = urlParams.get('search');
            if (urlParams.get('kantor')) kantorFilter.value = urlParams.get('kantor');
            if (urlParams.get('pengupload')) penguploadFilter.value = urlParams.get('pengupload');
            if (urlParams.get('start_date')) startDate.value = urlParams.get('start_date');
            if (urlParams.get('end_date')) endDate.value = urlParams.get('end_date');
        });

        function confirmDelete(id) {
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
                    window.location.href = `delete-m-teller.php?id=${id}`;
                }
            });
            return false;
        }
    </script>

</body>

</html>