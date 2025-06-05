<?php
include '../config/koneksi.php';

// Validasi input sederhana
$nama  = htmlspecialchars($_POST['nama']);
$email = htmlspecialchars($_POST['email']);
$usia  = (int) $_POST['usia'];
$jk    = $_POST['jenis_kelamin'];
$pesan = htmlspecialchars($_POST['pesan']);

$query = "INSERT INTO feedback (nama, email, usia, jenis_kelamin, pesan)
          VALUES ('$nama', '$email', '$usia', '$jk', '$pesan')";

if (mysqli_query($conn, $query)) {
  echo "<script>alert('Terima kasih atas masukan Anda!'); window.location.href='index.php';</script>";
} else {
  echo "<script>alert('Gagal mengirim feedback!'); window.history.back();</script>";
}
?>
