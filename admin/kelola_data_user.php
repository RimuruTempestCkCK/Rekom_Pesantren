<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/framework.css">
    <link rel="stylesheet" href="../css/master.css">
    <title>Kelola Data User</title>
    <style>
        /* Modal Overlay */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
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
            <!-- start content header -->
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

            <h1 class="p-relative">Kelola Data User</h1>

            <!-- start tabel data user -->
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
                                <th class="client">Email</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>1</td>
                                <td>Ahmad Fauzi</td>
                                <td>ahmadf</td>
                                <td class="client">ahmad@email.com</td>
                                <td><span class="bg-blue c-white btn-shape fs-13-mobile">Admin</span></td>
                                <td class="txt-c"><span class="bg-green c-white btn-shape fs-13-mobile">Aktif</span></td>
                                <td>
                                    <a href="edit_user.php?id=1" class="c-white bg-orange btn-shape fs-13-mobile">Edit</a>
                                    <a href="hapus_user.php?id=1" class="c-white bg-yt btn-shape fs-13-mobile" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td>Siti Aisyah</td>
                                <td>sitiaisyah</td>
                                <td class="client">siti@email.com</td>
                                <td><span class="bg-eee btn-shape fs-13-mobile">User</span></td>
                                <td class="txt-c"><span class="bg-green c-white btn-shape fs-13-mobile">Aktif</span></td>
                                <td>
                                    <a href="edit_user.php?id=2" class="c-white bg-orange btn-shape fs-13-mobile">Edit</a>
                                    <a href="hapus_user.php?id=2" class="c-white bg-yt btn-shape fs-13-mobile" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td>Muhammad Rizki</td>
                                <td>mrizki</td>
                                <td class="client">rizki@email.com</td>
                                <td><span class="bg-eee btn-shape fs-13-mobile">User</span></td>
                                <td class="txt-c"><span class="bg-green c-white btn-shape fs-13-mobile">Aktif</span></td>
                                <td>
                                    <a href="edit_user.php?id=3" class="c-white bg-orange btn-shape fs-13-mobile">Edit</a>
                                    <a href="hapus_user.php?id=3" class="c-white bg-yt btn-shape fs-13-mobile" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                                </td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td>Fatimah Zahra</td>
                                <td>fatimahz</td>
                                <td class="client">fatimah@email.com</td>
                                <td><span class="bg-eee btn-shape fs-13-mobile">User</span></td>
                                <td class="txt-c"><span class="bg-yt c-white btn-shape fs-13-mobile">Non-Aktif</span></td>
                                <td>
                                    <a href="edit_user.php?id=4" class="c-white bg-orange btn-shape fs-13-mobile">Edit</a>
                                    <a href="hapus_user.php?id=4" class="c-white bg-yt btn-shape fs-13-mobile" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                                </td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td>Hendra Saputra</td>
                                <td>hendras</td>
                                <td class="client">hendra@email.com</td>
                                <td><span class="bg-blue c-white btn-shape fs-13-mobile">Admin</span></td>
                                <td class="txt-c"><span class="bg-green c-white btn-shape fs-13-mobile">Aktif</span></td>
                                <td>
                                    <a href="edit_user.php?id=5" class="c-white bg-orange btn-shape fs-13-mobile">Edit</a>
                                    <a href="hapus_user.php?id=5" class="c-white bg-yt btn-shape fs-13-mobile" onclick="return confirm('Yakin hapus user ini?')">Hapus</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- end tabel data user -->

        </div>
    </div>

    <!-- Modal Tambah User -->
    <div class="modal-overlay" id="modalTambah">
        <div class="modal-box">
            <span class="modal-close c-grey" onclick="document.getElementById('modalTambah').classList.remove('active')">
                <i class="fa-solid fa-xmark"></i>
            </span>
            <h2 class="m-0 mb-10">Tambah User Baru</h2>
            <span class="c-grey d-block mb-20">Isi form berikut untuk menambahkan user baru</span>
            <form action="proses_tambah_user.php" method="POST">
                <label for="nama" class="d-block mb-5 c-grey">Nama Lengkap</label>
                <input id="nama" name="nama" type="text" placeholder="Nama Lengkap" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee">

                <label for="username" class="d-block mb-5 c-grey">Username</label>
                <input id="username" name="username" type="text" placeholder="Username" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee">

                <label for="email" class="d-block mb-5 c-grey">Email</label>
                <input id="email" name="email" type="email" placeholder="Email" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee">

                <label for="password" class="d-block mb-5 c-grey">Password</label>
                <input id="password" name="password" type="password" placeholder="Password" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee">

                <label for="role" class="d-block mb-5 c-grey">Role</label>
                <select id="role" name="role" class="d-block b-none p-10 rad-6 w-full mb-20 bg-eee">
                    <option value="admin">Admin</option>
                    <option value="user">User</option>
                </select>

                <input type="submit" value="Simpan User" class="save d-block bg-blue c-white t-0-3 btn-shape b-none fs-14">
            </form>
        </div>
    </div>
    <!-- end modal -->

    <script src="../main.js"></script>
</body>
</html>