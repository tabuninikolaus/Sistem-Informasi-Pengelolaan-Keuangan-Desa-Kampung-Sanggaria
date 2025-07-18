<?php
include '../config/koneksi.php';

$queryKegiatan = mysqli_query($conn, "
  SELECT 
    a.nama_kegiatan,
    a.alokasi_dana,
    ROUND(
      (IFNULL(SUM(
        CASE 
          WHEN p.status_detail_pengeluaran = 'Valid' THEN p.jumlah 
          ELSE 0 
        END
      ), 0) / a.alokasi_dana) * 100, 2
    ) AS progres_kegiatan
  FROM anggaran a
  LEFT JOIN pengeluaran p ON p.id_anggaran = a.id_anggaran
  GROUP BY a.id_anggaran
");


// Ambil file PDF yang sudah dipublikasikan
$folder = '../laporan_publik/';
$files = array_diff(scandir($folder), ['.', '..']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Transparansi Keuangan Desa Sanggaria</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          fontFamily: { sans: ['Inter', 'sans-serif'] },
          colors: {
            primary: '#0f766e',
            secondary: '#2dd4bf',
            accent: '#134e4a'
          }
        }
      }
    }
  </script>
</head>
<body class="bg-gray-50 font-sans text-gray-800 scroll-smooth">

<!-- Navbar -->
<header class="bg-white shadow sticky top-0 z-50">
  <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between items-center">
    <h1 class="text-xl font-bold text-primary">🌿 Keuangan Desa Sanggaria</h1>
    <nav class="space-x-4 text-sm font-medium hidden md:flex">
      <a href="#" class="text-gray-700 hover:text-primary">Beranda</a>
      <a href="#tentang" class="text-gray-700 hover:text-primary">Tentang</a>
      <a href="#kegiatan" class="text-gray-700 hover:text-primary">Kegiatan</a>
      <a href="#feedback" class="text-gray-700 hover:text-primary">Feedback</a>
    </nav>
  </div>
</header>

<!-- Hero -->
<section class="relative h-[60vh] md:h-[70vh] bg-cover bg-center" style="background-image: url('../assets/img/sanggaria.jpeg');">
  <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/50 to-black/20 flex items-center justify-center px-4">
    <div class="text-center text-white">
      <h1 class="text-3xl md:text-5xl font-extrabold mb-3 drop-shadow-lg">Sistem Informasi Keuangan Desa</h1>
      <p class="text-lg max-w-2xl mx-auto drop-shadow">Transparansi dan Akuntabilitas Dana Desa Kampung Sanggaria.</p>
    </div>
  </div>
</section>

<!-- Tentang -->
<section id="tentang" class="py-20 px-6 md:px-16 bg-white max-w-7xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-primary mb-6">Tentang Sistem</h2>
  <p class="text-center max-w-4xl mx-auto text-gray-600 text-lg leading-relaxed">
    Website ini dibuat sebagai bentuk keterbukaan informasi publik. Masyarakat dapat mengakses informasi keuangan desa secara real-time, mengetahui kegiatan yang sedang dan telah dilakukan, serta mengirimkan saran maupun pertanyaan langsung.
  </p>
</section>

<!-- Unduh Laporan -->
<section class="py-20 px-6 md:px-16 bg-gray-100 max-w-7xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-primary mb-10">📥 Unduh Laporan Keuangan</h2>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php
    $pdfFound = false;
    foreach ($files as $file):
      if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf'):
        $pdfFound = true;
        $filepath = $folder . $file;
        $timestamp = filemtime($filepath);
        $filenameDisplay = ucwords(str_replace(['_', '.pdf'], [' ', ''], pathinfo($file, PATHINFO_FILENAME)));
        $url = $filepath . '?v=' . $timestamp;
    ?>
      <a href="<?= $url ?>" target="_blank" class="bg-white p-6 shadow rounded-xl hover:shadow-lg transition text-center">
        <h3 class="text-lg font-semibold text-gray-700 mb-2"><?= $filenameDisplay ?></h3>
        <p class="text-sm text-gray-500">Klik untuk unduh</p>
      </a>
    <?php endif; endforeach; ?>

    <?php if (!$pdfFound): ?>
      <p class="col-span-2 text-center text-gray-500 italic">Belum ada laporan dipublikasikan.</p>
    <?php endif; ?>
  </div>
</section>

<!-- Kegiatan -->
<section id="kegiatan" class="py-20 px-6 md:px-16 bg-white max-w-7xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-primary mb-10">📌 Kegiatan Dana Desa Tahun <?= date('Y') ?></h2>
  <div class="overflow-x-auto">
    <table class="min-w-full text-sm bg-white shadow rounded">
      <thead class="bg-secondary text-white">
        <tr>
          <th class="px-4 py-2 text-left">No</th>
          <th class="px-4 py-2 text-left">Nama Kegiatan</th>
          <th class="px-4 py-2 text-left">Jumlah Alokasi</th>
          <th class="px-4 py-2 text-left">Progres Kegiatan (%)</th>
        </tr>
      </thead>
      <tbody>
        <?php $no = 1; while($row = mysqli_fetch_assoc($queryKegiatan)): ?>
        <tr class="hover:bg-gray-50 border-b">
          <td class="px-4 py-2"><?= $no++ ?></td>
          <td class="px-4 py-2"><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
          <td class="px-4 py-2">Rp <?= number_format($row['alokasi_dana'], 0, ',', '.') ?></td>
          <td class="px-4 py-2 font-semibold text-primary"><?= $row['progres_kegiatan'] ?>%</td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</section>

<!-- Form Feedback -->
<section id="feedback" class="py-20 px-6 md:px-16 bg-gray-100 max-w-7xl mx-auto">
  <h2 class="text-3xl font-bold text-center text-primary mb-10">💬 Masukan & Saran Masyarakat</h2>
  <form action="simpan_feedback.php" method="POST" class="max-w-2xl mx-auto bg-white p-8 rounded-xl shadow space-y-5">
    <input type="text" name="nama" required placeholder="Nama Lengkap" class="w-full px-4 py-2 border rounded-lg">
    <input type="email" name="email" required placeholder="Alamat Email" class="w-full px-4 py-2 border rounded-lg">
    <input type="number" name="usia" required placeholder="Usia" class="w-full px-4 py-2 border rounded-lg">
    <select name="jenis_kelamin" required class="w-full px-4 py-2 border rounded-lg">
      <option value="">-- Pilih Jenis Kelamin --</option>
      <option value="Laki-laki">Laki-laki</option>
      <option value="Perempuan">Perempuan</option>
    </select>
    <textarea name="pesan" rows="4" required placeholder="Pesan atau Saran..." class="w-full px-4 py-2 border rounded-lg"></textarea>
    <button type="submit" class="bg-primary hover:bg-accent text-white px-6 py-2 rounded-lg font-semibold">
      Kirim Feedback
    </button>
  </form>
</section>

<!-- Footer -->
<?php include '../includes/footer.php'; ?>
</body>
</html>
