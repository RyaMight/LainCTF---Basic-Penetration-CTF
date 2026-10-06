-- ============================================================
-- Lain CTF - SQL Injection practice lab (db/init.sql)
-- Dijalankan otomatis oleh entrypoint MySQL saat volume baru
-- ============================================================

-- ---------- Database 1: lain (isi publik, titik injeksi) ----------
CREATE DATABASE IF NOT EXISTS lain CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lain;

CREATE TABLE IF NOT EXISTS episodes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  description TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO episodes (title, description) VALUES
('WEIRD', 'Lain Iwakura menerima email dari Chisa Yomoda, teman sekelas yang dikabarkan meninggal dunia. Di dalam email, Chisa mengatakan bahwa ia tidak mati, melainkan hanya melepas tubuhnya dan sekarang hidup di dalam Wired. Pesan itu menyebar di kalangan murid lain, dan rasa penasaran Lain terhadap dunia digital pun mulai tumbuh.'),
('GIRLS', 'Lain meminta Navi (komputer pribadi) baru kepada ayahnya dan pergi ke Tachibana General Laboratories untuk merakitnya. Bersama Alice, Julie, dan Reika, ia berkunjung ke klub Cyberia. Di sana Lain mendengar rumor tentang seorang hacker berbahaya di Wired, dan malam itu terjadi insiden menembak di Cyberia yang disebabkan pengguna narkoba Accela.'),
('PSYCHE', 'Sebuah chip misterius bernama Psyche dikirim ke rumah Lain tanpa nama pengirim. Setelah dipasang pada Navi-nya, kemampuan komputer Lain melonjak luar biasa. Para pria berjas lab terus menguntitnya, sementara rumor di Cyberia mulai mengaitkan kejadian-kejadian aneh dengan kehadiran Lain.'),
('RELIGION', 'Nama kelompok misterius Knights of the Eastern Calculus mulai muncul. Lain diterorir oleh orang-orang tak dikenal dan menerima kiriman barang yang tidak ia minta. Ia mulai memasang sensor pada tubuhnya untuk bereksperimen, dan sosok Masami Iwakura perlahan mulai mendekati kehidupannya.'),
('DISTORTION', 'Obat biotechnological Accela menyebar di Cyberia dan membuat penggunanya bisa berpikir ribuan kali lebih cepat. Lain menyelidiki hubungan Accela dengan Knights, sementara kejadian penembakan di klub dan perilaku aneh adiknya, Mika, mulai mengacaukan kehidupan nyata Lain. Batas antara dunia nyata dan Wired semakin kabur.'),
('KIDS', 'Fenomena aneh terjadi: anak-anak di seluruh dunia menatap ke langit sambil menonton layar berisi derau statis. Lain juga melihat sosok dirinya sendiri yang berjalan di tempat lain pada saat bersamaan. Eksperimen lama tentang pencitraan pikiran anak-anak ke dalam Wired mulai terungkap.'),
('SOCIETY', 'Lain mencoba mengubah citranya agar terlihat lebih ekspresif dan terhubung dengan teman-temannya. Namun di Cyberia, rumor menyebar tentang sesosok "Lain" di Wired yang berbeda dari dirinya. Penggerebekan terhadap Knights terjadi, dan keberadaan dua Lain yang berbeda semakin dirasakan oleh orang-orang di sekitarnya.'),
('LANDMARK', 'Masami Iwakura muncul di depan Lain dan mengaku sebagai penguasa sekaligus "tuhan" dari Wired. Lain bertemu dengan seorang murid perempuan yang mengaku pernah diselamatkan oleh Lain di dunia maya. Arus data dan kekuatan yang tak seharusnya dimiliki manusia mulai terkumpul di sekitar Lain.'),
('PROTOCOL', 'Sejarah protokol jaringan terungkap: Masami Eiri, mantan insinyur Tachibana Labs, menyisipkan Protocol 7 ke dalam arsitektur internet untuk menghubungkan Wired langsung dengan kesadaran manusia melalui resonansi Schumann. Lain mulai mengenal sosok asli Eiri dan maksud tersembunyinya.'),
('LOVE', 'Rahasia gelap Alice tentang hubungannya dengan seorang guru menyebar di seluruh Wired. Untuk menyelamatkan Alice, Lain menggunakan kekuatannya menghapus ingatan semua orang tentang rumor tersebut, termasuk ingatan Alice akan dirinya sendiri. Namun tindakan itu menimbulkan konsekuensi besar bagi eksistensi Lain.'),
('INFO', 'Identitas asli Lain mulai terkuak: dirinya adalah program buatan yang diciptakan oleh Eiri untuk menjadi jembatan antara dunia nyata dan Wired. Berbagai "Lain" dari lapisan realitas yang berbeda saling berbicara, dan Lain mulai mempertanyakan ingatan serta masa lalunya yang sebenarnya.'),
('LANDSCAPE', 'Dunia di sekitar Lain perlahan membubarkan diri menjadi data. Keluarganya ternyata tidak pernah benar-benar ada, rumahnya menjadi kosong, dan kota yang ia tempati hanyalah lapisan informasi. Lain kini benar-benar berdiri di antara dua dunia yang tidak lagi memiliki batas.'),
('EGO', 'Di dalam Wired, Lain berhadapan dengan Masami Eiri yang mengaku sebagai dewa. Lain menyadari bahwa ia sendirilah yang sesungguhnya omnipresent, lalu menghapus Eiri. Pada akhirnya Lain memilih menjadi semua dan tidak menjadi siapa-siapa, mengamati dunia dari mana-mana, termasuk dari sisi Alice yang ia cintai.');

-- ---------- Database 2: admin (target dump sqlmap) ----------
CREATE DATABASE IF NOT EXISTS admin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE admin;

CREATE TABLE IF NOT EXISTS admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL,
  password_hash CHAR(32) NOT NULL,
  role VARCHAR(20) NOT NULL DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Password: "cyberia" (MD5) -> sengaja simple agar mudah dicrack peserta
INSERT INTO admins (username, password_hash, role) VALUES
('Lain', MD5('cyberia'), 'admin');

-- Catatan: flag TIDAK disimpan di database.
-- Dump sqlmap hanya memberikan hash MD5 -> peserta harus crack lalu login.
-- Flag 3 hanya tampil di dashboard setelah login berhasil.

-- Beri hak user aplikasi (lain) ke kedua database
GRANT ALL PRIVILEGES ON lain.* TO 'lain'@'%';
GRANT ALL PRIVILEGES ON admin.* TO 'lain'@'%';
FLUSH PRIVILEGES;
