<?php
include '../config/koneksi.php';
session_start();

$success = '';
$error = '';

// Simpan Ringkasan
if (isset($_POST['simpan_ringkasan'])) {
  $tahun = $_POST['tahun'];
  $total_pemasukan = str_replace('.', '', $_POST['total_pemasukan']);
  $total_pengeluaran = str_replace('.', '', $_POST['total_pengeluaran']);
  $sisa_dana = str_replace('.', '', $_POST['sisa_dana']);
  $jumlah_kegiatan = $_POST['jumlah_kegiatan'];

  $cek = mysqli_query($conn, "SELECT * FROM publikasi_ringkasan WHERE tahun='$tahun'");
  if (mysqli_num_rows($cek) > 0) {
    $error = "Ringkasan untuk tahun $tahun sudah ada.";
  } else {
    $sql = "INSERT INTO publikasi_ringkasan (tahun, total_pemasukan, total_pengeluaran, sisa_dana, jumlah_kegiatan)
            VALUES ('$tahun', '$total_pemasukan', '$total_pengeluaran', '$sisa_dana', '$jumlah_kegiatan')";
    $success = mysqli_query($conn, $sql) ? 'Ringkasan berhasil disimpan.' : 'Gagal menyimpan ringkasan.';
  }
}

// Simpan Kegiatan
if (isset($_POST['simpan_kegiatan'])) {
  $tahun = $_POST['tahun_kegiatan'];
  $nama_kegiatan = $_POST['nama_kegiatan'];
  $alokasi_dana = str_replace('.', '', $_POST['alokasi_dana']);
  $realisasi_persen = $_POST['realisasi_persen'];

  $sql = "INSERT INTO publikasi_kegiatan (tahun, nama_kegiatan, alokasi_dana, realisasi_persen)
          VALUES ('$tahun', '$nama_kegiatan', '$alokasi_dana', '$realisasi_persen')";
  $success = mysqli_query($conn, $sql) ? 'Kegiatan berhasil ditambahkan.' : 'Gagal menyimpan kegiatan.';
}

// Ambil daftar kegiatan
$daftar_kegiatan = mysqli_query($conn, "SELECT * FROM publikasi_kegiatan ORDER BY tahun DESC, id_kegiatan DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Publikasi Laporan Keuangan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<header class="bg-purple-700 text-white px-6 py-6 shadow-lg flex justify-between items-center h-28">
  <div>
    <h1 class="text-2xl font-bold mb-1">Sistem Informasi Keuangan Desa</h1>
    <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
  </div>
  <div class="text-right">
    <p class="text-sm">Selamat datang, <strong>Bendahara</strong></p>
    <a href="../logout.php" class="text-red-300 hover:text-white text-xs underline">Keluar</a>
  </div>
</header>

<div class="flex">
  <aside class="w-64 bg-purple-800 text-white min-h-screen px-6 py-8">
    <h2 class="text-xl font-bold mb-8 text-center">Menu</h2>
    <nav class="space-y-3">
       <a href="dashboard_bendahara.php" class="block bg-purple-700 px-4 py-2 rounded-md">⬅️ Kembali ke Dashboard</a>
    </nav>
  </aside>
  <main class="flex-1 p-10">
    <div class="max-w-3xl mx-auto bg-white p-8 rounded shadow">
      <h1 class="text-2xl font-bold text-purple-700 mb-6">📢 Publikasi Laporan Keuangan Desa</h1>

      <?php if ($success): ?>
        <div class="bg-green-100 text-green-700 px-4 py-2 mb-4 rounded border border-green-400">
          <?= $success ?>
        </div>
      <?php elseif ($error): ?>
        <div class="bg-red-100 text-red-700 px-4 py-2 mb-4 rounded border border-red-400">
          <?= $error ?>
        </div>
      <?php endif; ?>

      <form method="POST" class="space-y-4">
        <h2 class="text-lg font-bold text-gray-700">🧾 Ringkasan Keuangan Desa</h2>
        <div class="grid grid-cols-2 gap-4">
          <input type="number" name="tahun" class="border px-3 py-2 rounded" placeholder="Tahun" required>
          <input type="text" name="total_pemasukan" class="border px-3 py-2 rounded" placeholder="Total Pemasukan" required>
          <input type="text" name="total_pengeluaran" class="border px-3 py-2 rounded" placeholder="Total Pengeluaran" required>
          <input type="text" name="sisa_dana" class="border px-3 py-2 rounded" placeholder="Sisa Dana" required>
          <input type="number" name="jumlah_kegiatan" class="border px-3 py-2 rounded" placeholder="Jumlah Kegiatan" required>
        </div>
        <button type="submit" name="simpan_ringkasan" class="bg-purple-700 text-white px-6 py-2 rounded hover:bg-purple-800 mt-2">Simpan Ringkasan</button>
      </form>

      <hr class="my-6">

      <form method="POST" class="space-y-4">
        <h2 class="text-lg font-bold text-gray-700">📋 Tambah Kegiatan yang Didanai</h2>
        <input type="number" name="tahun_kegiatan" class="w-full border px-3 py-2 rounded" placeholder="Tahun" required>
        <input type="text" name="nama_kegiatan" class="w-full border px-3 py-2 rounded" placeholder="Nama Kegiatan" required>
        <div class="grid grid-cols-2 gap-4">
          <input type="text" name="alokasi_dana" class="border px-3 py-2 rounded" placeholder="Alokasi Dana" required>
          <input type="number" name="realisasi_persen" class="border px-3 py-2 rounded" placeholder="Realisasi (%)" max="100" required>
        </div>
        <button type="submit" name="simpan_kegiatan" class="bg-blue-700 text-white px-6 py-2 rounded hover:bg-blue-800">Tambah Kegiatan</button>
      </form>

      <hr class="my-6">

      <h2 class="text-lg font-bold text-gray-700 mb-3">📑 Daftar Kegiatan yang Dipublikasikan</h2>
      <table class="w-full text-sm border">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-2 py-1">Tahun</th>
            <th class="border px-2 py-1">Nama Kegiatan</th>
            <th class="border px-2 py-1">Alokasi</th>
            <th class="border px-2 py-1">Realisasi</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($daftar_kegiatan)): ?>
            <tr>
              <td class="border px-2 py-1 text-center"><?= $row['tahun'] ?></td>
              <td class="border px-2 py-1"><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
              <td class="border px-2 py-1">Rp <?= number_format($row['alokasi_dana'], 0, ',', '.') ?></td>
              <td class="border px-2 py-1 text-center"><?= $row['realisasi_persen'] ?>%</td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
