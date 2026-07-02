<?php
session_start();
include 'koneksi.php';

// 1. Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$tanggal_hari_ini = date("Y-m-d");
$jml_gelas_tambah = 1; 

$cek_query = "SELECT id, jml_gelas FROM log_air WHERE tanggal = '$tanggal_hari_ini' AND user_id = '$user_id'";
$cek_hasil = mysqli_query($koneksi, $cek_query);

if (!$cek_hasil) {
    die("Terjadi kesalahan pada database (Tabel log_air): " . mysqli_error($koneksi));
}

if (mysqli_num_rows($cek_hasil) > 0) {
  
    $row = mysqli_fetch_assoc($cek_hasil);
    $id_log = $row['id'];
    $query_simpan = "UPDATE log_air SET jml_gelas = jml_gelas + $jml_gelas_tambah WHERE id = '$id_log'";
} else {
  
    $query_simpan = "INSERT INTO log_air (user_id, tanggal, jml_gelas) VALUES ('$user_id', '$tanggal_hari_ini', '$jml_gelas_tambah')";
}

if (mysqli_query($koneksi, $query_simpan)) {
    header("Location: index.php?pesan=air_ditambah");
    exit;
} else {
    die("Gagal menyimpan data hidrasi: " . mysqli_error($koneksi));
}
?>