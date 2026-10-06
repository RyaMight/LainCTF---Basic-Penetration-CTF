<?php
// docker/seed_flag.php
// Menanam Flag 3 ke tabel admin.secrets saat container web start.
// Flag dibaca dari environment variable FLAG_DUMP agar mudah diganti.

$host = getenv('DB_HOST') ?: 'db';
$user = getenv('DB_USER') ?: 'lain';
$pass = getenv('DB_PASS') ?: 'lainpass';
$flagDump = getenv('FLAG_DUMP') ?: 'LainCTF{pr0t0c0l_7_h4s_b33n_3st4bl1sh3d}';

// Tunggu sampai MySQL siap (max ~60 detik)
mysqli_report(MYSQLI_REPORT_OFF);
for ($i = 0; $i < 30; $i++) {
    $conn = @mysqli_connect($host, $user, $pass, 'admin');
    if ($conn) {
        break;
    }
    echo "[seed_flag] MySQL belum siap, retry {$i}...\n";
    sleep(2);
}

if (!$conn || !$conn->ping()) {
    echo "[seed_flag] GAGAL terhubung ke MySQL, flag tidak di-seed!\n";
    exit(1);
}

$flag = $conn->real_escape_string($flagDump);

// Pastikan tabel ada (idempotent)
$conn->query("CREATE TABLE IF NOT EXISTS secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flag VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Reset isi tabel agar selalu sinkron dengan env FLAG_DUMP terbaru
$conn->query("TRUNCATE TABLE secrets");
$conn->query("INSERT INTO secrets (flag) VALUES ('$flag')");

echo "[seed_flag] Flag 3 berhasil ditanam ke admin.secrets\n";
$conn->close();
