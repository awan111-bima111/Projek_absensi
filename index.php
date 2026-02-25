<?php
include_once("koneksi.php");
date_default_timezone_set('Asia/Jakarta');

// Ambil data absensi untuk ditampilkan di tabel
$result = mysqli_query($mysqli, "SELECT * FROM absensi3 ORDER BY id DESC");

// Jika tombol Check In ditekan
if (isset($_POST['Submit'])) {
    $nisn = $_POST['nisn'];
    $alasan = $_POST['alasan'] ?? 'hadir'; // Default 'hadir'

    // Cek apakah NISN sudah Check In dan belum Check Out
    $cekDuplikat = mysqli_query($mysqli, "SELECT * FROM absensi3 WHERE nisn = '$nisn' AND waktu_keluar IS NULL");

    if (mysqli_num_rows($cekDuplikat) > 0) {
        echo "<script>alert('NISN ini sudah Check In dan belum melakukan Check Out!');</script>";
    } else {
        // Ambil data siswa berdasarkan NISN
        $query_siswa = mysqli_query($mysqli, "SELECT * FROM siswa WHERE nisn = '$nisn'");
        $data_siswa = mysqli_fetch_assoc($query_siswa);

        if ($data_siswa) {
            $nama = $data_siswa['nama'];
            $jurusan = $data_siswa['jurusan'];

            // Tentukan status Tepat Waktu / Terlambat
            $waktuSekarang = date('H:i:s');
            $status = ($waktuSekarang > '06:45:00') ? 'Terlambat' : 'Tepat Waktu';

            // Simpan data ke absensi dengan alasan
            mysqli_query($mysqli, "INSERT INTO absensi3 (nisn, nama, jurusan, waktu_kehadiran, status, alasan) 
                VALUES('$nisn', '$nama', '$jurusan', NOW(), '$status', '$alasan')");
        } else {
            echo "<script>alert('NISN tidak ditemukan dalam data siswa!');</script>";
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Absensi</title>
    <link rel="stylesheet" href="style.css" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, #e0f7fa, #ffffff);
            z-index: -1;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
        }

        .navbar {
            background-color: #212529;
            padding: 1rem;
            color: white;
        }

        .navbar-brand {
            font-size: 1.25rem;
            font-weight: bold;
        }

        .header-section {
            background-color: rgba(25, 135, 84, 0.1);
            padding: 2rem 1rem;
        }

        .header-section h1 {
            text-align: center;
            margin: 0 0 2rem 0;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow-x: auto;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }

        .form-control {
            width: 100%;
            padding: 0.5rem;
            font-size: 1rem;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .text-center {
            text-align: center;
            margin-top: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            font-size: 1rem;
            border: none;
            border-radius: 0.25rem;
            cursor: pointer;
            margin: 0 0.5rem;
        }

        .tombol-checkin {
            background-color: #28a745;
            color: white;
        }

        .tombol-checkin:hover {
            background-color: #218838;
        }

        .tombol-checkout {
            background-color: #ffc107;
            color: white;
        }

        .tombol-checkout:hover {
            background-color: #e0a800;
        }

        .tombol-rekap {
            background-color: #007bff;
            color: white;
        }

        .tombol-rekap:hover {
            background-color: #0056b3;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3rem;
            font-size: 1rem;
            table-layout: auto;
        }

        th, td {
            padding: 0.75rem;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        .table-dark {
            background-color: #343a40;
            color: white;
        }

        .table-primary {
            background-color: #cfe2ff;
        }

        .table-striped tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .header-section {
            background-color: rgba(25, 135, 84, 0.1);
            padding: 2rem 1rem;
            position: relative;
            z-index: 1;
        }

        .tabel-section {
            margin-top: 2rem;
            padding-bottom: 3rem;
        }
    </style>
</head>
<body onload="document.getElementById('nisn').focus();">
    <div class="content-wrapper">
        <nav class="navbar">
            <div class="navbar-brand">ABSEN SMKN 1 LUMAJANG</div>
        </nav>

        <div class="header-section">
            <h1>DAFTAR HADIR SISWA</h1>
            <div class="container">
                <form action="" method="post" name="form_absen" id="form_absen">
                    <div class="form-group">
                        <label class="form-label">NISN</label>
                        <input type="text" class="form-control" name="nisn" id="nisn" placeholder="Masukkan NISN" required autofocus />
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alasan (jika tidak hadir)</label>
                        <select name="alasan" class="form-control" id="alasan">
                            <option value="hadir" selected>Hadir</option>
                            <option value="sakit">Sakit</option>
                            <option value="ijin">Ijin</option>
                            <!-- Tidak ada pilihan Alpa di sini -->
                        </select>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="btn tombol-checkin" name="Submit">Check In</button>
                        <button type="button" class="btn tombol-checkout" onclick="window.location.href='CekOut.php'">Check Out</button>
                        <button type="button" class="btn tombol-rekap" onclick="window.location.href='rekapan.php'">Rekap Absensi</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="container tabel-section">
            <table class="table-striped">
                <thead>
                    <tr class="table-dark">
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Jurusan</th>
                        <th>Waktu Kehadiran</th>
                        <th>Status</th>
                        <th>Alasan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($r = mysqli_fetch_array($result)) { ?>
                    <tr class="table-primary">
                        <td><?php echo $r['nisn']; ?></td>
                        <td><?php echo $r['nama']; ?></td>
                        <td><?php echo $r['jurusan']; ?></td>
                        <td><?php echo $r['waktu_kehadiran']; ?></td>
                        <td><?php echo $r['status']; ?></td>
                        <td><?php echo ucfirst($r['alasan']); ?></td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
    crossorigin="anonymous"></script>
</html>
