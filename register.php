<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username      = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password      = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $berat         = $_POST['berat_badan'];
    $tinggi        = $_POST['tinggi_badan'];
    $tujuan        = $_POST['tujuan'];
    $target_kalori = $_POST['target_kalori'];

    $cek = mysqli_query($koneksi, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan! Pilih yang lain.";
    } else {
        $query = "INSERT INTO users (username, password, berat_badan, tinggi_badan, tujuan, target_kalori, current_streak) 
                  VALUES ('$username', '$password', '$berat', '$tinggi', '$tujuan', '$target_kalori', 1)";
        mysqli_query($koneksi, $query);
        header("Location: login.php?pesan=daftar_sukses");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <title>Daftar Akun - FatTracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
</head>
<body class="bg-light d-flex align-items-center justify-content-center py-5" style="min-height: 100vh;">
    <div class="card shadow-sm p-4 border-0" style="width: 100%; max-width: 480px;">
        <div class="text-center mb-4">
            <h3 class="text-success fw-bold"><i class="bi bi-apple"></i> FatTracker</h3>
            <p class="text-muted small">Buat akun dan atur target nutrisi pribadimu</p>
        </div>

        <?php if(isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label fw-bold small">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Buat username unik" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold small">Password</label>
                <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
            </div>

            <hr class="my-3">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-lines-fill text-primary"></i> Profil Fisik & Target</h6>

            <div class="row g-2 mb-3">
                <div class="col-6">
                    <label class="form-label small">Berat Badan (kg)</label>
                    <input type="number" step="0.5" id="berat" name="berat_badan" class="form-control" value="65" required oninput="hitungKalori()">
                </div>
                <div class="col-6">
                    <label class="form-label small">Tinggi Badan (cm)</label>
                    <input type="number" id="tinggi" name="tinggi_badan" class="form-control" value="170" required oninput="hitungKalori()">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold small">Tujuan Utama Kamu</label>
                <select id="tujuan" name="tujuan" class="form-select border-success" required onchange="hitungKalori()">
                    <option value="Hidup Sehat">🌱 Hidup Sehat (Seimbang)</option>
                    <option value="Membentuk Otot">💪 Membentuk Otot (Tinggi Protein & Karbo)</option>
                    <option value="Turun Berat Badan">🔥 Turun Berat Badan (Defisit Kalori)</option>
                </select>
            </div>

            <div class="mb-4 p-3 bg-light rounded border">
                <label class="form-label fw-bold text-success mb-1 d-flex justify-content-between">
                    <span>Target Kalori Harian (Otomatis)</span>
                    <i class="bi bi-magic"></i>
                </label>
                <div class="input-group">
                    <input type="number" id="target_kalori" name="target_kalori" class="form-control fw-bold fs-5 text-success" value="2000" required>
                    <span class="input-group-text bg-white">kcal / hari</span>
                </div>
                <div class="form-text small" id="teks_rekomendasi">Dihitung otomatis berdasarkan berat, tinggi, & tujuanmu.</div>
            </div>

            <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                <i class="bi bi-check-circle-fill"></i> Daftar & Mulai Tracking
            </button>
        </form>
        <div class="text-center mt-3 small">
            Sudah punya akun? <a href="login.php" class="text-decoration-none fw-bold">Login di sini</a>
        </div>
    </div>

    <script>
    function hitungKalori() {
        let berat = parseFloat(document.getElementById('berat').value) || 0;
        let tinggi = parseFloat(document.getElementById('tinggi').value) || 0;
        let tujuan = document.getElementById('tujuan').value;
        
      
        let bmr = (10 * berat) + (6.25 * tinggi) - 250; 
        let target = bmr * 1.35; // Activity factor rata-rata

        let teks = "";
        if (tujuan === "Membentuk Otot") {
            target = target + 400;
            teks = "💡 Surplus kalori diaktifkan! Fokus pada asupan tinggi protein & serat.";
        } elseif (tujuan === "Turun Berat Badan") {
            target = target - 400; 
            teks = "💡 Defisit kalori diaktifkan! Pastikan minum cukup air & utamakan protein.";
        } else {
            teks = "💡 Kalori seimbang untuk menjaga berat badan optimal & energi harian.";
        }

        document.getElementById('target_kalori').value = Math.round(target);
        document.getElementById('teks_rekomendasi').innerText = teks;
    }
   
    window.onload = hitungKalori;
    </script>
</body>
</html>