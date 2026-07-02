<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    $user_id  = $_SESSION['user_id'];
    $hari_ini = date("Y-m-d");

    // Ambil data minum hari ini
    $query = mysqli_query($koneksi, "SELECT air_hari_ini, tgl_air FROM users WHERE id = '$user_id'");
    $data  = mysqli_fetch_assoc($query);

    $air     = $data['air_hari_ini'];
    $tgl_air = $data['tgl_air'];
1
    if ($tgl_air != $hari_ini) {
        $air = 1;
    } else {

        if ($air < 12) {
            $air++;
        }
    }

    mysqli_query($koneksi, "UPDATE users SET air_hari_ini = '$air', tgl_air = '$hari_ini' WHERE id = '$user_id'");
}

header("Location: index.php");
exit;
?>