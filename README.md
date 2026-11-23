# GovLy 2.0 — Barangay Sta. Cruz

PHP + MySQL portal for Hostinger.  
Live design match of the Vercel finale · **Email OTP + certificate emails only (no SMS)**

Repo: https://github.com/Kyubii12/Govly-2.0

---

## Auto-update: GitHub → Hostinger

Every `git push` to `main` deploys via GitHub Actions FTP.

### One-time secrets (required)

1. hPanel → **FTP Accounts** → create/copy:
   - FTP server / host  
   - Username  
   - Password  
2. GitHub → **Govly-2.0** → **Settings** → **Secrets and variables** → **Actions** → **New repository secret**:

| Name | Value |
|------|--------|
| `FTP_SERVER` | FTP hostname from Hostinger |
| `FTP_USERNAME` | FTP username |
| `FTP_PASSWORD` | FTP password |
| `FTP_SERVER_DIR` | `/public_html/` |

3. Push once → Actions tab should show **Deploy to Hostinger** = green.

`app/config.php` is **never** deployed (passwords stay on the server only).

### First-time server config (once)

1. Upload `app/config.php` manually (or use `setup.php` once), with MySQL + SMTP filled.  
2. phpMyAdmin → import `sql/schema.sql` into your database.  
3. PHP version **8.2** or **8.3**.  
4. Delete `setup.php` after success.

Default admin: `admin@stacruzbacnotan.com` / `Admin@12345`

---

## Local push command

```powershell
cd C:\Users\Arc\Desktop\GovLy2.0
git add .
git commit -m "Update GovLy 2.0"
git push origin main
```

---

## Email-only flow

Register → OTP email → Admin approve → Request certificate → Admin Completes → Email notification (no SMS)
