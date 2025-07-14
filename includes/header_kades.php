<?php
if (!isset($_SESSION)) session_start();
include_once '../config/koneksi.php';

$user_id = $_SESSION['id_user'] ?? null;

if ($user_id) {
  $q_user = mysqli_query($conn, "SELECT nama_lengkap, foto_profil FROM users WHERE id_user = '$user_id'");
  $user = mysqli_fetch_assoc($q_user);
} else {
  $user = ['nama_lengkap' => 'Pengguna', 'foto_profil' => 'assets/img/default-user.png'];
}
?>

<!-- Header Start-->
<header class="relative bg-teal-600 text-white px-6 py-8 shadow-md overflow-hidden">
  <!-- Motif gelombang atas -->
  <svg class="absolute top-0 left-0 w-full h-10 text-teal-500 opacity-30" preserveAspectRatio="none" viewBox="0 0 1440 320">
    <path fill="currentColor" d="M0,64L48,96C96,128,192,192,288,192C384,192,480,128,576,112C672,96,768,128,864,160C960,192,1056,224,1152,208C1248,192,1344,128,1392,96L1440,64L1440,0L1392,0C1344,0,1248,0,1152,0C1056,0,960,0,864,0C768,0,672,0,576,0C480,0,384,0,288,0C192,0,96,0,48,0L0,0Z"></path>
  </svg>

  <!-- Konten Header -->
  <div class="relative z-10 flex justify-between items-center">
    <div>
      <h1 class="text-3xl font-bold mb-1">🌿 Sistem Informasi Keuangan Desa</h1>
      <p class="text-sm opacity-80">Dashboard Kepala Desa | Transparansi & Akuntabilitas</p>
    </div>

    <!-- Info User -->
    <div class="flex items-center gap-3 text-right">
      <img src="../<?= $user['foto_profil'] ?? 'assets/img/default-user.png' ?>" alt="Foto Profil" class="w-10 h-10 rounded-full border-2 border-white shadow">
      <div class="text-sm">
        <p>Selamat datang, <strong><?= $user['nama_lengkap'] ?? 'Pengguna' ?></strong></p>
        <a href="../logout.php" class="text-red-200 hover:text-white text-xs underline">Keluar</a>
      </div>
    </div>
  </div>
</header>
<!-- Header End -->
