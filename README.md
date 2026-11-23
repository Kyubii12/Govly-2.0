# GovLy 2.0 — Barangay Sta. Cruz (PHP · Hostinger)

PHP + MySQL remake of the live portal ([stacruzbacnotan.vercel.app](https://stacruzbacnotan.vercel.app/)) for Hostinger.

**Brand:** GovLy 2.0  
**Auth:** Email OTP only (no SMS)  
**Alerts:** Email when admin marks a certificate **Completed** (or Rejected)

---

## What’s included (copied from finale)

| Area | Features |
|------|----------|
| Home | Hero, services, officials, news, emergency, map, contact (same layout as Vercel) |
| Auth | Register → email OTP → admin approve · Login Resident/Admin tabs (email) |
| Resident | Request certificates, message barangay |
| Admin | Dashboard, Registrations, Registered, Census, Requests, Activity, Certificates/print+QR, Announcements, Officials, Prices & Contacts, Admins, Messages |
| Extra | Emergency page, verify QR `/verify/{controlNo}` |

---

## Deploy with GitHub → Hostinger

### A. First-time Hostinger setup

1. Upload once (or use FTP below) into `public_html`
2. phpMyAdmin → Import `sql/schema.sql`
3. Copy `app/config.example.php` → `app/config.php` on the server and fill MySQL + SMTP
4. Create Hostinger email (e.g. `noreply@yourdomain.com`) for OTP + completion emails
5. Writable folders: `uploads/ids`, `uploads/selfies`, `uploads/officials`

Default Super Admin after import:

- Email: `admin@stacruzbacnotan.com`
- Password: `Admin@12345`

### B. Connect GitHub (recommended)

1. Create a GitHub repo and push this project  
2. Hostinger hPanel → **FTP Accounts** → note host, user, password, path (`public_html/`)  
3. GitHub → **Settings → Secrets and variables → Actions** → add:

| Secret | Example |
|--------|---------|
| `FTP_SERVER` | `ftp.yourdomain.com` |
| `FTP_USERNAME` | your FTP user |
| `FTP_PASSWORD` | your FTP password |
| `FTP_SERVER_DIR` | `/public_html/` |

4. Push to `main` (or run **Actions → Deploy to Hostinger → Run workflow**)  
5. Keep `app/config.php` **only on the server** (it is excluded from deploy so secrets stay safe)

### C. Or Hostinger “Git” deploy

hPanel → **Advanced → Git** → connect your GitHub repo → deploy into `public_html`.  
Still create `app/config.php` on the server after first pull.

---

## Email-only flow

```
Register (email) → OTP email → Admin approve
  → Login with email → Request certificate
  → Admin prints / sets Completed → Resident gets email
```

Phone is optional contact only. **No SMS OTP. No SMS certificate alerts.**

---

## Local / config

Edit `app/config.php`:

```php
'db_name' => '…',
'db_user' => '…',
'db_pass' => '…',
'mail' => [
  'username' => 'noreply@yourdomain.com',
  'password' => '…',
  'from'     => 'noreply@yourdomain.com',
],
```

SMTP: `smtp.hostinger.com` port `465` SSL.
