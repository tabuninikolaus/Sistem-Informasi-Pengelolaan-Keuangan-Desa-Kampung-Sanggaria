<!-- header.php -->
<?php
  if (!isset($_SESSION)) session_start();
  include '../config/koneksi.php';

  if (!isset($_SESSION['id_user'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
  }

  $user_id = $_SESSION['id_user'];
  $queryUser = mysqli_query($conn, "SELECT nama_lengkap, foto_profil FROM users WHERE id_user = '$user_id'");
  $user = mysqli_fetch_assoc($queryUser);
?>

<!-- Header Start-->
<header class="relative bg-teal-600 text-white px-6 py-8 shadow-md overflow-hidden">
  <!-- Konten Header -->
  <div class="relative z-10 flex justify-between items-center">
    <div>
      <h1 class="text-3xl font-bold mb-1">🌿 Sistem Informasi Keuangan Desa</h1>
      <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
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
