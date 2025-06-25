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

  <!-- Header -->
  <header class="bg-purple-700 text-white px-6 py-6 shadow flex justify-between items-center h-28">
    <div>
      <h1 class="text-2xl font-bold">💰 Sistem Informasi Keuangan Desa</h1>
      <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
    </div>
    <div class="text-right">
      <p class="text-sm">Selamat datang, <strong>Bendahara</strong></p>
      <a href="../logout.php" class="text-red-300 hover:text-white text-xs underline">Keluar</a>
    </div>
  </header>

  <!-- Konten Utama: Sidebar + Konten -->
  <div class="flex flex-1 min-h-[calc(100vh-7rem)]">
    <!-- Sidebar -->
    <aside class="w-72 bg-purple-800 text-white px-6 py-8 flex-shrink-0">
      <h2 class="text-xl font-bold mb-6 text-center">Menu</h2>
      <nav class="space-y-3">
        <a href="#" class="block bg-purple-700 px-4 py-2 rounded">📊 Dashboard</a>
        <a href="input_pemasukan.php" class="block hover:bg-purple-700 px-4 py-2 rounded">➕ Input Pemasukan</a>
        <a href="kelola_anggaran.php" class="block hover:bg-purple-700 px-4 py-2 rounded">📁 Kelola Anggaran</a>
        <a href="input_pengeluaran.php" class="block hover:bg-purple-700 px-4 py-2 rounded">💸 Input Pengeluaran</a>
        <a href="laporan.php" class="block hover:bg-purple-700 px-4 py-2 rounded">📁 Laporan</a>
        <a href="notifikasi_verifikasi.php" class="block hover:bg-purple-700 px-4 py-2 rounded">🔔 Notifikasi Verifikasi</a>
        <a href="publikasi_laporan.php" class="block hover:bg-purple-700 px-4 py-2 rounded">📢 Publikasi Laporan</a>
        <a href="managemen_akun.php" class="block hover:bg-purple-700 px-4 py-2 rounded">👥 Manajemen Akun</a>
        <a href="lihat_feddback.php" class="block hover:bg-purple-700 px-4 py-2 rounded">💬 Feedback</a>
        <a href="backup_data.php" class="block hover:bg-purple-700 px-4 py-2 rounded">📂 Backup</a>
      </nav>
    </aside>

    <!-- Konten Dashboard -->
    <main class="flex-1 p-8">
      <h2 class="text-2xl font-bold text-purple-700 mb-6">Dashboard Bendahara</h2>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-green-100 p-5 rounded-xl shadow text-center">
          <p class="text-sm font-semibold text-gray-600">Total Pemasukan</p>
          <p class="text-2xl font-bold text-green-700">Rp 5.500.000.000</p>
        </div>
        <div class="bg-yellow-100 p-5 rounded-xl shadow text-center">
          <p class="text-sm font-semibold text-gray-600">Total Alokasi Dana</p>
          <p class="text-2xl font-bold text-yellow-700">Rp 4.000.000.000</p>
        </div>
        <div class="bg-red-100 p-5 rounded-xl shadow text-center">
          <p class="text-sm font-semibold text-gray-600">Total Pengeluaran</p>
          <p class="text-2xl font-bold text-red-700">Rp 2.000.000.000</p>
        </div>
        <div class="bg-indigo-100 p-5 rounded-xl shadow text-center">
          <p class="text-sm font-semibold text-gray-600">Sisa Saldo</p>
          <p class="text-2xl font-bold text-indigo-700">Rp 3.500.000.000</p>
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

 <footer class="text-white py-10 px-8 w-full"
        style="background-image: url('../assets/img/nc.jpg'); background-size: cover; background-position: center;">
  <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center text-sm">
    <p>&copy; <?= date('Y') ?> <span class="font-semibold">Sistem Informasi Keuangan Desa Sanggaria</span>. All rights reserved.</p>
    <div class="flex space-x-6 mt-3 md:mt-0">
      <a href="#" class="hover:underline">Tentang</a>
      <a href="#" class="hover:underline">Kegiatan</a>
      <a href="#" class="hover:underline">Feedback</a>
    </div>
  </div>
</footer>



  <script>
    const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const pemasukanData = [500000, 800000, 900000, 1200000, 700000, 650000, 980000, 870000, 920000, 1100000, 800000, 1000000];
    const pengeluaranData = [200000, 300000, 250000, 400000, 350000, 300000, 450000, 500000, 550000, 600000, 700000, 750000];

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
