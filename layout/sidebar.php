<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$role = $_SESSION['role'] ?? '';
?>

<!-- start side bar -->
<div class="side-bar bg-white p-20">
    <h3 class="txt-c p-relative">Sistem Rekomendasi Pesantren</h3>

    <ul class="style-none m-0 p-0 mt-40">

        <?php if ($role == 'admin') { ?>

            <li class="mb-5 rad-6">
                <a href="../admin/dashboard.php" class="c-black p-10 d-block">
                    <i class="fa-solid fa-chart-line fa-fw"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="mb-5 rad-6">
                <a href="../admin/kelola_data_kriteria.php" class="c-black p-10 d-block">
                    <i class="fa-solid fa-list fa-fw"></i>
                    <span>Data Kriteria</span>
                </a>
            </li>

            <li class="mb-5 rad-6">
                <a href="../admin/kelola_data_pondok.php" class="c-black p-10 d-block">
                    <i class="fa-solid fa-school fa-fw"></i>
                    <span>Data Pondok</span>
                </a>
            </li>

            <li class="mb-5 rad-6">
                <a href="../admin/kelola_data_user.php" class="c-black p-10 d-block">
                    <i class="fa-solid fa-users fa-fw"></i>
                    <span>Data User</span>
                </a>
            </li>

        <?php } elseif ($role == 'santri') { ?>

            <li class="mb-5 rad-6">
                <a href="../CSantri/dashboard.php" class="c-black p-10 d-block">
                    <i class="fa-solid fa-chart-line fa-fw"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <li class="mb-5 rad-6">
                <a href="../CSantri/informasi_pesantren.php" class="c-black p-10 d-block">
                    <i class="fa-solid fa-school fa-fw"></i>
                    <span>Informasi Pesantren</span>
                </a>
            </li>

            <li class="mb-5 rad-6">
                <a href="../CSantri/rekomendasi_pesantren.php" class="c-black p-10 d-block">
                    <i class="fa-solid fa-star fa-fw"></i>
                    <span>Rekomendasi Pesantren</span>
                </a>
            </li>

        <?php } ?>

    </ul>
</div>
<!-- end of side bar -->