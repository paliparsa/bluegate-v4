# BlueGate V4 Starter

BlueGate V4 foundation برای تبدیل BlueGate به یک Service Platform قابل نصب روی VPS. این repository هسته اولیه Catalog، Orders/Payments/Wallet، Node Manager، 3x-ui Provider، Services، Subscription Gateway، Queueها، Health API و ابزارهای Deploy/Update را فراهم می‌کند. UI کامل، OTP، درگاه‌های واقعی، Provisioning نهایی و Admin UI در فازهای بعد روی همین هسته توسعه داده می‌شوند.

## Stack

- Laravel 12
- PHP 8.3+
- PostgreSQL
- Redis
- Nginx + PHP-FPM
- Supervisor
- 3x-ui به عنوان Provisioning Provider، نه Source of Truth
- BlueGate Subscription Gateway مستقل

## VPS پیشنهادی

برای نصب اتومات، **Ubuntu 24.04 LTS** پیشنهاد و هدف اصلی Installer است.

حداقل پیشنهادی برای شروع:

- 2 vCPU
- 2 GB RAM
- 25 GB SSD
- Public IPv4
- یک Domain/Subdomain که A Record آن به VPS اشاره کند

برای Production بهتر است حداقل 4 GB RAM داشته باشید و VPN Nodeها را از Web/API Server جدا نگه دارید.

---

# نصب مستقیم از GitHub

فرض این README این است که repository در آدرس زیر قرار گرفته است:

```text
https://github.com/paliparsa/bluegate-v4
```

اگر نام repository را تغییر دادید، مقدار `REPO_URL` در `install.sh` و مثال‌های README را نیز تغییر دهید.

## روش 1 — نصب تعاملی با یک دستور

روی VPS تازه:

```bash
curl -fsSL https://raw.githubusercontent.com/paliparsa/bluegate-v4/main/install.sh | sudo bash
```

Installer دامنه و HTTPS را از شما می‌پرسد و ادامه نصب را خودش انجام می‌دهد.

## روش 2 — نصب کامل بدون سؤال

```bash
curl -fsSL https://raw.githubusercontent.com/paliparsa/bluegate-v4/main/install.sh | \
  sudo env DOMAIN=app.bluegate.ir INSTALL_SSL=1 LE_EMAIL=admin@example.com bash
```

اگر SSL را فعلاً نمی‌خواهید:

```bash
curl -fsSL https://raw.githubusercontent.com/paliparsa/bluegate-v4/main/install.sh | \
  sudo env DOMAIN=app.bluegate.ir INSTALL_SSL=0 bash
```

## نصب از Branch یا Repository دیگر

```bash
curl -fsSL https://raw.githubusercontent.com/paliparsa/bluegate-v4/main/install.sh | \
  sudo env \
  REPO_URL=https://github.com/paliparsa/bluegate-v4.git \
  BRANCH=dev \
  DOMAIN=dev.bluegate.ir \
  INSTALL_SSL=0 \
  bash
```

---

# Installer چه کار می‌کند؟

به طور خلاصه:

1. Git و ابزارهای bootstrap را نصب می‌کند.
2. Repository را در `/opt/bluegate/source` clone می‌کند.
3. Nginx, PHP-FPM, PostgreSQL, Redis, Supervisor و extensionهای لازم را نصب می‌کند.
4. Composer را با checksum verification نصب می‌کند.
5. PostgreSQL user/database را با password تصادفی می‌سازد.
6. یک Laravel 12 clean skeleton ایجاد می‌کند و فایل‌های BlueGate را روی آن overlay می‌کند.
7. dependencyهای Production را نصب می‌کند.
8. `.env`، `APP_KEY` و `BLUEGATE_TOKEN_PEPPER` را می‌سازد.
9. Migrationها را اجرا می‌کند.
10. Nginx را روی `/var/www/bluegate/public` تنظیم می‌کند.
11. Queue Workerها را با Supervisor فعال می‌کند.
12. Laravel Scheduler را از طریق cron فعال می‌کند.
13. در صورت درخواست، Certbot/Let's Encrypt را راه‌اندازی می‌کند.
14. Health endpoint را روی localhost تست می‌کند.
15. اطلاعات Deploy را در `/etc/bluegate/install.env` ذخیره می‌کند تا Updateهای بعدی امکان‌پذیر باشند.

---

# مسیرهای مهم روی VPS

```text
/opt/bluegate/source          Git repository clone
/var/www/bluegate             Laravel production app
/var/www/bluegate/.env        Production secrets/config
/etc/nginx/sites-available/bluegate
/etc/supervisor/conf.d/bluegate-worker.conf
/etc/cron.d/bluegate
/etc/bluegate/install.env     Deploy metadata
```

`.env` و credentialهای Production نباید داخل GitHub commit شوند.

---

# تست بعد از نصب

```bash
curl https://app.bluegate.ir/api/v1/health
```

یا از داخل VPS:

```bash
curl -H "Host: app.bluegate.ir" http://127.0.0.1/api/v1/health
```

خروجی مورد انتظار مشابه این است:

```json
{
  "ok": true,
  "service": "bluegate-api",
  "checks": {
    "database": true,
    "redis": true
  }
}
```

---

# آپدیت پروژه از GitHub

بعد از هر Push/Release روی همان branch نصب‌شده:

```bash
sudo bash /opt/bluegate/source/deploy/scripts/update-vps.sh
```

Updater به ترتیب:

- Maintenance Mode را فعال می‌کند.
- آخرین commit را از GitHub می‌گیرد.
- فایل‌های BlueGate را روی Production sync می‌کند ولی `.env` را دست نمی‌زند.
- `composer install --no-dev` اجرا می‌کند.
- Migrationهای جدید را اجرا می‌کند.
- Cacheها را rebuild می‌کند.
- Queue Workerها را restart می‌کند.
- سایت را از Maintenance Mode خارج می‌کند.

برای دیدن commit نصب‌شده:

```bash
git -C /opt/bluegate/source rev-parse --short HEAD
```

---

# نصب مجدد

اجرای دوباره installer روی همان VPS، `.env` فعلی را حفظ می‌کند و Database را پاک نمی‌کند:

```bash
curl -fsSL https://raw.githubusercontent.com/paliparsa/bluegate-v4/main/install.sh | sudo bash
```

با این حال برای سرور Production، برای Deployهای عادی از `update-vps.sh` استفاده کنید نه Installer کامل.

---

# تنظیمات سفارشی Installer

می‌توانید Environment Variableهای زیر را قبل از `bash` بدهید:

```text
DOMAIN           Required domain
INSTALL_SSL      1 / 0 / ask
LE_EMAIL         Let's Encrypt email
REPO_URL         Git repository URL
BRANCH           Git branch; default main
APP_DIR          default /var/www/bluegate
SOURCE_DIR       default /opt/bluegate/source
DB_NAME          default bluegate
DB_USER          default bluegate
DB_PASS          optional custom PostgreSQL password
```

مثال:

```bash
curl -fsSL https://raw.githubusercontent.com/paliparsa/bluegate-v4/main/install.sh | \
sudo env \
DOMAIN=panel.example.com \
INSTALL_SSL=1 \
LE_EMAIL=ops@example.com \
DB_NAME=bluegate_prod \
DB_USER=bluegate_prod \
bash
```

---

# SSL دستی

اگر هنگام نصب SSL را رد کردید، ابتدا DNS را به IP VPS متصل کنید، سپس:

```bash
sudo apt update
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d app.bluegate.ir
```

---

# Queueها

Supervisor Queueهای زیر را مصرف می‌کند:

```text
payments
provisioning
node-sync
usage
notifications
default
```

وضعیت:

```bash
sudo supervisorctl status
```

Restart:

```bash
sudo supervisorctl restart 'bluegate-worker:*'
```

Logs:

```bash
tail -f /var/www/bluegate/storage/logs/worker.log
```

---

# Laravel Logs

```bash
tail -f /var/www/bluegate/storage/logs/laravel.log
```

Nginx:

```bash
sudo tail -f /var/log/nginx/error.log
```

---

# PostgreSQL

اطلاعات اتصال Production داخل فایل زیر قرار دارد:

```text
/var/www/bluegate/.env
```

پسورد Database در خروجی عمومی Installer چاپ نمی‌شود.

برای باز کردن PostgreSQL:

```bash
sudo -u postgres psql
```

---

# افزودن Node 3x-ui

`nodes` شامل panel URL و credentials است. Credentials با encrypted cast در Laravel ذخیره می‌شوند و نباید plaintext در DB نگهداری شوند.

نمونه منطقی:

```json
{
  "username": "admin",
  "password": "strong-password"
}
```

`ThreeXUIProvider` مسئول ارتباط با 3x-ui است و متدهای اصلی Provider را از Business Logic جدا می‌کند. بنابراین اگر endpointهای نسخه 3x-ui تغییر کنند، فقط Provider باید اصلاح شود.

---

# Subscription Gateway

کاربر لینک مستقیم 3x-ui دریافت نمی‌کند. BlueGate URL خودش را ارائه می‌کند:

```text
https://sub.example.com/s/CLIENT_TOKEN
```

Raw token نباید در DB ذخیره شود؛ BlueGate hash آن را همراه pepper نگه می‌دارد.

Gateway قبل از برگرداندن Config وضعیت سرویس، انقضا، حجم و Endpointهای فعال را بررسی می‌کند.

---

# ساختار مهم Repository

```text
install.sh

deploy/
├── nginx/
│   └── bluegate.conf
├── supervisor/
│   └── bluegate-worker.conf
└── scripts/
    ├── install-vps.sh
    └── update-vps.sh

app/Domain/
database/migrations/
routes/
docs/
```

---

# قبل از Push به GitHub

مطمئن شوید این موارد هرگز Commit نشوند:

```text
.env
vendor/
node_modules/
Production backups
Database dumps
3x-ui credentials
API keys
Private keys
Subscription raw tokens
```

`.gitignore` مناسب داخل Repository قرار داده شده است.

اولین Push پیشنهادی:

```bash
git init
git add .
git commit -m "Initial BlueGate V4 foundation"
git branch -M main
git remote add origin https://github.com/paliparsa/bluegate-v4.git
git push -u origin main
```

بعد از Push، روی VPS فقط Installer یک‌خطی را اجرا کنید.

---

# Production Security Checklist

قبل از فروش واقعی:

- Admin 2FA
- Firewall
- PostgreSQL/Redis فقط روی localhost/private network
- HTTPS
- Off-site Backup
- Audit Logs
- OTP/Login/Subscription Rate Limit
- عدم نمایش 3x-ui panel به مشتری
- عدم ذخیره raw subscription token
- Secret rotation policy
- Separate VPN Nodes from application server

---

# قدم بعدی توسعه

روی همین Repository، ترتیب بعدی پیشنهادی:

1. Authentication + OTP + Roles
2. Pricing Engine
3. Atomic Wallet + Checkout
4. Payment Adapters
5. `ProvisionService` Job واقعی
6. 3x-ui Node Sync / Health
7. My Services API/UI
8. Renewal / Add Traffic / Reset Subscription / Change Location
9. Admin Panel
10. Telegram Bot / Tickets / Referral / Reseller

## Troubleshooting: SQLite driver / stale composer.lock during install

If an older installer stops with errors similar to:

```text
could not find driver (Connection: sqlite ...)
Required package "laravel/sanctum" is not present in the lock file.
Required package "predis/predis" is not present in the lock file.
```

Update the repository to the latest installer and run the one-line installer again. The current installer creates the Laravel base with Composer scripts disabled, overlays BlueGate first, resolves BlueGate dependencies, writes the PostgreSQL `.env`, and only then runs package discovery and migrations.

Do **not** fix this by enabling SQLite in production; BlueGate uses PostgreSQL.

---

# Phase 2 UI / Dashboard

این نسخه علاوه بر Foundation فنی، UI قابل استفاده هم دارد:

- `/` صفحه اصلی BlueGate
- `/login` و `/register`
- `/app` داشبورد کاربر
- `/app/services` سرویس‌های من
- `/app/buy` خرید و ساخت سفارش
- `/app/wallet` کیف پول
- `/app/orders` سفارش‌ها
- `/admin` پنل مدیریت پایه
- `/admin/products` مشاهده محصولات و پلن‌ها
- `/admin/nodes` مشاهده Nodeها

بعد از Update، Catalog اولیه BluePing به شکل idempotent Seed می‌شود و داده قبلی پاک نمی‌شود.

## ساخت اولین ادمین

ابتدا در سایت ثبت‌نام کنید و سپس روی VPS اجرا کنید:

```bash
cd /var/www/bluegate
sudo -u www-data php artisan bluegate:make-admin YOUR_EMAIL
```

بعد از خروج و ورود مجدد، گزینه «پنل مدیریت» در داشبورد نمایش داده می‌شود.

> در Phase 2 ساخت Order واقعی است، اما درگاه پرداخت و Provisioning خودکار 3x-ui هنوز عمداً فعال نشده‌اند و در فاز بعدی تکمیل می‌شوند.
