<?php
include '../config/koneksi.php';
session_start();

// Cegah akses langsung oleh user yang tidak login atau bukan admin/bendahara
// if (!isset($_SESSION['id_user']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'bendahara')) {
//     header("Location: login.php");
//     exit;
// }

// Hanya tangani jika method POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jenis = $_POST['jenis_laporan'] ?? '';
    $tahun = $_POST['tahun'] ?? date('Y');
    $tanggal_pengajuan = date('Y-m-d');
    $status = 'menunggu';

    // Validasi dasar
    if (!$jenis || !$tahun) {
        die("Data tidak lengkap.");
    }

    // Untuk Laporan Pertahap
    if ($jenis === 'pertahap') {
        $tahap = $_POST['tahap'] ?? '';

        if (!$tahap) {
            die("Tahap tidak boleh kosong.");
        }

        // Hapus pengajuan lama yang statusnya bukan menunggu
        mysqli_query($conn, "
            DELETE FROM laporan_ajuan 
            WHERE jenis_laporan = 'pertahap' 
            AND tahap = '$tahap' 
            AND tahun = '$tahun' 
            AND status_ajuan != 'menunggu'
        ");

        $stmt = mysqli_prepare($conn, "
            INSERT INTO laporan_ajuan (jenis_laporan, tahap, tahun, tanggal_pengajuan, status_ajuan) 
            VALUES (?, ?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, 'ssiss', $jenis, $tahap, $tahun, $tanggal_pengajuan, $status);

    } elseif ($jenis === 'tahunan') {
        // Untuk LPJ Tahunan
        mysqli_query($conn, "
            DELETE FROM laporan_ajuan 
            WHERE jenis_laporan = 'tahunan' 
            AND tahun = '$tahun' 
            AND status_ajuan != 'menunggu'
        ");

        $stmt = mysqli_prepare($conn, "
            INSERT INTO laporan_ajuan (jenis_laporan, tahun, tanggal_pengajuan, status_ajuan) 
            VALUES (?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmt, 'siss', $jenis, $tahun, $tanggal_pengajuan, $status);

    } else {
        die("Jenis laporan tidak dikenali.");
    }

    // Eksekusi query dan arahkan kembali ke laporan.php
    if (mysqli_stmt_execute($stmt)) {
        header("Location: laporan.php?status=berhasil");
        exit;
    } else {
        die("Gagal menyimpan pengajuan: " . mysqli_stmt_error($stmt));
    }
}
?>
