<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../koneksi.php';

// Proses Tambah
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $kode = trim($_POST['kode_kriteria']);
    $nama = trim($_POST['nama_kriteria']);
    $jenis = $_POST['jenis'];
    $bobot = (float) $_POST['bobot'];
    $stmt = $conn->prepare("INSERT INTO kriteria (kode_kriteria, nama_kriteria, jenis, bobot) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sssd", $kode, $nama, $jenis, $bobot);
    $stmt->execute();
    header("Location: kelola_data_kriteria.php?success=tambah");
    exit;
}

// Proses Edit
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id = (int) $_POST['id'];
    $kode = trim($_POST['kode_kriteria']);
    $nama = trim($_POST['nama_kriteria']);
    $jenis = $_POST['jenis'];
    $bobot = (float) $_POST['bobot'];
    $stmt = $conn->prepare("UPDATE kriteria SET kode_kriteria=?, nama_kriteria=?, jenis=?, bobot=? WHERE id=?");
    $stmt->bind_param("sssdi", $kode, $nama, $jenis, $bobot, $id);
    $stmt->execute();
    header("Location: kelola_data_kriteria.php?success=edit");
    exit;
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    $stmt = $conn->prepare("DELETE FROM kriteria WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: kelola_data_kriteria.php?success=hapus");
    exit;
}

// Ambil data edit
$dataEdit = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $res = $conn->prepare("SELECT * FROM kriteria WHERE id = ?");
    $res->bind_param("i", $id);
    $res->execute();
    $dataEdit = $res->get_result()->fetch_assoc();
}

// Ambil semua kriteria
$result = $conn->query("SELECT * FROM kriteria ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/framework.css">
    <link rel="stylesheet" href="../css/master.css">
    <title>Kelola Data Kriteria</title>
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-box {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            width: 450px;
            max-width: 95vw;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 15px;
            right: 20px;
            cursor: pointer;
            font-size: 18px;
        }
    </style>
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

            <h1 class="p-relative">Kelola Data Kriteria</h1>

            <!-- Notifikasi -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-white rad-6 p-10 mb-15 ml-20 mr-20">
                    <?php if ($_GET['success'] === 'tambah'): ?>
                        <span class="c-green fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> Kriteria berhasil
                            ditambahkan.</span>
                    <?php elseif ($_GET['success'] === 'edit'): ?>
                        <span class="c-blue fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> Kriteria berhasil
                            diperbarui.</span>
                    <?php elseif ($_GET['success'] === 'hapus'): ?>
                        <span class="c-orange fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> Kriteria berhasil
                            dihapus.</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Tabel Kriteria -->
            <div class="projects bg-white rad-10 p-20">
                <div class="flex-between mb-20">
                    <h2 class="m-0">Daftar Kriteria KNN</h2>
                    <button onclick="document.getElementById('modalTambah').classList.add('active')"
                        class="save bg-blue c-white btn-shape b-none fs-14 c-p">
                        <i class="fa-solid fa-plus fa-fw"></i> Tambah Kriteria
                    </button>
                </div>
                <div class="table-holder">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Kode</th>
                                <th>Nama Kriteria</th>
                                <th>Jenis</th>
                                <th>Bobot</th>
                                <th>Keterangan Nilai</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            // Keterangan nilai per kriteria
                            $keterangan = [
                                'Biaya Pendidikan' => '1=Sangat Murah, 2=Murah, 3=Sedang, 4=Mahal, 5=Sangat Mahal',
                                'Jarak / Lokasi' => '1=Sangat Dekat, 2=Dekat, 3=Sedang, 4=Jauh, 5=Sangat Jauh',
                                'Fasilitas' => '1=Sangat Kurang, 2=Kurang, 3=Cukup, 4=Lengkap, 5=Sangat Lengkap',
                                'Program Pendidikan' => '1=Salaf, 2=Modern, 3=Tahfidz',
                                'Jumlah Santri' => '1=<100, 2=100–300, 3=300–500, 4=500–700, 5=>700',
                            ];
                            while ($row = $result->fetch_assoc()):
                                $ket = $keterangan[$row['nama_kriteria']] ?? '-';
                                ?>
                                <tr>
                                    <td><?= $no++ ?></td>
                                    <td><strong><?= htmlspecialchars($row['kode_kriteria']) ?></strong></td>
                                    <td><?= htmlspecialchars($row['nama_kriteria']) ?></td>
                                    <td>
                                        <?php if (strtolower($row['jenis']) === 'cost'): ?>
                                            <span class="bg-orange c-white btn-shape fs-13-mobile">Cost</span>
                                        <?php else: ?>
                                            <span class="bg-green c-white btn-shape fs-13-mobile">Benefit</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $row['bobot'] ?></td>
                                    <td class="c-grey fs-14"><?= $ket ?></td>
                                    <td>
                                        <a href="kelola_data_kriteria.php?edit=<?= $row['id'] ?>"
                                            class="c-white bg-orange btn-shape fs-13-mobile">Edit</a>
                                        <a href="kelola_data_kriteria.php?hapus=<?= $row['id'] ?>"
                                            class="c-white bg-yt btn-shape fs-13-mobile"
                                            onclick="return confirm('Yakin hapus kriteria ini?')">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Tambah -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <span class="modal-close c-grey"
                onclick="document.getElementById('modalTambah').classList.remove('active')">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <h2 class="m-0 mb-10">Tambah Kriteria</h2>
            <span class="c-grey d-block mb-20">Isi form untuk menambahkan kriteria KNN</span>
            <form action="kelola_data_kriteria.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">

                <label class="d-block mb-5 c-grey">Kode Kriteria</label>
                <input type="text" name="kode_kriteria" placeholder="Contoh: C1"
                    class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

                <label class="d-block mb-5 c-grey">Nama Kriteria</label>
                <select name="nama_kriteria" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>
                    <option value="">-- Pilih Kriteria --</option>
                    <option value="Biaya Pendidikan">Biaya Pendidikan</option>
                    <option value="Jarak / Lokasi">Jarak / Lokasi</option>
                    <option value="Fasilitas">Fasilitas</option>
                    <option value="Program Pendidikan">Program Pendidikan</option>
                    <option value="Jumlah Santri">Jumlah Santri</option>
                </select>

                <label class="d-block mb-5 c-grey">Jenis</label>
                <select name="jenis" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee">
                    <option value="benefit">Benefit</option>
                    <option value="cost">Cost</option>
                </select>

                <label class="d-block mb-5 c-grey">Bobot (total semua kriteria = 1)</label>
                <input type="number" step="0.01" min="0" max="1" name="bobot" placeholder="Contoh: 0.25"
                    class="d-block b-none p-10 rad-6 w-full mb-20 bg-eee" required>

                <input type="submit" value="Simpan Kriteria"
                    class="save d-block bg-blue c-white t-0-3 btn-shape b-none fs-14">
            </form>
        </div>
    </div>

    <!-- Modal Edit -->
    <?php if ($dataEdit): ?>
        <div class="modal-overlay active" id="modalEdit">
            <div class="modal-box">
                <a href="kelola_data_kriteria.php" class="modal-close c-grey">
                    <i class="fa-solid fa-xmark"></i>
                </a>
                <h2 class="m-0 mb-10">Edit Kriteria</h2>
                <span class="c-grey d-block mb-20">Perbarui data kriteria KNN</span>
                <form action="kelola_data_kriteria.php" method="POST">
                    <input type="hidden" name="aksi" value="edit">
                    <input type="hidden" name="id" value="<?= $dataEdit['id'] ?>">

                    <label class="d-block mb-5 c-grey">Kode Kriteria</label>
                    <input type="text" name="kode_kriteria" value="<?= htmlspecialchars($dataEdit['kode_kriteria']) ?>"
                        class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

                    <label class="d-block mb-5 c-grey">Nama Kriteria</label>
                    <select name="nama_kriteria" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>
                        <?php
                        $pilihanKriteria = ['Biaya Pendidikan', 'Jarak / Lokasi', 'Fasilitas', 'Program Pendidikan', 'Jumlah Santri'];
                        foreach ($pilihanKriteria as $p):
                            ?>
                            <option value="<?= $p ?>" <?= $dataEdit['nama_kriteria'] === $p ? 'selected' : '' ?>><?= $p ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label class="d-block mb-5 c-grey">Jenis</label>
                    <select name="jenis" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee">
                        <option value="benefit" <?= $dataEdit['jenis'] === 'benefit' ? 'selected' : '' ?>>Benefit</option>
                        <option value="cost" <?= $dataEdit['jenis'] === 'cost' ? 'selected' : '' ?>>Cost</option>
                    </select>

                    <label class="d-block mb-5 c-grey">Bobot</label>
                    <input type="number" step="0.01" min="0" max="1" name="bobot" value="<?= $dataEdit['bobot'] ?>"
                        class="d-block b-none p-10 rad-6 w-full mb-20 bg-eee" required>

                    <input type="submit" value="Simpan Perubahan"
                        class="save d-block bg-blue c-white t-0-3 btn-shape b-none fs-14">
                </form>
            </div>
        </div>
    <?php endif; ?>

    <script src="../main.js"></script>
</body>

</html>