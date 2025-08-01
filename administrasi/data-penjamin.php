<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Handle AJAX request untuk update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nikpenj = mysqli_real_escape_string($connect, $_POST['nikpenj']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    $query = "UPDATE penjamin SET status = '$status' WHERE nikpenj = '$nikpenj'";

    if (mysqli_query($connect, $query)) {
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => mysqli_error($connect)]);
        exit;
    }
}

// Ambil data droping berdasarkan noreg
$noreg = $_GET['noreg'];
$query = mysqli_query($connect, "SELECT d.*, p.niknas, n.nama as nama_nasabah 
    FROM droping d 
    LEFT JOIN pengajuan p ON p.noreg = d.noreg
    LEFT JOIN nasabah n ON n.nik = p.niknas
    WHERE d.noreg='$noreg'");
$data = mysqli_fetch_array($query);

// Query untuk mengambil data penjamin berdasarkan niknas dari pengajuan
$query_penjamin = mysqli_query($connect, "SELECT p.* 
    FROM penjamin p
    INNER JOIN pengajuan pj ON p.niknas = pj.niknas
    WHERE pj.noreg = '$noreg'");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Droping - <?php echo $noreg; ?></title>
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
                            <h4 class="card-title mb-4">Data Penjamin</h4>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIK Penjamin</th>
                                            <th>Nama</th>
                                            <th>Hubungan</th>
                                            <th>Tempat, Tanggal Lahir</th>
                                            <th>Pekerjaan</th>
                                            <th>No. Telepon</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        while ($row = mysqli_fetch_array($query_penjamin)) {
                                            $isLocked = $row['status'] == $noreg;
                                            $telepon = $row['tlfn1'];
                                            if (!empty($row['tlfn2'])) {
                                                $telepon .= " / " . $row['tlfn2'];
                                            }
                                            echo "<tr>
                                                <td>$no</td>
                                                <td>{$row['nikpenj']}</td>
                                                <td><a href='#' class='text-decoration-none' onclick='showDetail(\"{$row['nikpenj']}\", \"{$row['niknas']}\", \"{$row['nama']}\", \"{$row['hub']}\", \"{$row['tmpt']}\", \"{$row['tgl']}\", \"{$row['usia']}\", \"{$row['almt']}\", \"{$row['kec']}\", \"{$row['kab']}\", \"{$row['pek']}\", \"{$row['tlfn1']}\", \"{$row['tlfn2']}\", \"{$row['suis']}\", \"{$row['nik_suis']}\", \"{$row['status']}\")'>{$row['nama']}</a></td>
                                                <td>{$row['hub']}</td>
                                                <td>{$row['tmpt']}, {$row['tgl']}</td>
                                                <td>{$row['pek']}</td>
                                                <td>{$telepon}</td>
                                                <td>{$row['status']}</td>
                                                <td class='d-flex justify-content-center'>
                                                    <button onclick=\"toggleLock('{$row['nikpenj']}', '$noreg', " . ($isLocked ? "'unlock'" : "'lock'") . ")\" 
                                                            class=\"btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . " me-1\">
                                                        <i class=\"bi bi-" . ($isLocked ? "unlock" : "lock") . "\"></i>
                                                    </button>
                                                    <a href=\"edit-penjamin.php?nikpenj={$row['nikpenj']}&noreg=$noreg\" 
                                                       class=\"btn btn-sm btn-warning\">
                                                        <i class=\"bi bi-pencil\"></i>
                                                    </a>
                                                </td>
                                            </tr>";
                                            $no++;
                                        }

                                        if (mysqli_num_rows($query_penjamin) == 0) {
                                            echo "<tr><td colspan='9' class='text-center'>Tidak ada data penjamin</td></tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Modal Detail -->
                            <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="detailModalLabel">Detail Data Penjamin</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <table class="table table-bordered">
                                                <tr>
                                                    <th width="30%">NIK Penjamin</th>
                                                    <td id="detail-nikpenj"></td>
                                                </tr>
                                                <tr>
                                                    <th>NIK Nasabah</th>
                                                    <td id="detail-niknas"></td>
                                                </tr>
                                                <tr>
                                                    <th>Nama</th>
                                                    <td id="detail-nama"></td>
                                                </tr>
                                                <tr>
                                                    <th>Hubungan</th>
                                                    <td id="detail-hub"></td>
                                                </tr>
                                                <tr>
                                                    <th>Tempat, Tanggal Lahir</th>
                                                    <td id="detail-ttl"></td>
                                                </tr>
                                                <tr>
                                                    <th>Usia</th>
                                                    <td id="detail-usia"></td>
                                                </tr>
                                                <tr>
                                                    <th>Alamat Lengkap</th>
                                                    <td id="detail-almt"></td>
                                                </tr>
                                                <tr>
                                                    <th>Kecamatan</th>
                                                    <td id="detail-kec"></td>
                                                </tr>
                                                <tr>
                                                    <th>Kabupaten</th>
                                                    <td id="detail-kab"></td>
                                                </tr>
                                                <tr>
                                                    <th>Pekerjaan</th>
                                                    <td id="detail-pek"></td>
                                                </tr>
                                                <tr>
                                                    <th>No. Telepon 1</th>
                                                    <td id="detail-tlfn1"></td>
                                                </tr>
                                                <tr>
                                                    <th>No. Telepon 2</th>
                                                    <td id="detail-tlfn2"></td>
                                                </tr>
                                                <tr>
                                                    <th>Status SUIS</th>
                                                    <td id="detail-suis"></td>
                                                </tr>
                                                <tr>
                                                    <th>NIK SUIS</th>
                                                    <td id="detail-nik-suis"></td>
                                                </tr>
                                                <tr>
                                                    <th>Status</th>
                                                    <td id="detail-status"></td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <script>
                                function showDetail(nikpenj, niknas, nama, hub, tmpt, tgl, usia, almt, kec, kab, pek, tlfn1,
                                    tlfn2, suis, nik_suis, status) {
                                    document.getElementById('detail-nikpenj').textContent = nikpenj;
                                    document.getElementById('detail-niknas').textContent = niknas;
                                    document.getElementById('detail-nama').textContent = nama;
                                    document.getElementById('detail-hub').textContent = hub;
                                    document.getElementById('detail-ttl').textContent = tmpt + ', ' + tgl;
                                    document.getElementById('detail-usia').textContent = usia;
                                    document.getElementById('detail-almt').textContent = almt;
                                    document.getElementById('detail-kec').textContent = kec;
                                    document.getElementById('detail-kab').textContent = kab;
                                    document.getElementById('detail-pek').textContent = pek;
                                    document.getElementById('detail-tlfn1').textContent = tlfn1;
                                    document.getElementById('detail-tlfn2').textContent = tlfn2;
                                    document.getElementById('detail-suis').textContent = suis;
                                    document.getElementById('detail-nik-suis').textContent = nik_suis;
                                    document.getElementById('detail-status').textContent = status;

                                    new bootstrap.Modal(document.getElementById('detailModal')).show();
                                }
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $base_url; ?>assets/js/date-input.js"></script>
    <script>
        function toggleLock(nikpenj, noreg, action) {
            let newStatus = action === 'lock' ? noreg : 'Tersedia';

            Swal.fire({
                title: 'Konfirmasi',
                text: action === 'lock' ?
                    'Apakah Anda yakin ingin mengunci penjamin ini?' : 'Apakah Anda yakin ingin membuka kunci penjamin ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('data-penjamin.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/x-www-form-urlencoded',
                            },
                            body: `nikpenj=${nikpenj}&status=${newStatus}`
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                Swal.fire({
                                    title: 'Berhasil!',
                                    text: 'Status penjamin berhasil diperbarui',
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    'Terjadi kesalahan saat memperbarui status',
                                    'error'
                                );
                            }
                        });
                }
            });
        }
    </script>
</body>

</html>