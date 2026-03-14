<?php
session_start();
include '../koneksi.php';

$labelProgram = [1 => 'Salaf', 2 => 'Modern', 3 => 'Tahfidz'];
$colorProgram = [1 => 'bg-green', 2 => 'bg-orange', 3 => 'bg-blue'];
$labelSantri = [1 => '< 100', 2 => '100–300', 3 => '300–500', 4 => '500–700', 5 => '> 700'];

$result = $conn->query("SELECT * FROM pondok ORDER BY id ASC");

// Foto fallback default (urutan berputar jika tidak ada foto)
$fotoDefault = ['course-01.jpg', 'course-02.jpg', 'course-03.jpg', 'course-04.jpg', 'course-05.jpg'];
$avatarDefault = ['team-01.png', 'team-02.png', 'team-03.png', 'team-04.png', 'team-05.png'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/framework.css">
    <link rel="stylesheet" href="../css/master.css">
    <title>Informasi Pesantren</title>
</head>

<body>
    <div class="page d-flex">
        <?php include '../layout/sidebar.php'; ?>
        <div class="content w-full">
            <!-- start content header -->
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
            <!-- end of content header -->

            <h1 class="p-relative">Informasi Pesantren</h1>

            <div class="courses-sections d-grid g-20">

                <?php
                $i = 0;
                while ($row = $result->fetch_assoc()):
                    $prog = (int) $row['nilai_program'];
                    $colorBadge = $colorProgram[$prog] ?? 'bg-blue';
                    $labelBadge = $labelProgram[$prog] ?? 'Info Pondok';

                    // Jumlah santri label
                    $santriIdx = (int) $row['nilai_santri'];
                    $santriLabel = $labelSantri[$santriIdx] ?? '-';

                    // Foto utama pesantren
                    if (!empty($row['foto']) && file_exists('../imgs/' . $row['foto'])) {
                        $fotoSrc = '../imgs/' . htmlspecialchars($row['foto']);
                    } else {
                        $fotoSrc = '../imgs/' . $fotoDefault[$i % count($fotoDefault)];
                    }

                    // Avatar (tetap pakai default berputar)
                    $avatarSrc = '../imgs/' . $avatarDefault[$i % count($avatarDefault)];
                    $i++;
                    ?>
                    <div class="course bg-white rad-10 p-relative">
                        <img src="<?= $fotoSrc ?>" alt="<?= htmlspecialchars($row['nama_pondok']) ?>" class="w-full">
                        <div class="course-text p-20">
                            <h3 class="m-0 mb-20"><?= htmlspecialchars($row['nama_pondok']) ?></h3>
                            <p class="m-0 c-grey fs-14"><?= htmlspecialchars($row['deskripsi'] ?? '') ?></p>
                        </div>
                        <div class="course-info">
                            <p class="m-0 <?= $colorBadge ?> c-white w-fit"><?= $labelBadge ?></p>
                            <div class="flex-between p-20 pt-0">
                                <span class="c-grey fs-14"><i class="fa-solid fa-location-dot fa-fw"></i>
                                    <?= htmlspecialchars($row['lokasi']) ?></span>
                                <span class="c-grey fs-14"><i class="fa-solid fa-user-graduate fa-fw"></i>
                                    <?= $santriLabel ?> Santri</span>
                            </div>
                        </div>
                        <img src="<?= $avatarSrc ?>" alt="" class="avatar rad-half">
                    </div>
                <?php endwhile; ?>

                <?php if ($i === 0): ?>
                    <div class="bg-white rad-10 p-20">
                        <p class="c-grey txt-c">Belum ada data pesantren.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
    <script src="../main.js"></script>
</body>

</html>