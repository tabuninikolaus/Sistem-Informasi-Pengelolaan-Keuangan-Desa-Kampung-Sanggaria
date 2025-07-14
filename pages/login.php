<?php
session_start();
include '../config/koneksi.php';

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    $data = mysqli_fetch_assoc($query);

    if ($data && $password == $data['password']) {
        $_SESSION['id_user'] = $data['id_user'];
        $_SESSION['role'] = $data['role'];
        $_SESSION['nama_lengkap'] = $data['nama_lengkap'];

        if ($data['role'] == 'admin') {
            header("Location: dashboard_bendahara.php");
        } elseif ($data['role'] == 'kades') {
            header("Location: dashboard_kades.php");
        }
    } else {
        $error = "Login gagal! Username atau password salah.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Login - Sistem Keuangan Desa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: {
            sans: ['Inter', 'sans-serif'],
          },
          colors: {
            primary: '#0f766e',
            secondary: '#2dd4bf',
            accent: '#134e4a',
          }
        }
      }
    }
  </script>
  <style>
    body {
      background-image: url('../assets/img/sanggaria.jpeg'); /* Ganti dengan gambar desa kamu */
      background-size: cover;
      background-position: center;
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center px-4 bg-black/50 bg-blend-overlay">

  <div class="bg-white/90 backdrop-blur-md shadow-xl rounded-xl p-8 w-full max-w-md">
    <div class="flex flex-col items-center mb-6">
      <div class="bg-primary p-4 rounded-full shadow-md mb-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10l9-7 9 7v9a1 1 0 01-1 1h-4m-8 0H4a1 1 0 01-1-1v-9z" />
        </svg>
      </div>
      <h2 class="text-2xl font-bold text-primary text-center">Login Sistem Keuangan Desa</h2>
    </div>

    <?php if (isset($error)): ?>
      <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm border border-red-300">
        <?= $error ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">
      <div>
        <label class="block text-gray-700 text-sm mb-1">👤 Username</label>
        <input type="text" name="username" required
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
      </div>
      <div>
        <label class="block text-gray-700 text-sm mb-1">🔒 Password</label>
        <input type="password" name="password" required
               class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
      </div>
      <button type="submit" name="login"
              class="w-full bg-primary hover:bg-accent text-white py-2 rounded-lg transition font-semibold">
        🔐 Masuk
      </button>
    </form>
  </div>

</body>
</html>
