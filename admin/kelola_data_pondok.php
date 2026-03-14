<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}
include '../koneksi.php';

$labelProgram = [1=>'Salaf', 2=>'Modern', 3=>'Tahfidz'];
$colorProgram = [1=>'bg-green', 2=>'bg-orange', 3=>'bg-blue'];
$labelSantri  = [1=>'< 100', 2=>'100–300', 3=>'300–500', 4=>'500–700', 5=>'> 700'];

// Fungsi upload foto
function uploadFoto($fileInput, $fotoLama = null) {
    if (!isset($_FILES[$fileInput]) || $_FILES[$fileInput]['error'] !== UPLOAD_ERR_OK) {
        return $fotoLama; // tidak ada file baru, kembalikan foto lama
    }

    $file     = $_FILES[$fileInput];
    $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

    if (!in_array($ext, $allowed)) return $fotoLama;

    $namaFile = 'pondok_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
    $tujuan   = '../imgs/' . $namaFile;

    if (move_uploaded_file($file['tmp_name'], $tujuan)) {
        // hapus foto lama jika ada
        if ($fotoLama && file_exists('../imgs/' . $fotoLama)) {
            unlink('../imgs/' . $fotoLama);
        }
        return $namaFile;
    }
    return $fotoLama;
}

// Proses Tambah
if (isset($_POST['aksi']) && $_POST['aksi'] === 'tambah') {
    $foto = uploadFoto('foto');
    $stmt = $conn->prepare("INSERT INTO pondok (nama_pondok, lokasi, deskripsi, foto, nilai_biaya, nilai_jarak, nilai_fasilitas, nilai_program, nilai_santri) VALUES (?,?,?,?,?,?,?,?,?)");
    $stmt->bind_param("ssssiiii",
        $_POST['nama_pondok'], $_POST['lokasi'], $_POST['deskripsi'], $foto,
        $_POST['nilai_biaya'], $_POST['nilai_jarak'], $_POST['nilai_fasilitas'],
        $_POST['nilai_program'], $_POST['nilai_santri']
    );
    $stmt->execute();
    header("Location: kelola_data_pondok.php?success=tambah"); exit;
}

// Proses Edit
if (isset($_POST['aksi']) && $_POST['aksi'] === 'edit') {
    $id = (int)$_POST['id'];

    // Ambil foto lama
    $res = $conn->prepare("SELECT foto FROM pondok WHERE id=?");
    $res->bind_param("i", $id);
    $res->execute();
    $fotoLama = $res->get_result()->fetch_assoc()['foto'] ?? null;

    $foto = uploadFoto('foto', $fotoLama);

    $stmt = $conn->prepare("UPDATE pondok SET nama_pondok=?, lokasi=?, deskripsi=?, foto=?, nilai_biaya=?, nilai_jarak=?, nilai_fasilitas=?, nilai_program=?, nilai_santri=? WHERE id=?");
    $stmt->bind_param("ssssiiiiii",
        $_POST['nama_pondok'], $_POST['lokasi'], $_POST['deskripsi'], $foto,
        $_POST['nilai_biaya'], $_POST['nilai_jarak'], $_POST['nilai_fasilitas'],
        $_POST['nilai_program'], $_POST['nilai_santri'], $id
    );
    $stmt->execute();
    header("Location: kelola_data_pondok.php?success=edit"); exit;
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    // hapus foto dulu
    $res = $conn->prepare("SELECT foto FROM pondok WHERE id=?");
    $res->bind_param("i", $id);
    $res->execute();
    $fotoHapus = $res->get_result()->fetch_assoc()['foto'] ?? null;
    if ($fotoHapus && file_exists('../imgs/' . $fotoHapus)) {
        unlink('../imgs/' . $fotoHapus);
    }
    $stmt = $conn->prepare("DELETE FROM pondok WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: kelola_data_pondok.php?success=hapus"); exit;
}

// Ambil data edit
$dataEdit = null;
if (isset($_GET['edit'])) {
    $id  = (int)$_GET['edit'];
    $res = $conn->prepare("SELECT * FROM pondok WHERE id=?");
    $res->bind_param("i", $id);
    $res->execute();
    $dataEdit = $res->get_result()->fetch_assoc();
}

// Ambil semua pondok
$result = $conn->query("SELECT * FROM pondok ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/all.min.css">
    <link rel="stylesheet" href="../css/framework.css">
    <link rel="stylesheet" href="../css/master.css">
    <title>Kelola Data Pondok</title>
    <style>
        .modal-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:999; justify-content:center; align-items:center; }
        .modal-overlay.active { display:flex; }
        .modal-box { background:#fff; border-radius:10px; padding:30px; width:500px; max-width:95vw; position:relative; max-height:90vh; overflow-y:auto; }
        .modal-close { position:absolute; top:15px; right:20px; cursor:pointer; font-size:18px; }
        .preview-foto { width:100%; height:160px; object-fit:cover; border-radius:6px; margin-bottom:10px; display:block; }
        .preview-placeholder { width:100%; height:160px; background:#eee; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#aaa; margin-bottom:10px; font-size:13px; }
        .foto-thumb { width:50px; height:40px; object-fit:cover; border-radius:4px; }
    </style>
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

        <h1 class="p-relative">Kelola Data Pondok</h1>

        <?php if (isset($_GET['success'])): ?>
        <div class="bg-white rad-6 p-10 mb-15 ml-20 mr-20">
            <?php if ($_GET['success']==='tambah'): ?><span class="c-green fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> Pondok berhasil ditambahkan.</span>
            <?php elseif ($_GET['success']==='edit'): ?><span class="c-blue fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> Pondok berhasil diperbarui.</span>
            <?php elseif ($_GET['success']==='hapus'): ?><span class="c-orange fs-14"><i class="fa-solid fa-circle-check fa-fw"></i> Pondok berhasil dihapus.</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="projects bg-white rad-10 p-20">
            <div class="flex-between mb-20">
                <h2 class="m-0">Daftar Pondok Pesantren</h2>
                <button onclick="document.getElementById('modalTambah').classList.add('active')"
                    class="save bg-blue c-white btn-shape b-none fs-14 c-p">
                    <i class="fa-solid fa-plus fa-fw"></i> Tambah Pondok
                </button>
            </div>
            <div class="table-holder">
                <table class="w-full">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Foto</th>
                            <th>Nama Pondok</th>
                            <th class="client">Lokasi</th>
                            <th>Program</th>
                            <th>C1 Biaya</th>
                            <th>C2 Jarak</th>
                            <th>C3 Fasilitas</th>
                            <th>C5 Santri</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no=1; while ($row = $result->fetch_assoc()):
                        $prog  = (int)$row['nilai_program'];
                        $color = $colorProgram[$prog] ?? 'bg-blue';
                        $label = $labelProgram[$prog] ?? '-';
                        $fotoSrc = (!empty($row['foto']) && file_exists('../imgs/' . $row['foto']))
                            ? '../imgs/' . htmlspecialchars($row['foto'])
                            : '../imgs/course-01.jpg';
                    ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><img src="<?= $fotoSrc ?>" alt="" class="foto-thumb"></td>
                            <td><?= htmlspecialchars($row['nama_pondok']) ?></td>
                            <td class="client"><?= htmlspecialchars($row['lokasi']) ?></td>
                            <td><span class="<?= $color ?> c-white btn-shape fs-13-mobile"><?= $label ?></span></td>
                            <td class="txt-c"><?= $row['nilai_biaya'] ?></td>
                            <td class="txt-c"><?= $row['nilai_jarak'] ?></td>
                            <td class="txt-c"><?= $row['nilai_fasilitas'] ?></td>
                            <td class="txt-c"><?= $row['nilai_santri'] ?></td>
                            <td>
                                <a href="kelola_data_pondok.php?edit=<?= $row['id'] ?>" class="c-white bg-orange btn-shape fs-13-mobile">Edit</a>
                                <a href="kelola_data_pondok.php?hapus=<?= $row['id'] ?>" class="c-white bg-yt btn-shape fs-13-mobile" onclick="return confirm('Yakin hapus data pondok ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<?php
function formPondok($d = null) {
    $v = fn($k) => htmlspecialchars($d[$k] ?? '');
    $sel = fn($k,$val) => isset($d[$k]) && $d[$k]==$val ? 'selected' : '';
    $formId = isset($d['id']) ? 'edit' : 'tambah';
    $previewId = 'preview_' . $formId;
    $fotoLama = $d['foto'] ?? '';
    $fotoSrc = (!empty($fotoLama) && file_exists('../imgs/' . $fotoLama))
        ? '../imgs/' . htmlspecialchars($fotoLama)
        : '';
?>
    <label class="d-block mb-5 c-grey">Nama Pondok</label>
    <input type="text" name="nama_pondok" value="<?= $v('nama_pondok') ?>" placeholder="Nama Pondok"
        class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

    <label class="d-block mb-5 c-grey">Alamat / Lokasi</label>
    <input type="text" name="lokasi" value="<?= $v('lokasi') ?>" placeholder="Contoh: Kab. Kampar, Riau"
        class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>

    <label class="d-block mb-5 c-grey">Deskripsi Singkat</label>
    <textarea name="deskripsi" placeholder="Deskripsi singkat pesantren..."
        class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee"><?= $v('deskripsi') ?></textarea>

    <label class="d-block mb-5 c-grey">Foto Pesantren <span class="c-grey fs-13">(JPG/PNG/WEBP, opsional)</span></label>
    <?php if ($fotoSrc): ?>
        <img src="<?= $fotoSrc ?>" id="<?= $previewId ?>" class="preview-foto" alt="Preview Foto">
    <?php else: ?>
        <div id="<?= $previewId ?>_placeholder" class="preview-placeholder"><i class="fa-solid fa-image fa-fw"></i>&nbsp; Belum ada foto</div>
        <img src="" id="<?= $previewId ?>" class="preview-foto" alt="Preview Foto" style="display:none;">
    <?php endif; ?>
    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp"
        class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee"
        onchange="previewGambar(this, '<?= $previewId ?>')">
    <?php if (!empty($fotoLama)): ?>
        <small class="c-grey fs-13 d-block mb-15">File saat ini: <strong><?= htmlspecialchars($fotoLama) ?></strong> — kosongkan jika tidak ingin mengganti.</small>
    <?php endif; ?>

    <label class="d-block mb-5 c-grey">C1 – Biaya Pendidikan <span class="c-orange fs-13">(Cost)</span></label>
    <select name="nilai_biaya" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>
        <option value="">-- Pilih --</option>
        <option value="1" <?= $sel('nilai_biaya',1) ?>>1 – Sangat Murah</option>
        <option value="2" <?= $sel('nilai_biaya',2) ?>>2 – Murah</option>
        <option value="3" <?= $sel('nilai_biaya',3) ?>>3 – Sedang</option>
        <option value="4" <?= $sel('nilai_biaya',4) ?>>4 – Mahal</option>
        <option value="5" <?= $sel('nilai_biaya',5) ?>>5 – Sangat Mahal</option>
    </select>

    <label class="d-block mb-5 c-grey">C2 – Jarak / Lokasi <span class="c-orange fs-13">(Cost)</span></label>
    <select name="nilai_jarak" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>
        <option value="">-- Pilih --</option>
        <option value="1" <?= $sel('nilai_jarak',1) ?>>1 – Sangat Dekat</option>
        <option value="2" <?= $sel('nilai_jarak',2) ?>>2 – Dekat</option>
        <option value="3" <?= $sel('nilai_jarak',3) ?>>3 – Sedang</option>
        <option value="4" <?= $sel('nilai_jarak',4) ?>>4 – Jauh</option>
        <option value="5" <?= $sel('nilai_jarak',5) ?>>5 – Sangat Jauh</option>
    </select>

    <label class="d-block mb-5 c-grey">C3 – Fasilitas <span class="c-green fs-13">(Benefit)</span></label>
    <select name="nilai_fasilitas" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>
        <option value="">-- Pilih --</option>
        <option value="1" <?= $sel('nilai_fasilitas',1) ?>>1 – Sangat Kurang</option>
        <option value="2" <?= $sel('nilai_fasilitas',2) ?>>2 – Kurang</option>
        <option value="3" <?= $sel('nilai_fasilitas',3) ?>>3 – Cukup</option>
        <option value="4" <?= $sel('nilai_fasilitas',4) ?>>4 – Lengkap</option>
        <option value="5" <?= $sel('nilai_fasilitas',5) ?>>5 – Sangat Lengkap</option>
    </select>

    <label class="d-block mb-5 c-grey">C4 – Program Pendidikan <span class="c-green fs-13">(Benefit)</span></label>
    <select name="nilai_program" class="d-block b-none p-10 rad-6 w-full mb-15 bg-eee" required>
        <option value="">-- Pilih --</option>
        <option value="1" <?= $sel('nilai_program',1) ?>>1 – Salaf</option>
        <option value="2" <?= $sel('nilai_program',2) ?>>2 – Modern</option>
        <option value="3" <?= $sel('nilai_program',3) ?>>3 – Tahfidz</option>
    </select>

    <label class="d-block mb-5 c-grey">C5 – Jumlah Santri <span class="c-green fs-13">(Benefit)</span></label>
    <select name="nilai_santri" class="d-block b-none p-10 rad-6 w-full mb-20 bg-eee" required>
        <option value="">-- Pilih --</option>
        <option value="1" <?= $sel('nilai_santri',1) ?>>1 – &lt; 100</option>
        <option value="2" <?= $sel('nilai_santri',2) ?>>2 – 100–300</option>
        <option value="3" <?= $sel('nilai_santri',3) ?>>3 – 300–500</option>
        <option value="4" <?= $sel('nilai_santri',4) ?>>4 – 500–700</option>
        <option value="5" <?= $sel('nilai_santri',5) ?>>5 – &gt; 700</option>
    </select>
<?php } ?>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal-box">
        <span class="modal-close c-grey" onclick="document.getElementById('modalTambah').classList.remove('active')">
            <i class="fa-solid fa-xmark"></i>
        </span>
        <h2 class="m-0 mb-10">Tambah Pondok Baru</h2>
        <span class="c-grey d-block mb-20">Isi nama, lokasi, foto, dan nilai KNN untuk setiap kriteria</span>
        <form action="kelola_data_pondok.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="tambah">
            <?php formPondok(); ?>
            <input type="submit" value="Simpan Pondok" class="save d-block bg-blue c-white t-0-3 btn-shape b-none fs-14">
        </form>
    </div>
</div>

<!-- Modal Edit -->
<?php if ($dataEdit): ?>
<div class="modal-overlay active" id="modalEdit">
    <div class="modal-box">
        <a href="kelola_data_pondok.php" class="modal-close c-grey">
            <i class="fa-solid fa-xmark"></i>
        </a>
        <h2 class="m-0 mb-10">Edit Pondok</h2>
        <span class="c-grey d-block mb-20">Perbarui data, foto, dan nilai KNN pondok</span>
        <form action="kelola_data_pondok.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" value="<?= $dataEdit['id'] ?>">
            <?php formPondok($dataEdit); ?>
            <input type="submit" value="Simpan Perubahan" class="save d-block bg-blue c-white t-0-3 btn-shape b-none fs-14">
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function previewGambar(input, previewId) {
    const file = input.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(e) {
        const img = document.getElementById(previewId);
        const placeholder = document.getElementById(previewId + '_placeholder');
        if (img) {
            img.src = e.target.result;
            img.style.display = 'block';
        }
        if (placeholder) placeholder.style.display = 'none';
    };
    reader.readAsDataURL(file);
}
</script>
<script src="../main.js"></script>
</body>
</html>