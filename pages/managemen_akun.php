<?php 
                include '../config/koneksi.php';
                session_start();

                // if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
                //     header("Location: login.php");
                //     exit;
                // }

                // Fungsi bantu untuk upload foto
                function uploadFoto($fieldName, $folder = "../uploads/") {
                    if ($_FILES[$fieldName]['error'] === 0) {
                        $namaFile = time() . '_' . basename($_FILES[$fieldName]['name']);
                        $lokasi = $folder . $namaFile;

                        // Pastikan folder ada
                        if (!is_dir($folder)) {
                            mkdir($folder, 0777, true);
                        }

                        if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $lokasi)) {
                            return "uploads/" . $namaFile; // Simpan path relatif
                        }
                    }
                    return null;
                }

                // Tambah User
                if (isset($_POST['tambah'])) {
                    $nama = $_POST['nama_lengkap'];
                    $username = $_POST['username'];
                    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
                    $role = $_POST['role'];
                    $fotoPath = uploadFoto('foto'); // Cek dan upload foto jika ada

                    $query = "INSERT INTO users (nama_lengkap, username, password, role, foto_profil) 
                              VALUES ('$nama', '$username', '$password', '$role', '$fotoPath')";

                    if (mysqli_query($conn, $query)) {
                        echo "<script>alert('✅ Akun berhasil ditambahkan!'); window.location='managemen_akun.php';</script>";
                    } else {
                        echo "<script>alert('❌ Gagal menambahkan user.'); window.location='managemen_akun.php';</script>";
                    }
                    exit;
                }

                // Hapus User
                if (isset($_GET['hapus'])) {
                    $id = $_GET['hapus'];
                    $q = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id_user = $id");
                    $d = mysqli_fetch_assoc($q);
                    if (!empty($d['foto_profil']) && file_exists("../" . $d['foto_profil'])) {
                        unlink("../" . $d['foto_profil']); // hapus file foto
                    }

                    if (mysqli_query($conn, "DELETE FROM users WHERE id_user = $id")) {
                        echo "<script>alert('✅ Akun berhasil dihapus.'); window.location='managemen_akun.php';</script>";
                    } else {
                        echo "<script>alert('❌ Gagal menghapus akun.'); window.location='managemen_akun.php';</script>";
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

                    // Ambil data lama
                    $q = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id_user = $id_user");
                    $lama = mysqli_fetch_assoc($q);

                    // Upload foto baru jika ada
                    $fotoBaru = uploadFoto('foto');

                    // Jika ada foto baru, hapus yang lama
                    if ($fotoBaru) {
                        if (!empty($lama['foto_profil']) && file_exists("../" . $lama['foto_profil'])) {
                            unlink("../" . $lama['foto_profil']);
                        }
                        $query = "UPDATE users SET nama_lengkap='$nama', username='$username', role='$role', foto_profil='$fotoBaru' 
                                  WHERE id_user=$id_user";
                    } else {
                        $query = "UPDATE users SET nama_lengkap='$nama', username='$username', role='$role' 
                                  WHERE id_user=$id_user";
                    }

                    if (mysqli_query($conn, $query)) {
                        echo "<script>alert('✅ Akun berhasil diperbarui.'); window.location='managemen_akun.php';</script>";
                    } else {
                        echo "<script>alert('❌ Gagal memperbarui akun.'); window.location='managemen_akun.php';</script>";
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
<body class="bg-[#f5f7fa] text-gray-800 font-sans">

<!-- HEADER -->
<?php include '../includes/header.php'; ?>

 

<div class="flex flex-1">
<!-- SIDEBAR -->
<?php include '../includes/sidebar.php'; ?>
  <!-- Main Content -->
  <main class="flex-1 p-8">
    <div class="bg-white shadow-lg rounded-xl p-6">
      <h2 class="text-2xl font-bold text-purple-700 mb-6">👥 Manajemen Pengguna</h2>

      <!-- Form Tambah / Edit User -->
              <form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <input type="hidden" name="id_user" value="<?= $editData['id_user'] ?? '' ?>">

                <input type="text" name="nama_lengkap" placeholder="Nama Lengkap" required 
                      value="<?= $editData['nama_lengkap'] ?? '' ?>" class="px-3 py-2 border rounded-lg">

                <input type="text" name="username" placeholder="Username" required 
                      value="<?= $editData['username'] ?? '' ?>" class="px-3 py-2 border rounded-lg">

                <?php if (!$editData): ?>
                  <input type="password" name="password" placeholder="Password" required 
                        class="px-3 py-2 border rounded-lg">
                <?php endif; ?>

                <select name="role" required class="px-3 py-2 border rounded-lg">
                  <option value="">-- Pilih Peran --</option>
                  <option value="admin" <?= (isset($editData['role']) && $editData['role'] == 'admin') ? 'selected' : '' ?>>
                    Admin / Bendahara
                  </option>
                  <option value="kades" <?= (isset($editData['role']) && $editData['role'] == 'kades') ? 'selected' : '' ?>>
                    Kepala Desa
                  </option>
                </select>

                <!-- Input Upload Foto -->
                <input type="file" name="foto" accept="image/*" class="px-3 py-2 border rounded-lg">

                <!-- Preview Foto jika edit -->
                <?php if ($editData && !empty($editData['foto_profil'])): ?>
                  <div class="md:col-span-2">
                    <label class="block text-sm mb-1">Foto Sebelumnya:</label>
                    <img src="../<?= $editData['foto_profil'] ?>" alt="Foto Profil" class="w-24 h-24 rounded-full border mb-2">
                  </div>
                <?php endif; ?>

                <div class="md:col-span-2">
                  <?php if ($editData): ?>
                    <button name="update" class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg">💾 Simpan Perubahan</button>
                    <a href="managemen_akun.php" class="ml-4 text-sm text-gray-600 underline">Batal Edit</a>
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
              <th class="border px-3 py-2">Foto</th> 
              <th class="border px-3 py-2">Nama Lengkap</th>
              <th class="border px-3 py-2">Username</th>
              <th class="border px-3 py-2">Peran</th>
              <th class="border px-3 py-2">Aksi</th>
            </tr>
            <tbody>
              <?php $no = 1; while ($u = mysqli_fetch_assoc($users)): ?>
              <tr class="hover:bg-gray-50">
                <td class="border px-3 py-2 text-center"><?= $no++ ?></td>
                <td class="border px-3 py-2 text-center">
                  <?php if (!empty($u['foto_profil'])): ?>
                    <img src="../<?= $u['foto_profil'] ?>" class="w-10 h-10 rounded-full mx-auto">
                  <?php else: ?>
                    <span class="text-gray-400 italic">No Photo</span>
                  <?php endif; ?>
                </td>
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
<!-- FOOTER -->
<?php include '../includes/footer.php'; ?>

</body>
</html>
