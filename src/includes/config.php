<?php
// includes/config.php
// =====================
// SATU TEMPAT untuk mengedit semua flag & kredensial admin CTF.
// Nilai default di sini bisa dioverride lewat environment variable Docker.

// --- FLAGS (format: LainCTF{...}) ---
define('FLAG_ERROR',      getenv('FLAG_ERROR')      ?: 'LainCTF{y0u_4r3_c0nn3ct3d}');
define('FLAG_ADMIN_PAGE', getenv('FLAG_ADMIN_PAGE') ?: 'LainCTF{l4y3r_07_pr0t0c0l}');
define('FLAG_DUMP',       getenv('FLAG_DUMP')       ?: 'LainCTF{pr0t0c0l_7_h4s_b33n_3st4bl1sh3d}');

// --- Kredensial admin (username terlihat via dump, password harus dicrack dari hash MD5) ---
define('ADMIN_USERNAME', 'Lain');
// Password asli: "cyberia" â€” cukup untuk verifikasi internal saja
define('ADMIN_PASSWORD_MD5', '26defa4f6e805a82e9d774080fad7312');

// --- Setting aplikasi ---
define('SITE_NAME', 'LAIN // WIRED ARCHIVE');
define('CTF_HINT', "Hint: There is no place like 127.0.0.1 ... but the Wired remembers everything. Are you sure a parameter is always just a number?");
