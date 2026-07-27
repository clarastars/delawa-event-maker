# Delawa Event Maker

Event invitation and voucher management platform for **Delawa** (ديلاوة), built on Laravel.

Manage events, upload vouchers, invite contacts via OTP-verified phone numbers, and generate closure reports — all branded for Delawa.

## Brand

- **Name:** Delawa / ديلاوة
- **Primary:** `#7d4651`
- **Primary dark:** `#4e2e36`
- **Accent:** `#ffd700`
- **Fonts:** Montserrat (Latin), Cairo (Arabic)

Brand assets are sourced from [delawa.adv-line.sa](https://delawa.adv-line.sa/).

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

For local development:

```bash
composer dev
```

## Tests

```bash
composer test
```

## Admin

Seed the default admin user:

```bash
php artisan db:seed
```

Then sign in at `/admin/login`.
