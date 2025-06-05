<?php
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'kades') {
    header("Location: login.php");
    exit;
}

// Ambil daftar pengeluaran yang belum diverifikasi
$query = "
    SELECT p.id_pengeluaran, p.tanggal, a.nama_kegiatan, p.jumlah, p.keterangan, p.bukti_pengeluaran
    FROM pengeluaran p
    JOIN anggaran a ON p.id_anggaran = a.id_anggaran
    WHERE p.status_verifikasi = 'pending'
";
$result = mysqli_query($conn, $query);

// Proses verifikasi
if (isset($_GET['verifikasi'])) {
    $id_pengeluaran = $_GET['verifikasi'];
    mysqli_query($conn, "UPDATE pengeluaran SET status_verifikasi='disetujui' WHERE id_pengeluaran='$id_pengeluaran'");
    header("Location: verifikasi_pengeluaran.php");
    exit;
}

if (isset($_GET['tolak'])) {
    $id_pengeluaran = $_GET['tolak'];
    mysqli_query($conn, "UPDATE pengeluaran SET status_verifikasi='ditolak' WHERE id_pengeluaran='$id_pengeluaran'");
    header("Location: verifikasi_pengeluaran.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Verifikasi Pengeluaran</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-8">

  <h1 class="text-3xl font-bold text-green-700 mb-6">Verifikasi Pengeluaran</h1>

  <div class="bg-white shadow-md rounded-lg overflow-x-auto">
    <table class="min-w-full text-sm text-left">
      <thead class="bg-green-600 text-white">
        <tr>
          <th class="px-4 py-2">Tanggal</th>
          <th class="px-4 py-2">Kegiatan</th>
          <th class="px-4 py-2">Jumlah</th>
          <th class="px-4 py-2">Keterangan</th>
          <th class="px-4 py-2">Bukti</th>
          <th class="px-4 py-2">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr class="border-b">
          <td class="px-4 py-2"><?= $row['tanggal']; ?></td>
          <td class="px-4 py-2"><?= $row['nama_kegiatan']; ?></td>
          <td class="px-4 py-2">Rp <?= number_format($row['jumlah'], 0, ',', '.'); ?></td>
          <td class="px-4 py-2"><?= $row['keterangan']; ?></td>
          <td class="px-4 py-2">
            <?php
              $buktiArray = json_decode($row['bukti_pengeluaran'], true);
              if (is_array($buktiArray)) {
                  foreach ($buktiArray as $file) {
                      echo "<a href='../uploads/$file' target='_blank' class='text-blue-600 underline block'>Lihat Bukti</a>";
                  }
              } else {
                  echo "-";
              }
            ?>
          </td>
          <td class="px-4 py-2 space-x-2">
            <a href="?verifikasi=<?= $row['id_pengeluaran']; ?>" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Setujui</a>
            <a href="?tolak=<?= $row['id_pengeluaran']; ?>" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Tolak</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</body>
</html>
