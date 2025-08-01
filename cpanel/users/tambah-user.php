<?php
session_start();
include '../../../config.php';
include '../../config/config.php';
include '../../includes/check-admin.php';

// Ambil data jabatan dari database
$query_jabatan = mysqli_query($connect, "SELECT DISTINCT jabatan, key_app FROM account WHERE jabatan IS NOT NULL ORDER BY jabatan ASC");
$jabatan_data = [];
while ($row = mysqli_fetch_assoc($query_jabatan)) {
    $jabatan_data[$row['jabatan']] = $row['key_app'];
}

// Array data kantor
$kantor_list = [
    '100' => 'Pusat',
    '200' => 'Ciparay',
    '300' => 'Garut',
    '400' => 'Situraja',
    '500' => 'Soreang',
    '600' => 'Bandung'
];

// Fungsi untuk generate NIK
function generateNIK($connect)
{
    do {
        // Generate random number untuk 4 digit pertama
        $first = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        // Generate random number untuk 3 digit kedua
        $second = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
        // Format NIK
        $nik = $first . "." . $second . ".TJS";

        // Cek apakah NIK sudah ada di database
        $check = mysqli_query($connect, "SELECT nik FROM account WHERE nik = '$nik'");
    } while (mysqli_num_rows($check) > 0); // Ulangi jika NIK sudah ada

    return $nik;
}

// Fungsi untuk generate Kode AO
function generateKodeAO($connect)
{
    do {
        // Generate random number 3 digit
        $kode_ao = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        // Cek apakah kode AO sudah ada di database
        $check = mysqli_query($connect, "SELECT kd_ao FROM account WHERE kd_ao = '$kode_ao'");
    } while (mysqli_num_rows($check) > 0); // Ulangi jika kode sudah ada

    return $kode_ao;
}

// Fungsi untuk generate ID
function generateID($connect)
{
    do {
        // Generate random number 3 digit
        $id = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

        // Cek apakah ID sudah ada di database
        $check = mysqli_query($connect, "SELECT id FROM account WHERE id = '$id'");
    } while (mysqli_num_rows($check) > 0); // Ulangi jika ID sudah ada

    return $id;
}

// Generate NIK saat halaman dimuat
$generated_nik = generateNIK($connect);

// Generate Kode AO saat halaman dimuat
$generated_kode_ao = generateKodeAO($connect);

// ID menggunakan value yang sama dengan kode AO
$generated_id = $generated_kode_ao;

// Proses tambah user
if (isset($_POST['submit'])) {
    $id = mysqli_real_escape_string($connect, $_POST['id']);
    $nik = mysqli_real_escape_string($connect, $_POST['nik']);
    $username = mysqli_real_escape_string($connect, $_POST['username']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);
    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $jabatan = mysqli_real_escape_string($connect, $_POST['jabatan']);
    $kantor = mysqli_real_escape_string($connect, $_POST['kantor']);
    $kd_ao = mysqli_real_escape_string($connect, $_POST['kd_ao']);
    $key_app = mysqli_real_escape_string($connect, $_POST['key_app']);
    $role_id = mysqli_real_escape_string($connect, $_POST['role_id']);
    $status = 'AKTIF';
    $kas = '-';
    $log = date('Y-m-d H:i:s'); // Menambahkan timestamp untuk log

    // Upload foto
    $foto = null; // Set default value ke null
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            // Format nama file: NIK-TAHUN-BULAN-TANGGAL-JAM-MENIT-DETIK
            $timestamp = date('Y-m-d-H-i-s');
            $new_filename = $nik . '-' . $timestamp . '.' . $ext;
            $upload_path = "../../assets/media/profile/" . $new_filename;

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
                $foto = $new_filename;
            }
        }
    }

    $query = mysqli_query($connect, "INSERT INTO account (id, nik, username, password, nama, jabatan, kantor, kd_ao, key_app, role_id, foto, status, kas, log) 
                                   VALUES ('$id', '$nik', '$username', '$password', '$nama', '$jabatan', '$kantor', '$kd_ao', '$key_app', '$role_id', '$foto', '$status', '$kas', '$log')");

    if ($query) {
        $_SESSION['success_message'] = "Data user berhasil ditambahkan";
        header("Location: index.php");
        exit();
    } else {
        $error_message = "Gagal menambahkan user: " . mysqli_error($connect);
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../../includes/navbar.php'; ?>

        <div class="container-fluid">
            <div class="row mb-3">
                <div class="col">
                    <h2>Tambah User</h2>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="row mb-3">
                            <!-- Kolom Kiri -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">ID (Kode FO)</label>
                                    <input type="text" class="form-control" name="id"
                                        value="<?php echo $generated_id; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">NIK</label>
                                    <input type="text" class="form-control" id="nik" name="nik"
                                        value="<?php echo $generated_nik; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" readonly
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Password</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Role</label>
                                    <select class="form-select" name="role_id" required>
                                        <option value="">Pilih Role</option>
                                        <option value="1">Super Admin</option>
                                        <option value="2">Staff</option>
                                        <option value="3">Kabid</option>
                                        <option value="4">Supervisor</option>
                                        <option value="5">Notaris</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Kolom Kanan -->
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Jabatan</label>
                                    <select class="form-select" name="jabatan" id="jabatan" required
                                        onchange="updateKeyApp()">
                                        <option value="">Pilih Jabatan</option>
                                        <?php foreach ($jabatan_data as $jabatan => $key_app) { ?>
                                        <option value="<?php echo htmlspecialchars($jabatan); ?>"
                                            data-keyapp="<?php echo htmlspecialchars($key_app); ?>">
                                            <?php echo htmlspecialchars($jabatan); ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Kantor</label>
                                    <select class="form-select" name="kantor" required>
                                        <option value="">Pilih Kantor</option>
                                        <?php foreach ($kantor_list as $kode => $nama_kantor) { ?>
                                        <option value="<?php echo $kode; ?>">
                                            <?php echo $kode . ' - ' . $nama_kantor; ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Kode AO</label>
                                    <input type="text" class="form-control" name="kd_ao"
                                        value="<?php echo $generated_kode_ao; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Key App</label>
                                    <input type="text" class="form-control" id="key_app" name="key_app" readonly
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Foto</label>
                                    <input type="file" class="form-control" name="foto" accept="image/*">
                                    <small class="text-muted">Format: jpg, jpeg, png (Opsional)</small>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <a href="index.php" class="btn btn-secondary me-2"><i class="bi bi-arrow-left"></i>
                                Kembali</a>
                            <button type="submit" name="submit" class="btn btn-primary"><i class="bi bi-save"></i>
                                Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // Set username otomatis saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        var nik = document.getElementById('nik').value;
        document.getElementById('username').value = nik;
    });

    function updateKeyApp() {
        var jabatanSelect = document.getElementById('jabatan');
        var keyAppInput = document.getElementById('key_app');
        var selectedOption = jabatanSelect.options[jabatanSelect.selectedIndex];

        if (selectedOption.value !== '') {
            keyAppInput.value = selectedOption.getAttribute('data-keyapp');
        } else {
            keyAppInput.value = '';
        }
    }

    // Set key_app saat halaman pertama kali dimuat
    document.addEventListener('DOMContentLoaded', function() {
        updateKeyApp();
    });

    <?php if (isset($error_message)): ?>
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '<?php echo $error_message; ?>',
        showConfirmButton: true
    });
    <?php endif; ?>
    </script>
</body>

</html>