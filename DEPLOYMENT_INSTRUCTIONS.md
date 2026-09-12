# JMOS — Production Deployment Guide for DirectAdmin
**Target Subdomain:** `http://jmos.jeotamedia.co.ke/`

---

## 📋 DirectAdmin Credentials Reference

| Setting | Value |
| :--- | :--- |
| **Subdomain URL** | `http://jmos.jeotamedia.co.ke/` |
| **DB Host** | `localhost` (or `127.0.0.1`) |
| **DB Name** | `jeotamed_jmos` |
| **DB Username** | `jeotamed_jmos` |
| **DB Password** | `@Munangwe212` |
| **SMTP Host** | `mail.jeotamedia.co.ke` (Port `587`, TLS) |
| **SMTP Email** | `jmos@jeotamedia.co.ke` |
| **SMTP Password** | `@Munangwe212` |

---

## 🚀 Step-by-Step Deployment Instructions

### Step 1: Upload Project Files to DirectAdmin

1. Log in to your **DirectAdmin Control Panel**.
2. Navigate to **System Info & Files &rarr; File Manager**.
3. Open the folder for your subdomain:
   - Path is usually: `/domains/jeotamedia.co.ke/public_html/jmos` (or the subdomain root folder created by DirectAdmin).
4. Upload `jmos_directadmin_deploy.zip` (or upload the project folders):
   - `app/`
   - `bootstrap/`
   - `config/`
   - `database/`
   - `public/`
   - `resources/`
   - `routes/`
   - `storage/`
   - `vendor/`
   - `.env.production`
   - `.htaccess`
   - `artisan`
   - `composer.json`
   - `jmos_production_dump.sql`
5. If you uploaded the `.zip`, right-click `jmos_directadmin_deploy.zip` and select **Extract**.

---

### Step 2: Import the Database in phpMyAdmin

1. In DirectAdmin, go to **Account Manager &rarr; phpMyAdmin** (or **MySQL Management**).
2. Click on the database **`jeotamed_jmos`** in the left sidebar.
3. Click the **Import** tab in the top navigation bar.
4. Click **Choose File** and select **`jmos_production_dump.sql`** from your computer or server folder.
5. Click **Import** (or **Go**) at the bottom.
   > This will automatically create and populate all 23 tables: `users`, `clients`, `projects`, `deals`, `tasks`, `invoices`, `expenses`, `service_recipes`, `calendar_events`, `chat_threads`, `chat_participants`, `chat_messages`, `system_settings`, `finance_settings`, etc.

---

### Step 3: Configure `.env` in the Subdomain Root

1. In File Manager inside the subdomain folder, locate `.env` (or rename `.env.production` to `.env`).
2. Verify that `.env` contains the following lines:

```env
APP_NAME="JMOS — Jeota Media"
APP_ENV=production
APP_KEY=base64:DNkJk217iBE7k+ZmnXy7BSTYOArPNmhLImhbaKHNcYI=
APP_DEBUG=false
APP_URL=http://jmos.jeotamedia.co.ke

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=jeotamed_jmos
DB_USERNAME=jeotamed_jmos
DB_PASSWORD="@Munangwe212"

SESSION_DRIVER=file
SESSION_LIFETIME=120
CACHE_STORE=file
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=mail.jeotamedia.co.ke
MAIL_PORT=587
MAIL_USERNAME=jmos@jeotamedia.co.ke
MAIL_PASSWORD="@Munangwe212"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="jmos@jeotamedia.co.ke"
MAIL_FROM_NAME="JMOS — Jeota Media"
```

---

### Step 4: Set File & Directory Permissions

1. In File Manager, ensure the following folders have write permissions (`775` or `755`):
   - `storage/` (and all subfolders: `storage/framework/`, `storage/logs/`)
   - `bootstrap/cache/`
2. If using SSH / Terminal on DirectAdmin:
   ```bash
   chmod -R 775 storage bootstrap/cache
   ```

---

### Step 5: Verify Web Routing (`.htaccess`)

The included root `.htaccess` handles routing transparently:
- Web traffic to `http://jmos.jeotamedia.co.ke/` is automatically served from `/public/` without requiring `/public` in the URL.
- Direct access to sensitive files (`.env`, `artisan`, `composer.json`) is blocked.

---

### Step 6: Test & Access JMOS

1. Open your browser and visit: **`http://jmos.jeotamedia.co.ke/`**
2. Sign in with any of the team credentials (all default accounts use password **`jeota2024`**):

| Team Member | Role / Access | Email |
| :--- | :--- | :--- |
| **Barny Kiome** | Founder & Super Admin (Full access) | `barny@jeotamedia.co.ke` |
| **Ian Aluda** | IT & Systems Admin / Designer | `ian@jeotamedia.co.ke` |
| **Matthew Muange** | Finance & Accounting | `matthew@jeotamedia.co.ke` |
| **Patrick Mwendwa** | Sales & Pipeline | `patrick@jeotamedia.co.ke` |
| **Stephen Otieno** | Lead Video Editor | `stephen@jeotamedia.co.ke` |
| **Amos Muthama** | Cinematographer & Drone Pilot | `amos@jeotamedia.co.ke` |
| **Lesley Chacha** | Copywriter & Client Relations | `lesley@jeotamedia.co.ke` |

3. Go to the **Settings Tab** (`/settings` as Super Admin) and click **"Send Test Email"** to verify SMTP connectivity with `mail.jeotamedia.co.ke`.
