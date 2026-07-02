<?php
session_start();
include 'koneksi.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $makanan_id = $_POST['makanan_id'];
    $porsi      = $_POST['porsi'];
    $tanggal    = date("Y-m-d");
    $user_id    = $_SESSION['user_id']; 

    $query = "INSERT INTO log_harian (makanan_id, tanggal, porsi, user_id) VALUES ('$makanan_id', '$tanggal', '$porsi', '$user_id')";
    mysqli_query($koneksi, $query);
    
    header("Location: index.php?pesan=log_ditambah");
}
?>