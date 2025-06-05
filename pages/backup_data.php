<?php 
session_start();
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include '../config/koneksi.php';

$backupFolder = '../backup';
if (!file_exists($backupFolder)) {
    mkdir($backupFolder, 0777, true);
}

$msg = '';
if (isset($_POST['backup'])) {
    $backupFile = $backupFolder . '/backup_' . date('Ymd_His') . '.sql';
    $dbhost = 'localhost';
    $dbuser = 'root';
    $dbpass = '';
    $dbname = 'keuangan_desa';
    $cmd = "mysqldump -h $dbhost -u $dbuser" . ($dbpass ? " -p$dbpass" : '') . " $dbname > \"$backupFile\"";
    system($cmd, $output);
    $msg = "✅ Backup berhasil dibuat: " . basename($backupFile);
}

if (isset($_POST['restore'])) {
    if (!empty($_FILES['restore_file']['tmp_name'])) {
        $tmpFile = $_FILES['restore_file']['tmp_name'];
        $dbhost = 'localhost';
        $dbuser = 'root';
        $dbpass = '';
        $dbname = 'keuangan_desa';
        $cmd = "mysql -h $dbhost -u $dbuser" . ($dbpass ? " -p$dbpass" : '') . " $dbname < \"$tmpFile\"";
        system($cmd, $output);
        $msg = "🔄 Restore berhasil dari file: " . $_FILES['restore_file']['name'];
    } else {
        $msg = "❌ Gagal, tidak ada file yang dipilih.";
    }
}

$files = array_diff(scandir($backupFolder), array('.', '..'));
usort($files, function ($a, $b) use ($backupFolder) {
    return filemtime($backupFolder . '/' . $b) - filemtime($backupFolder . '/' . $a);
});
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Backup & Restore Database</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex">

<!-- Sidebar -->
<aside class="w-64 bg-purple-700 text-white min-h-screen shadow-lg">
  <div class="p-6 text-xl font-bold border-b border-purple-500">⚙️ Admin Panel</div>
  <nav class="flex flex-col p-4 space-y-2">
    <a href="dashboard_bendahara.php" class="hover:bg-purple-800 p-2 rounded">🏠 Dashboard</a>
  </nav>
</aside>

<!-- Main Content -->
<main class="flex-1 p-8">
  <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-xl p-6">
    <h2 class="text-2xl font-bold text-purple-700 mb-6">🗂️ Backup & Restore Database</h2>

    <?php if (!empty($msg)): ?>
      <div class="mb-4 p-3 bg-blue-100 border border-blue-300 rounded text-blue-700">
        <?= $msg ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="mb-6">
      <button type="submit" name="backup" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
        💾 Buat Backup Sekarang
      </button>
    </form>

    <form method="POST" enctype="multipart/form-data" class="mb-10">
      <label class="block mb-2 font-semibold">📤 Pilih File SQL untuk Restore:</label>
      <input type="file" name="restore_file" accept=".sql"
             class="mb-4 px-4 py-2 border rounded-lg w-full">
      <button type="submit" name="restore"
              class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg">
        🔁 Restore Database
      </button>
    </form>

    <h3 class="text-lg font-semibold text-gray-800 mb-4">📁 Daftar File Backup Tersimpan:</h3>
    <table class="w-full text-sm border mb-4">
      <thead class="bg-gray-100">
        <tr>
          <th class="border px-3 py-2 text-left">#</th>
          <th class="border px-3 py-2 text-left">Nama File</th>
          <th class="border px-3 py-2 text-left">Waktu Dibuat</th>
          <th class="border px-3 py-2 text-left">Download</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($files)): ?>
          <?php $no = 1; foreach ($files as $file): ?>
            <tr>
              <td class="border px-3 py-2"><?= $no++ ?></td>
              <td class="border px-3 py-2"><?= $file ?></td>
              <td class="border px-3 py-2"><?= date('d M Y H:i', filemtime($backupFolder . '/' . $file)) ?></td>
              <td class="border px-3 py-2">
                <a href="<?= $backupFolder . '/' . $file ?>" class="text-blue-600 underline" download>⬇️ Download</a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="border px-3 py-2 text-center text-gray-500">Belum ada file backup.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</main>

</body>
</html>
