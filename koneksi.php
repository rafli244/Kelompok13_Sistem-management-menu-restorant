<?php
$koneksi = mysqli_connect("localhost", "root", "", "restoran_lezat");

if (mysqli_connect_errno()) {
    die("Database connection failed: " . mysqli_connect_error());
}
?>