<?php
include '../config/koneksi.php';
session_start();

// Ambil data verifikasi pengeluaran langsung dari pengeluaran_ajuan
$log_pengeluaran = mysqli_query($conn, "
  SELECT 
    pa.tanggal_pengajuan, 
    ang.nama_kegiatan, 
    pa.jumlah_ajuan, 
    pa.status_ajuan,
    pa.tanggal_verifikasi, 
    pa.alasan_penerimaan_pengeluaran, 
    pa.alasan_penolakan_pengeluaran 
  FROM pengeluaran_ajuan pa
  JOIN anggaran ang ON pa.id_anggaran = ang.id_anggaran
  WHERE pa.status_ajuan IN ('disetujui', 'ditolak')
  ORDER BY pa.tanggal_verifikasi DESC, pa.id_ajuan DESC
");

if (!$log_pengeluaran) {
  die("Query pengeluaran gagal: " . mysqli_error($conn));
}

// Ambil data verifikasi laporan langsung dari laporan_ajuan
$log_laporan = mysqli_query($conn, "
  SELECT 
    jenis_laporan, tahap, tahun, 
    status_ajuan AS status_laporan, 
    tanggal_verifikasi AS tgl_verif_laporan, 
    alasan_penerimaan_laporan, alasan_penolakan_laporan 
  FROM laporan_ajuan
  WHERE status_ajuan IN ('disetujui', 'ditolak')
  ORDER BY tanggal_verifikasi DESC, id_ajuan DESC
");

if (!$log_laporan) {
  die("Query laporan gagal: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Notifikasi Verifikasi</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 flex">

<!-- Sidebar -->
<aside class="w-64 bg-purple-800 text-white min-h-screen px-6 py-8 sticky top-0">
  <h2 class="text-xl font-bold mb-8 text-center">Menu</h2>
  <nav class="space-y-3">
    <a href="dashboard_bendahara.php" class="block bg-purple-700 px-4 py-2 rounded-md">⬅️ Kembali ke Dashboard</a>
  </nav>
</aside>

<!-- Header -->
<div class="flex-1">
  <header class="bg-purple-700 text-white px-6 py-6 shadow-lg flex justify-between items-center">
    <div>
      <h1 class="text-2xl font-bold mb-1">Sistem Informasi Keuangan Desa</h1>
      <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
    </div>
    <div class="text-right">
      <p class="text-sm">Selamat datang, <strong>Bendahara</strong></p>
      <a href="../logout.php" class="text-red-300 hover:text-white text-xs underline">Keluar</a>
    </div>
  </header>

  <!-- Konten Utama -->
  <main class="p-6">
    <header class="mb-8">
      <h1 class="text-3xl font-bold text-purple-700">🔔 Log Notifikasi Verifikasi</h1>
    </header>

    <!-- Log Verifikasi Pengeluaran -->
    <div class="bg-white p-4 rounded-lg shadow mb-10 overflow-x-auto">
      <h2 class="text-xl font-semibold text-purple-600 mb-4">📄 Log Verifikasi Pengeluaran (di atas 50 Juta)</h2>
      <table class="table-auto w-full text-sm border border-gray-200">
        <thead class="bg-purple-100">
          <tr>
            <th class="px-3 py-2 border">Nama Kegiatan</th>
            <th class="px-3 py-2 border">Jumlah Ajuan</th>
            <th class="px-3 py-2 border">Tanggal Ajuan</th>
            <th class="px-3 py-2 border">Status</th>
            <th class="px-3 py-2 border">Alasan</th>
            <th class="px-3 py-2 border">Tanggal Verifikasi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($log_pengeluaran)): ?>
          <tr class="border-b">
            <td class="px-3 py-2"><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
            <td class="px-3 py-2">Rp <?= number_format($row['jumlah_ajuan'], 0, ',', '.') ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['tanggal_pengajuan']) ?></td>
            <td class="px-3 py-2">
              <?= $row['status_ajuan'] === 'disetujui' 
                ? '<span class="text-green-600 font-semibold">Disetujui</span>' 
                : '<span class="text-red-600 font-semibold">Ditolak</span>' ?>
            </td>
            <td class="px-3 py-2">
              <?= $row['status_ajuan'] === 'disetujui'
                ? (!empty($row['alasan_penerimaan_pengeluaran']) ? htmlspecialchars($row['alasan_penerimaan_pengeluaran']) : '-')
                : (!empty($row['alasan_penolakan_pengeluaran']) ? htmlspecialchars($row['alasan_penolakan_pengeluaran']) : '-') ?>
            </td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['tanggal_verifikasi']) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- Log Verifikasi Laporan -->
    <div class="bg-white p-4 rounded-lg shadow overflow-x-auto">
      <h2 class="text-xl font-semibold text-purple-600 mb-4">📉 Log Verifikasi Laporan (Pertahap & LPJ Tahunan)</h2>
      <table class="table-auto w-full text-sm border border-gray-200">
        <thead class="bg-purple-100">
          <tr>
            <th class="px-3 py-2 border">Jenis Laporan</th>
            <th class="px-3 py-2 border">Tahap</th>
            <th class="px-3 py-2 border">Tahun</th>
            <th class="px-3 py-2 border">Status</th>
            <th class="px-3 py-2 border">Alasan</th>
            <th class="px-3 py-2 border">Tanggal Verifikasi</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = mysqli_fetch_assoc($log_laporan)): ?>
          <tr class="border-b">
            <td class="px-3 py-2"><?= $row['jenis_laporan'] === 'tahunan' ? 'LPJ Tahunan' : 'Laporan Pertahap' ?></td>
            <td class="px-3 py-2"><?= $row['jenis_laporan'] === 'tahunan' ? '-' : strtoupper(str_replace('_', ' ', $row['tahap'])) ?></td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['tahun']) ?></td>
            <td class="px-3 py-2">
              <?= $row['status_laporan'] === 'disetujui'
                ? '<span class="text-green-600 font-semibold">Disetujui</span>'
                : '<span class="text-red-600 font-semibold">Ditolak</span>' ?>
            </td>
            <td class="px-3 py-2">
              <?= $row['status_laporan'] === 'disetujui'
                ? (!empty($row['alasan_penerimaan_laporan']) ? htmlspecialchars($row['alasan_penerimaan_laporan']) : '-')
                : (!empty($row['alasan_penolakan_laporan']) ? htmlspecialchars($row['alasan_penolakan_laporan']) : '-') ?>
            </td>
            <td class="px-3 py-2"><?= htmlspecialchars($row['tgl_verif_laporan']) ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>
