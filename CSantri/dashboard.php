<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'santri') {
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

// Ambil data santri yang login
$santri_id = $_SESSION['user_id'] ?? 0;
$stmt = $pdo->prepare("SELECT nama FROM users WHERE id = ?");
$stmt->execute([$santri_id]);
$santri_nama = $stmt->fetchColumn() ?: 'Santri';

// Ambil statistik umum
$stmt = $pdo->query("SELECT COUNT(*) as total FROM pondok");
$total_pondok = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role='santri' AND status='aktif'");
$total_santri = $stmt->fetch()['total'];

// Top 5 pesantren berdasarkan nilai gabungan (untuk santri)
$stmt = $pdo->query("
    SELECT nama_pondok, lokasi, 
           (nilai_fasilitas + nilai_program + nilai_santri - nilai_biaya - nilai_jarak) as skor_total
    FROM pondok 
    ORDER BY skor_total DESC 
    LIMIT 5
");
$top_pondok = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/framework.css">
    <link rel="stylesheet" href="../css/master.css">
    <link rel="stylesheet" href="../css/all.min.css">
    <title>Dashboard Santri</title>
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
            
            <!-- start sections wrapper -->
            <div class="sections g-20 d-grid">
                <!-- start card statistik utama -->
                <div class="tickets bg-white p-20 rad-10">
                    <h2 class="m-0">Statistik Sistem
                        <span class="c-grey d-block fs-14 mt-5 fw-300 mt-10 mb-20">Data Pesantren Tersedia</span>
                    </h2>
                    <div class="ticket-boxes d-flex g-20 f-w align-center">
                        <div class="box flex-center rad-6 p-20">
                            <i class="fa-solid fa-school c-blue fa-lg mb-5 mt-5"></i>
                            <p class="fw-bold mb-5 fs-20"><?php echo $total_pondok; ?></p>
                            <span class="c-grey fs-14">Pesantren Tersedia</span>
                        </div>
                        <div class="box flex-center rad-6 p-20">
                            <i class="fa-solid fa-user-graduate c-orange fa-lg mb-5 mt-5"></i>
                            <p class="fw-bold mb-5 fs-20"><?php echo $total_santri; ?></p>
                            <span class="c-grey fs-14">Total Santri</span>
                        </div>
                        <div class="box flex-center rad-6 p-20">
                            <i class="fa-solid fa-star c-yellow fa-lg mb-5 mt-5"></i>
                            <p class="fw-bold mb-5 fs-20">5</p>
                            <span class="c-grey fs-14">Kriteria Penilaian</span>
                        </div>
                    </div>
                </div>

                <!-- start top pesantren untuk santri -->
                <div class="items bg-white p-20 rad-10">
                    <h2 class="m-0 mb-10">Pesantren Terbaik</h2>
                    <span class="flex-between mb-20">
                        <span class="c-grey fs-14">Nama Pesantren</span>
                        <span class="c-grey fs-14">Skor</span>
                    </span>
                    <?php foreach($top_pondok as $pondok): ?>
                    <div class="item flex-between">
                        <p><?php echo htmlspecialchars($pondok['nama_pondok']); ?></p>
                        <span class="bg-blue c-white btn-shape"><?php echo $pondok['skor_total']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                
            </div>
        </div>
    </div>
</body>
</html>