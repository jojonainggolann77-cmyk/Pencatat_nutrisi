<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); 
    $target_kalori = $_POST['target_kalori'];


    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan! Pilih yang lain.";
    } else {
        mysqli_query($koneksi, "INSERT INTO users (username, password, target_kalori) VALUES ('$username', '$password', '$target_kalori')");
        header("Location: login.php?pesan=daftar_sukses");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar - FatTracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
    <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px;">
        <h3 class="text-center text-success fw-bold mb-4">Daftar Akun</h3>
        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST">
            <div class="mb-3">
                <label>Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="mb-4">
                <label>Target Kalori Harian (kcal)</label>
                <input type="number" name="target_kalori" class="form-control" value="2000" required>
            </div>
            <button type="submit" class="btn btn-success w-100 fw-bold">Daftar</button>
        </form>
        <div class="text-center mt-3">
            Sudah punya akun? <a href="login.php">Login di sini</a>
        </div>
    </div>
</body>
</html>