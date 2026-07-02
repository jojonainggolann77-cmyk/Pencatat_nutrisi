<?php
session_start(); 
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$tanggal_hari_ini = date("Y-m-d");


$query_user = mysqli_query($koneksi, "SELECT * FROM users WHERE id = '$user_id'");
$data_user = $query_user ? mysqli_fetch_assoc($query_user) : null;

if (!$data_user) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$target_kalori = $data_user['target_kalori'] ?? 2000;
$tujuan = $data_user['tujuan'] ?? '';

if ($tujuan == 'Membentuk Otot') {
    $target_protein = ($target_kalori * 0.35) / 4;  
    $target_karbo   = ($target_kalori * 0.45) / 4;  
    $target_lemak   = ($target_kalori * 0.20) / 9;  
} elseif ($tujuan == 'Turun Berat Badan') {
    $target_protein = ($target_kalori * 0.40) / 4;
    $target_karbo   = ($target_kalori * 0.30) / 4;
    $target_lemak   = ($target_kalori * 0.30) / 9;
} else {
    $target_protein = ($target_kalori * 0.25) / 4;
    $target_karbo   = ($target_kalori * 0.50) / 4;
    $target_lemak   = ($target_kalori * 0.25) / 9;
}


$query_total_sql = "SELECT 
                        SUM(m.kalori * l.porsi) as tot_kalori,
                        SUM(m.protein * l.porsi) as tot_protein,
                        SUM(m.karbohidrat * l.porsi) as tot_karbo,
                        SUM(m.lemak * l.porsi) as tot_lemak,
                        SUM(m.serat * l.porsi) as tot_serat
                    FROM log_harian l
                    JOIN makanan m ON l.makanan_id = m.id
                    WHERE l.tanggal = '$tanggal_hari_ini' AND l.user_id = '$user_id'";
$hasil_total = mysqli_query($koneksi, $query_total_sql);
$total = $hasil_total ? mysqli_fetch_assoc($hasil_total) : [];

$tot_kalori_saat_ini = round($total['tot_kalori'] ?? 0);
$persentase_kalori = ($target_kalori > 0) ? min(round(($tot_kalori_saat_ini / $target_kalori) * 100), 100) : 0;

// 3. Query total air minum hari ini dengan pengecekan aman
$query_air_sql = "SELECT SUM(jml_gelas) as tot_air FROM log_air WHERE tanggal = '$tanggal_hari_ini' AND user_id = '$user_id'";
$query_air = mysqli_query($koneksi, $query_air_sql);
$data_air = $query_air ? mysqli_fetch_assoc($query_air) : [];

$jml_air = $data_air['tot_air'] ?? 0;
$persen_air = min(round(($jml_air / 8) * 100), 100);

// 4. Penanganan pesan alert
$pesan_alert = "";
if (isset($_GET['pesan'])) {
    switch ($_GET['pesan']) {
        case 'makanan_ditambah': $pesan_alert = "Katalog makanan baru berhasil ditambahkan!"; break;
        case 'log_ditambah': $pesan_alert = "Asupan makanan berhasil dicatat!"; break;
        case 'log_diupdate': $pesan_alert = "Porsi makanan berhasil diperbarui!"; break;
        case 'log_dihapus': $pesan_alert = "Catatan asupan berhasil dihapus!"; break;
        case 'air_ditambah': $pesan_alert = "Asupan air minum berhasil ditambahkan!"; break;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FatTracker - Dashboard Nutrisi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        .card-stat { transition: 0.2s; }
        .card-stat:hover { transform: translateY(-3px); }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold" href="#"><i class="bi bi-apple"></i> FatTracker</a>
        
        <div class="d-flex align-items-center">
            <span class="badge bg-warning text-dark me-3 py-2 px-3 fs-6 shadow-sm">
                🔥 <strong><?= $data_user['current_streak'] ?? 0 ?> Hari</strong> Streak
            </span>

            <span class="navbar-text text-white me-3">
                Halo, <strong><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></strong>!
            </span>
            <a href="logout.php" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Keluar</a>
        </div>
    </div>
</nav>

<div class="container my-4">

    <?php if ($pesan_alert !== ""): ?>
        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i> <?= $pesan_alert ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <h5 class="fw-bold mb-3"><i class="bi bi-pie-chart-fill text-success"></i> Ringkasan Asupan Hari Ini</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card card-stat border-0 shadow-sm bg-primary text-white text-center p-3">
                <h6 class="text-white-50">Kalori Total</h6>
                <h2 class="fw-bold my-1"><?= $tot_kalori_saat_ini ?> <small class="fs-6">/ <?= round($target_kalori) ?> kcal</small></h2>
                <div class="progress mt-2" style="height: 6px;">
                    <div class="progress-bar bg-white" style="width: <?= $persentase_kalori ?>%;"></div>
                </div>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card card-stat border-0 shadow-sm bg-danger text-white text-center p-3">
                <h6 class="text-white-50">Protein</h6>
                <h3 class="fw-bold my-1"><?= round($total['tot_protein'] ?? 0, 1) ?> <small class="fs-6">/ <?= round($target_protein) ?>g</small></h3>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card card-stat border-0 shadow-sm bg-warning text-dark text-center p-3">
                <h6 class="text-black-50">Karbohidrat</h6>
                <h3 class="fw-bold my-1"><?= round($total['tot_karbo'] ?? 0, 1) ?> <small class="fs-6">/ <?= round($target_karbo) ?>g</small></h3>
            </div>
        </div>
        <div class="col-md-2 col-6">
            <div class="card card-stat border-0 shadow-sm bg-info text-white text-center p-3">
                <h6 class="text-white-50">Lemak</h6>
                <h3 class="fw-bold my-1"><?= round($total['tot_lemak'] ?? 0, 1) ?> <small class="fs-6">/ <?= round($target_lemak) ?>g</small></h3>
            </div>
        </div>
        <div class="col-md-3 col-12">
            <div class="card card-stat border-0 shadow-sm bg-secondary text-white text-center p-3">
                <h6 class="text-white-50">Serat (Fiber)</h6>
                <h3 class="fw-bold my-1"><?= round($total['tot_serat'] ?? 0, 1) ?> <small class="fs-6">g</small></h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bar-chart-line-fill text-success"></i> Grafik Asupan Kalori (7 Hari Terakhir)</span>
            <span class="badge bg-light text-muted border">Target: <?= round($target_kalori) ?> kcal / hari</span>
        </div>
        <div class="card-body">
            <canvas id="grafikKalori" style="max-height: 250px;"></canvas>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm mb-4 bg-primary text-white">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold"><i class="bi bi-droplet-fill text-info"></i> Hidrasi Air Minum</span>
                        <span class="badge bg-white text-primary fw-bold"><?= $jml_air ?> / 8 Gelas</span>
                    </div>
                    <div class="progress bg-dark bg-opacity-25 mb-3" style="height: 10px;">
                        <div class="progress-bar bg-info progress-bar-striped progress-bar-animated" style="width: <?= $persen_air ?>%;"></div>
                    </div>
                    <a href="tambah_air.php" class="btn btn-light btn-sm w-100 fw-bold text-primary shadow-sm">
                        <i class="bi bi-plus-lg"></i> Minum +1 Gelas Air (250ml)
                    </a>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3">
                    <i class="bi bi-plus-circle-fill text-success"></i> Catat Makanan Kamu
                </div>
                <div class="card-body">
                    <form action="catat_log.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Pilih Makanan dari Katalog</label>
                            <select id="select_makanan" name="makanan_id" class="form-select" required>
                                <option value="">-- Pilih Makanan --</option>
                                <?php
                                $query_makanan = mysqli_query($koneksi, "SELECT * FROM makanan ORDER BY nama ASC");
                                while($m = mysqli_fetch_assoc($query_makanan)) {
                                    echo "<option value='{$m['id']}'>{$m['nama']} ({$m['kalori']} kcal)</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Porsi</label>
                            <input type="number" step="0.5" name="porsi" class="form-control" value="1" min="0.5" required>
                            <div class="form-text">Contoh: 1 (satu porsi penuh), 0.5 (setengah porsi).</div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 fw-bold"><i class="bi bi-plus-lg"></i> Tambahkan ke Log</button>
                    </form>

                    <hr class="my-4">
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#modalLihatKatalog">
                            <i class="bi bi-search"></i> 🔍 Cari & Lihat Semua Katalog Makanan
                        </button>
                        
                        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMakanan">
                            <i class="bi bi-journal-plus"></i> + Buat Makanan Baru ke Katalog
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-clock-history text-primary"></i> Riwayat Makan Hari Ini</span>
                    <span class="badge bg-success">Live Sync</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Nama Makanan</th>
                                    <th>Porsi</th>
                                    <th>Kalori</th>
                                    <th>Makronutrisi (P / K / L)</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query_log_sql = "SELECT l.id as log_id, l.porsi, m.* FROM log_harian l
                                                  JOIN makanan m ON l.makanan_id = m.id 
                                                  WHERE l.tanggal = '$tanggal_hari_ini' AND l.user_id = '$user_id' 
                                                  ORDER BY l.id DESC";
                                $hasil_log = mysqli_query($koneksi, $query_log_sql);

                                if (!$hasil_log || mysqli_num_rows($hasil_log) == 0) {
                                    echo "<tr><td colspan='5' class='text-center text-muted py-4'>Belum ada makanan yang dicatat hari ini. Yuk mulai catat!</td></tr>";
                                } else {
                                    while ($row = mysqli_fetch_assoc($hasil_log)) {
                                        $kalori_tot = $row['kalori'] * $row['porsi'];
                                        $prot_tot   = $row['protein'] * $row['porsi'];
                                        $karb_tot   = $row['karbohidrat'] * $row['porsi'];
                                        $lem_tot    = $row['lemak'] * $row['porsi'];
                                ?>
                                <tr>
                                    <td class="ps-3 fw-bold"><?= htmlspecialchars($row['nama']) ?></td>
                                    <td>
                                        <form action="update_log.php" method="POST" class="d-flex align-items-center" style="max-width: 110px;">
                                            <input type="hidden" name="id" value="<?= $row['log_id'] ?>">
                                            <input type="number" step="0.1" name="porsi" class="form-control form-control-sm me-1 text-center fw-bold" value="<?= $row['porsi'] ?>" min="0.1">
                                            <button type="submit" class="btn btn-sm btn-light border" title="Update Porsi"><i class="bi bi-arrow-repeat text-success"></i></button>
                                        </form>
                                    </td>
                                    <td><span class="badge bg-primary fs-6"><?= round($kalori_tot) ?> kcal</span></td>
                                    <td class="small text-muted">
                                        P: <b><?= round($prot_tot,1) ?>g</b> | 
                                        K: <b><?= round($karb_tot,1) ?>g</b> | 
                                        L: <b><?= round($lem_tot,1) ?>g</b>
                                    </td>
                                    <td class="text-center">
                                        <a href="hapus_log.php?id=<?= $row['log_id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin menghapus makanan ini dari log?')">
                                            <i class="bi bi-trash"></i> Hapus
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    } 
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

<div class="modal fade" id="modalTambahMakanan" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-apple"></i> Tambah Katalog Makanan Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="tambah_makanan.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Makanan + Takaran</label>
                        <input type="text" name="nama" class="form-control" placeholder="Misal: Apel Merah (1 buah)" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label class="form-label">Kalori (kcal)</label>
                            <input type="number" name="kalori" class="form-control" placeholder="0" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Protein (g)</label>
                            <input type="number" step="0.1" name="protein" class="form-control" placeholder="0.0" value="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Karbohidrat (g)</label>
                            <input type="number" step="0.1" name="karbohidrat" class="form-control" placeholder="0.0" value="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">Lemak (g)</label>
                            <input type="number" step="0.1" name="lemak" class="form-control" placeholder="0.0" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Serat / Fiber (g)</label>
                            <input type="number" step="0.1" name="serat" class="form-control" placeholder="0.0" value="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan ke Katalog</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalLihatKatalog" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-search"></i> Daftar & Pencarian Katalog Makanan (350+ Item)</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3 sticky-top shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-success"></i></span>
                    <input type="text" id="inputCariKatalog" class="form-control border-start-0" placeholder="🔍 Ketik nama makanan untuk mencari dengan cepat (misal: Sate, Buah, Ayam)...">
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle" id="tabelKatalog">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Makanan (Takaran)</th>
                                <th>Kalori</th>
                                <th>Protein</th>
                                <th>Karbo</th>
                                <th>Lemak</th>
                                <th>Serat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $q_semua_makanan = mysqli_query($koneksi, "SELECT * FROM makanan ORDER BY nama ASC");
                            while($k = mysqli_fetch_assoc($q_semua_makanan)):
                            ?>
                            <tr>
                                <td class="fw-bold"><?= $k['nama'] ?></td>
                                <td><span class="badge bg-primary"><?= $k['kalori'] ?> kcal</span></td>
                                <td><?= $k['protein'] ?>g</td>
                                <td><?= $k['karbohidrat'] ?>g</td>
                                <td><?= $k['lemak'] ?>g</td>
                                <td class="text-success fw-bold"><?= $k['serat'] ?>g</td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
<script>
    // Inisialisasi Chart.js
    const ctx = document.getElementById('grafikKalori');
    const targetKaloriUser = <?= round($target_kalori) ?>;
    
    new Chart(ctx, {
        type: 'bar', 
        data: {
            labels: ['6 Hari Lalu', '5 Hari Lalu', '4 Hari Lalu', '3 Hari Lalu', '2 Hari Lalu', 'Kemarin', 'Hari Ini'],
            datasets: [{
                label: 'Total Kalori (kcal)',
                data: [1750, 2100, 1850, 2200, 1900, 1600, <?= $tot_kalori_saat_ini ?>], 
                backgroundColor: 'rgba(25, 135, 84, 0.75)', 
                borderColor: '#198754',
                borderWidth: 1,
                borderRadius: 6
            },
            {
                label: 'Target Batas Kalori',
                data: Array(7).fill(targetKaloriUser), 
                type: 'line',
                borderColor: '#dc3545', 
                borderWidth: 2,
                pointRadius: 0,
                borderDash: [5, 5]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: Math.max(2500, Math.round(targetKaloriUser * 1.2)) 
                }
            }
        }
    });

   
    new TomSelect("#select_makanan", {
        create: false,
        sortField: {
            field: "text",
            direction: "asc"
        },
        placeholder: "🔍 Ketik nama makanan untuk mencari..."
    });

    
    document.getElementById("inputCariKatalog").addEventListener("keyup", function() {
        let filter = this.value.toLowerCase();
        let barisTabel = document.querySelectorAll("#tabelKatalog tbody tr");
        
        barisTabel.forEach(baris => {
            let teksBaris = baris.innerText.toLowerCase();
            if(teksBaris.includes(filter)) {
                baris.style.display = "";
            } else {
                baris.style.display = "none";
            }
        });
    });
</script>
</body>
</html>