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


---

## Phase 3 — Commerce + Provisioning

این نسخه اولین مسیر عملیاتی end-to-end را اضافه می‌کند:

- پرداخت اتمیک سفارش با Wallet Ledger
- قفل ردیف کیف پول برای جلوگیری از double-spend
- اتصال Node Manager به 3x-ui
- Health Check و Sync Inbound از پنل
- انتخاب خودکار نود سالم
- Provisioning idempotent برای سفارش پرداخت‌شده
- ساخت Client در 3x-ui
- ساخت Service و Service Endpoint
- Subscription Gateway اختصاصی BlueGate
- تولید URI برای VLESS / VMess / Trojan (MVP)
- Token اشتراک به‌صورت encrypted در دیتابیس
- Usage Sync هر ۵ دقیقه
- شارژ دستی کیف پول از Admin برای تست یا پرداخت تاییدشده
- Updater جدید: launcher ابتدا خودش را از Git تازه می‌کند و سپس deploy اتمیک/rollback را اجرا می‌کند

### راه‌اندازی اولین Node

در `/admin/nodes` اطلاعات پنل 3x-ui را وارد کن. `VPN Public Host/IP` باید آدرس عمومی‌ای باشد که کلاینت‌ها به آن وصل می‌شوند، نه لزوماً hostname پنل. سپس Health و Sync Inbounds را بزن.

### تست خرید end-to-end

1. از `/admin/wallets` کیف پول کاربر تست را شارژ کن.
2. کاربر از `/app/buy` سفارش بسازد.
3. در `/app/orders` روی «پرداخت با کیف پول» بزند.
4. BlueGate نود سالم را انتخاب، Client را در 3x-ui ایجاد و Service را فعال می‌کند.
5. لینک BlueGate Subscription در `/app/services/{id}` نمایش داده می‌شود.

> API endpointهای 3x-ui بین بعضی نسخه‌ها/forkها تفاوت دارند. Provider در یک Adapter مستقل نگه داشته شده تا در صورت تفاوت نسخه فقط همان فایل اصلاح شود.


---

## Phase 4 — Payments + Service Lifecycle

Phase 4 مسیر فروش را از «پرداخت کیف پول» به چرخه کامل عملیات سرویس توسعه می‌دهد.

### قابلیت‌ها

- Zarinpal payment driver (Request / Callback / Verify)
- Payment idempotency + callback token
- Payment Center در `/admin/payments`
- Provisioning خودکار بعد از Verify موفق
- Telegram notification برای پرداخت/تحویل/خطای provisioning
- QR Code برای لینک Subscription
- تمدید سرویس با Wallet
- خرید حجم 10 / 25 / 50 / 100 GB با Wallet
- تغییر لوکیشن و مهاجرت Client بین Nodeها
- حذف Client قدیمی بعد از مهاجرت موفق
- Refund خودکار Wallet در صورت fail شدن عملیات تمدید/حجم/لوکیشن
- `service_operations` برای audit/idempotency
- ثبت current location هنگام Provision اولیه

### تنظیم زرین‌پال

بعد از Deploy، فایل `/var/www/bluegate/.env` را باز کن:

```env
ZARINPAL_MERCHANT_ID=YOUR_MERCHANT_ID
ZARINPAL_AMOUNT_MULTIPLIER=10
```

در نسخه فعلی قیمت‌های Catalog با ظاهر «تومان» ذخیره/نمایش داده شده‌اند، بنابراین مقدار پیش‌فرض 10 مبلغ را برای Gateway به ریال تبدیل می‌کند. اگر دیتابیس خودت را از ابتدا با ریال نگهداری می‌کنی، این مقدار را `1` کن.

سپس:

```bash
cd /var/www/bluegate
sudo -u www-data php artisan config:clear
sudo -u www-data php artisan config:cache
```

### Telegram

اختیاری:

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_ADMIN_CHAT_ID=
```

اگر `users.telegram_id` برای کاربر موجود باشد، Notification برای خود کاربر هم ارسال می‌شود؛ در غیر این صورت فقط Admin Chat (در صورت تنظیم) پیام می‌گیرد.

### قیمت عملیات

```env
BLUEGATE_TRAFFIC_PRICE_PER_GB=5000
BLUEGATE_LOCATION_CHANGE_PRICE=0
```

این مقادیر فعلاً با واحد Catalog فعلی BlueGate محاسبه می‌شوند.

### تست پیشنهادی Phase 4

1. Node واقعی را Health + Sync کن.
2. یک User تست و Wallet دارای موجودی داشته باش.
3. سفارش را یک‌بار با Wallet تست کن.
4. در My Services: QR، Renew، Add Traffic و Change Location را تست کن.
5. Merchant ID را تنظیم کن و یک پرداخت کم‌مبلغ آنلاین تست کن.
6. `/admin/payments` را برای authority/ref id/status بررسی کن.

> Endpointهای 3x-ui در بعضی fork/versionها متفاوت‌اند. Update/Delete Client در Adapter مستقل قرار دارد تا در صورت تفاوت نسخه پنل فقط Provider تغییر کند.


### Phase 4.2 hotfix

رفع Rollback کاذب با exit code 141 در تشخیص PHP-FPM. علت، ترکیب `set -o pipefail` با `awk ... exit` بود که باعث SIGPIPE در `systemctl` می‌شد. تشخیص سرویس PHP-FPM اکنون بدون early-exit انجام می‌شود و fallback برای php8.4/8.3/8.2/8.1 دارد.


---

## Phase 5 — Growth + Support + Catalog

Phase 5 لایه فروش و پشتیبانی BlueGate را کامل‌تر می‌کند:

- Coupon Engine درصدی/ثابت
- محدودیت تعداد استفاده، هر کاربر، حداقل سفارش، تاریخ شروع/پایان و سقف تخفیف
- Referral code برای هر کاربر
- Referral link روی Register
- اعتبار خودکار معرف برای خریدهای واجد شرایط
- پیش‌فرض: 10% برای 3 خرید اول کاربر معرفی‌شده
- Trial واقعی با Provisioning روی 3x-ui
- انتخاب Trial Plan از Admin با `trial_enabled`
- محدودیت یک Trial برای User و محافظت IP/Fingerprint
- Ticket Center کاربر
- Support Desk ادمین + پاسخ و بستن Ticket
- Notification Center داخل پنل
- Telegram account linking با deep-link یک‌بارمصرف
- Telegram Webhook hook
- Catalog CRUD برای Product / Plan / Price / Trial
- Coupon Center در Admin

### Referral

مقادیر پیش‌فرض:

```env
BLUEGATE_REFERRAL_RATE=10
BLUEGATE_REFERRAL_ELIGIBLE_ORDERS=3
```

Commission بعد از پرداخت و Provisioning موفق به Wallet معرف Credit می‌شود و `order_id` unique است، بنابراین دوباره پرداخت نمی‌شود.

### Trial

در `/admin/products` فقط برای پلنی که می‌خواهی به‌عنوان تست ارائه شود `Trial` را فعال کن. بهتر است این پلن حجم و مدت کوتاه داشته باشد.

```env
BLUEGATE_TRIAL_BLOCK_REUSED_IP=true
BLUEGATE_TRIAL_BLOCK_REUSED_FINGERPRINT=true
```

### Telegram account linking

`.env`:

```env
TELEGRAM_BOT_TOKEN=
TELEGRAM_BOT_USERNAME=
TELEGRAM_ADMIN_CHAT_ID=
TELEGRAM_WEBHOOK_SECRET=A_LONG_RANDOM_SECRET
```

بعد از config cache، Webhook بات را روی این آدرس ثبت کن:

```text
https://YOUR_DOMAIN/telegram/webhook/YOUR_TELEGRAM_WEBHOOK_SECRET
```

کاربر از بخش «دعوت دوستان» لینک اتصال موقت می‌سازد؛ لینک 20 دقیقه اعتبار دارد و `/start TOKEN` شناسه Telegram را به همان User متصل می‌کند.

### صفحات جدید

User:
- `/app/referral`
- `/app/tickets`
- `/app/notifications`
- Trial action در `/app/buy`

Admin:
- `/admin/products`
- `/admin/coupons`
- `/admin/tickets`

### نکته Deployment

Phase 5 بر پایه Phase 4.2 ساخته شده و Hotfix تشخیص PHP-FPM بدون SIGPIPE/exit 141 را حفظ می‌کند.
