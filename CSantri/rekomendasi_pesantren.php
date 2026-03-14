<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'santri') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

// ================================================================
// ALGORITMA KNN - Euclidean Distance Berbobot
// C1=Biaya(cost), C2=Jarak(cost), C3=Fasilitas(benefit),
// C4=Program(benefit), C5=JumlahSantri(benefit)
// ================================================================

$hasil      = [];
$sudahCari  = false;
$k          = 3;

// Ambil bobot dari DB, petakan by kode
$bobotDB = [];
$resCrit = $conn->query("SELECT kode_kriteria, jenis, bobot FROM kriteria ORDER BY kode_kriteria ASC");
while ($c = $resCrit->fetch_assoc()) {
    $bobotDB[$c['kode_kriteria']] = [
        'jenis' => $c['jenis'],
        'bobot' => (float) $c['bobot']
    ];
}

// Ambil semua pondok
$semuaPondok = [];
$resPondok   = $conn->query("SELECT * FROM pondok ORDER BY id ASC");
while ($p = $resPondok->fetch_assoc()) {
    $semuaPondok[] = $p;
}

$fotoDefault   = ['course-01.jpg', 'course-02.jpg', 'course-03.jpg', 'course-04.jpg', 'course-05.jpg'];
$avatarDefault = ['team-01.png', 'team-02.png', 'team-03.png', 'team-04.png', 'team-05.png'];

$labelProgram = [1 => 'Salaf', 2 => 'Modern', 3 => 'Tahfidz'];
$colorProgram = [1 => 'bg-green', 2 => 'bg-orange', 3 => 'bg-blue'];
$labelSantri  = [1 => '< 100', 2 => '100–300', 3 => '300–500', 4 => '500–700', 5 => '> 700'];

$kriteria = [
    'C1' => ['label' => 'Biaya Pendidikan', 'icon' => 'fa-money-bill',      'jenis' => 'Cost',    'color' => 'c-orange'],
    'C2' => ['label' => 'Jarak / Lokasi',   'icon' => 'fa-location-dot',    'jenis' => 'Cost',    'color' => 'c-orange'],
    'C3' => ['label' => 'Fasilitas',         'icon' => 'fa-building',        'jenis' => 'Benefit', 'color' => 'c-green'],
    'C4' => ['label' => 'Program',           'icon' => 'fa-graduation-cap',  'jenis' => 'Benefit', 'color' => 'c-green'],
    'C5' => ['label' => 'Jumlah Santri',     'icon' => 'fa-users',           'jenis' => 'Benefit', 'color' => 'c-green'],
];

$opsiKriteria = [
    'C1' => [1 => 'Sangat Murah', 2 => 'Murah', 3 => 'Sedang', 4 => 'Mahal', 5 => 'Sangat Mahal'],
    'C2' => [1 => 'Sangat Dekat', 2 => 'Dekat', 3 => 'Sedang', 4 => 'Jauh',  5 => 'Sangat Jauh'],
    'C3' => [1 => 'Sangat Kurang', 2 => 'Kurang', 3 => 'Cukup', 4 => 'Lengkap', 5 => 'Sangat Lengkap'],
    'C4' => [1 => 'Salaf', 2 => 'Modern', 3 => 'Tahfidz'],
    'C5' => [1 => '< 100', 2 => '100–300', 3 => '300–500', 4 => '500–700', 5 => '> 700'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sudahCari = true;
    $query = [
        'C1' => (int) $_POST['c1'],
        'C2' => (int) $_POST['c2'],
        'C3' => (int) $_POST['c3'],
        'C4' => (int) $_POST['c4'],
        'C5' => (int) $_POST['c5'],
    ];

    foreach ($semuaPondok as $idx => $pondok) {
        $nilai = [
            'C1' => (int) $pondok['nilai_biaya'],
            'C2' => (int) $pondok['nilai_jarak'],
            'C3' => (int) $pondok['nilai_fasilitas'],
            'C4' => (int) $pondok['nilai_program'],
            'C5' => (int) $pondok['nilai_santri'],
        ];
        $jarak = 0;
        foreach ($bobotDB as $kode => $info) {
            $diff  = $query[$kode] - $nilai[$kode];
            $jarak += $info['bobot'] * ($diff * $diff);
        }
        $semuaPondok[$idx]['jarak'] = round(sqrt($jarak), 4);

        // Foto pesantren
        if (!empty($pondok['foto']) && file_exists('../imgs/' . $pondok['foto'])) {
            $semuaPondok[$idx]['foto'] = '../imgs/' . htmlspecialchars($pondok['foto']);
        } else {
            $semuaPondok[$idx]['foto'] = '../imgs/' . $fotoDefault[$idx % count($fotoDefault)];
        }

        $semuaPondok[$idx]['avatar'] = '../imgs/' . $avatarDefault[$idx % count($avatarDefault)];
    }

    usort($semuaPondok, fn($a, $b) => $a['jarak'] <=> $b['jarak']);
    $hasil = array_slice($semuaPondok, 0, $k);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/framework.css">
    <link rel="stylesheet" href="../css/master.css">
    <title>Rekomendasi Pesantren</title>
</head>

<body>
    <div class="page d-flex">
        <?php include '../layout/sidebar.php'; ?>
        <div class="content w-full">

            <!-- Header -->
            <div class="header d-flex justify-between bg-white p-10 align-center">
                <div class="search p-relative"></div>
                <div class="user d-flex justify-center align-center g-10">
                    <span class="c-grey fs-14">
                        <i class="fa-regular fa-user fa-fw"></i>
                        <?= htmlspecialchars($_SESSION['nama'] ?? '') ?>
                    </span>
                    <a href="../logout.php" class="c-red fs-14">
                        <i class="fa-solid fa-right-from-bracket fa-fw"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Page Title -->
            <div class="d-flex align-center justify-between mb-20" style="padding: 0 0 0 4px;">
                <div>
                    <h1 class="m-0">Rekomendasi Pesantren</h1>
                    <span class="c-grey fs-14">Gunakan algoritma KNN untuk menemukan pesantren terbaik sesuai preferensimu</span>
                </div>
                <span class="bg-blue c-white btn-shape fs-13 p-10 pl-15 pr-15">
                    <i class="fa-solid fa-robot fa-fw"></i> KNN Algorithm
                </span>
            </div>

            <!-- Form Preferensi -->
            <div class="general-info bg-white rad-10 p-20 mb-20">

                <div class="d-flex align-center g-10 mb-5">
                    <i class="fa-solid fa-sliders c-blue fa-fw"></i>
                    <h2 class="m-0">Preferensi Pencarian</h2>
                </div>
                <p class="c-grey fs-14 m-0 mb-20">
                    Isi seluruh kriteria di bawah sesuai kebutuhanmu. Sistem akan menghitung kedekatan menggunakan
                    <strong>Euclidean Distance</strong> berbobot dan menampilkan
                    <strong><?= $k ?> pesantren</strong> terbaik.
                </p>

                <!-- Bobot Kriteria -->
                <?php if (!empty($bobotDB)): ?>
                <div class="bg-eee rad-6 p-15 mb-20">
                    <span class="fw-bold fs-13 c-grey d-block mb-10">
                        <i class="fa-solid fa-scale-balanced fa-fw"></i> Bobot Kriteria yang Digunakan
                    </span>
                    <div class="d-flex g-10 f-w">
                        <?php foreach ($kriteria as $kode => $info):
                            $bobot = $bobotDB[$kode]['bobot'] ?? 0;
                        ?>
                        <div class="bg-white rad-6 p-10" style="min-width: 100px;">
                            <span class="d-block fs-12 c-grey mb-3">
                                <i class="fa-solid <?= $info['icon'] ?> fa-fw"></i> <?= $kode ?>
                            </span>
                            <strong class="c-blue fs-16"><?= $bobot ?></strong>
                            <span class="d-block fs-12 <?= $info['color'] ?>"><?= $info['jenis'] ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="d-flex g-20 f-w mb-20">
                        <?php foreach ($kriteria as $kode => $info):
                            $postKey = strtolower($kode);
                        ?>
                        <div>
                            <label class="d-block mb-5 c-grey fs-14 fw-bold">
                                <i class="fa-solid <?= $info['icon'] ?> fa-fw"></i>
                                <?= $info['label'] ?>
                                <span class="<?= $info['color'] ?> fs-12">(<?= $info['jenis'] ?>)</span>
                            </label>
                            <select name="<?= $postKey ?>" class="b-none p-10 rad-6 bg-eee" required>
                                <option value="">-- Pilih --</option>
                                <?php foreach ($opsiKriteria[$kode] as $val => $teks): ?>
                                <option value="<?= $val ?>"
                                    <?= isset($_POST[$postKey]) && (int) $_POST[$postKey] === $val ? 'selected' : '' ?>>
                                    <?= $val ?> – <?= $teks ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex g-10 align-center">
                        <button type="submit" class="save bg-blue c-white btn-shape b-none fs-14 c-p">
                            <i class="fa-solid fa-wand-magic-sparkles fa-fw"></i> Dapatkan Rekomendasi
                        </button>
                        <?php if ($sudahCari): ?>
                        <button type="reset"
                            onclick="window.location.href='rekomendasi_pesantren.php'"
                            class="save bg-eee c-grey btn-shape b-none fs-14 c-p">
                            <i class="fa-solid fa-rotate-left fa-fw"></i> Reset
                        </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Hasil Rekomendasi -->
            <?php if ($sudahCari): ?>

                <?php if (empty($hasil)): ?>
                    <div class="bg-white rad-10 p-30 mb-20 txt-c">
                        <i class="fa-solid fa-box-open fa-2x c-grey mb-10 d-block"></i>
                        <span class="c-grey fs-14">Tidak ada data pondok tersedia. Hubungi admin untuk menambahkan data.</span>
                    </div>

                <?php else: ?>

                    <!-- Info Bar -->
                    <div class="bg-white rad-10 p-15 mb-20 d-flex align-center justify-between">
                        <div class="d-flex align-center g-10">
                            <i class="fa-solid fa-circle-check c-green fa-fw fa-lg"></i>
                            <span class="fs-14 c-grey">
                                Ditemukan <strong class="c-blue"><?= count($hasil) ?> pesantren</strong>
                                paling sesuai berdasarkan preferensimu (K = <?= $k ?>).
                            </span>
                        </div>
                        <span class="c-grey fs-12">
                            <i class="fa-solid fa-info-circle fa-fw"></i>
                            Jarak lebih kecil = lebih cocok
                        </span>
                    </div>

                    <!-- Ringkasan Preferensi Input -->
                    <div class="bg-white rad-10 p-15 mb-20">
                        <span class="fw-bold fs-14 c-grey d-block mb-10">
                            <i class="fa-solid fa-clipboard-list fa-fw"></i> Preferensi yang Kamu Masukkan
                        </span>
                        <div class="d-flex g-10 f-w">
                            <?php
                            $postKeys = ['c1','c2','c3','c4','c5'];
                            $kriteriaArr = array_values($kriteria);
                            foreach ($postKeys as $i => $pk):
                                $val     = (int) $_POST[$pk];
                                $kode    = 'C' . ($i + 1);
                                $info    = $kriteria[$kode];
                                $teks    = $opsiKriteria[$kode][$val] ?? '-';
                            ?>
                            <div class="bg-eee rad-6 p-10 d-flex align-center g-8">
                                <i class="fa-solid <?= $info['icon'] ?> fa-fw c-grey"></i>
                                <div>
                                    <span class="d-block fs-12 c-grey"><?= $kode ?> – <?= $info['label'] ?></span>
                                    <strong class="fs-13"><?= $val ?> – <?= htmlspecialchars($teks) ?></strong>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Card Hasil -->
                    <div class="courses-sections d-grid g-20 mb-20">
                        <?php
                        $rankIcon  = ['🥇', '🥈', '🥉'];
                        $rankLabel = ['Terbaik', 'Runner Up', 'Alternatif'];
                        foreach ($hasil as $idx => $p):
                            $prog  = (int) $p['nilai_program'];
                            $label = $labelProgram[$prog] ?? '-';
                            $color = $colorProgram[$prog] ?? 'bg-blue';
                        ?>
                        <div class="course bg-white rad-10 p-relative">
                            <img src="<?= $p['foto'] ?>"
                                 alt="<?= htmlspecialchars($p['nama_pondok']) ?>"
                                 class="w-full">

                            <!-- Badge peringkat -->
                            <div class="p-absolute bg-blue c-white rad-6"
                                 style="top: 12px; left: 12px; padding: 4px 10px; font-size: 12px; font-weight: 600;">
                                <?= $rankIcon[$idx] ?? '' ?> <?= $rankLabel[$idx] ?? '#' . ($idx+1) ?>
                            </div>

                            <div class="course-text p-20">
                                <h3 class="m-0 mb-5"><?= htmlspecialchars($p['nama_pondok']) ?></h3>
                                <p class="m-0 c-grey fs-14 mb-10"><?= htmlspecialchars($p['deskripsi'] ?? '') ?></p>

                                <!-- Jarak KNN visual -->
                                <div class="d-flex align-center g-8">
                                    <span class="c-grey fs-13">Jarak KNN:</span>
                                    <span class="bg-blue c-white btn-shape fs-13" style="padding: 2px 10px;">
                                        <?= $p['jarak'] ?>
                                    </span>
                                    <?php
                                    $maxJarak = max(array_column($hasil, 'jarak')) ?: 1;
                                    $pct = round((1 - $p['jarak'] / ($maxJarak + 0.001)) * 100);
                                    ?>
                                    <span class="c-green fs-12 fw-bold"><?= $pct ?>% cocok</span>
                                </div>
                            </div>

                            <div class="course-info">
                                <p class="m-0 <?= $color ?> c-white w-fit"><?= $label ?></p>
                                <div class="flex-between p-20 pt-0">
                                    <span class="c-grey fs-14">
                                        <i class="fa-solid fa-location-dot fa-fw"></i>
                                        <?= htmlspecialchars($p['lokasi']) ?>
                                    </span>
                                    <span class="c-grey fs-14">
                                        <i class="fa-solid fa-user-graduate fa-fw"></i>
                                        <?= $labelSantri[(int) $p['nilai_santri']] ?? '-' ?>
                                    </span>
                                </div>
                            </div>

                            <img src="<?= $p['avatar'] ?>" alt="" class="avatar rad-half">
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Tabel Detail Perhitungan -->
                    <div class="projects bg-white rad-10 p-20 mb-20">
                        <div class="d-flex align-center justify-between mb-20">
                            <div class="d-flex align-center g-10">
                                <i class="fa-solid fa-table c-blue fa-fw"></i>
                                <h2 class="m-0">Detail Perhitungan KNN</h2>
                            </div>
                            <span class="c-grey fs-12">
                                d(q,p) = √Σ w<sub>i</sub>(q<sub>i</sub> − p<sub>i</sub>)²
                            </span>
                        </div>

                        <div class="table-holder">
                            <table class="w-full">
                                <thead>
                                    <tr>
                                        <th>Peringkat</th>
                                        <th>Nama Pesantren</th>
                                        <th title="Biaya Pendidikan">C1 Biaya</th>
                                        <th title="Jarak / Lokasi">C2 Jarak</th>
                                        <th title="Fasilitas">C3 Fasilitas</th>
                                        <th title="Program Pendidikan">C4 Program</th>
                                        <th title="Jumlah Santri">C5 Santri</th>
                                        <th>Jarak KNN</th>
                                        <th>Kesesuaian</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Baris preferensi -->
                                    <tr style="background: #f8f9fa;">
                                        <td class="txt-c">
                                            <span class="bg-eee btn-shape fs-12 c-grey">Input</span>
                                        </td>
                                        <td class="fw-bold fs-13">— Preferensi Kamu —</td>
                                        <?php foreach (['c1','c2','c3','c4','c5'] as $pk): ?>
                                        <td class="txt-c">
                                            <span class="bg-blue c-white btn-shape fs-13"><?= (int) $_POST[$pk] ?></span>
                                        </td>
                                        <?php endforeach; ?>
                                        <td class="txt-c c-grey">—</td>
                                        <td class="txt-c c-grey">—</td>
                                    </tr>

                                    <?php foreach ($hasil as $i => $p):
                                        $pct = round((1 - $p['jarak'] / ($maxJarak + 0.001)) * 100);
                                    ?>
                                    <tr>
                                        <td class="txt-c">
                                            <strong><?= $rankIcon[$i] ?? '#'.($i+1) ?></strong>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($p['nama_pondok']) ?></strong>
                                            <span class="d-block c-grey fs-12"><?= htmlspecialchars($p['lokasi']) ?></span>
                                        </td>
                                        <td class="txt-c"><?= $p['nilai_biaya'] ?></td>
                                        <td class="txt-c"><?= $p['nilai_jarak'] ?></td>
                                        <td class="txt-c"><?= $p['nilai_fasilitas'] ?></td>
                                        <td class="txt-c"><?= $labelProgram[(int) $p['nilai_program']] ?? '-' ?></td>
                                        <td class="txt-c"><?= $labelSantri[(int) $p['nilai_santri']] ?? '-' ?></td>
                                        <td class="txt-c">
                                            <span class="bg-blue c-white btn-shape fs-13"><?= $p['jarak'] ?></span>
                                        </td>
                                        <td class="txt-c">
                                            <span class="c-green fw-bold fs-13"><?= $pct ?>%</span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Keterangan Bobot -->
                        <div class="mt-20 bg-eee rad-6 p-15">
                            <span class="fw-bold fs-13 c-grey d-block mb-10">
                                <i class="fa-solid fa-circle-info fa-fw"></i> Keterangan Bobot
                            </span>
                            <div class="d-flex g-10 f-w">
                                <?php foreach ($bobotDB as $kode => $info): ?>
                                <span class="bg-white rad-6 p-8 fs-12 c-grey">
                                    <strong><?= $kode ?></strong>
                                    · Bobot <strong class="c-blue"><?= $info['bobot'] ?></strong>
                                    · <em><?= ucfirst($info['jenis']) ?></em>
                                </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                <?php endif; ?>
            <?php endif; ?>

        </div><!-- end .content -->
    </div><!-- end .page -->

    <script src="../main.js"></script>
</body>
</html>