<?php
session_start();
include '../config/koneksi.php';
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
  header("Location: login.php");
  exit;
}

$total_pemasukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pemasukan"))['total'] ?? 0;
$total_alokasi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(alokasi_dana) AS total FROM anggaran"))['total'] ?? 0;
$total_pengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran"))['total'] ?? 0;

// Hitung data
$pemasukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pemasukan"))['total'] ?? 0;
$pengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran"))['total'] ?? 0;
$kegiatan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anggaran"));
$menunggu = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM pengeluaran WHERE status_verifikasi = 'pending'"));

// Data untuk grafik
$pemasukanPerBulan = [];
$pengeluaranPerBulan = [];
for ($i = 1; $i <= 12; $i++) {
    $p1 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pemasukan WHERE MONTH(tanggal) = $i"));
    $pemasukanPerBulan[] = (int) $p1['total'];
    $p2 = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran WHERE MONTH(tanggal) = $i"));
    $pengeluaranPerBulan[] = (int) $p2['total'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Bendahara</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-[#f5f7fa] text-gray-800 font-sans">

 <header class="bg-purple-700 text-white px-6 py-6 shadow-lg flex justify-between items-center h-28">
  <div>
    <h1 class="text-2xl font-bold mb-1">💰 Sistem Informasi Keuangan Desa</h1>
    <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
  </div>
  <div class="text-right">
    <p class="text-sm">👋 Selamat datang, <strong>Bendahara</strong></p>
    <a href="../logout.php" class="text-red-300 hover:text-white text-xs underline">Keluar</a>
  </div>
</header>
  <div class="flex">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-purple-800 text-white min-h-screen px-6 py-8">
      <h2 class="text-xl font-bold mb-8 text-center">💰 Keuangan Desa</h2>
      <nav class="space-y-3">
        <a href="#" class="block bg-purple-700 px-4 py-2 rounded-md">📊 Dashboard</a>
        <a href="input_pemasukan.php" class="block hover:bg-purple-700 px-4 py-2 rounded-md">➕ Input Pemasukan</a>
        <a href="kelola_anggaran.php" class="block hover:bg-purple-700 px-4 py-2 rounded-md">📁 Kelola Anggaran</a>
        <a href="input_pengeluaran.php" class="block hover:bg-purple-700 px-4 py-2 rounded-md">💸 Input Pengeluaran</a>
        <a href="laporan.php" class="block hover:bg-purple-700 px-4 py-2 rounded-md">📑 Laporan</a>
        <a href="managemen_akun.php" class="block hover:bg-purple-700 px-4 py-2 rounded-md">👥 Manajemen Akun</a>
        <a href="lihat_feddback.php" class="block hover:bg-purple-700 px-4 py-2 rounded-md">💬 Feedback</a>
        <a href="backup_data.php" class="block hover:bg-purple-700 px-4 py-2 rounded-md">📂 Backup</a>
        <a href="../logout.php" class="block bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md mt-8 text-white">🚪 Logout</a>
      </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-8">
      <h2 class="text-2xl font-bold mb-6 text-purple-700">Dashboard Bendahara</h2>

      <!-- FAST MENU -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-10">
        <a href="input_pemasukan.php" class="bg-green-100 hover:bg-green-200 text-green-700 px-6 py-4 rounded-lg shadow text-center font-semibold">
          ➕ Input Pemasukan
        </a>
        <a href="kelola_anggaran.php" class="bg-yellow-100 hover:bg-yellow-200 text-yellow-700 px-6 py-4 rounded-lg shadow text-center font-semibold">
          📁 Kelola Anggaran
        </a>
        <a href="input_pengeluaran.php" class="bg-red-100 hover:bg-red-200 text-red-700 px-6 py-4 rounded-lg shadow text-center font-semibold">
          💸 Input Pengeluaran
        </a>
        <a href="laporan.php" class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-6 py-4 rounded-lg shadow text-center font-semibold">
          📑 Laporan
        </a>
      </div>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
  <!-- Total Pemasukan -->
  <div class="bg-green-100 p-6 rounded-xl shadow text-center">
    <h3 class="text-gray-600 text-sm font-semibold mb-1">Total Pemasukan</h3>
    <p class="text-2xl font-bold text-green-700">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></p>
  </div>

  <!-- Total Alokasi Dana -->
  <div class="bg-yellow-100 p-6 rounded-xl shadow text-center">
    <h3 class="text-gray-600 text-sm font-semibold mb-1">Total Alokasi Dana</h3>
    <p class="text-2xl font-bold text-yellow-700">Rp <?= number_format($total_alokasi, 0, ',', '.') ?></p>
  </div>

  <!-- Total Pengeluaran -->
  <div class="bg-red-100 p-6 rounded-xl shadow text-center">
    <h3 class="text-gray-600 text-sm font-semibold mb-1">Total Pengeluaran</h3>
    <p class="text-2xl font-bold text-red-700">Rp <?= number_format($total_pengeluaran, 0, ',', '.') ?></p>
  </div>

  <!-- Sisa Saldo -->
  <div class="bg-indigo-100 p-6 rounded-xl shadow text-center">
    <h3 class="text-gray-600 text-sm font-semibold mb-1">Sisa Saldo</h3>
    <p class="text-2xl font-bold text-indigo-700">
      Rp <?= number_format($total_pemasukan - $total_pengeluaran, 0, ',', '.') ?>
    </p>
  </div>
</div>


      <!-- GRAFIK -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-6 rounded-lg shadow">
          <h4 class="text-lg font-semibold mb-4">📊 Grafik Keuangan per Bulan</h4>
          <canvas id="grafikKeuangan" height="120"></canvas>
        </div>
        <div class="bg-white p-6 rounded-lg shadow">
          <h4 class="text-lg font-semibold mb-4">📈 Tren Penggunaan Dana</h4>
          <canvas id="grafikTren" height="120"></canvas>
        </div>
      </div>
    </main>
  </div>

  <!-- FOOTER -->
  <footer class="text-center text-sm py-4 bg-gray-200 text-gray-600">
    &copy; <?= date('Y') ?> Sistem Informasi Keuangan Desa Sanggaria. All rights reserved.
  </footer>

  <!-- CHART.JS -->
  <script>
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const pemasukanData = <?= json_encode($pemasukanPerBulan) ?>;
    const pengeluaranData = <?= json_encode($pengeluaranPerBulan) ?>;

    new Chart(document.getElementById('grafikKeuangan'), {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'Pemasukan', backgroundColor: '#16a34a', data: pemasukanData },
          { label: 'Pengeluaran', backgroundColor: '#dc2626', data: pengeluaranData }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'Grafik Pemasukan dan Pengeluaran Bulanan' }
        }
      }
    });

    new Chart(document.getElementById('grafikTren'), {
      type: 'line',
      data: {
        labels,
        datasets: [
          { label: 'Pemasukan', borderColor: '#16a34a', data: pemasukanData, fill: false },
          { label: 'Pengeluaran', borderColor: '#dc2626', data: pengeluaranData, fill: false }
        ]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'Trend Penggunaan Dana Tahunan' }
        }
      }
    });
  </script>
</body>
</html>
