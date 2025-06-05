<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'kades') {
    header("Location: login.php");
    exit;
}

// Ambil data laporan tahunan yang belum diverifikasi
$query = "
    SELECT t.id_laporan, t.nama_laporan, t.tahun_anggaran, t.tanggal_upload, t.file_laporan, t.status_verifikasi, u.nama
    FROM laporan_tahunan t
    JOIN user u ON t.dibuat_oleh = u.id_user
    WHERE t.status_verifikasi = 'pending'
";
$result = mysqli_query($conn, $query);

// Verifikasi
if (isset($_GET['verifikasi'])) {
    $id = $_GET['verifikasi'];
    mysqli_query($conn, "UPDATE laporan_tahunan SET status_verifikasi='disetujui' WHERE id_laporan='$id'");
    header("Location: verifikasi_laporan_tahunan.php");
    exit;
}

if (isset($_GET['tolak'])) {
    $id = $_GET['tolak'];
    mysqli_query($conn, "UPDATE laporan_tahunan SET status_verifikasi='ditolak' WHERE id_laporan='$id'");
    header("Location: verifikasi_laporan_tahunan.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Verifikasi Laporan Tahunan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8 min-h-screen">

  <h1 class="text-3xl font-bold text-green-700 mb-6">Verifikasi Laporan Pertanggungjawaban Tahunan</h1>

  <div class="bg-white shadow rounded-lg overflow-x-auto">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-green-600 text-white">
        <tr>
          <th class="px-4 py-2">Nama Laporan</th>
          <th class="px-4 py-2">Tahun</th>
          <th class="px-4 py-2">Tanggal Upload</th>
          <th class="px-4 py-2">Dibuat Oleh</th>
          <th class="px-4 py-2">File</th>
          <th class="px-4 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr class="border-b">
          <td class="px-4 py-2"><?= htmlspecialchars($row['nama_laporan']); ?></td>
          <td class="px-4 py-2"><?= $row['tahun_anggaran']; ?></td>
          <td class="px-4 py-2"><?= $row['tanggal_upload']; ?></td>
          <td class="px-4 py-2"><?= $row['nama']; ?></td>
          <td class="px-4 py-2">
            <a href="../uploads/<?= $row['file_laporan']; ?>" target="_blank" class="text-blue-600 underline">Lihat File</a>
          </td>
          <td class="px-4 py-2 space-x-2">
            <a href="?verifikasi=<?= $row['id_laporan']; ?>" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Setujui</a>
            <a href="?tolak=<?= $row['id_laporan']; ?>" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Tolak</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
