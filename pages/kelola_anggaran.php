<?php 
include '../config/koneksi.php';
session_start();

// Cek hak akses
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Ambil total pemasukan
$result = mysqli_query($conn, "SELECT SUM(jumlah) AS total_pemasukan FROM pemasukan");
$row = mysqli_fetch_assoc($result);
$total_pemasukan = $row['total_pemasukan'] ?? 0;

// Ambil total anggaran yang sudah dialokasikan
$result = mysqli_query($conn, "SELECT SUM(alokasi_dana) AS total_alokasi FROM anggaran");
$row = mysqli_fetch_assoc($result);
$total_alokasi = $row['total_alokasi'] ?? 0;

// Hitung sisa dana
$sisa_dana = $total_pemasukan - $total_alokasi;

if (isset($_POST['simpan'])) {
    $tahun = $_POST['tahun'];
    $kegiatan = $_POST['kegiatan'];
    $alokasi = $_POST['alokasi']; // dari hidden input sudah bersih
    $keterangan = $_POST['keterangan'];

    if ($alokasi > $sisa_dana) {
        echo "<script>alert('Alokasi dana melebihi sisa dana yang tersedia!');</script>";
    } else {
        $query = "INSERT INTO anggaran (tahun, nama_kegiatan, alokasi_dana, keterangan)
                  VALUES ('$tahun', '$kegiatan', '$alokasi', '$keterangan')";

        if (mysqli_query($conn, $query)) {
            echo "<script>alert('Data anggaran berhasil disimpan'); window.location.href='kelola_anggaran.php';</script>";
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Kelola Anggaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
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
    <h2 class="text-xl font-bold mb-8 text-center">💰 Menu</h2>
    <nav class="space-y-3">
      <a href="dashboard_bendahara.php" class="block bg-purple-700 px-4 py-2 rounded-md">⬅️ Kembali ke Dashboard</a>
    </nav>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="flex-1 p-10">
    <h2 class="text-2xl font-bold mb-6 text-purple-700">Form Kelola Anggaran (APBDes)</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-white border-l-4 border-green-500 shadow p-4 rounded-xl">
        <p class="text-sm text-gray-600">💰 Total Dana Masuk</p>
        <p class="text-xl font-bold text-green-700">Rp <?= number_format($total_pemasukan, 0, ',', '.') ?></p>
      </div>

      <div class="bg-white border-l-4 border-yellow-500 shadow p-4 rounded-xl">
        <p class="text-sm text-gray-600">📊 Dana Dialokasikan</p>
        <p class="text-xl font-bold text-yellow-600">Rp <?= number_format($total_alokasi, 0, ',', '.') ?></p>
      </div>

      <div class="bg-white border-l-4 border-blue-500 shadow p-4 rounded-xl">
        <p class="text-sm text-gray-600">💼 Sisa Dana Tersedia</p>
        <p class="text-xl font-bold text-blue-700">Rp <?= number_format($sisa_dana, 0, ',', '.') ?></p>
      </div>
    </div>

    <div class="bg-white p-8 rounded-xl shadow-md max-w-2xl">
      <form method="POST" class="space-y-5">
        <div>
          <label class="block text-gray-700">Tahun Anggaran</label>
          <input type="number" name="tahun" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700">Nama Kegiatan</label>
          <input type="text" name="kegiatan" placeholder="Contoh: Pembangunan Jalan Kampung" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600">
        </div>

        <div>
          <label class="block text-gray-700">Alokasi Dana (Rp)</label>
          <input type="text" id="alokasiFormatted" placeholder="Contoh: 500.000.000"
                 class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" required>
          <input type="hidden" name="alokasi" id="alokasi">
        </div>

        <div>
          <label class="block text-gray-700">Keterangan</label>
          <textarea name="keterangan" rows="3" required class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-600" placeholder="Misal: Program dari DD Tahap I 2025"></textarea>
        </div>

        <button type="submit" name="simpan" class="w-full bg-purple-700 hover:bg-purple-800 text-white py-2 rounded-lg font-semibold transition">
          Simpan Anggaran
        </button>
      </form>
    </div>
  </main>
</div>

<footer class="text-center text-sm text-gray-500 py-4">
  &copy; <?= date('Y') ?> Sistem Keuangan Desa. All rights reserved.
</footer>

<script>
  const formatInput = document.getElementById('alokasiFormatted');
  const hiddenInput = document.getElementById('alokasi');

  formatInput.addEventListener('input', function(e) {
    let numericValue = e.target.value.replace(/\D/g, '');
    hiddenInput.value = numericValue;
    let formatted = new Intl.NumberFormat('id-ID').format(numericValue);
    formatInput.value = formatted;
  });
</script>

</body>
</html>
