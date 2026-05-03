# VastuVeda CRM — cPanel Deployment Guide

## Login Credentials (default)
| Employee ID | Password   | Role          |
|-------------|------------|---------------|
| EMP001      | Admin@1234 | Administrator |
| EMP002      | Admin@1234 | Manager       |
| EMP003      | Admin@1234 | Sales Exec    |

---

## Step 1 — Upload Files to cPanel

1. Download / clone the project and zip **only the inner files** (not the folder itself):
   - Select: `app/`, `config/`, `core/`, `database/`, `public/`, `scripts/`, `storage/`, `.htaccess`, `SETUP.md`
   - Do **NOT** include `vastuveda_crm_fixed/` or `vastuveda_crm_fixed.zip`

2. In **cPanel → File Manager**, navigate to your home directory (e.g. `/home/yourusername/`)

3. Create a folder named `vastuveda_crm` and upload the zip into it, then extract.

   Your server structure should look like:
   ```
   /home/yourusername/vastuveda_crm/
       app/
       config/
       core/
       database/
       public/        ← this is the web root
       scripts/
       storage/
       .htaccess
   ```

---

## Step 2 — Point Domain / Subdomain to `public/`

**Option A — Subdomain (recommended)**
1. cPanel → **Subdomains** → Create subdomain (e.g. `crm.yourdomain.com`)
2. Set **Document Root** to:
   ```
   /home/yourusername/vastuveda_crm/public
   ```

**Option B — Addon Domain**
1. cPanel → **Addon Domains** → Add domain
2. Set **Document Root** to:
   ```
   /home/yourusername/vastuveda_crm/public
   ```

**Option C — Main domain** (if this is your only site)
1. Move all files directly to `/home/yourusername/public_html/`
   - But put everything **except** the `public/` contents one level above `public_html/`
   - Move contents of `public/` INTO `public_html/`
   - Update `public/index.php` line 5: `define('ROOT', dirname(__DIR__));`
     → change to the actual path of your project root

> **Never** point the document root to the project root (`vastuveda_crm/`).
> It must point to `vastuveda_crm/public/`.

---

## Step 3 — Create MySQL Database

1. cPanel → **MySQL Databases**
2. **Create Database**: e.g. `vastuveda` → full name will be `yourusername_vastuveda`
3. **Create User**: e.g. `vvuser` + a strong password → full name: `yourusername_vvuser`
4. **Add User to Database**: select user + database → grant **ALL PRIVILEGES**

---

## Step 4 — Import Database

1. cPanel → **phpMyAdmin** → select your new database on the left
2. Click **Import** tab → choose file: `database/install.sql` → click **Go**
3. You should see all 12 tables created with sample data

---

## Step 5 — Edit Database Config

Open `config/database.php` in cPanel File Manager (or via FTP) and fill in:

```php
return [
    'host'   => 'localhost',
    'port'   => 3306,
    'dbname' => 'yourusername_vastuveda',  // exact name from Step 3
    'user'   => 'yourusername_vvuser',     // exact username from Step 3
    'pass'   => 'YourStrongPassword',      // password you set in Step 3
];
```

---

## Step 6 — Set Folder Permissions

In **cPanel → File Manager**, right-click each folder → **Change Permissions**:

| Folder                           | Permission |
|----------------------------------|------------|
| `storage/`                       | 755        |
| `storage/logs/`                  | 755        |
| `storage/uploads/`               | 755        |
| `storage/uploads/projects/`      | 755        |
| `storage/uploads/employees/`     | 755        |

Or via **SSH / Terminal**:
```bash
chmod -R 755 storage/
```

---

## Step 7 — Select PHP Version

1. cPanel → **MultiPHP Manager** (or **PHP Selector**)
2. Select your domain → choose **PHP 8.1** or higher (8.2 / 8.3 recommended)
3. Ensure these extensions are enabled:
   - `pdo_mysql`
   - `mbstring`
   - `json`
   - `fileinfo`
   - `gd`

---

## Step 8 — Verify mod_rewrite

The `.htaccess` file in `public/` handles URL routing. If you get 404 on all pages:

1. cPanel → **Apache Handlers** or check if `mod_rewrite` is enabled
2. If on LiteSpeed (most shared hosts): it's compatible — the `.htaccess` will work
3. Contact your host if rewrite rules aren't working

---

## Step 9 — Test the Site

Visit your domain/subdomain. You should see the VastuVeda login page.

Login with:
- **Employee ID**: `EMP001`
- **Password**: `Admin@1234`

---

## Face Recognition Attendance — Extra Setup

The face recognition feature needs model weight files downloaded into:
`public/assets/face-models/`

Run via SSH:
```bash
cd /home/yourusername/vastuveda_crm
php scripts/download_face_models.php
```

Or manually download the 7 files listed in `public/assets/face-models/README.md`
from the face-api.js GitHub repository and upload them via File Manager.

> Face recognition **only works on HTTPS** (browsers block webcam on plain HTTP).
> Use an SSL certificate (free via cPanel → Let's Encrypt / AutoSSL).

---

## Troubleshooting

| Symptom | Fix |
|---------|-----|
| White page / 500 error | Enable `'debug' => true` in `config/app.php` temporarily |
| 404 on all pages | Check document root points to `public/`, check mod_rewrite |
| "Service unavailable" | Check database credentials in `config/database.php` |
| Can't login | Re-import `database/install.sql` via phpMyAdmin |
| Upload fails | Check `storage/uploads/` permissions are 755 |
| Camera not working (face) | Must use HTTPS; install SSL via AutoSSL |
