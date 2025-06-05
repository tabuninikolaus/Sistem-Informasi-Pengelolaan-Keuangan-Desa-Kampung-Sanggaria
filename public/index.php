<?php
include '../config/koneksi.php';

// Hitung Total Pemasukan
$pemasukan_q = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pemasukan");
$pemasukan = mysqli_fetch_assoc($pemasukan_q)['total'] ?? 0;

// Hitung Total Pengeluaran
$pengeluaran_q = mysqli_query($conn, "SELECT SUM(jumlah) AS total FROM pengeluaran");
$pengeluaran = mysqli_fetch_assoc($pengeluaran_q)['total'] ?? 0;

// Hitung Sisa Dana
$sisa_dana = $pemasukan - $pengeluaran;

// Jumlah Kegiatan
$jumlah_kegiatan = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM anggaran"));

// Ambil data kegiatan dan hitung realisasi
$kegiatan = mysqli_query($conn, "SELECT * FROM anggaran");
function getRealisasi($conn, $id_anggaran, $alokasi_dana) {
  $query = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran'");
  $hasil = mysqli_fetch_assoc($query);
  $total_pengeluaran = $hasil['total'] ?? 0;
  if ($alokasi_dana == 0) return '0%';
  $persen = round(($total_pengeluaran / $alokasi_dana) * 100);
  return $persen . '%';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Beranda - Sistem Keuangan Desa Sanggaria</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">

<header class="bg-white shadow sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
    <h1 class="text-lg font-bold text-indigo-700">Keuangan Desa Sanggaria</h1>
    <nav class="space-x-4">
      <a href="#" class="text-gray-700 hover:text-indigo-700 font-medium">Beranda</a>
      <a href="#tentang" class="text-gray-700 hover:text-indigo-700 font-medium">Tentang</a>
      <a href="#kegiatan" class="text-gray-700 hover:text-indigo-700 font-medium">Kegiatan</a>
      <a href="#feedback" class="text-gray-700 hover:text-indigo-700 font-medium">Feedback</a>
    </nav>
  </div>
</header>

<div class="relative h-[60vh] max-h-[500px] bg-cover bg-center" style="background-image: url('../assets/img/sanggaria.jpeg');">
  <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
    <div class="text-center text-white px-4">
      <h1 class="text-4xl md:text-5xl font-bold mb-4">Sistem Pengelolaan Keuangan Desa Sanggaria</h1>
      <p class="text-lg max-w-2xl mx-auto">Transparansi dan Akuntabilitas Keuangan untuk Masyarakat Sanggaria yang Lebih Baik.</p>
    </div>
  </div>
</div>

<section id="tentang" class="py-12 px-6 md:px-16 bg-white max-w-7xl mx-auto">
  <h2 class="text-2xl md:text-3xl font-bold text-center text-indigo-700 mb-6">Apa Itu Website Pengelolaan Keuangan Desa?</h2>
  <p class="text-center max-w-4xl mx-auto text-gray-700 text-lg">
    Website ini dirancang untuk memberikan informasi terbuka mengenai pemasukan, pengeluaran, dan kegiatan yang didanai oleh Dana Desa di Kampung Sanggaria, Distrik Arso Barat, Kabupaten Keerom.
  </p>
</section>

<section class="py-12 px-6 md:px-16 bg-gray-100 max-w-7xl mx-auto">
  <h2 class="text-2xl font-bold text-center text-indigo-700 mb-8"> Ringkasan Keuangan Desa</h2>
  <div class="grid md:grid-cols-4 gap-6 text-center">
    <div class="bg-white p-6 shadow rounded-xl">
      <h3 class="text-lg font-semibold text-gray-600">Total Pemasukan</h3>
      <p class="text-2xl font-bold text-green-600">Rp <?= number_format($pemasukan, 0, ',', '.') ?></p>
    </div>
    <div class="bg-white p-6 shadow rounded-xl">
      <h3 class="text-lg font-semibold text-gray-600">Jumlah Kegiatan</h3>
      <p class="text-2xl font-bold text-blue-600"><?= $jumlah_kegiatan ?> Kegiatan</p>
    </div>
    <div class="bg-white p-6 shadow rounded-xl">
      <h3 class="text-lg font-semibold text-gray-600">Total Pengeluaran</h3>
      <p class="text-2xl font-bold text-red-600">Rp <?= number_format($pengeluaran, 0, ',', '.') ?></p>
    </div>
    <div class="bg-white p-6 shadow rounded-xl">
      <h3 class="text-lg font-semibold text-gray-600">Sisa Dana</h3>
      <p class="text-2xl font-bold text-indigo-600">Rp <?= number_format($sisa_dana, 0, ',', '.') ?></p>
    </div>
  </div>
</section>

<section id="kegiatan" class="py-12 px-6 md:px-16 bg-white max-w-7xl mx-auto">
  <h2 class="text-2xl font-bold text-center text-indigo-700 mb-8"> Kegiatan yang Didanai</h2>
  <div class="overflow-auto">
    <table class="w-full text-sm border">
      <thead class="bg-indigo-100">
        <tr>
          <th class="px-4 py-2 border">No</th>
          <th class="px-4 py-2 border">Nama Kegiatan</th>
          <th class="px-4 py-2 border">Alokasi Dana</th>
          <th class="px-4 py-2 border">Realisasi</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($row = mysqli_fetch_assoc($kegiatan)): ?>
        <tr>
          <td class="border px-4 py-2 text-center"><?= $no++ ?></td>
          <td class="border px-4 py-2"><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
          <td class="border px-4 py-2">Rp <?= number_format($row['alokasi_dana'], 0, ',', '.') ?></td>
          <td class="border px-4 py-2 text-center text-indigo-700 font-semibold">
            <?= getRealisasi($conn, $row['id_anggaran'], $row['alokasi_dana']) ?>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>

<section id="feedback" class="py-12 px-6 md:px-16 bg-gray-100 max-w-7xl mx-auto">
  <h2 class="text-2xl font-bold text-center text-indigo-700 mb-6"> Kirim Masukan & Saran</h2>
  <form action="simpan_feedback.php" method="POST" class="max-w-2xl mx-auto bg-white p-6 rounded-xl shadow space-y-5">
    <input type="text" name="nama" required placeholder="Nama" class="w-full px-4 py-2 border rounded-lg">
    <input type="email" name="email" required placeholder="Email" class="w-full px-4 py-2 border rounded-lg">
    <input type="number" name="usia" required placeholder="Usia" class="w-full px-4 py-2 border rounded-lg">
    <select name="jenis_kelamin" required class="w-full px-4 py-2 border rounded-lg">
      <option value="">-- Pilih Jenis Kelamin --</option>
      <option value="Laki-laki">Laki-laki</option>
      <option value="Perempuan">Perempuan</option>
    </select>
    <textarea name="pesan" rows="4" required placeholder="Pesan / Saran" class="w-full px-4 py-2 border rounded-lg"></textarea>
    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg font-semibold">
      Kirim Feedback
    </button>
  </form>
</section>

<footer class="bg-gray-900 text-white py-6 text-center text-sm">
  <p>&copy; <?= date('Y') ?> Pemerintah Kampung Sanggaria | Sistem Informasi Keuangan Desa</p>
</footer>

</body>
</html>
