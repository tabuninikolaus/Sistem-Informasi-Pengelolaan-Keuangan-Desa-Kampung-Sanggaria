<?php
session_start();
// if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");
//     exit;
// }
include '../config/koneksi.php';

$backupFolder = '../backup';
if (!file_exists($backupFolder)) {
    mkdir($backupFolder, 0777, true);
}

$msg = '';
if (isset($_POST['backup'])) {
    $nama_file = 'backup_' . date('Ymd_His') . '.sql';
    $backupFile = $backupFolder . '/' . $nama_file;
    $cmd = "mysqldump -u root keuangan_desa > \"$backupFile\"";
    system($cmd, $output);

    // Simpan log backup
    $path_file = $backupFile;
    $sql_log = "INSERT INTO log_backup (nama_file, path_file) VALUES ('$nama_file', '$path_file')";
    mysqli_query($conn, $sql_log);

    $msg = "✅ Backup berhasil dibuat: " . $nama_file;
}

if (isset($_POST['restore'])) {
    if (!empty($_FILES['restore_file']['tmp_name'])) {
        $tmpFile = $_FILES['restore_file']['tmp_name'];
        $cmd = "mysql -u root keuangan_desa < \"$tmpFile\"";
        system($cmd, $output);
        $msg = "🔁 Restore berhasil dari file: " . $_FILES['restore_file']['name'];
    } else {
        $msg = "❌ Gagal, tidak ada file yang dipilih.";
    }
}

// Ambil daftar log backup
$log_backup = mysqli_query($conn, "SELECT * FROM log_backup ORDER BY waktu_backup DESC");

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Backup & Restore Database</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f5f7fa] text-gray-800 font-sans">

<!-- HEADER -->
<?php include '../includes/header.php'; ?>

<div class="flex flex-1">
  <!-- SIDEBAR -->
<?php include '../includes/sidebar.php'; ?>
 
  <main class="p-8 max-w-5xl mx-auto">
    <h2 class="text-2xl font-bold text-purple-700 mb-6">📂 Backup & Restore Database</h2>

    <?php if (!empty($msg)): ?>
      <div class="mb-6 p-4 rounded border text-sm font-medium <?= str_starts_with($msg, '✅') || str_starts_with($msg, '🔄') ? 'bg-green-100 border-green-300 text-green-700' : 'bg-red-100 border-red-300 text-red-700' ?>">
        <?= $msg ?>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
      <form method="POST" class="bg-green-50 p-6 rounded-lg border border-green-200 shadow">
        <h3 class="text-lg font-semibold mb-4 text-green-800">💾 Buat Backup</h3>
        <button type="submit" name="backup" class="w-full bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold">
          Buat Backup Sekarang
        </button>
      </form>

      <form method="POST" enctype="multipart/form-data" class="bg-yellow-50 p-6 rounded-lg border border-yellow-200 shadow">
        <h3 class="text-lg font-semibold mb-4 text-yellow-800">🔁 Restore Database</h3>
        <label class="block mb-2 font-medium">Pilih File SQL:</label>
        <input type="file" name="restore_file" accept=".sql" class="mb-4 px-4 py-2 border rounded w-full bg-white">
        <button type="submit" name="restore" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold">
          Mulai Restore
        </button>
      </form>
    </div>

    <h3 class="text-lg font-semibold mb-4">📁 Daftar Log Backup Tersimpan</h3>
    <div class="overflow-x-auto">
      <table class="w-full text-sm border">
        <thead class="bg-gray-100">
          <tr>
            <th class="border px-3 py-2 text-left">No</th>
            <th class="border px-3 py-2 text-left">Nama File</th>
            <th class="border px-3 py-2 text-left">Waktu Backup</th>
            <th class="border px-3 py-2 text-left">Download</th>
          </tr>
        </thead>
        <tbody>
          <?php if (mysqli_num_rows($log_backup) > 0): $no = 1; while ($row = mysqli_fetch_assoc($log_backup)): ?>
            <tr>
              <td class="border px-3 py-2"><?= $no++ ?></td>
              <td class="border px-3 py-2"><?= $row['nama_file'] ?></td>
              <td class="border px-3 py-2"><?= date('d M Y H:i', strtotime($row['waktu_backup'])) ?></td>
              <td class="border px-3 py-2">
                <a href="<?= $row['path_file'] ?>" class="text-blue-600 underline" download>⬇️ Download</a>
              </td>
            </tr>
          <?php endwhile; else: ?>
            <tr>
              <td colspan="4" class="border px-3 py-4 text-center text-gray-500">Belum ada file backup tercatat di log.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>

</body>
</html>
