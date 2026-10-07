<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/ratelimit.php';

$id = $_GET['id'] ?? '1';

// --- WAF: layer 7 filter ---
$blocked = [' ', '/**/', '/*', '*/', 'union', 'select', 'and', 'or', 'sleep', 'benchmark', 'extractvalue', 'updatexml', 'information_schema', 'outfile', 'loadfile'];
$lower = strtolower($id);
foreach ($blocked as $word) {
    if (strpos($lower, $word) !== false) {
        http_response_code(403);
        exit('403 Forbidden');
    }
}
if (!preg_match('/^[0-9\x28\x29+\-*<>=!&|,%\'".`a-z_]+$/i', $id)) {
    http_response_code(403);
    exit('403 Forbidden');
}

// --- Rate limit (sliding window) ---
if (!rate_limit_check('sqli', client_identifier(), SQLI_RATELIMIT_MAX, SQLI_RATELIMIT_WINDOW)) {
    http_response_code(429);
    exit('429 Too Many Requests');
}

$sql = "SELECT id, title, description FROM episodes WHERE id = " . $id;

// Error TIDAK ditampilkan (blind) - pesan generik saja
$errorPage = '<h1>Not Found</h1><p>Episode tidak ditemukan.</p><a class="btn" href="home.php">Back to Home</a>';
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
    if ($result && $row = $result->fetch_assoc()) {
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
        echo $errorPage;
    }
} catch (mysqli_sql_exception $e) {
    echo $errorPage;
}
?>
</div>
<div class="footer">© 2026 Lain CTF - Basic Penetration CTF</div>
</body>
</html>
