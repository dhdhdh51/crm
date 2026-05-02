# VastuVeda CRM — Setup Guide

## 1. File Placement (CRITICAL)
Point your domain/subdomain document root to the `public/` folder, NOT the project root.

In cPanel → Subdomains → set the document root to:
`/home/YOUR_CPANEL_USER/vastuveda_crm/public`

## 2. Database Setup
1. In cPanel → MySQL Databases, create a database + user + assign full privileges.
2. Import `database/schema.sql` then `database/seed.sql` via phpMyAdmin.
3. Edit `config/database.php` and fill in your actual credentials:
   - `dbname` → your database name (format: cpaneluser_dbname)
   - `user`   → your DB username (format: cpaneluser_dbuser)
   - `pass`   → your DB password

## 3. Folder Permissions
Run in cPanel File Manager or SSH:
```
chmod 755 storage/
chmod 755 storage/logs/
chmod 755 storage/uploads/
chmod 755 storage/uploads/projects/
chmod 755 storage/uploads/employees/
```

## 4. After Everything Works
In `config/app.php`, set `'debug' => false` for production.

## Bugs Fixed in This Version
- Removed corrupted directories from bad zip extraction (`{app`, `{core,config,public`)
- Fixed database.php: was using `root` / empty password (doesn't work on shared hosting)
- Fixed enum mismatch: `unitStatusBadge()` was using `'held'` but DB schema uses `'reserved'`
- Enabled `display_errors` in .htaccess for debugging (mod_php7 + mod_php8 variants added)
- Added root-level `.htaccess` to block direct access to `config/`, `core/`, `app/`, `database/`, `storage/`
- Added `debug => true` temporarily (set back to false once site works)

## Default Login (from seed.sql)
Check `database/seed.sql` for admin credentials.
