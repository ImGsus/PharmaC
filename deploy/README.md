# Running PharmaC on the internet — free / student-friendly options

Analysis of this repo (`Laravel 8.65` + MySQL, dev box = PHP 8.2.12 + MySQL on `127.0.0.1:3307`)
and what has to change for the app to be reachable from a phone.

## 1. What this app actually needs (measured, not guessed)

| Requirement | Where it comes from | Consequence for hosting |
|---|---|---|
| **Real MySQL/MariaDB** (not Postgres) | `DB::select('SHOW TABLES')`, `DB::statement('SET FOREIGN_KEY_CHECKS=0/1')` in `app/Http/Controllers/Admin/BackupController.php`; `->after()` column modifiers in ~8 migrations | Free **Postgres** tiers (Neon, Supabase) are **out** without a rewrite. TiDB's free tier is MySQL-wire-compatible but rejects `SET FOREIGN_KEY_CHECKS` → the backup-restore screen breaks. |
| **Persistent disk for uploads** | `App\Services\OrganizedFileStorage` writes with `$file->move()` into `public/storage/...`; `config/filesystems.php` already falls back to `public_path('storage')` when `public/storage` is a real folder (so **no symlink needed**) | Serverless (Vercel/Workers) erases uploads between requests → needs an S3/R2 rewrite. A VM or shared host needs nothing. |
| **Persistent disk for backups** | `BackupController::create()` builds `storage/app/backups/pharmacy-bundle-*.zip` (PHP `ZipArchive`, full-table JSON dump, no `mysqldump`), import capped at 512 MB | Needs a writable disk + `upload_max_filesize/post_max_size ≈ 128M`, `max_execution_time ≈ 300`, `memory_limit ≈ 512M`. Shared hosts usually cap all three. |
| **A cron/scheduler** | `app/Console/Kernel.php`: `products:mark-expired` daily | Needs real cron (VM) or a web-cron hitting a token route (`shared-hosting/webcron-route.php`). |
| **Session / cache / settings on disk** | `SESSION_DRIVER=file`, `CACHE_DRIVER=file`, `qcod/laravel-app-settings` caches to the file store | Fine on VM + shared host. Needs the `database` driver on serverless — and this repo has **no sessions/cache migration** yet. |
| **Static assets already built** | `public/mix-manifest.json`, `public/css`, `public/js`, `public/assets` are committed; no blade calls `mix()` | **No npm/node needed on the server.** |
| **`vendor/` is not in git** | `git ls-files vendor` = 0 (9,536 vendor files on disk) | Must run `composer install` on the server, or upload `vendor/` by FTP/zip for hosts without shell. |
| **Laravel 8 is EOL, officially PHP ≤ 8.1** | `composer.json`: `php: ^7.3\|^8.0\|^8.1`, runs fine on 8.2.12 locally | Pin the server to **PHP 8.2** (proven on this code) — avoid a host that only offers 8.3+ with no choice. |

## 2. Verdict (September 2026 reality)

| Option | Cost | Fits this app? | Notes |
|---|---|---|---|
| **A. Oracle Cloud Always Free VM** (recommended) | $0 | ✅ **as-is, zero code changes** | 2 OCPU / 12 GB ARM Ampere + 200 GB disk + 10 TB egress (Oracle halved the ARM free allowance in July 2026; docs now state "equivalent to 2 OCPUs and 12 GB"). Full control: MySQL 8, cron, `storage/`, HTTPS. ~45–90 min setup. |
| **B. Free PHP+MySQL shared host** (InfinityFree / Byet.host) | $0 | ⚠️ works, 3 features degraded | ~5 GB disk, MySQL included, selectable PHP, free SSL, custom domain, **FTP only** (no SSH/Composer/cron), fair-use ≈50k hits/day, small DB cap. Sessions, uploads and migrations work; the 512 MB backup import and the daily expiry job need workarounds; doc root needs the `htdocs` bootstrap from this folder. |
| **C. Vercel + external DB/storage** | $0 | ❌ **not recommended for this app** | Vercel container functions are **stateless, scale to zero, no volumes** — their docs say state must live in a backing service. You would need: a Postgres port *or* TiDB (which rejects `SET FOREIGN_KEY_CHECKS`), an R2/S3 disk (**`league/flysystem-aws-s3-v3` is not installed**), `database` sessions/cache (+ new migrations), uploads rewritten off-disk, and no `ZipArchive` writes. Hobby is non-commercial-only with 4 CPU-hrs/month. Revisit after a Laravel 11/12 upgrade. |
| **D. Cheap VPS / student credits** | €3–4/mo (Hetzner CAX11 ARM) or $0 ≈12 months with the GitHub Student Pack's **Azure for Students** credit ($100, no card) | ✅ identical to A | DigitalOcean no longer ships in the Student Pack; Fly.io has no free tier for new accounts; Render's free web service = 512 MB, sleeps, **ephemeral disk** (uploads lost); Koyeb free = 1 service, 512 MB, 1 GB egress/mo. |
| **E. Just show it from your laptop** | $0 | ✅ for a 10-min demo | `cloudflared tunnel --url http://localhost:8000` (free quick tunnel, no account, gives an HTTPS `*.trycloudflare.com` URL that opens on a phone), or the ngrok config you already keep in `env ngrok.txt`. For **your devices only**: Tailscale (free, 100 devices) — phone reaches the PC with zero public exposure. |

**Recommendation:** use **E** today for the demo, build **A** as the real deployment, keep **B** as the
zero-effort fallback if Oracle's free capacity ("out of host capacity") fights you. Don't spend effort on C.

## 3. Free domain + HTTPS recipe (for options A / D)

1. `yourname.duckdns.org` → point it at the VM's public IP (free, instant, refresh with a 1-line cron).
2. Optional, nicer: Cloudflare **free** plan — add your own domain, create a `CNAME`
   (e.g. `pharmac.example.dev`) to `yourname.duckdns.org`, proxy it. Free CDN + SSL + bot filtering.
   Behind Cloudflare, set the proxies in `app/Http/Middleware/TrustProxies.php` to `'*'`
   (otherwise audit logs record Cloudflare IPs instead of the cashier's real IP).
3. Let's Encrypt via `certbot` (the setup script runs it). Also check the GitHub Student Pack page for a
   rotating free-domain partner (historically Name.com / `.tech`) if you want a real TLD for free.

## 4. Files in this folder

| File | Use |
|---|---|
| `oracle/setup.sh` | One-shot provisioning of an Ubuntu 22.04 VM: PHP 8.2 + nginx + MySQL 8 + Composer + cron + certbot |
| `oracle/nginx-pharmac.conf` | nginx vhost (doc root = `public/`, upload caching, 160 MB body limit) |
| `oracle/env.production.example` | `.env` template for the VM |
| `oracle/README.md` | Step-by-step: Oracle sign-up → VM → security lists → run setup → restore data |
| `shared-hosting/htdocs-index.php` | `htdocs/index.php` bootstrap for hosts that won't point the doc root at `public/` |
| `shared-hosting/webcron-route.php` | token-protected route so a free web-cron can run `schedule:run` (no shell needed) |
| `shared-hosting/env.example` | `.env` template for shared hosting |
| `shared-hosting/README.md` | Upload layout, what works / what is degraded, how to schedule the daily job |
| `vercel/*` | Reference only for the (not recommended) serverless path + the refactor checklist |

## 5. Moving your existing data off this PC

```powershell
# 1. dump (dev MySQL is on port 3307 here)
mysqldump -h 127.0.0.1 -P 3307 -u root --single-transaction --routines --triggers pharmacy > pharmac.sql

# 2. zip the uploads exactly as the app's own backup feature stores them
Compress-Archive -Path public\storage\* -DestinationPath uploads.zip

# 3. on the server
mysql -u pharmac -p pharmacy < pharmac.sql
unzip uploads.zip -d /var/www/pharmac/public/storage
chown -R www-data:www-data /var/www/pharmac/public/storage
```

Keep `APP_KEY` from `.env` — `two_factor_secret`, `remember_token` and anything `Crypt::encrypt()`ed is
unreadable if the key changes. Copy the value across instead of running `key:generate` on the server.

## 6. Gotchas that bite this specific app

- **Oracle's "out of host capacity"** on the ARM shape: try another availability domain or another
  always-free region bucket; the 2 × AMD micro (1 GB) shapes also work with swap (the script adds 2 GB).
- **Two firewalls on Oracle**: the VCN security list (ingress 80/443) *and* the instance's own
  firewall. `certbot` fails silently-ish when only one is open.
- **MySQL 8 → MariaDB import** fails on `utf8mb4_0900_ai_ci`; the script installs MySQL 8 so the dump
  restores unchanged. If you switch to MariaDB, run
  `sed -i 's/utf8mb4_0900_ai_ci/utf8mb4_unicode_ci/g' pharmac.sql` first.
- **`php artisan storage:link` is not what serves uploads here** — `public/storage` is a real directory
  and `config/filesystems.php` picks it up; don't delete it, don't replace it with a symlink.
- **Free tier inactivity**: Render/Oracle can sleep or reclaim idle always-free instances. A
  uptime-kuma/UptimeRobot ping every 5 min keeps Render awake; Oracle mails you before reclaiming.
- **This is a pharmacy system with patient-adjacent data** (prescriptions, sales, cashier accounts).
  Free tiers are fine for a demo/thesis, but for real dispensing: `APP_DEBUG=false`, HTTPS only,
  daily off-site copy of `storage/app/backups`, and no `APP_DEBUG=true` on a public host.

