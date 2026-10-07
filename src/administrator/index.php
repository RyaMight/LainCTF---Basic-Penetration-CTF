<?php
session_start();
require_once __DIR__ . '/../includes/config.php';

header('X-Hidden-Flag: ' . FLAG_ADMIN_PAGE);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = $_POST['username'] ?? '';
    $p = $_POST['password'] ?? '';

    if ($u === ADMIN_USERNAME && md5($p) === ADMIN_PASSWORD_MD5) {
        $_SESSION['admin'] = ADMIN_USERNAME;
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid credentials.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<link rel="stylesheet" href="../style.css">
</head>
<body>
<div class="login-wrap">
    <div class="login-box">
        <h1>Admin Login</h1>
        <div class="flag">[FLAG] <?= htmlspecialchars(FLAG_ADMIN_PAGE) ?></div>
        <?php if ($error): ?><p style="color:#dc3545"><?= htmlspecialchars($error) ?></p><?php endif; ?>
        <form method="POST" action="index.php">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <p class="back"><a href="../home.php">&laquo; Back to list</a></p>
    </div>
</div>
<div class="footer">© 2026 Lain CTF - Basic Penetration CTF</div>
</body>
</html>
