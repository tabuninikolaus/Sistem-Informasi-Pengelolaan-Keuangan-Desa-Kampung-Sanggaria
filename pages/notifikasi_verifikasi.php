<?php
include '../config/koneksi.php';
session_start();


// NOTIF STUFFS
mysqli_query($conn, "UPDATE log_verifikasi SET status_dibaca = 'sudah' WHERE untuk = 'bendahara' AND status_dibaca = 'belum'");

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
<body class="bg-[#f5f7fa] text-gray-800 font-sans">

<!-- SIDEBAR -->
<?php include '../includes/header.php'; ?>

<div class="flex">
  <!-- SIDEBAR -->
<?php include '../includes/sidebar.php'; ?>
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
    </div>
      <!-- FOOTER -->
<?php include '../includes/footer.php'; ?>
  </main>


</div>
</body>
</html>
