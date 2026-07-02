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
   
    $hari_ini = date("Y-m-d");
    $kemarin  = date("Y-m-d", strtotime("-1 day"));

  
    $q_user = mysqli_query($koneksi, "SELECT current_streak, last_log_date FROM users WHERE id = '$user_id'");
    $u_data = mysqli_fetch_assoc($q_user);

    $last_date = $u_data['last_log_date'];
    $streak    = $u_data['current_streak'];

    if ($last_date == $kemarin) {
     
        $streak++;
        mysqli_query($koneksi, "UPDATE users SET current_streak = '$streak', last_log_date = '$hari_ini' WHERE id = '$user_id'");
    } elseif ($last_date != $hari_ini) {
      
        mysqli_query($koneksi, "UPDATE users SET current_streak = '1', last_log_date = '$hari_ini' WHERE id = '$user_id'");
    }
  
    mysqli_query($koneksi, $query);
    
    header("Location: index.php?pesan=log_ditambah");
}
?>