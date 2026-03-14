<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

include '../koneksi.php';

// Proses Tambah
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role     = $_POST['role'];
    $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nama, $username, $password, $role);
    $stmt->execute();
    header("Location: kelola_data_user.php?success=tambah");
    exit;
}

// Proses Edit
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id       = (int) $_POST['id'];
    $nama     = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $role     = $_POST['role'];

    // Jika password diisi, update sekalian; jika kosong, biarkan
    if (!empty(trim($_POST['password']))) {
        $password = trim($_POST['password']);
        $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id=?");
        $stmt->bind_param("ssssi", $nama, $username, $password, $role, $id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id=?");
        $stmt->bind_param("sssi", $nama, $username, $role, $id);
    }
    $stmt->execute();
    header("Location: kelola_data_user.php?success=edit");
    exit;
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    // Jangan hapus diri sendiri
    if ($id !== (int) $_SESSION['id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        header("Location: kelola_data_user.php?success=hapus");
    } else {
        header("Location: kelola_data_user.php?success=self");
    }
    exit;
}

// Ambil data untuk edit
$dataEdit = null;
if (isset($_GET['edit'])) {
    $id  = (int) $_GET['edit'];
    $res = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $res->bind_param("i", $id);
    $res->execute();
    $dataEdit = $res->get_result()->fetch_assoc();
}

// Ambil semua user
$result = $conn->query("SELECT * FROM users ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/framework.css">
    <link rel="stylesheet" href="../css/master.css">
    <title>Kelola Data User</title>
    <style>
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }
        .modal-overlay.active { display: flex; }
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
            top: 15px; right: 20px;
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

            <h1 class="p-relative">Kelola Data User</h1>

            <!-- Notifikasi -->
            <?php if (isset($_GET['success'])): ?>
                <div class="bg-white rad-6 p-10 mb-15 ml-20 mr-20">
                    <?php if ($_GET['success'] === 'tambah'): ?>
                        <span class="c-green fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> User berhasil ditambahkan.</span>
                    <?php elseif ($_GET['success'] === 'edit'): ?>
                        <span class="c-blue fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> Data user berhasil diperbarui.</span>
                    <?php elseif ($_GET['success'] === 'hapus'): ?>
                        <span class="c-orange fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> User berhasil dihapus.</span>
                    <?php elseif ($_GET['success'] === 'self'): ?>
                        <span class="c-red fs-14"><i class="fa-solid fa-circle-exclamation fa-fw"></i> Tidak dapat menghapus akun yang sedang aktif.</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Tabel User -->
            <div class="projects bg-white rad-10 p-20">
                <div class="flex-between mb-20">
                    <h2 class="m-0">Daftar User</h2>
                    <button onclick="document.getElementById('modalTambah').classList.add('active')"
                        class="save bg-blue c-white btn-shape b-none fs-14 c-p">
                        <i class="fa-solid fa-plus fa-fw"></i> Tambah User
                    </button>
                </div>
                <div class="table-holder">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $no = 1;
                            while ($row = $result->fetch_assoc()):
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($row['nama']) ?></strong>
                                    <?php if ((int)$row['id'] === (int)$_SESSION['id']): ?>
                                        <span class="bg-eee btn-shape fs-12 c-grey" style="margin-left:6px;">Kamu</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>
                                    <?php if ($row['role'] === 'admin'): ?>
                                        <span class="bg-blue c-white btn-shape fs-13-mobile">Admin</span>
                                    <?php else: ?>
                                        <span class="bg-green c-white btn-shape fs-13-mobile">Santri</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="kelola_data_user.php?edit=<?= $row['id'] ?>"
                                        class="c-white bg-orange btn-shape fs-13-mobile">
                                        <i class="fa-solid fa-pen fa-fw"></i> Edit
                                    </a>
                                    <?php if ((int)$row['id'] !== (int)$_SESSION['id']): ?>
                                    <a href="kelola_data_user.php?hapus=<?= $row['id'] ?>"
                                        class="c-white bg-yt btn-shape fs-13-mobile"
                                        onclick="return confirm('Yakin ingin menghapus user <?= htmlspecialchars(addslashes($row['nama'])) ?>?')">
                                        <i class="fa-solid fa-trash fa-fw"></i> Hapus
                                    </a>
                                    <?php else: ?>
                                    <span class="bg-eee btn-shape fs-13-mobile c-grey">
                                        <i class="fa-solid fa-lock fa-fw"></i> Hapus
                                    </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal Tambah User -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <span class="modal-close c-grey"
                onclick="document.getElementById('modalTambah').classList.remove('active')">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <h2 class="m-0 mb-10">Tambah User Baru</h2>
            <span class="c-grey d-block mb-20">Isi form untuk menambahkan akun user baru</span>
            <form action="kelola_data_user.php" method="POST">
                <input type="hidden" name="aksi" value="tambah">

                <label class="d-block mb-5 c-grey">Nama Lengkap</label>
                <input type="text" name="nama" placeholder="Contoh: Ahmad Fauzi"
                    class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

                <label class="d-block mb-5 c-grey">Username</label>
                <input type="text" name="username" placeholder="Contoh: ahmadf"
                    class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

                <label class="d-block mb-5 c-grey">Password</label>
                <input type="password" name="password" placeholder="Masukkan password"
                    class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

                <label class="d-block mb-5 c-grey">Role</label>
                <select name="role" class="d-block b-none p-10 rad-6 w-full mb-20 bg-eee">
                    <option value="santri">Santri</option>
                    <option value="admin">Admin</option>
                </select>

                <input type="submit" value="Simpan User"
                    class="save d-block bg-blue c-white t-0-3 btn-shape b-none fs-14">
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <?php if ($dataEdit): ?>
    <div class="modal-overlay active" id="modalEdit">
        <div class="modal-box">
            <a href="kelola_data_user.php" class="modal-close c-grey">
                <i class="fa-solid fa-xmark"></i>
            </a>
            <h2 class="m-0 mb-10">Edit User</h2>
            <span class="c-grey d-block mb-20">Perbarui data user. Kosongkan password jika tidak ingin mengubahnya.</span>
            <form action="kelola_data_user.php" method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" value="<?= $dataEdit['id'] ?>">

                <label class="d-block mb-5 c-grey">Nama Lengkap</label>
                <input type="text" name="nama" value="<?= htmlspecialchars($dataEdit['nama']) ?>"
                    class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

                <label class="d-block mb-5 c-grey">Username</label>
                <input type="text" name="username" value="<?= htmlspecialchars($dataEdit['username']) ?>"
                    class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

                <label class="d-block mb-5 c-grey">Password Baru <span class="c-grey fs-12">(opsional)</span></label>
                <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah"
                    class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee">

                <label class="d-block mb-5 c-grey">Role</label>
                <select name="role" class="d-block b-none p-10 rad-6 w-full mb-20 bg-eee">
                    <option value="santri" <?= $dataEdit['role'] === 'santri' ? 'selected' : '' ?>>Santri</option>
                    <option value="admin"  <?= $dataEdit['role'] === 'admin'  ? 'selected' : '' ?>>Admin</option>
                </select>

                <input type="submit" value="Simpan Perubahan"
                    class="save d-block bg-blue c-white t-0-3 btn-shape b-none fs-14">
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="../main.js"></script>
</body>

</html>