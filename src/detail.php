<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';

$id = $_GET['id'] ?? '1';

// VULNERABLE: input langsung disuntikkan ke query (untuk latihan SQLi)
$sql = "SELECT id, title, description FROM episodes WHERE id = " . $id;
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Episode Detail</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
<?php
try {
    $result = $conn->query($sql);
    if ($row = $result->fetch_assoc()) {
        $imgId = (int)$row['id'];
        $imgExt = '';
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $e) {
            if (file_exists(__DIR__ . "/images/ep{$imgId}.{$e}")) {
                $imgExt = $e;
                break;
            }
        }
        ?>
        <h1>Episode Detail</h1>
        <div class="detail">
            <?php if ($imgExt): ?>
            <div class="detail-img">
                <img src="images/ep<?= $imgId ?>.<?= $imgExt ?>" alt="<?= htmlspecialchars($row['title']) ?>">
            </div>
            <?php endif; ?>
            <div class="detail-info">
                <h2>Title: <?= htmlspecialchars($row['title']) ?></h2>
                <p>Description: <?= nl2br(htmlspecialchars($row['description'])) ?></p>
                <p>ID: <?= $imgId ?></p>
            </div>
        </div>
        <a class="btn" href="home.php">Back to Home</a>
        <?php
    } else {
        echo '<h1>Not Found</h1><p>Episode tidak ditemukan.</p><a class="btn" href="home.php">Back to Home</a>';
    }
} catch (mysqli_sql_exception $e) {
    ?>
    <h1>SQL Error</h1>
    <div class="error-box"><?= htmlspecialchars($e->getMessage()) ?></div>
    <div class="flag">[FLAG] <?= FLAG_ERROR ?></div>
    <p style="margin-top:20px"><a class="btn" href="home.php">Back to Home</a></p>
    <?php
}
?>
</div>
<div class="footer">© 2026 Lain CTF - Basic Penetration CTF</div>
</body>
</html>
