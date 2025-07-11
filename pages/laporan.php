<?php 
include '../config/koneksi.php';
session_start();


function getPersentaseRealisasi($conn, $id_anggaran) {
  $total = getTotalPengeluaranByAnggaran($conn, $id_anggaran);
  $alokasi = mysqli_fetch_assoc(mysqli_query($conn, "SELECT alokasi_dana FROM anggaran WHERE id_anggaran='$id_anggaran'"))['alokasi_dana'] ?? 0;
  return ($alokasi > 0) ? round(($total / $alokasi) * 100) : 0;
}

function getTahap($tanggal) {
  $bulan = date('n', strtotime($tanggal));
  if ($bulan >= 1 && $bulan <= 3) return 'Tahap I (Jan - Mar)';
  elseif ($bulan >= 4 && $bulan <= 8) return 'Tahap II (Apr - Aug)';
  else return 'Tahap III (Sep - Dec)';
}

function getAnggaranByTahapCustom($conn, $startMonth, $endMonth, $tahun, $tahap_key) {
  $tahun_lalu = $tahun - 1;
  $anggaran = [];

  // Ambil kegiatan tahun ini berdasarkan tanggal_input_kegiatan
  $q1 = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN $startMonth AND $endMonth");
  while ($row = mysqli_fetch_assoc($q1)) {
    $anggaran[] = $row;
  }

  // Tambahkan kegiatan tahun sebelumnya tahap III untuk tahap I
  if ($tahap_key === 'tahap_i') {
    $q2 = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun_lalu' AND MONTH(tanggal_input_kegiatan) BETWEEN 9 AND 12");
    while ($row = mysqli_fetch_assoc($q2)) {
      $alokasi = $row['alokasi_dana'];
      $total = getTotalPengeluaranTahap($conn, $row['id_anggaran'], 1, 12);
      if ($total < $alokasi) {
        $anggaran[] = $row;
      }
    }
  }

  // Tambahkan kegiatan dari tahap sebelumnya jika belum 100% realisasi
  if ($tahap_key === 'tahap_ii') {
    $q3 = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 3");
    while ($row = mysqli_fetch_assoc($q3)) {
      $alokasi = $row['alokasi_dana'];
      $total = getTotalPengeluaranTahap($conn, $row['id_anggaran'], 1, 12);
      if ($total < $alokasi) {
        $anggaran[] = $row;
      }
    }
  } elseif ($tahap_key === 'tahap_iii') {
    $q3 = mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun' AND MONTH(tanggal_input_kegiatan) BETWEEN 1 AND 8");
    while ($row = mysqli_fetch_assoc($q3)) {
      $alokasi = $row['alokasi_dana'];
      $total = getTotalPengeluaranTahap($conn, $row['id_anggaran'], 1, 12);
      if ($total < $alokasi) {
        $anggaran[] = $row;
      }
    }
  }

  return $anggaran;
}


function getPengeluaranTahap($conn, $id_anggaran, $startMonth, $endMonth) {
  return mysqli_query($conn, "SELECT * FROM pengeluaran WHERE id_anggaran='$id_anggaran' AND MONTH(tanggal) BETWEEN $startMonth AND $endMonth");
}

function getTotalPengeluaranTahap($conn, $id_anggaran, $startMonth, $endMonth) {
  $result = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran' AND MONTH(tanggal) BETWEEN $startMonth AND $endMonth");
  $row = mysqli_fetch_assoc($result);
  return $row['total'] ?? 0;
}

function getTahunList($conn) {
  $tahunQuery = mysqli_query($conn, "SELECT DISTINCT tahun FROM anggaran ORDER BY tahun DESC");
  $tahunList = [];
  while ($t = mysqli_fetch_assoc($tahunQuery)) {
    $tahunList[] = $t['tahun'];
  }
  return $tahunList;
}

function getAnggaranByTahun($conn, $tahun) {
  return mysqli_query($conn, "SELECT * FROM anggaran WHERE tahun='$tahun'");
}

function getTotalPengeluaranByAnggaran($conn, $id_anggaran) {
  $query = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM pengeluaran WHERE id_anggaran='$id_anggaran'");
  $row = mysqli_fetch_assoc($query);
  return $row['total'] ?? 0;
}

function getStatusVerifikasiLaporan($conn, $jenis_laporan, $tahap, $tahun) {
  $sql = ($jenis_laporan === 'tahunan')
    ? "SELECT * FROM laporan_ajuan WHERE jenis_laporan='tahunan' AND tahun='$tahun' ORDER BY tanggal_pengajuan DESC LIMIT 1"
    : "SELECT * FROM laporan_ajuan WHERE jenis_laporan='$jenis_laporan' AND tahap='$tahap' AND tahun='$tahun' ORDER BY tanggal_pengajuan DESC LIMIT 1";

  $cek = mysqli_query($conn, $sql);
  if (!$cek) return null;
  return mysqli_fetch_assoc($cek);
}

function cekPerubahanData($conn, $jenis_laporan, $tahap, $tahun) {
  $verifikasi = getStatusVerifikasiLaporan($conn, $jenis_laporan, $tahap, $tahun);
  if (!$verifikasi || $verifikasi['status_ajuan'] !== 'disetujui') return false;

  $tgl = $verifikasi['tanggal_verifikasi'];

  // Filter tahap jika pertahap
  $filter_tahap = "";
  if ($jenis_laporan === 'pertahap') {
    if ($tahap === 'tahap_i') $filter_tahap = "AND MONTH(tanggal) BETWEEN 1 AND 3";
    elseif ($tahap === 'tahap_ii') $filter_tahap = "AND MONTH(tanggal) BETWEEN 4 AND 8";
    elseif ($tahap === 'tahap_iii') $filter_tahap = "AND MONTH(tanggal) BETWEEN 9 AND 12";
  }

  // Cek data pemasukan (menggunakan updated_at)
  $q1 = mysqli_query($conn, "SELECT COUNT(*) as c FROM pemasukan WHERE updated_at IS NOT NULL AND updated_at > '$tgl' AND YEAR(tanggal) = '$tahun'");
  $pemasukan_baru = mysqli_fetch_assoc($q1)['c'];

  // Cek data pengeluaran (menggunakan updated_at)
  $q2 = mysqli_query($conn, "SELECT COUNT(*) as c FROM pengeluaran WHERE updated_at IS NOT NULL AND updated_at > '$tgl' AND YEAR(tanggal) = '$tahun' $filter_tahap");
  $pengeluaran_baru = mysqli_fetch_assoc($q2)['c'];

  // Cek anggaran yang diubah (updated_at)
  $q3 = mysqli_query($conn, "SELECT COUNT(*) as c FROM anggaran WHERE tahun='$tahun' AND updated_at IS NOT NULL AND updated_at > '$tgl'");
  $anggaran_baru = mysqli_fetch_assoc($q3)['c'];

  return ($pemasukan_baru + $pengeluaran_baru + $anggaran_baru) > 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Keuangan Desa</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <header class="bg-purple-700 text-white px-6 py-6 shadow-lg flex justify-between items-center h-28">
  <div>
    <h1 class="text-2xl font-bold mb-1">💰 Sistem Informasi Keuangan Desa</h1>
    <p class="text-sm opacity-80">Dashboard Bendahara | Transparansi & Akuntabilitas</p>
  </div>
  <div class="text-right">
    <p class="text-sm">👋 Selamat datang, <strong>Bendahara</strong></p>
    <a href="../logout.php" class="text-red-300 hover:text-white text-xs underline">Keluar</a>
  </div>
</header>
<div class="flex">
  <aside class="w-64 bg-purple-800 text-white min-h-screen px-6 py-8">
    <h2 class="text-xl font-bold mb-8 text-center">Menu</h2>
    <nav class="space-y-3">
       <a href="dashboard_bendahara.php" class="block bg-purple-700 px-4 py-2 rounded-md">⬅️ Kembali ke Dashboard</a>
    </nav>
  </aside>
  <main class="flex-1 p-6">
    <div class="bg-white p-6 rounded shadow">
      <h1 class="text-2xl font-bold text-green-700 mb-6">📋 Laporan Keuangan Pertahap</h1>
      <?php
      $tahapan = [
        'tahap_i' => ['Tahap I (Jan - Mar)', 1, 3],
        'tahap_ii' => ['Tahap II (Apr - Aug)', 4, 8],
        'tahap_iii' => ['Tahap III (Sep - Dec)', 9, 12],
      ];
      $tahun = date('Y');
      foreach ($tahapan as $key => [$label, $start, $end]):
        $anggaran = getAnggaranByTahapCustom($conn, $start, $end, $tahun, $key);
        $verif = getStatusVerifikasiLaporan($conn, 'pertahap', $key, $tahun);
        $perubahan = cekPerubahanData($conn, 'pertahap', $key, $tahun);
      ?>
      <div class="mb-10">
        <h2 class="text-lg font-semibold text-green-600 mb-2"><?= $label ?></h2>
        <div class="overflow-auto">
          <table class="w-full border text-sm">
            <thead class="bg-green-100">
              <tr>
                <th class="border px-2 py-1">Nama Kegiatan</th>
                <th class="border px-2 py-1">Alokasi Dana</th>
                <th class="border px-2 py-1">Realisasi (%)</th>
                <th class="border px-2 py-1">Detail Transaksi</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($anggaran as $a):
              $total = getTotalPengeluaranTahap($conn, $a['id_anggaran'], $start, $end);
              $persen = $a['alokasi_dana'] > 0 ? round(($total / $a['alokasi_dana']) * 100) : 0;
              $detail = getPengeluaranTahap($conn, $a['id_anggaran'], $start, $end);
            ?>
              <tr>
                <td class="border px-2 py-1 align-top"><?= $a['nama_kegiatan'] ?></td>
                <td class="border px-2 py-1 align-top">Rp <?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
                <td class="border px-2 py-1 align-top"><?= $persen ?>%</td>
                <td class="border px-2 py-1">
                  <?php if (mysqli_num_rows($detail) > 0): ?>
                    <div class="overflow-auto max-h-48">
                      <table class="text-xs border w-full">
                        <thead class="bg-gray-100">
                          <tr>
                            <th class="border px-2">Tanggal</th>
                            <th class="border px-2">Jumlah</th>
                            <th class="border px-2">Bukti</th>
                            <th class="border px-2">Keterangan</th>
                            <th class="border px-2">Nama Kegiatan</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php while ($p = mysqli_fetch_assoc($detail)): ?>
                            <tr>
                              <td class="border px-2"><?= $p['tanggal'] ?></td>
                              <td class="border px-2">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                              <td class="border px-2">
                                <?php
                                $bukti = json_decode($p['bukti_pengeluaran'], true);
                                if ($bukti) {
                                  foreach ($bukti as $file) {
                                    echo "<a href='../uploads/$file' class='text-blue-500 underline block' target='_blank'>Lihat</a>";
                                  }
                                }
                                ?>
                              </td>
                              <td class="border px-2"><?= $p['keterangan'] ?></td>
                              <td class="border px-2"><?= $a['nama_kegiatan'] ?></td>
                            </tr>
                          <?php endwhile; ?>
                        </tbody>
                      </table>
                    </div>
                  <?php else: ?>
                    <span class="text-gray-500 italic">Tidak ada transaksi</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="mt-2 flex items-center gap-2">
          <a href="export_pdf_laporan.php?tahap=<?= $key ?>&tahun=<?= $tahun ?>" class="bg-green-600 text-white px-3 py-1 rounded">Unduh PDF</a>
          <a href="export_excel_laporan.php?tahap=<?= $key ?>&tahun=<?= $tahun ?>" class="bg-blue-600 text-white px-3 py-1 rounded">Unduh Excel</a>
         <?php
                if (!$verif) {
                  echo "<span class='text-sm text-gray-500'>Belum diajukan</span>
                  <button onclick=\"showModalAjukan('pertahap', '$key', $tahun)\" class='bg-yellow-500 text-white px-2 py-1 rounded'>Ajukan</button>";
                } elseif ($verif['status_ajuan'] === 'menunggu') {
                  echo '<span class="text-sm italic text-gray-500">Menunggu verifikasi</span>';
                } elseif ($verif['status_ajuan'] === 'ditolak') {
                  echo "<span class='text-sm text-red-600'>Verifikasi ditolak</span>
                  <button onclick=\"showModalAjukan('pertahap', '$key', $tahun)\" class='bg-yellow-500 text-white px-2 py-1 rounded'>Ajukan Ulang</button>";
                } elseif ($verif['status_ajuan'] === 'disetujui') {
                  if ($perubahan) {
                    echo "<span class='text-sm text-orange-600'>Ada perubahan data, ajukan ulang</span>
                    <button onclick=\"showModalAjukan('pertahap', '$key', $tahun)\" class='bg-yellow-500 text-white px-2 py-1 rounded'>Ajukan Ulang</button>";
                  } else {
                    echo "<span class='text-sm text-green-600'>Telah diverifikasi</span>";
                  }
                }
              ?>

        </div>
      </div>
      <?php endforeach; ?>

      <h2 class="text-2xl font-bold text-indigo-700 mt-10 mb-4">📁 Laporan Tahunan (LPJ)</h2>
      <form method="GET" class="mb-4">
        <label class="mr-2">Tahun:</label>
        <select name="tahun" onchange="this.form.submit()" class="border px-2 py-1 rounded">
          <option value="">-- Pilih Tahun --</option>
          <?php foreach (getTahunList($conn) as $t): ?>
            <option value="<?= $t ?>" <?= (isset($_GET['tahun']) && $_GET['tahun'] == $t) ? 'selected' : '' ?>><?= $t ?></option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if (isset($_GET['tahun'])):
        $tahun = $_GET['tahun'];
        $verif = getStatusVerifikasiLaporan($conn, 'tahunan', 'tahunan', $tahun);
        $perubahan = cekPerubahanData($conn, 'tahunan', 'tahunan', $tahun);
        $data = getAnggaranByTahun($conn, $tahun);
      ?>
      <div class="overflow-auto mb-4">
        <table class="w-full border text-sm">
          <thead class="bg-indigo-100">
            <tr>
              <th class="border px-2 py-1">Nama Kegiatan</th>
              <th class="border px-2 py-1">Alokasi Dana</th>
              <th class="border px-2 py-1">Pengeluaran</th>
              <th class="border px-2 py-1">Realisasi (%)</th>
            </tr>
          </thead>
          <tbody>
          <?php while ($a = mysqli_fetch_assoc($data)):
            $total = getTotalPengeluaranByAnggaran($conn, $a['id_anggaran']);
           $persen = getPersentaseRealisasi($conn, $a['id_anggaran']);
          ?>
            <tr>
              <td class="border px-2 py-1"><?= $a['nama_kegiatan'] ?></td>
              <td class="border px-2 py-1">Rp <?= number_format($a['alokasi_dana'], 0, ',', '.') ?></td>
              <td class="border px-2 py-1">Rp <?= number_format($total, 0, ',', '.') ?></td>
              <td class="border px-2 py-1"><?= $persen ?>%</td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <div class="flex gap-2 items-center">
        <a href="export_pdf.php?tahap=tahunan&tahun=<?= $tahun ?>" class="bg-green-600 text-white px-3 py-1 rounded">Unduh PDF</a>
        <a href="export_excel.php?tahap=tahunan&tahun=<?= $tahun ?>" class="bg-blue-600 text-white px-3 py-1 rounded">Unduh Excel</a>
        <?php
          if (!$verif) {
            echo "<span class='text-sm text-gray-500'>Belum diajukan</span>
            <button onclick=\"showModalAjukan('tahunan', 'tahunan', $tahun)\" class='bg-yellow-500 text-white px-2 py-1 rounded'>Ajukan</button>";
          } elseif ($verif['status_ajuan'] === 'menunggu') {
            echo '<span class="text-sm italic text-gray-500">Menunggu verifikasi</span>';
          } elseif ($verif['status_ajuan'] === 'ditolak') {
            echo "<span class='text-sm text-red-600'>Verifikasi ditolak</span>
            <button onclick=\"showModalAjukan('tahunan', 'tahunan', $tahun)\" class='bg-yellow-500 text-white px-2 py-1 rounded'>Ajukan Ulang</button>";
          } elseif ($verif['status_ajuan'] === 'disetujui' && $perubahan) {
            echo "<span class='text-sm text-orange-600'>Ada perubahan data, ajukan ulang</span>
            <button onclick=\"showModalAjukan('tahunan', 'tahunan', $tahun)\" class='bg-yellow-500 text-white px-2 py-1 rounded'>Ajukan Ulang</button>";
          } elseif ($verif['status_ajuan'] === 'disetujui') {
            echo "<span class='text-sm text-green-600'>Telah diverifikasi</span>
            <button onclick=\"showModalAjukan('tahunan', 'tahunan', $tahun)\" class='bg-yellow-500 text-white px-2 py-1 rounded'>Ajukan</button>";
          }
        ?>
      </div>
      <?php endif; ?>
    </div>
  </main>
</div>

<!-- Modal Ajukan -->
<div id="modal-ajukan" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50">
  <div class="flex items-center justify-center min-h-screen">
    <div class="bg-white p-6 rounded shadow-lg w-96">
      <h2 class="text-lg font-bold mb-2 text-green-700">Konfirmasi Pengajuan</h2>
      <p class="text-sm mb-4" id="modal-ajukan-text">Apakah Anda yakin ingin mengajukan laporan ini?</p>
      <form method="POST" action="ajukan_laporan.php">
        <input type="hidden" id="jenis_laporan" name="jenis_laporan">
        <input type="hidden" id="tahap" name="tahap">
        <input type="hidden" id="tahun" name="tahun">
        <div class="flex justify-end gap-2">
          <button type="button" onclick="hideModalAjukan()" class="px-4 py-1 bg-gray-400 text-white rounded">Batal</button>
          <button type="submit" class="px-4 py-1 bg-yellow-600 text-white rounded hover:bg-yellow-700">Ajukan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function showModalAjukan(jenis, tahap, tahun) {
  document.getElementById("modal-ajukan").classList.remove("hidden");
  document.getElementById("jenis_laporan").value = jenis;
  document.getElementById("tahap").value = tahap;
  document.getElementById("tahun").value = tahun;
  const label = jenis === 'tahunan' ? 'Tahunan' : tahap.replace('_', ' ').toUpperCase();
  document.getElementById("modal-ajukan-text").innerText = `Yakin ingin mengajukan laporan ${label} tahun ${tahun} kepada Kepala Desa?`;
}
function hideModalAjukan() {
  document.getElementById("modal-ajukan").classList.add("hidden");
}
</script>
</body>
</html>
