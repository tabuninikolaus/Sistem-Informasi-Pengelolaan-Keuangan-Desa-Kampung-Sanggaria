<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Bendahara</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans text-gray-800 min-h-screen flex flex-col">
<?php
  session_start();
  include '../config/koneksi.php'; // 

  if (!isset($_SESSION['id_user'])) {
    die("Akses ditolak. Silakan login terlebih dahulu.");
  }

  $user_id = $_SESSION['id_user']; // harus sama dengan yang disimpan saat login

  // Ambil data user dari tabel
  $queryUser = mysqli_query($conn, "SELECT nama_lengkap, foto_profil FROM users WHERE id_user = '$user_id'");
  
  if (!$queryUser) {
    die("Query user gagal: " . mysqli_error($conn));
  }

  $user = mysqli_fetch_assoc($queryUser);
  // Ambil total data keuangan
  $q_pemasukan = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pemasukan");
  $q_anggaran = mysqli_query($conn, "SELECT SUM(alokasi_dana) as total FROM anggaran");
  $q_pengeluaran = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran");

  $total_pemasukan = mysqli_fetch_assoc($q_pemasukan)['total'] ?? 0;
  $total_anggaran = mysqli_fetch_assoc($q_anggaran)['total'] ?? 0;
  $total_pengeluaran = mysqli_fetch_assoc($q_pengeluaran)['total'] ?? 0;
  $saldo = $total_pemasukan - $total_pengeluaran;

          // NOTIFIKASI QUERY

          // Cek notifikasi untuk bendahara
          $notif_query = mysqli_query($conn, "SELECT * FROM log_verifikasi WHERE untuk = 'bendahara' AND status_dibaca = 'belum'");

          $jml_lap_disetujui = 0;
          $jml_lap_ditolak = 0;
          $jml_peng_disetujui = 0;
          $jml_peng_ditolak = 0;

              while ($row = mysqli_fetch_assoc($notif_query)) {
          if ($row['jenis'] == 'laporan') {
              if ($row['status'] == 'disetujui') $jml_lap_disetujui++;
              if ($row['status'] == 'ditolak') $jml_lap_ditolak++;
          }
          if ($row['jenis'] == 'pengeluaran') {
              if ($row['status'] == 'diterima') $jml_peng_disetujui++;  // UBAH INI
              if ($row['status'] == 'ditolak') $jml_peng_ditolak++;
          }
          }

$total_bendahara_notif = $jml_lap_disetujui + $jml_lap_ditolak + $jml_peng_disetujui + $jml_peng_ditolak;
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
      <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
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

  <!-- Konten Utama: Sidebar + Konten -->
  <div class="flex flex-1 min-h-[calc(100vh-7rem)]">
    <!-- Sidebar -->
    <aside class="tosca bg-teal-700 text-white px-6 py-8 shadow-md overflow-hidden">
      <h2 class="text-xl font-bold mb-6 text-center">Menu</h2>
      <nav class="space-y-3">
        <a href="#" class="block bg-slate-700 px-4 py-2 rounded">📊 Dashboard</a>
        <a href="input_pemasukan.php" class="block hover:bg-slate-600 px-4 py-2 rounded">➕ Input Pemasukan</a>
        <a href="kelola_anggaran.php" class="block hover:bg-slate-600 px-4 py-2 rounded">📁 Kelola Anggaran</a>
        <a href="input_pengeluaran.php" class="block hover:bg-slate-600 px-4 py-2 rounded">💸 Input Pengeluaran</a>
        <a href="laporan.php" class="block hover:bg-slate-600 px-4 py-2 rounded">📁 Laporan</a>
        <a href="notifikasi_verifikasi.php" class="block hover:bg-slate-600 px-4 py-2 rounded">🔔 Notifikasi Verifikasi</a>
        <a href="input_detail_pengeluaran.php" class="block hover:bg-slate-600 px-4 py-2 rounded">🔔 Input Detail Pengeluaran</a>
        <a href="managemen_akun.php" class="block hover:bg-slate-600 px-4 py-2 rounded">👥 Manajemen Akun</a>
        <a href="backup_data.php" class="block hover:bg-slate-600 px-4 py-2 rounded">📂 Backup</a>
        <a href="lihat_feddback.php" class="block hover:bg-slate-600 px-4 py-2 rounded">💬 Feedback</a>
      </nav>
    </aside>

    <!-- Konten Dashboard -->
    <main class="flex-1 p-8">
      <h2 class="text-2xl font-bold text-purple-700 mb-6">Dashboard Bendahara</h2>
      <!-- NOTIFIKASI -->
           <?php if ($total_bendahara_notif > 0): ?>
              <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-4 mb-6 rounded-md flex justify-between items-center">
                <div>
                  <p class="font-semibold">🔔 Ada <?= $total_bendahara_notif ?> verifikasi baru dari Kepala Desa</p>
                  <ul class="text-sm ml-4 list-disc">
                    <?php if ($jml_peng_disetujui > 0): ?><li><?= $jml_peng_disetujui ?> pengeluaran disetujui</li><?php endif; ?>
                    <?php if ($jml_peng_ditolak > 0): ?><li><?= $jml_peng_ditolak ?> pengeluaran ditolak</li><?php endif; ?>
                    <?php if ($jml_lap_disetujui > 0): ?><li><?= $jml_lap_disetujui ?> laporan keuangan disetujui</li><?php endif; ?>
                    <?php if ($jml_lap_ditolak > 0): ?><li><?= $jml_lap_ditolak ?> laporan keuangan ditolak</li><?php endif; ?>
                  </ul>
                </div>
                <a href="notifikasi_verifikasi.php" class="bg-yellow-500 text-white px-4 py-2 rounded hover:bg-yellow-600">
                  Lihat Notifikasi
                </a>
              </div>
            <?php endif; ?>

     <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-green-100 p-5 rounded-xl shadow text-center">
      <p class="text-sm font-semibold text-gray-600">Total Pemasukan</p>
      <p class="text-2xl font-bold text-green-700">Rp <?php echo number_format($total_pemasukan, 0, ',', '.'); ?></p>
    </div>
    <div class="bg-yellow-100 p-5 rounded-xl shadow text-center">
      <p class="text-sm font-semibold text-gray-600">Total Alokasi Dana</p>
      <p class="text-2xl font-bold text-yellow-700">Rp <?php echo number_format($total_anggaran, 0, ',', '.'); ?></p>
    </div>
    <div class="bg-red-100 p-5 rounded-xl shadow text-center">
      <p class="text-sm font-semibold text-gray-600">Total Pengeluaran</p>
      <p class="text-2xl font-bold text-red-700">Rp <?php echo number_format($total_pengeluaran, 0, ',', '.'); ?></p>
    </div>
    <div class="bg-indigo-100 p-5 rounded-xl shadow text-center">
      <p class="text-sm font-semibold text-gray-600">Sisa Saldo</p>
      <p class="text-2xl font-bold text-indigo-700">Rp <?php echo number_format($saldo, 0, ',', '.'); ?></p>
    </div>
  </div>

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

 <!-- Footer Start-->
<footer class="bg-slate-700 text-gray-300 px-6 py-8 mt-0">
  <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
    
    <!-- Tentang -->
    <div>
      <h3 class="text-lg font-semibold text-white mb-4">🌿 Sistem Keuangan Desa</h3>
      <p class="text-sm text-gray-400">
        Aplikasi keuangan untuk transparansi & akuntabilitas Desa Sanggaria.
      </p>
    </div>

    <!-- Navigasi -->
    <div>
      <h3 class="text-lg font-semibold text-white mb-4">🔗 Navigasi</h3>
      <ul class="space-y-1 text-sm">
        <li><a href="#" class="hover:text-white transition">Beranda</a></li>
        <li><a href="#" class="hover:text-white transition">Laporan Publik</a></li>
        <li><a href="#" class="hover:text-white transition">Tentang Sistem</a></li>
        <li><a href="#" class="hover:text-white transition">Kontak</a></li>
      </ul>
    </div>

    <!-- Kontak -->
    <div>
      <h3 class="text-lg font-semibold text-white mb-4">📬 Kontak</h3>
      <p class="text-sm text-gray-400">📍 Desa Sanggaria, Distrik Arso</p>
      <p class="text-sm text-gray-400">✉️ info@sanggaria.desa.id</p>
      <p class="text-xs text-gray-500 mt-4">&copy; <?= date('Y') ?> <span class="text-white font-semibold">Desa Sanggaria</span>. All rights reserved.</p>
    </div>
  </div>

  <!-- Bawah Footer -->
  <div class="text-center text-xs text-gray-500 mt-8 border-t border-gray-700 pt-4">
    Dibangun dengan ❤️ oleh Tim IT Desa Sanggaria
  </div>
</footer>
<!-- Footer Start End-->




<?php
// Inisialisasi array kosong
$pemasukan_per_bulan = array_fill(1, 12, 0);
$pengeluaran_per_bulan = array_fill(1, 12, 0);

// Query pemasukan per bulan
$q1 = mysqli_query($conn, "SELECT MONTH(tanggal) as bulan, SUM(jumlah) as total FROM pemasukan GROUP BY bulan");
while ($row = mysqli_fetch_assoc($q1)) {
    $pemasukan_per_bulan[(int)$row['bulan']] = (int)$row['total'];
}

// Query pengeluaran per bulan
$q2 = mysqli_query($conn, "SELECT MONTH(tanggal) as bulan, SUM(jumlah) as total FROM pengeluaran GROUP BY bulan");
while ($row = mysqli_fetch_assoc($q2)) {
    $pengeluaran_per_bulan[(int)$row['bulan']] = (int)$row['total'];
}
?>

  <script>
  const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

  const pemasukanData = <?= json_encode(array_values($pemasukan_per_bulan)) ?>;
  const pengeluaranData = <?= json_encode(array_values($pengeluaran_per_bulan)) ?>;

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
