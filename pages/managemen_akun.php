<?php 
include '../config/koneksi.php';
session_start();

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Tambah User
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = $_POST['role'];

    $query = "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('$nama', '$username', '$password', '$role')";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('✅ Akun berhasil ditambahkan!'); window.location='manajemen_user.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal menambahkan user.'); window.location='manajemen_user.php';</script>";
    }
    exit;
}

// Hapus User
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    if (mysqli_query($conn, "DELETE FROM users WHERE id_user = $id")) {
        echo "<script>alert('✅ Akun berhasil dihapus.'); window.location='manajemen_user.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal menghapus akun.'); window.location='manajemen_user.php';</script>";
    }
    exit;
}

// Ambil Data User untuk Form Edit
$editData = null;
if (isset($_GET['edit'])) {
    $id_edit = $_GET['edit'];
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id_user = $id_edit");
    $editData = mysqli_fetch_assoc($result);
}

// Simpan Edit
if (isset($_POST['update'])) {
    $id_user = $_POST['id_user'];
    $nama = $_POST['nama_lengkap'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $query = "UPDATE users SET nama_lengkap='$nama', username='$username', role='$role' WHERE id_user=$id_user";
    if (mysqli_query($conn, $query)) {
        echo "<script>alert('✅ Akun berhasil diperbarui.'); window.location='manajemen_user.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal memperbarui akun.'); window.location='manajemen_user.php';</script>";
    }
    exit;
}

$users = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Manajemen Pengguna</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<!-- Header -->
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

<div class="flex flex-1">
  <!-- Sidebar -->
  <aside class="w-64 bg-purple-800 text-white min-h-full px-6 py-8">
    <h2 class="text-xl font-bold mb-8 text-center">Menu</h2>
    <nav class="space-y-3">
       <a href="dashboard_bendahara.php" class="block bg-purple-700 px-4 py-2 rounded-md">⬅️ Kembali ke Dashboard</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="flex-1 p-8">
    <div class="bg-white shadow-lg rounded-xl p-6">
      <h2 class="text-2xl font-bold text-purple-700 mb-6">👥 Manajemen Pengguna</h2>

      <!-- Form Tambah / Edit User -->
      <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
        <input type="hidden" name="id_user" value="<?= $editData['id_user'] ?? '' ?>">
        <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required value="<?= $editData['nama_lengkap'] ?? '' ?>" class="px-3 py-2 border rounded-lg">
        <input type="text" name="username" placeholder="Username" required value="<?= $editData['username'] ?? '' ?>" class="px-3 py-2 border rounded-lg">
        <?php if (!$editData): ?>
          <input type="password" name="password" placeholder="Password" required class="px-3 py-2 border rounded-lg">
        <?php endif; ?>
        <select name="role" required class="px-3 py-2 border rounded-lg">
          <option value="">-- Pilih Peran --</option>
          <option value="admin" <?= (isset($editData['role']) && $editData['role'] == 'admin') ? 'selected' : '' ?>>Admin / Bendahara</option>
          <option value="kades" <?= (isset($editData['role']) && $editData['role'] == 'kades') ? 'selected' : '' ?>>Kepala Desa</option>
        </select>
        <div class="md:col-span-2">
          <?php if ($editData): ?>
            <button name="update" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">💾 Simpan Perubahan</button>
            <a href="manajemen_user.php" class="ml-4 text-sm text-gray-600 underline">Batal Edit</a>
          <?php else: ?>
            <button name="tambah" class="bg-purple-700 hover:bg-purple-800 text-white px-4 py-2 rounded-lg">➕ Tambah User</button>
          <?php endif; ?>
        </div>
      </form>

      <!-- Tabel User -->
      <div class="overflow-x-auto">
        <table class="w-full border text-sm">
          <thead class="bg-purple-100">
            <tr>
              <th class="border px-3 py-2">No</th>
              <th class="border px-3 py-2">Nama Lengkap</th>
              <th class="border px-3 py-2">Username</th>
              <th class="border px-3 py-2">Peran</th>
              <th class="border px-3 py-2">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php $no = 1; while ($u = mysqli_fetch_assoc($users)): ?>
            <tr class="hover:bg-gray-50">
              <td class="border px-3 py-2 text-center"><?= $no++ ?></td>
              <td class="border px-3 py-2"><?= htmlspecialchars($u['nama_lengkap']) ?></td>
              <td class="border px-3 py-2"><?= htmlspecialchars($u['username']) ?></td>
              <td class="border px-3 py-2 capitalize"><?= htmlspecialchars($u['role']) ?></td>
              <td class="border px-3 py-2 space-x-3 text-center">
                <a href="?edit=<?= $u['id_user'] ?>" class="text-blue-600 hover:underline">✏️ Edit</a>
                <a href="?hapus=<?= $u['id_user'] ?>" onclick="return confirm('Yakin ingin menghapus user ini?')" class="text-red-600 hover:underline">🗑️ Hapus</a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

</body>
</html>
