<?php
session_start();
include '../../config.php';
include '../config/config.php';

// Handle AJAX request untuk update status
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nikpend = mysqli_real_escape_string($connect, $_POST['nikpend']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);

    $query = "UPDATE pendamping SET status = '$status' WHERE nikpend = '$nikpend'";

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

// Query untuk mengambil data pendamping berdasarkan niknas dari pengajuan
$query_pendamping = mysqli_query($connect, "SELECT p.* 
    FROM pendamping p
    INNER JOIN pengajuan pj ON p.niknas = pj.niknas
    WHERE pj.noreg = '$noreg'");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendamping - <?php echo $data['noreg']; ?></title>
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
                            <h4 class="card-title mb-4">Data Pendamping</h4>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>No</th>
                                            <th>NIK Pendamping</th>
                                            <th>Nama</th>
                                            <th>Hubungan</th>
                                            <th>Tempat, Tanggal Lahir</th>
                                            <th>Pekerjaan</th>
                                            <th>No. Telepon 1</th>
                                            <th>No. Telepon 2</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        while ($row = mysqli_fetch_array($query_pendamping)) {
                                            $isLocked = $row['status'] == $noreg;
                                            echo "<tr>
                                                <td>$no</td>
                                                <td>{$row['nikpend']}</td>
                                                <td>{$row['nama']}</td>
                                                <td>{$row['hub']}</td>
                                                <td>{$row['tmpt']}, {$row['tgl']}</td>
                                                <td>{$row['pek']}</td>
                                                <td>{$row['tlfn1']}</td>
                                                <td>{$row['tlfn2']}</td>
                                                <td>{$row['status']}</td>
                                                <td class='d-flex justify-content-center gap-1'>
                                                <button onclick=\"toggleLock('{$row['nikpend']}', '$noreg', " . ($isLocked ? "'unlock'" : "'lock'") . ")\" 
                                                        class=\"btn btn-sm " . ($isLocked ? "btn-success" : "btn-danger") . "\">
                                                    <i class=\"bi bi-" . ($isLocked ? "unlock" : "lock") . "\"></i>
                                                </button>
                                                    <a href=\"edit-pendamping.php?nikpend={$row['nikpend']}&noreg={$noreg}\" 
                                                       class=\"btn btn-sm btn-warning\">
                                                        <i class=\"bi bi-pencil\"></i>
                                                    </a>
                                                </td>
                                            </tr>";
                                            $no++;
                                        }

                                        if (mysqli_num_rows($query_pendamping) == 0) {
                                            echo "<tr><td colspan='10' class='text-center'>Tidak ada data pendamping</td></tr>";
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
    function toggleLock(nikpend, noreg, action) {
        let newStatus = action === 'lock' ? noreg : 'Tersedia';

        Swal.fire({
            title: 'Konfirmasi',
            text: action === 'lock' ?
                'Apakah Anda yakin ingin mengunci pendamping ini?' :
                'Apakah Anda yakin ingin membuka kunci pendamping ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Kirim request AJAX ke file yang sama
                fetch('data-pendamping.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `nikpend=${nikpend}&status=${newStatus}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'Status pendamping berhasil diperbarui',
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