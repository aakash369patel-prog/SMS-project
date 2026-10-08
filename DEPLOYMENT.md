# Production deployment

## Environment

Copy `backend/.env.example` to `backend/.env` and set:

| Variable | Purpose |
|----------|---------|
| `APP_ENV=production` | Disable debug |
| `APP_DEBUG=false` | Never true in production |
| `APP_URL=https://your-domain` | Public API URL |
| `FORCE_HTTPS=true` | Force HTTPS URLs when behind TLS terminator |
| `FRONTEND_URL` | React app origin (CORS / Sanctum) |
| `SANCTUM_STATEFUL_DOMAINS` | Same host(s) as frontend |
| `CORS_ALLOWED_ORIGINS` | Comma-separated frontend URLs |
| `QUEUE_CONNECTION=database` | Queue emails (run worker below) |
| `MAIL_*` | SMTP for real mail |

Frontend build (`frontend/.env.production` or env at build time):

```env
VITE_API_URL=https://your-domain/api/v1
```

## Backend

```bash
cd backend
composer install --no-dev --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force   # optional demo data
php artisan storage:link        # required for uploaded photos/documents
php artisan config:cache
php artisan route:cache
```

### Queue worker (notification emails)

Emails use `SchoolAlertMail` on the queue when `QUEUE_CONNECTION` is not `sync`:

```bash
php artisan queue:work --tries=3
```

Use Supervisor/systemd in production to keep the worker running.

### HTTPS

Terminate TLS at nginx/Apache/Caddy and set `FORCE_HTTPS=true`. Set trusted proxies in Laravel if needed (`TrustProxies` middleware).

### Backups

```bash
php artisan school:backup              # manifest under storage/backups
php artisan school:backup --storage    # also zip public uploads
```

Schedule daily DB dumps with your host tools (e.g. `mysqldump`) using credentials from `.env`.

## Frontend

```bash
cd frontend
npm ci
npm run build
```

Serve `frontend/dist` as static files (nginx, S3+CloudFront, etc.). Point SPA fallback to `index.html`.

Preview locally: `npm run preview`

## File uploads

Uploads are stored on the `public` disk (`storage/app/public`). The `storage:link` command exposes them at `/storage/...`. Ensure the web server can read `public/storage`.
