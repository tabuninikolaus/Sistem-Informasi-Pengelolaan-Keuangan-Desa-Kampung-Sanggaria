<?php
include '../config/koneksi.php';
$feedbacks = mysqli_query($conn, "SELECT * FROM feedback ORDER BY tanggal DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Feedback</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f5f7fa] text-gray-800 font-sans">

<!-- HEADER -->
<?php include '../includes/header.php'; ?>

<div class="flex flex-1">
  <!-- SIDEBAR -->
<?php include '../includes/sidebar.php'; ?>
  <!-- Konten Tabel -->
  <div class="p-8 max-w-6xl mx-auto">
    <h2 class="text-2xl font-bold text-indigo-700 mb-4">💬 Data Masukan Masyarakat</h2>
    <div class="bg-white rounded-lg shadow p-6 overflow-x-auto">
      <table class="table-auto w-full text-sm border border-gray-200">
        <thead class="bg-indigo-100">
          <tr>
            <th class="border px-3 py-2">No</th>
            <th class="border px-3 py-2">Nama</th>
            <th class="border px-3 py-2">Email</th>
            <th class="border px-3 py-2">JK</th>
            <th class="border px-3 py-2">Usia</th>
            <th class="border px-3 py-2">Pesan</th>
            <th class="border px-3 py-2">Tanggal</th>
          </tr>
        </thead>
        <tbody>
          <?php $no=1; while($f = mysqli_fetch_assoc($feedbacks)): ?>
          <tr class="hover:bg-gray-50 align-top">
            <td class="border px-3 py-2"><?= $no++ ?></td>
            <td class="border px-3 py-2"><?= htmlspecialchars($f['nama']) ?></td>
            <td class="border px-3 py-2"><?= htmlspecialchars($f['email']) ?></td>
            <td class="border px-3 py-2"><?= $f['jenis_kelamin'] ?></td>
            <td class="border px-3 py-2"><?= $f['usia'] ?></td>
            <td class="border px-3 py-2 max-w-xs break-words"><?= nl2br(htmlspecialchars($f['pesan'])) ?></td>
            <td class="border px-3 py-2"><?= $f['tanggal'] ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>
</body>
</html>
