<?php
session_start();

$host   = "localhost";
$dbname = "rekom_pesantren";
$user   = "root";
$pass   = "";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT id, nama, username, password, role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        if ($password === $row['password']) {
            $_SESSION['id']       = $row['id'];
            $_SESSION['nama']     = $row['nama'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role']     = $row['role'];

            if ($row['role'] === 'admin') {
                header("Location: admin/dashboard.php");
            } else {
                header("Location: CSantri/dashboard.php");
            }
            exit;
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/framework.css">
    <link rel="stylesheet" href="css/master.css">
    <link rel="stylesheet" href="css/all.min.css">
    <title>Login</title>
</head>
<body>
    <div class="page d-flex justify-center align-center">
        <!-- Login Box -->
        <div class="login bg-white rad-10 p-20" style="width: 400px;">
            <!-- Login Header -->
            <div class="welcome-header d-flex p-20 bg-eee justify-between align-center rad-6 mb-20">
                <h2 class="m-0">
                    Login
                    <span class="c-grey d-block fs-14 mt-5 fw-300">Masuk ke akun Anda</span>
                </h2>
                <i class="fa-solid fa-right-to-bracket fa-2x c-grey"></i>
            </div>

            <!-- Pesan Error -->
            <?php if ($error): ?>
                <div class="bg-eee rad-6 p-10 mb-15 txt-c">
                    <span class="c-red fs-14"><i class="fa-solid fa-circle-exclamation fa-fw"></i> <?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="login.php" method="POST">
                <!-- Username -->
                <div class="mb-15">
                    <label class="d-block fw-bold fs-14 mb-5 c-grey">
                        <i class="fa-regular fa-user fa-fw"></i>
                        Username
                    </label>
                    <div class="p-relative">
                        <span class="p-absolute c-grey" style="top: 50%; transform: translateY(-50%); left: 10px;">
                            <i class="fa-regular fa-user fa-fw"></i>
                        </span>
                        <input
                            type="text"
                            name="username"
                            placeholder="Masukkan username"
                            class="b-none d-block p-10 pl-30 rad-6 w-full bg-eee"
                            value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                            required
                        >
                    </div>
                </div>
                <!-- Password -->
                <div class="mb-20">
                    <label class="d-block fw-bold fs-14 mb-5 c-grey">
                        <i class="fa-solid fa-lock fa-fw"></i>
                        Password
                    </label>
                    <div class="p-relative">
                        <span class="p-absolute c-grey" style="top: 50%; transform: translateY(-50%); left: 10px;">
                            <i class="fa-solid fa-lock fa-fw"></i>
                        </span>
                        <input
                            type="password"
                            name="password"
                            placeholder="Masukkan password"
                            class="b-none d-block p-10 pl-30 rad-6 w-full bg-eee"
                            required
                        >
                    </div>
                </div>
                
                <!-- Submit Button -->
                <input
                    type="submit"
                    value="Login"
                    class="save d-block bg-blue c-white t-0-3 btn-shape b-none fs-14 w-full"
                >
            </form>
        </div>
        <!-- End Login Box -->
    </div>
</body>
</html>