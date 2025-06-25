<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'kades') {
    header("Location: login.php");
    exit;
}

// Ambil data pengajuan pengeluaran yang menunggu
$pengeluaran = mysqli_query($conn, "
  SELECT a.*, ang.nama_kegiatan 
  FROM pengeluaran_ajuan a 
  JOIN anggaran ang ON a.id_anggaran = ang.id_anggaran 
  WHERE a.status_ajuan = 'menunggu' 
  ORDER BY a.tanggal_pengajuan DESC
");

// Ambil data laporan pertahap yang menunggu
$laporan_tahap = mysqli_query($conn, "
  SELECT * FROM laporan_ajuan 
  WHERE jenis = 'tahap' AND status_ajuan = 'menunggu' 
  ORDER BY tanggal_pengajuan DESC
");

// Ambil data laporan tahunan yang menunggu
$laporan_tahunan = mysqli_query($conn, "
  SELECT * FROM laporan_ajuan 
  WHERE jenis = 'lpj' AND status_ajuan = 'menunggu' 
  ORDER BY tanggal_pengajuan DESC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Notifikasi Verifikasi</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 text-gray-800">
  <h1 class="text-2xl font-bold text-purple-700 mb-6">📋 Daftar Notifikasi Verifikasi</h1>

  <!-- PENGAJUAN PENGELUARAN -->
  <div class="mb-10">
    <h2 class="text-xl font-semibold text-red-600 mb-2">💸 Pengajuan Pengeluaran</h2>
    <div class="bg-white p-4 shadow rounded-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-red-100">
          <tr>
            <th class="px-4 py-2">Tanggal</th>
            <th class="px-4 py-2">Nama Kegiatan</th>
            <th class="px-4 py-2">Jumlah</th>
            <th class="px-4 py-2">Keterangan</th>
            <th class="px-4 py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($pengeluaran)): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= $row['tanggal_pengajuan'] ?></td>
              <td class="px-4 py-2"><?= $row['nama_kegiatan'] ?></td>
              <td class="px-4 py-2">Rp <?= number_format($row['jumlah_ajuan'], 0, ',', '.') ?></td>
              <td class="px-4 py-2"><?= $row['keterangan'] ?></td>
              <td class="px-4 py-2">
                <a href="verifikasi_pengeluaran.php" class="text-purple-600 underline">Verifikasi</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- LAPORAN PERTAHAP -->
  <div class="mb-10">
    <h2 class="text-xl font-semibold text-yellow-600 mb-2">📁 Laporan Pertahap</h2>
    <div class="bg-white p-4 shadow rounded-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-yellow-100">
          <tr>
            <th class="px-4 py-2">Tanggal</th>
            <th class="px-4 py-2">Periode</th>
            <th class="px-4 py-2">Tahun</th>
            <th class="px-4 py-2">Keterangan</th>
            <th class="px-4 py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($laporan_tahap)): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= $row['tanggal_pengajuan'] ?></td>
              <td class="px-4 py-2">Tahap <?= $row['periode'] ?></td>
              <td class="px-4 py-2"><?= $row['tahun'] ?></td>
              <td class="px-4 py-2"><?= $row['keterangan'] ?></td>
              <td class="px-4 py-2">
                <a href="verifikasi_laporan_pertahap.php" class="text-purple-600 underline">Verifikasi</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- LAPORAN TAHUNAN -->
  <div class="mb-10">
    <h2 class="text-xl font-semibold text-blue-600 mb-2">📑 Laporan LPJ Tahunan</h2>
    <div class="bg-white p-4 shadow rounded-xl">
      <table class="min-w-full text-sm">
        <thead class="bg-blue-100">
          <tr>
            <th class="px-4 py-2">Tanggal</th>
            <th class="px-4 py-2">Tahun</th>
            <th class="px-4 py-2">Keterangan</th>
            <th class="px-4 py-2">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php while($row = mysqli_fetch_assoc($laporan_tahunan)): ?>
            <tr class="border-b">
              <td class="px-4 py-2"><?= $row['tanggal_pengajuan'] ?></td>
              <td class="px-4 py-2"><?= $row['tahun'] ?></td>
              <td class="px-4 py-2"><?= $row['keterangan'] ?></td>
              <td class="px-4 py-2">
                <a href="verifikasi_laporan_tahunan.php" class="text-purple-600 underline">Verifikasi</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
