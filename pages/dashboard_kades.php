<?php
session_start();
include '../config/koneksi.php';

// if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'kades') {
//     header("Location: login.php");
//     exit;
// }

      // NOTIFIKASI QUERY
      $q_peng = mysqli_query($conn, "SELECT COUNT(*) as jml FROM pengeluaran_ajuan WHERE status_ajuan = 'menunggu'");
      $q_lap = mysqli_query($conn, "SELECT COUNT(*) as jml FROM laporan_ajuan WHERE status_ajuan = 'menunggu'");
      $jml_peng = mysqli_fetch_assoc($q_peng)['jml'];
      $jml_lap = mysqli_fetch_assoc($q_lap)['jml'];
      $total_kades_notif = $jml_peng + $jml_lap;

$user_id = $_SESSION['id_user'];
$q_user = mysqli_query($conn, "SELECT nama_lengkap, foto_profil FROM users WHERE id_user = '$user_id'");
$user = mysqli_fetch_assoc($q_user);

// Total Keuangan
$pemasukan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pemasukan"))['total'] ?? 0;
$pengeluaran = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran"))['total'] ?? 0;
$sisa = $pemasukan - $pengeluaran;

// Notifikasi
$laporanMasuk = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM laporan_ajuan WHERE status_ajuan ='diajukan'"));
$pengeluaranMasuk = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM pengeluaran_ajuan WHERE status_ajuan ='diajukan'"));
$totalNotif = $laporanMasuk + $pengeluaranMasuk;

if (!isset($_SESSION['notifikasi_suara_kades']) && $totalNotif > 0) {
    echo '<audio autoplay>
        <source src="../assets/sound/notification.mp3" type="audio/mpeg">
    </audio>';

    $_SESSION['notifikasi_suara_kades'] = true; // supaya tidak berulang
}

// Grafik: Ambil data bulanan
$pemasukan_bulanan = array_fill(1, 12, 0);
$pengeluaran_bulanan = array_fill(1, 12, 0);

$q_pemasukan = mysqli_query($conn, "SELECT MONTH(tanggal) as bulan, SUM(jumlah) as total FROM pemasukan GROUP BY bulan");
while ($row = mysqli_fetch_assoc($q_pemasukan)) {
    $pemasukan_bulanan[(int)$row['bulan']] = (int)$row['total'];
}
$q_pengeluaran = mysqli_query($conn, "SELECT MONTH(tanggal) as bulan, SUM(jumlah) as total FROM pengeluaran GROUP BY bulan");
while ($row = mysqli_fetch_assoc($q_pengeluaran)) {
    $pengeluaran_bulanan[(int)$row['bulan']] = (int)$row['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Kepala Desa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 flex flex-col min-h-screen">

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

    <!-- Info User dengan Foto -->
    <div class="flex items-center gap-3 text-right">
      <img src="../<?= $user['foto_profil'] ?? 'assets/img/default-user.png' ?>" alt="Foto Profil" class="w-10 h-10 rounded-full border-2 border-white shadow">
      <div class="text-sm">
        <p>Selamat datang, <strong><?= $user['nama_lengkap'] ?? 'Pengguna' ?></strong></p>
        <a href="../logout.php" class="text-red-200 hover:text-white text-xs underline">Keluar</a>
      </div>
    </div>
  </div>
</header>
 <!-- Header End-->

<div class="flex flex-1">
  <!-- Sidebar -->
    <aside class="tosca bg-teal-700 text-white px-6 py-8 shadow-md overflow-hidden">
      <h2 class="text-xl font-bold mb-6 text-center">Menu</h2>
      <nav class="space-y-3">
        <a href="#" class="block bg-slate-700 px-4 py-2 rounded">📊 Dashboard</a>
        <a href="verifikasi_pengeluaran.php" class="block hover:bg-purple-800 px-4 py-2 rounded">💸 Verifikasi Pengeluaran</a>
        <a href="verifikasi_laporan.php" class="block hover:bg-purple-800 px-4 py-2 rounded">📁 Verifikasi Semua Laporan</a>
      </nav>
    </aside>

  <!-- Konten -->
  <main class="flex-1 p-8">
    <h1 class="text-3xl font-bold text-purple-700 mb-6">Dashboard Kepala Desa</h1>
    <!-- NOTIFIKASI STUFFS -->
          <?php if ($total_kades_notif > 0): ?>
          <div class="bg-red-100 border-l-4 border-red-500 text-red-800 p-4 mb-6 rounded-md flex justify-between items-center">
            <div>
              <p class="font-semibold">📢 Ada <?= $total_kades_notif ?> verifikasi menunggu</p>
              <ul class="text-sm ml-4 list-disc">
                <?php if ($jml_peng > 0): ?><li><?= $jml_peng ?> pengeluaran baru</li><?php endif; ?>
                <?php if ($jml_lap > 0): ?><li><?= $jml_lap ?> laporan keuangan baru</li><?php endif; ?>
              </ul>
            </div>
            <div class="flex gap-2">
              <?php if ($jml_peng > 0): ?>
                <a href="verifikasi_pengeluaran.php" class="bg-red-500 text-white px-3 py-2 rounded hover:bg-red-600">Verifikasi Pengeluaran</a>
              <?php endif; ?>
              <?php if ($jml_lap > 0): ?>
                <a href="verifikasi_laporan.php" class="bg-blue-500 text-white px-3 py-2 rounded hover:bg-blue-600">Verifikasi Laporan</a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">
  <?php if ($laporanMasuk > 0): ?>
  <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 rounded shadow">
    <p class="text-yellow-800 font-semibold">📁 <?= $laporanMasuk ?> Laporan keuangan baru menunggu verifikasi.</p>
    <a href="verifikasi_laporan.php" class="text-sm text-blue-700 hover:underline">🔍 Lihat Sekarang</a>
  </div>
  <?php endif; ?>

  <?php if ($pengeluaranMasuk > 0): ?>
  <div class="bg-red-100 border-l-4 border-red-500 p-4 rounded shadow">
    <p class="text-red-800 font-semibold">💸 <?= $pengeluaranMasuk ?> Pengeluaran di atas 50jt menunggu verifikasi.</p>
    <a href="verifikasi_pengeluaran.php" class="text-sm text-blue-700 hover:underline">🔍 Lihat Sekarang</a>
  </div>
  <?php endif; ?>
</div>
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10">
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

    <!-- Grafik -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-lg font-bold text-purple-700 mb-4">📊 Grafik Pemasukan vs Pengeluaran</h3>
        <canvas id="grafikKeuangan" height="120"></canvas>
      </div>
      <div class="bg-white p-6 rounded-xl shadow">
        <h3 class="text-lg font-bold text-purple-700 mb-4">📈 Tren Keuangan Tahunan</h3>
        <canvas id="grafikTren" height="120"></canvas>
      </div>
    </div>
  </main>
</div>

<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>

<script>
  const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
  const pemasukanData = <?= json_encode(array_values($pemasukan_bulanan)) ?>;
  const pengeluaranData = <?= json_encode(array_values($pengeluaran_bulanan)) ?>;

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
