<?php
session_start();
include '../../config.php';
include '../config/config.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($connect, "SELECT * FROM account WHERE username='$username' AND password='$password'");
    $data = mysqli_fetch_array($query);

    if (mysqli_num_rows($query) > 0) {
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = $data['role_id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['key_app'] = $data['key_app'];
        $_SESSION['foto'] = $data['foto'];

        header("Location: ../dashboard.php");
    } else {
        echo "<script>alert('Username atau password salah!');</script>";
    }
}

// Proses form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['login'])) {
    $nik = mysqli_real_escape_string($connect, $_POST['nik']);

    // Cek apakah NIK sudah ada di database
    $check_nik = mysqli_query($connect, "SELECT nik FROM nasabah WHERE nik='$nik'");
    if (mysqli_num_rows($check_nik) > 0) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'NIK/NPWP sudah terdaftar dalam sistem!'
            });
        </script>";
        exit;
    }

    $nama = mysqli_real_escape_string($connect, $_POST['nama']);
    $tmpt = mysqli_real_escape_string($connect, $_POST['tmpt']);
    $tgl_input = mysqli_real_escape_string($connect, $_POST['tgl']);
    $tgl = date('d-m-Y', strtotime($tgl_input));
    $usia = mysqli_real_escape_string($connect, $_POST['usia']);
    $jk = mysqli_real_escape_string($connect, $_POST['jk']);
    $almt = mysqli_real_escape_string($connect, $_POST['almt']);
    $kec = mysqli_real_escape_string($connect, $_POST['kec']);
    $kab = mysqli_real_escape_string($connect, $_POST['kab']);
    $status = mysqli_real_escape_string($connect, $_POST['status']);
    $pend = mysqli_real_escape_string($connect, $_POST['pend']);
    $pek = mysqli_real_escape_string($connect, $_POST['pek']);
    $ttktp = mysqli_real_escape_string($connect, $_POST['ttktp_formatted']);
    $mb = mysqli_real_escape_string($connect, $_POST['mb']);
    $ibu = mysqli_real_escape_string($connect, $_POST['ibu']);
    $tlfn1 = mysqli_real_escape_string($connect, $_POST['tlfn1']);
    $tlfn2 = mysqli_real_escape_string($connect, $_POST['tlfn2']);
    $jns = mysqli_real_escape_string($connect, $_POST['jns']);
    $jmlh = mysqli_real_escape_string($connect, $_POST['jmlh']);
    $anak = mysqli_real_escape_string($connect, $_POST['anak']);

    // Validasi nomor telepon
    $tlfn1 = empty($tlfn1) ? '+62' : $tlfn1;
    $tlfn2 = empty($tlfn2) ? '+62' : $tlfn2;

    $query = "INSERT INTO nasabah (nik, nama, tmpt, tgl, usia, jk, almt, kec, kab, status, pend, pek, ttktp, mb, ibu, tlfn1, tlfn2, jns, jmlh, anak) 
              VALUES ('$nik', '$nama', '$tmpt', '$tgl', '$usia', '$jk', '$almt', '$kec', '$kab', '$status', '$pend', '$pek', '$ttktp', '$mb', '$ibu', '$tlfn1', '$tlfn2', '$jns', '$jmlh', '$anak')";

    if (mysqli_query($connect, $query)) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: 'Data nasabah berhasil disimpan!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(function() {
                    window.location.href='input-nasabah.php';
                });
            });
        </script>";
    } else {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Error: " . mysqli_error($connect) . "'
                });
            });
        </script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Input Data Nasabah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Nunito:wght@400;600;700&display=swap"
        rel="stylesheet">
    <link href="<?php echo $base_url; ?>assets/css/style_main.css" rel="stylesheet">
    <!-- Tambahkan SweetAlert2 CSS dan JS -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include '../includes/navbar.php'; ?>
        <div class="container-fluid">
            <h2 class="mb-4">Input Data Nasabah / Perusahaan</h2>

            <!-- Tambahkan bagian list data nasabah -->
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Data Nasabah</h5>
                        <div class="col-md-4 d-flex gap-2">
                            <input type="text" id="searchInput" class="form-control"
                                placeholder="Cari NIK atau Nama...">
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
                                    <th>NIK/NPWP</th>
                                    <th>Nama</th>
                                    <th>Alamat</th>
                                    <th>No. Telepon</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                                <?php
                                // Konfigurasi pagination
                                $limit = 5;
                                $page = isset($_GET['page']) ? $_GET['page'] : 1;
                                $start = ($page - 1) * $limit;

                                // Tambahkan kondisi pencarian
                                $search = isset($_GET['search']) ? mysqli_real_escape_string($connect, $_GET['search']) : '';
                                $where = '';
                                if (!empty($search)) {
                                    $where = "WHERE nik LIKE '%$search%' OR nama LIKE '%$search%'";
                                }

                                // Query dengan kondisi pencarian
                                $query = mysqli_query($connect, "SELECT * FROM nasabah $where ORDER BY nik DESC LIMIT $start, $limit");

                                // Query untuk total data dengan kondisi pencarian
                                $total_records = mysqli_fetch_array(mysqli_query($connect, "SELECT COUNT(*) FROM nasabah $where"))[0];
                                $total_pages = ceil($total_records / $limit);

                                $no = $start + 1;
                                while ($row = mysqli_fetch_array($query)) {
                                    echo "<tr>
                                        <td>$no</td>
                                        <td>{$row['nik']}</td>
                                        <td>{$row['nama']}</td>
                                        <td>{$row['almt']}</td>
                                        <td>{$row['tlfn1']}</td>
                                        <td>
                                            <button type='button' class='btn btn-sm btn-warning' onclick='editNasabah(\"{$row['nik']}\")'>
                                                <i class='bi bi-pencil'></i>
                                            </button>
                                            <button type='button' class='btn btn-sm btn-danger' onclick='deleteNasabah(\"{$row['nik']}\")'>
                                                <i class='bi bi-trash'></i>
                                            </button>
                                        </td>
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
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>"
                                        tabindex="-1">Previous</a>
                                </li>

                                <?php
                                $start_number = max(1, min($page - 4, $total_pages - 9));
                                $end_number = min($total_pages, $start_number + 9);

                                if ($start_number > 1) {
                                    echo '<li class="page-item"><a class="page-link" href="?page=1">1</a></li>';
                                    if ($start_number > 2) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                }

                                for ($i = $start_number; $i <= $end_number; $i++) {
                                    echo '<li class="page-item ' . ($page == $i ? 'active' : '') . '">
                                            <a class="page-link" href="?page=' . $i . '">' . $i . '</a>
                                          </li>';
                                }

                                if ($end_number < $total_pages) {
                                    if ($end_number < $total_pages - 1) {
                                        echo '<li class="page-item disabled"><a class="page-link">...</a></li>';
                                    }
                                    echo '<li class="page-item"><a class="page-link" href="?page=' . $total_pages . '">' . $total_pages . '</a></li>';
                                }
                                ?>

                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>

            <div class="card mb-4 mt-3">
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIK/NPWP Perusahaan</label>
                                    <input type="text" class="form-control" name="nik" id="nik" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tempat Lahir</label>
                                    <input type="text" class="form-control" name="tmpt" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Lahir</label>
                                    <input type="date" class="form-control" name="tgl" required
                                        onchange="hitungUsia(this.value);" data-date="">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usia</label>
                                    <input type="text" class="form-control" name="usia" readonly required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Kelamin</label>
                                    <select class="form-select" name="jk" required>
                                        <option value="L">Laki-laki</option>
                                        <option value="P">Perempuan</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Alamat</label>
                                    <textarea class="form-control" name="almt" required></textarea>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kabupaten/Kota</label>
                                    <select class="form-select" name="kab" id="kabupaten" required>
                                        <option value="">Pilih Kabupaten/Kota</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kecamatan</label>
                                    <select class="form-select" name="kec" id="kecamatan" required>
                                        <option value="">Pilih Kecamatan</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Status Pernikahan</label>
                                    <select class="form-select" name="status" required>
                                        <option value="Belum Menikah">Belum Menikah</option>
                                        <option value="Menikah">Menikah</option>
                                        <option value="Cerai Hidup">Cerai Hidup</option>
                                        <option value="Cerai Mati">Cerai Mati</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Pendidikan Terakhir</label>
                                    <select class="form-select" name="pend" required>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                        <option value="SD">SD</option>
                                        <option value="SMP">SMP</option>
                                        <option value="SMA">SMA</option>
                                        <option value="SMK">SMK</option>
                                        <option value="D1">D1</option>
                                        <option value="D2">D2</option>
                                        <option value="D3">D3</option>
                                        <option value="D4">D4</option>
                                        <option value="S1">S1</option>
                                        <option value="S2">S2</option>
                                        <option value="S3">S3</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Pekerjaan</label>
                                    <select class="form-select" name="pek" required>
                                        <option value="">Pilih Pekerjaan</option>
                                        <option value="PNS">PNS</option>
                                        <option value="TNI/POLRI">TNI/POLRI</option>
                                        <option value="Pegawai Swasta">Pegawai Swasta</option>
                                        <option value="Wiraswasta">Wiraswasta</option>
                                        <option value="Petani">Petani</option>
                                        <option value="Nelayan">Nelayan</option>
                                        <option value="Buruh">Buruh</option>
                                        <option value="Guru">Guru</option>
                                        <option value="Dosen">Dosen</option>
                                        <option value="Dokter">Dokter</option>
                                        <option value="Perawat">Perawat</option>
                                        <option value="Pedagang">Pedagang</option>
                                        <option value="Pengacara">Pengacara</option>
                                        <option value="Notaris">Notaris</option>
                                        <option value="Arsitek">Arsitek</option>
                                        <option value="Akuntan">Akuntan</option>
                                        <option value="Konsultan">Konsultan</option>
                                        <option value="Freelancer">Freelancer</option>
                                        <option value="Ibu Rumah Tangga">Ibu Rumah Tangga</option>
                                        <option value="Pensiunan">Pensiunan</option>
                                        <option value="Lainnya">Lainnya</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Tanggal Pembuatan KTP</label>
                                    <input type="date" class="form-control" name="ttktp" required
                                        onchange="formatTanggalKTP(this.value);">
                                    <input type="hidden" name="ttktp_formatted" id="ttktp_formatted">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Masa Berlaku KTP</label>
                                    <input type="text" class="form-control" name="mb" value="SEUMUR HIDUP" readonly
                                        required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nama Ibu Kandung</label>
                                    <input type="text" class="form-control" name="ibu" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon 1</label>
                                    <input type="text" class="form-control" name="tlfn1" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon 2</label>
                                    <input type="text" class="form-control" name="tlfn2">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Jenis Tanggungan</label>
                                    <select class="form-select" name="jns" id="jenisTanggungan"
                                        onchange="toggleTanggungan()" required>
                                        <option value="Tidak Ada">Tidak Ada</option>
                                        <option value="Istri">Istri</option>
                                        <option value="Suami">Suami</option>
                                        <option value="Anak">Anak</option>
                                    </select>
                                </div>
                                <div class="mb-3" id="jmlhDiv" style="display: none;">
                                    <label class="form-label">Jumlah Tanggungan</label>
                                    <input type="number" class="form-control" name="jmlh" id="jmlh" value="0">
                                </div>
                                <div class="mb-3" id="anakDiv" style="display: none;">
                                    <label class="form-label">Jumlah Anak</label>
                                    <input type="number" class="form-control" name="anak" id="anak" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
    }

    // Close sidebar when clicking outside
    document.addEventListener('click', function(event) {
        const sidebar = document.getElementById('sidebar');
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');

        if (!sidebar.contains(event.target) && !mobileMenuBtn.contains(event.target)) {
            sidebar.classList.remove('show');
        }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 767) {
            document.getElementById('sidebar').classList.remove('show');
        }
    });

    // Tambahkan fungsi untuk cek NIK real-time
    document.getElementById('nik').addEventListener('input', function() {
        let nik = this.value;
        if (nik.length > 0) {
            fetch('check-nik.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'nik=' + encodeURIComponent(nik)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'NIK/NPWP sudah terdaftar dalam sistem!'
                        });
                        this.value = ''; // Mengosongkan input
                    }
                });
        }
    });

    function hitungUsia(tglLahir) {
        const today = new Date();
        const birthDate = new Date(tglLahir);
        let usia = today.getFullYear() - birthDate.getFullYear();
        const m = today.getMonth() - birthDate.getMonth();

        if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) {
            usia--;
        }

        // Pastikan input usia ada dan nilai valid sebelum diset
        const usiaInput = document.querySelector('input[name="usia"]');
        if (usiaInput && !isNaN(usia)) {
            usiaInput.value = usia;
        }
    }

    // Tambahkan event listener saat dokumen dimuat
    document.addEventListener('DOMContentLoaded', function() {
        const tglInput = document.querySelector('input[name="tgl"]');
        if (tglInput) {
            tglInput.addEventListener('change', function() {
                hitungUsia(this.value);
            });
        }
    });

    // Fungsi untuk mengambil data kabupaten
    function getKabupaten() {
        fetch('https://www.emsifa.com/api-wilayah-indonesia/api/regencies/32.json')
            .then(response => response.json())
            .then(kabupaten => {
                let kabupatenSelect = document.getElementById('kabupaten');
                kabupatenSelect.innerHTML = '<option value="">Pilih Kabupaten/Kota</option>';

                kabupaten.forEach(kab => {
                    let option = document.createElement('option');
                    option.value = kab.name;
                    option.textContent = kab.name;
                    option.setAttribute('data-id', kab.id);
                    kabupatenSelect.appendChild(option);
                });
            });
    }

    // Fungsi untuk mengambil data kecamatan berdasarkan kabupaten
    function getKecamatan(kabupatenId) {
        fetch(`https://www.emsifa.com/api-wilayah-indonesia/api/districts/${kabupatenId}.json`)
            .then(response => response.json())
            .then(kecamatan => {
                let kecamatanSelect = document.getElementById('kecamatan');
                kecamatanSelect.innerHTML = '<option value="">Pilih Kecamatan</option>';

                kecamatan.forEach(kec => {
                    let option = document.createElement('option');
                    option.value = kec.name;
                    option.textContent = kec.name;
                    kecamatanSelect.appendChild(option);
                });
            });
    }

    // Load kabupaten saat halaman dimuat
    document.addEventListener('DOMContentLoaded', getKabupaten);

    // Event listener untuk perubahan kabupaten
    document.getElementById('kabupaten').addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            const kabupatenId = selectedOption.getAttribute('data-id');
            getKecamatan(kabupatenId);
        }
    });

    function formatTanggalKTP(tanggal) {
        const date = new Date(tanggal);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        const formatted = `${day}-${month}-${year}`;
        document.getElementById('ttktp_formatted').value = formatted;
    }

    function toggleTanggungan() {
        const jenisTanggungan = document.getElementById('jenisTanggungan').value;
        const jmlhDiv = document.getElementById('jmlhDiv');
        const anakDiv = document.getElementById('anakDiv');
        const jmlhInput = document.getElementById('jmlh');
        const anakInput = document.getElementById('anak');

        if (jenisTanggungan === 'Tidak Ada') {
            jmlhDiv.style.display = 'none';
            anakDiv.style.display = 'none';
            jmlhInput.value = '0';
            anakInput.value = '0';
        } else {
            jmlhDiv.style.display = 'block';
            anakDiv.style.display = 'block';
            jmlhInput.required = true;
            anakInput.required = true;
        }
    }

    // Ganti fungsi pencarian dengan tombol
    document.getElementById('searchButton').addEventListener('click', function() {
        let searchValue = document.getElementById('searchInput').value;
        let currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('search', searchValue);
        currentUrl.searchParams.set('page', '1'); // Reset ke halaman pertama
        window.location.href = currentUrl.toString();
    });

    // Tambahkan event listener untuk tombol Enter pada input
    document.getElementById('searchInput').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('searchButton').click();
        }
    });

    // Isi nilai pencarian dari parameter URL
    document.addEventListener('DOMContentLoaded', function() {
        let urlParams = new URLSearchParams(window.location.search);
        let searchValue = urlParams.get('search');
        if (searchValue) {
            document.getElementById('searchInput').value = searchValue;
        }
    });

    // Fungsi hapus nasabah
    function deleteNasabah(nik) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data nasabah akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `delete-nasabah.php?nik=${nik}`;
            }
        });
    }

    // Fungsi edit nasabah
    function editNasabah(nik) {
        window.location.href = `edit-nasabah.php?nik=${nik}`;
    }
    </script>
</body>

</html>