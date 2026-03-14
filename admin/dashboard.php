<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Koneksi database
$host = 'localhost';
$dbname = 'rekom_pesantren';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// Ambil statistik
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pondok");
$total_pondok = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role='santri' AND status='aktif'");
$total_santri = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role='admin' AND status='aktif'");
$total_admin = $stmt->fetch()['total'];

// Top 5 pesantren berdasarkan jumlah santri (estimasi dari nilai_santri)
$stmt = $pdo->query("
    SELECT nama_pondok, lokasi, nilai_santri 
    FROM pondok 
    ORDER BY nilai_santri DESC 
    LIMIT 5
");
$top_pondok = $stmt->fetchAll();

// Latest pondok
$stmt = $pdo->query("SELECT nama_pondok, lokasi FROM pondok ORDER BY id DESC LIMIT 5");
$latest_pondok = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/framework.css">
    <link rel="stylesheet" href="../css/master.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <title>Dashboard Admin</title>
</head>
<body>
    <div class="page d-flex">
        <?php include '../layout/sidebar.php'; ?>
        <div class="content w-full">
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
            <h1 class="p-relative">Dashboard Admin</h1>
            
            <!-- start sections wrapper -->
            <div class="sections g-20 d-grid">
                <!-- start card statistik utama -->
                <div class="tickets bg-white p-20 rad-10">
                    <h2 class="m-0">Statistik Utama
                        <span class="c-grey d-block fs-14 mt-5 fw-300 mt-10 mb-20">Ringkasan Data Sistem</span>
                    </h2>
                    <div class="ticket-boxes d-flex g-20 f-w align-center">
                        <div class="box flex-center rad-6 p-20">
                            <i class="fa-solid fa-school c-blue fa-lg mb-5 mt-5"></i>
                            <p class="fw-bold mb-5 fs-20"><?php echo $total_pondok; ?></p>
                            <span class="c-grey fs-14">Total Pesantren</span>
                        </div>
                        <div class="box flex-center rad-6 p-20">
                            <i class="fa-solid fa-user-graduate c-orange fa-lg mb-5 mt-5"></i>
                            <p class="fw-bold mb-5 fs-20"><?php echo $total_santri; ?></p>
                            <span class="c-grey fs-14">Total Santri</span>
                        </div>
                        <div class="box flex-center rad-6 p-20">
                            <i class="fa-solid fa-users c-grey fa-lg mb-5 mt-5"></i>
                            <p class="fw-bold mb-5 fs-20"><?php echo $total_admin; ?></p>
                            <span class="c-grey fs-14">Total Admin</span>
                        </div>
                        <div class="box flex-center rad-6 p-20">
                            <i class="fa-solid fa-list c-green fa-lg mb-5 mt-5"></i>
                            <p class="fw-bold mb-5 fs-20"><?php echo count($kriteria ?? []); ?></p>
                            <span class="c-grey fs-14">Kriteria</span>
                        </div>
                    </div>
                </div>

                <!-- start top pesantren -->
                <div class="items bg-white p-20 rad-10">
                    <h2 class="m-0 mb-10">Pesantren Terpopuler</h2>
                    <span class="flex-between mb-20">
                        <span class="c-grey fs-14">Nama Pesantren</span>
                        <span class="c-grey fs-14">Skor Santri</span>
                    </span>
                    <?php foreach($top_pondok as $pondok): ?>
                    <div class="item flex-between">
                        <p><?php echo htmlspecialchars($pondok['nama_pondok']); ?></p>
                        <span class="bg-eee btn-shape"><?php echo $pondok['nilai_santri']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
          
            </div>

            <!-- start table pondok terbaru -->
            <div class="projects bg-white rad-10 p-20">
                <h2 class="m-0 mb-20">Data Pesantren Terbaru</h2>
                <div class="table-holder">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>Nama Pesantren</th>
                                <th>Lokasi</th>
                                <th>Biaya</th>
                                <th>Jarak</th>
                                <th>Fasilitas</th>
                                <th>Program</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $stmt = $pdo->query("SELECT * FROM pondok ORDER BY id DESC LIMIT 6");
                            while($row = $stmt->fetch()):
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nama_pondok']); ?></td>
                                <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                <td><?php echo $row['nilai_biaya']; ?></td>
                                <td><?php echo $row['nilai_jarak']; ?></td>
                                <td><?php echo $row['nilai_fasilitas']; ?></td>
                                <td><?php echo $row['nilai_program']; ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>