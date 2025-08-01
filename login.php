<?php
session_start();
include '../config.php';

// Cek apakah user sudah login
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit();
}

$login_error = false;

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($connect, "SELECT a.*, k.kd_kantor 
                                   FROM account a 
                                   LEFT JOIN kantor k ON a.kantor = k.kd_kantor 
                                   WHERE a.username='$username' AND a.password='$password'");
    $data = mysqli_fetch_array($query);

    if (mysqli_num_rows($query) > 0) {
        $_SESSION['username'] = $username;
        $_SESSION['role_id'] = $data['role_id'];
        $_SESSION['nama'] = $data['nama'];
        $_SESSION['key_app'] = $data['key_app'];
        $_SESSION['foto'] = $data['foto'];
        $_SESSION['kantor'] = $data['kd_kantor'];
        $_SESSION['kd_ao'] = $data['kd_ao'];
        // Update field log dengan waktu login terakhir
        date_default_timezone_set('Asia/Jakarta');
        $current_time = date('Y-m-d H:i:s');
        $update_log = mysqli_query($connect, "UPDATE account SET log='$current_time' WHERE username='$username'");

        header("Location: dashboard.php");
        exit();
    } else {
        $login_error = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Laporan Bidang Operasional</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
    body {
        background: linear-gradient(135deg, #001f4d, #0143a3, #0273d4);
        background-size: 400% 400%;
        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        position: relative;
        overflow: hidden;
        animation: gradientBG 15s ease infinite;
    }

    @keyframes gradientBG {
        0% {
            background-position: 0% 50%;
        }

        50% {
            background-position: 100% 50%;
        }

        100% {
            background-position: 0% 50%;
        }
    }

    /* Animasi Background */
    .bg-animation {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
    }

    .circles {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    .circle {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        animation: circleAnimation 8s infinite;
    }

    /* Banyak lingkaran dengan posisi dan animasi berbeda */
    .circle:nth-child(1) {
        width: 80px;
        height: 80px;
        left: 10%;
        top: 10%;
        animation-delay: 0s;
        animation-duration: 8s;
    }

    .circle:nth-child(2) {
        width: 20px;
        height: 20px;
        left: 20%;
        top: 40%;
        animation-delay: 2s;
        animation-duration: 12s;
    }

    .circle:nth-child(3) {
        width: 40px;
        height: 40px;
        left: 25%;
        top: 70%;
        animation-delay: 4s;
        animation-duration: 10s;
    }

    .circle:nth-child(4) {
        width: 60px;
        height: 60px;
        left: 40%;
        top: 25%;
        animation-delay: 0s;
        animation-duration: 15s;
    }

    .circle:nth-child(5) {
        width: 30px;
        height: 30px;
        left: 50%;
        top: 60%;
        animation-delay: 7s;
        animation-duration: 9s;
    }

    .circle:nth-child(6) {
        width: 70px;
        height: 70px;
        left: 65%;
        top: 15%;
        animation-delay: 5s;
        animation-duration: 12s;
    }

    .circle:nth-child(7) {
        width: 25px;
        height: 25px;
        left: 75%;
        top: 50%;
        animation-delay: 1s;
        animation-duration: 10s;
    }

    .circle:nth-child(8) {
        width: 90px;
        height: 90px;
        left: 80%;
        top: 70%;
        animation-delay: 3s;
        animation-duration: 14s;
    }

    .circle:nth-child(9) {
        width: 50px;
        height: 50px;
        left: 5%;
        top: 45%;
        animation-delay: 6s;
        animation-duration: 11s;
    }

    .circle:nth-child(10) {
        width: 35px;
        height: 35px;
        left: 50%;
        top: 10%;
        animation-delay: 8s;
        animation-duration: 9s;
    }

    .circle:nth-child(11) {
        width: 55px;
        height: 55px;
        left: 85%;
        top: 30%;
        animation-delay: 4s;
        animation-duration: 13s;
    }

    .circle:nth-child(12) {
        width: 45px;
        height: 45px;
        left: 30%;
        top: 85%;
        animation-delay: 2s;
        animation-duration: 10s;
    }

    .circle:nth-child(13) {
        width: 65px;
        height: 65px;
        left: 60%;
        top: 80%;
        animation-delay: 0s;
        animation-duration: 12s;
    }

    .circle:nth-child(14) {
        width: 15px;
        height: 15px;
        left: 15%;
        top: 70%;
        animation-delay: 5s;
        animation-duration: 8s;
    }

    .circle:nth-child(15) {
        width: 75px;
        height: 75px;
        left: 70%;
        top: 40%;
        animation-delay: 3s;
        animation-duration: 11s;
    }

    @keyframes circleAnimation {
        0% {
            transform: scale(0) translateY(0) rotate(0);
            opacity: 0;
        }

        20% {
            transform: scale(1.2) translateY(-20px) rotate(45deg);
            opacity: 0.5;
        }

        40% {
            transform: scale(1) translateY(-40px) rotate(90deg);
            opacity: 0.3;
        }

        60% {
            transform: scale(0.8) translateY(-60px) rotate(135deg);
            opacity: 0.5;
        }

        80% {
            transform: scale(0.6) translateY(-80px) rotate(180deg);
            opacity: 0.2;
        }

        100% {
            transform: scale(0) translateY(-100px) rotate(225deg);
            opacity: 0;
        }
    }

    /* Pastikan login container tetap di atas background */
    .login-container {
        width: 100%;
        max-width: 450px;
        padding: 0px;
        position: relative;
        z-index: 1;
    }

    .card {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(10px);
        border: none;
        position: relative;
        background: #0143a3;
    }

    .card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: #0143a3;
        z-index: 0;
    }

    .card-header {
        background: #0143a3;
        color: white;
        text-align: center;
        padding: 5px 0;
        border-bottom: none;
        position: relative;
        z-index: 1;
    }

    .bank-logo {
        width: 180px;
        height: 180px;
        object-fit: contain;
        margin-bottom: 5px;
    }

    .card-body {
        padding: 40px 30px;
        background: white;
        position: relative;
        z-index: 1;
        margin-top: -1px;
        box-shadow: 0 -3px 0 #0143a3;
    }

    .form-label {
        font-weight: 600;
        color: #555;
    }

    .input-group {
        margin-bottom: 25px;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
    }

    .input-group-text {
        background-color: #f8f9fa;
        border: none;
        color: #0143a3;
        padding-left: 15px;
        padding-right: 15px;
    }

    .form-control {
        border: none;
        padding: 12px 15px;
        height: auto;
    }

    .form-control:focus {
        box-shadow: none;
        border-color: #0143a3;
    }

    .btn-login {
        background: linear-gradient(135deg, #0143a3, #0273d4);
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
        width: 100%;
        margin-top: 10px;
        box-shadow: 0 5px 15px rgba(2, 115, 212, 0.3);
        transition: all 0.3s ease;
    }

    .btn-login:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(2, 115, 212, 0.4);
    }

    .footer-text {
        text-align: center;
        color: #6c757d;
        font-size: 14px;
        margin-top: 20px;
    }

    .bank-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .system-name {
        font-size: 1.1rem;
        opacity: 0.9;
    }

    /* Tambahan CSS untuk tampilan mobile */
    @media (max-width: 576px) {
        .login-container {
            width: 100%;
            max-width: 100%;
            padding: 15px;
        }

        body {
            padding: 0;
            margin: 0;
            height: 100%;
            min-height: 100vh;
        }

        .card {
            border-radius: 10px;
            margin-bottom: 0;
        }

        .bank-logo {
            width: 150px;
            height: 150px;
        }

        .card-body {
            padding: 25px 20px;
        }

        .input-group {
            margin-bottom: 20px;
        }
    }
    </style>
</head>

<body>
    <!-- Background animasi lingkaran -->
    <div class="bg-animation">
        <div class="circles">
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
            <div class="circle"></div>
        </div>
    </div>

    <div class="login-container">
        <div class="card">
            <div class="card-header">
                <!-- Ganti dengan logo bank Anda jika ada -->
                <img src="assets/logo.png" alt="Logo Bank" class="bank-logo">
                <!-- <div class="bank-name">BANK OPERASIONAL</div>
                <div class="system-name">SISTEM LAPORAN BIDANG OPERASIONAL</div> -->
            </div>
            <div class="card-body">
                <?php if ($login_error): ?>
                <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Username atau password salah!
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                <form method="POST" action="">
                    <div class="mb-4">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" name="username" placeholder="Masukkan username"
                                required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Masukkan password"
                                required>
                        </div>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary btn-login">
                        <i class="bi bi-box-arrow-in-right me-2"></i> MASUK
                    </button>
                </form>
                <div class="footer-text mt-4">
                    &copy; <?php echo date('Y'); ?> Bank Nusamba. Hak Cipta Dilindungi.
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>