<?php
include_once("koneksi.php");

$bulan_arr = [
    1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April",
    5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus",
    9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
];

$filter_bulan = $_POST['filter_bulan'] ?? '';

$siswa_query = "SELECT * FROM siswa ORDER BY nama ASC";
$siswa_result = mysqli_query($mysqli, $siswa_query);

$siswa_data = [];
while ($row = mysqli_fetch_assoc($siswa_result)) {
    $jurusan = $row['jurusan'];
    $nama = $row['nama'];
    $nisn = $row['nisn'];
    $siswa_data[$jurusan][$nisn] = [
        'nama' => $nama,
        'absen' => []
    ];
}

$absensi_query = "SELECT * FROM absensi3 WHERE 1=1";
if ($filter_bulan) {
    $absensi_query .= " AND MONTH(waktu_kehadiran) = '" . intval($filter_bulan) . "'";
}
$absensi_result = mysqli_query($mysqli, $absensi_query);

while ($row = mysqli_fetch_assoc($absensi_result)) {
    $nisn = $row['nisn'];
    $jurusan = $row['jurusan'];
    $tanggal = date('Y-m-d', strtotime($row['waktu_kehadiran']));
    $alasan = strtolower(trim($row['alasan'] ?? ''));

    // Normalisasi sinonim
    if ($alasan === 'ijin') {
        $alasan = 'izin';
    }
    
    if (!in_array($alasan, ['hadir', 'izin', 'sakit'])) {
        $alasan = 'alpa';
    }
    

    if (isset($siswa_data[$jurusan][$nisn])) {
        $siswa_data[$jurusan][$nisn]['absen'][$tanggal] = $alasan;
    }
}

$libur_result = mysqli_query($mysqli, "SELECT tanggal FROM hari_libur");
$hari_libur = [];
while ($row = mysqli_fetch_assoc($libur_result)) {
    $hari_libur[] = $row['tanggal'];
}

function getAllDatesInMonth($month, $year = null) {
    if (!$year) $year = date('Y');
    $num_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    $dates = [];
    for ($i = 1; $i <= $num_days; $i++) {
        $dates[] = date('Y-m-d', strtotime("$year-$month-$i"));
    }
    return $dates;
}

$year = date('Y');
$dates_in_month = $filter_bulan ? getAllDatesInMonth(intval($filter_bulan), $year) : [];

date_default_timezone_set('Asia/Jakarta');
$now_date = date('Y-m-d');
$now_time = date('H:i:s');
$cutoff_time = "16:00:00";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8" />
<title>Rekap Absensi Bulanan</title>
<style>
/* CSS sama seperti kode kamu sebelumnya */
@media print {
        body {
            margin: 0;
            font-size: 10px;
            zoom: 85%;
        }
        table { page-break-inside: avoid; }
        thead { display: table-header-group; }
        tfoot { display: table-footer-group; }
        .toolbar, .export-menu, button { display: none !important; }
    }

    body {
        font-family: 'Segoe UI', sans-serif;
        background: #eef2f3;
        padding: 20px;
    }
    h2 {
        text-align: center;
        color: #2c3e50;
    }
    .toolbar {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }
    select, button {
        padding: 10px 15px;
        font-size: 1rem;
        border: 1px solid #ccc;
        border-radius: 6px;
        cursor: pointer;
    }
    button {
        background-color: #000000;
        color: white;
        transition: background-color 0.3s ease;
    }
    button:hover {
        background-color: #333333;
    }
    .btn-secondary {
        background-color: #7f8c8d;
        color: white;
    }
    .btn-secondary:hover {
        background-color: #95a5a6;
    }
    .export-menu {
        position: absolute;
        background: white;
        border: 1px solid #ddd;
        display: none;
        z-index: 10;
        border-radius: 5px;
    }
    .export-menu button {
        border: none;
        padding: 10px;
        text-align: left;
        width: 100%;
        background: none;
        color: #000;
        cursor: pointer;
    }
    .export-menu button:hover {
        background-color: #f2f2f2;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    th, td {
        border: 1px solid #ddd;
        padding: 6px;
        text-align: center;
        vertical-align: middle;
    }
    th {
        background: #3498db;
        color: white;
    }
    tr.group-header th {
        background: #2c3e50;
        color: white;
        text-align: left;
        font-size: 1.1rem;
        padding-left: 10px;
    }
    .absen-hadir {
        background: #d4edda;
        color: #155724;
        font-weight: bold;
    }
    .absen-izin {
        background: #fff3cd;
        color: #856404;
        font-weight: bold;
    }
    .absen-sakit {
        background: #cce5ff;
        color: #004085;
        font-weight: bold;
    }
    .absen-alpa {
        background: #f8d7da;
        color: #721c24;
        font-weight: bold;
        white-space: nowrap;
    }
    .hari-libur {
        color: #777;
    }
</style>
</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<body>
<h2>Rekap Absensi Bulan <?= $filter_bulan ? $bulan_arr[intval($filter_bulan)] : "Semua Bulan" ?></h2>
<div class="toolbar">
    <form method="post">
        <label for="filter_bulan">Pilih Bulan:</label>
        <select name="filter_bulan" id="filter_bulan" required>
            <option value="">-- Pilih Bulan --</option>
            <?php foreach ($bulan_arr as $num => $name): ?>
                <option value="<?= $num ?>" <?= ($filter_bulan == $num) ? 'selected' : '' ?>><?= $name ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Tampilkan</button>
    </form>

    <div style="position: relative;">
        <button onclick="toggleExportMenu()">Ekspor ▼</button>
        <div class="export-menu" id="exportMenu">
            <button onclick="window.print()">Cetak</button>
            <button onclick="exportPDF()">Ekspor PDF</button>
        </div>  
    </div>

    <button class="btn-secondary" onclick="window.location.href='index.php'">Kembali</button>
</div>

<div id="rekap-absen">
<?php if (!$filter_bulan): ?>
    <p style="text-align:center; color:gray;">Silakan pilih bulan terlebih dahulu.</p>
<?php else: ?>
    <?php foreach ($siswa_data as $jurusan => $murid): ?>
    <table>
        <tr class="group-header"><th colspan="<?= count($dates_in_month) + 7 ?>"><?= htmlspecialchars($jurusan) ?></th></tr>
        <thead>
    <tr>
        <th>No</th>
        <th>Nama</th>
        <th>NISN</th>
        <?php foreach ($dates_in_month as $tgl): ?>
            <th><?= date('d', strtotime($tgl)) ?></th>
        <?php endforeach; ?>
        <th>Hadir</th>
        <th>Ijin</th> <!-- diubah dari Izin -->
        <th>Sakit</th>
        <th>Alpa</th>
    </tr>
</thead>
<tbody>
<?php
$no = 1;
$total_hadir_jurusan = $total_izin_jurusan = $total_sakit_jurusan = $total_alpa_jurusan = 0;

foreach ($murid as $nisn => $info):
    $hadir = $izin = $sakit = $alpa = 0;
?>
    <tr>
        <td><?= $no++ ?></td>
        <td style="text-align:left;"><?= htmlspecialchars($info['nama']) ?></td>
        <td><?= htmlspecialchars($nisn) ?></td>
        <?php foreach ($dates_in_month as $tgl):
            $hari_ke = date('N', strtotime($tgl));
            $status = $info['absen'][$tgl] ?? null;

            if (in_array($tgl, $hari_libur) || $hari_ke >= 6) {
                echo '<td class="hari-libur">.</td>';
                continue;
            }

            if ($tgl > $now_date) {
                echo '<td></td>';
                continue;
            }

            if ($tgl == $now_date && $now_time < $cutoff_time) {
                if (isset($status)) {
                    if ($status === 'hadir') {
                        echo '<td class="absen-hadir">✓</td>';
                        $hadir++;
                    } elseif ($status === 'izin') {
                        echo '<td class="absen-izin">I</td>';
                        $izin++;
                    } elseif ($status === 'sakit') {
                        echo '<td class="absen-sakit">S</td>';
                        $sakit++;
                    } else {
                        echo '<td class="absen-alpa">A</td>';
                        $alpa++;
                    }
                } else {
                    echo '<td style="color:gray;">?</td>';
                }
                continue;
            }
            
            if ($status === 'hadir') {
                echo '<td class="absen-hadir">✓</td>';
                $hadir++;
            } elseif ($status === 'izin') {
                echo '<td class="absen-izin">I</td>';
                $izin++;
            } elseif ($status === 'sakit') {
                echo '<td class="absen-sakit">S</td>';
                $sakit++;
            } else {
                echo '<td class="absen-alpa">A</td>';
                $alpa++;
            }            
        endforeach; ?>
        <td><?= $hadir ?></td>
        <td><?= $izin ?></td>
        <td><?= $sakit ?></td>
        <td><?= $alpa ?></td>
    </tr>
<?php
    $total_hadir_jurusan += $hadir;
    $total_izin_jurusan += $izin;
    $total_sakit_jurusan += $sakit;
    $total_alpa_jurusan += $alpa;
endforeach;
?>
<tr style="font-weight:bold; background:#d1ecf1;">
    <td colspan="<?= 3 + count($dates_in_month) ?>">Total <?= htmlspecialchars($jurusan) ?></td>
    <td><?= $total_hadir_jurusan ?></td>
    <td><?= $total_izin_jurusan ?></td>
    <td><?= $total_sakit_jurusan ?></td>
    <td><?= $total_alpa_jurusan ?></td>
</tr>
</tbody>

    </table>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<script>
function toggleExportMenu() {
    const menu = document.getElementById('exportMenu');
    if (menu.style.display === 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
}

window.onclick = function(event) {
    if (!event.target.matches('button')) {
        document.getElementById('exportMenu').style.display = 'none';
    }
}

function exportPDF() {
    var element = document.getElementById('rekap-absen');
    var opt = {
        margin:       0.3,
        filename:     'Rekap_Absensi_<?= $filter_bulan ? $bulan_arr[intval($filter_bulan)] : "Semua" ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'in', format: 'a4', orientation: 'landscape' }
    };
    html2pdf().set(opt).from(element).save();
}
</script>
</body>
</html>