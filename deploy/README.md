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
