<?php
include_once("koneksi.php");

// Menangani form Cek Out
if (isset($_POST['CekOut'])) {
    $nisn = $_POST['nisn'];

    // Cari data siswa berdasarkan NISN
    $getSiswa = mysqli_query($mysqli, "SELECT * FROM absensi3 WHERE nisn = '$nisn' AND waktu_keluar IS NULL ORDER BY id DESC LIMIT 1");
    $dataSiswa = mysqli_fetch_assoc($getSiswa);

    if ($dataSiswa) {
        // Update waktu keluar untuk siswa yang sedang melakukan cek out
        $query = "UPDATE absensi3 SET waktu_keluar = NOW() WHERE id = '" . $dataSiswa['id'] . "'";
        $result = mysqli_query($mysqli, $query);

        if ($result) {
            echo "<script>
                Swal.fire({
                    title: 'Cek Out Berhasil!',
                    text: 'Anda telah berhasil Cek Out.',
                    icon: 'success'
                });
            </script>";
        } else {
            echo "<script>
                Swal.fire({
                    title: 'Gagal Cek Out!',
                    text: 'Terjadi kesalahan saat mencatat waktu keluar.',
                    icon: 'error'
                });
            </script>";
        }
    } else {
        echo "<script>
            Swal.fire({
                title: 'Gagal!',
                text: 'Data NISN tidak ditemukan atau sudah Cek Out.',
                icon: 'error'
            });
        </script>";
    }
}

// Ambil data absensi untuk ditampilkan
$result = mysqli_query($mysqli, "SELECT * FROM absensi3 ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Cek Out</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="style.css" />
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #e0f7fa, #ffffff);
            background-attachment: fixed;
        }

        .navbar {
            background-color: #212529;
            padding: 1rem;
            color: white;
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }

        .header-section {
            padding: 2rem 1rem;
            text-align: center;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
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
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .text-center {
            text-align: center;
        }

        .btn {
            padding: 0.5rem 1.5rem;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            margin: 0 0.5rem;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #198754;
            color: white;
        }

        .btn-primary:hover {
            background-color: #157347;
        }

        .btn-custom {
            background-color: #05acf0;
            border-color: #05acf0;
            color: white;
        }

        .btn-custom:hover {
            background-color: #0394c6;
            border-color: #0394c6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }

        th, td {
            padding: 0.75rem;
            border: 1px solid #ccc;
            text-align: center;
        }

        .table-dark {
            background-color: #343a40;
            color: white;
        }

        .table-striped tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        h2 {
            margin-top: 2rem;
            text-align: center;
        }
    </style>
</head>
<body onload="document.getElementById('nisn').focus();">
    <nav class="navbar">
        <div class="navbar-brand">ABSEN SMKN 1 LUMAJANG - Cek Out</div>
    </nav>

    <div class="header-section">
        <h1>FORM CEK OUT</h1>
        <div class="container">
            <form action="" method="post">
                <div class="form-group">
                    <label class="form-label">NISN</label>
                    <input type="text" class="form-control" name="nisn" id="nisn" placeholder="Masukkan NISN" required />
                </div>
                <div class="text-center">
                    <button type="submit" class="btn btn-primary" name="CekOut">Cek Out</button>
                    <button type="button" onclick="window.location.href='index.php'" class="btn btn-custom">Kembali</button>
                </div>
            </form>

            <h2>Daftar Kehadiran</h2>
            <table class="table-striped">
                <tr class="table-dark">
                    <th>Nama</th>
                    <th>Jurusan</th>
                    <th>Waktu Kehadiran</th>
                    <th>Waktu Keluar</th>
                </tr>

                <?php while ($r = mysqli_fetch_array($result)) { ?>
                    <tr>
                        <td><?php echo $r['nama']; ?></td>
                        <td><?php echo $r['jurusan']; ?></td>
                        <td><?php echo $r['waktu_kehadiran']; ?></td>
                        <td><?php echo $r['waktu_keluar'] ?? '-'; ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    </div>
</body>
</html>
