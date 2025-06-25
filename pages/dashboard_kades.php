<?php
// session_start();
// if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'kades') {
//     header("Location: login.php");
//     exit;
// }
include '../config/koneksi.php';

// Total Pemasukan
$pemasukanQuery = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pemasukan");
$pemasukan = mysqli_fetch_assoc($pemasukanQuery)['total'] ?? 0;

// Total Pengeluaran
$pengeluaranQuery = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran");
$pengeluaran = mysqli_fetch_assoc($pengeluaranQuery)['total'] ?? 0;

// Sisa Saldo
$sisa = $pemasukan - $pengeluaran;

// Notifikasi pengajuan yang menunggu verifikasi
$q1 = mysqli_query($conn, "SELECT id_ajuan FROM pengeluaran_ajuan WHERE status_ajuan = 'menunggu'");
$menunggu_pengeluaran = $q1 ? mysqli_num_rows($q1) : 0;

$q2 = mysqli_query($conn, "SELECT id_laporan FROM laporan_pertahap WHERE status_verifikasi = 'pending'");
$menunggu_pertahap = $q2 ? mysqli_num_rows($q2) : 0;

$q3 = mysqli_query($conn, "SELECT id_laporan FROM laporan_tahunan WHERE status_verifikasi = 'pending'");
$menunggu_tahunan = $q3 ? mysqli_num_rows($q3) : 0;

$total_notif = $menunggu_pengeluaran + $menunggu_pertahap + $menunggu_tahunan;
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Kepala Desa</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 bg-purple-700 text-white min-h-screen p-6">
    <h2 class="text-2xl font-bold text-center mb-8">Kepala Desa</h2>
    <nav class="space-y-3">
      <a href="dashboard_kades.php" class="block px-4 py-2 rounded bg-purple-800">🏠 Dashboard</a>
      <a href="verifikasi_pengeluaran.php" class="block hover:bg-purple-800 px-4 py-2 rounded">💸 Verifikasi Pengeluaran</a>
      <a href="verifikasi_laporan.php" class="block hover:bg-purple-800 px-4 py-2 rounded">📁 Verifikasi Semua Laporan</a>
      <a href="../logout.php" class="block hover:bg-red-600 bg-red-500 text-white px-4 py-2 rounded mt-10">🚪 Logout</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-10">
    <h1 class="text-3xl font-bold text-purple-700 mb-6">Dashboard Kepala Desa</h1>

    <?php if($total_notif > 0): ?>
    <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded">
      🔔 Anda memiliki <strong><?= $menunggu_pengeluaran ?></strong> pengajuan pengeluaran, <strong><?= $menunggu_pertahap ?></strong> laporan pertahap, dan <strong><?= $menunggu_tahunan ?></strong> LPJ tahunan yang menunggu verifikasi.
      <div class="mt-2">
        <a href="verifikasi_pengeluaran.php" class="underline font-semibold text-purple-700">📄 Lihat Pengeluaran</a> |
        <a href="verifikasi_laporan.php" class="underline font-semibold text-purple-700">📑 Lihat Semua Laporan</a>
      </div>
    </div>
    <?php endif; ?>

    <!-- Menu Box -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700">Verifikasi Pengeluaran</h2>
        <p class="text-gray-500 mt-2">Cek dan setujui pengeluaran dari bendahara.</p>
        <a href="verifikasi_pengeluaran.php" class="text-purple-600 mt-4 inline-block">Lihat Detail →</a>
      </div>
      <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold text-gray-700">Semua Laporan</h2>
        <p class="text-gray-500 mt-2">Verifikasi laporan pertahap dan LPJ tahunan.</p>
        <a href="verifikasi_laporan.php" class="text-purple-600 mt-4 inline-block">Lihat Detail →</a>
      </div>
    </div>

    <!-- Ringkasan Keuangan -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-gray-600">Total Pemasukan</h3>
        <p class="text-2xl font-bold text-green-600 mt-2">Rp <?= number_format($pemasukan, 0, ',', '.') ?></p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-gray-600">Total Pengeluaran</h3>
        <p class="text-2xl font-bold text-red-600 mt-2">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-gray-600">Sisa Saldo</h3>
        <p class="text-2xl font-bold text-purple-600 mt-2">Rp <?= number_format($sisa, 0, ',', '.') ?></p>
      </div>
    </div>
  </main>
</body>
</html>
