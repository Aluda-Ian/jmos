# JMOS — API Integrations & Third-Party Services Setup Guide

This document provides complete, step-by-step instructions for connecting and configuring all external APIs and third-party services in **JMOS (Jeota Media Operating System)**.

---

## Table of Contents
1. [Google Calendar & Google Meet API](#1-google-calendar--google-meet-api)
2. [Kenya Revenue Authority (KRA) eTIMS & iTax ("Connect Gava")](#2-kenya-revenue-authority-kra-etims--itax-connect-gava)
3. [SMTP Email Gateway & Automated Notifications](#3-smtp-email-gateway--automated-notifications)
4. [Web Push Notifications & PWA Background Alerts](#4-web-push-notifications--pwa-background-alerts)
5. [WhatsApp Direct Dispatch (Quotes & Invoices)](#5-whatsapp-direct-dispatch-quotes--invoices)
6. [Banking & M-Pesa Payment Gateways](#6-banking--m-pesa-payment-gateways)
7. [Environment Variables Reference](#7-environment-variables-reference)

---

## 1. Google Calendar & Google Meet API

JMOS seamlessly synchronizes discovery meetings, production shoots, project delivery deadlines, and task milestones with **Google Calendar**, automatically provisioning **Google Meet** video conference rooms with real-time attendee notification.

### Step 1: Create a Google Cloud Console Project
1. Navigate to the [Google Cloud Console](https://console.cloud.google.com/).
2. Log in with your corporate Google Workspace or Gmail account.
3. Click the project dropdown at the top and select **New Project**.
4. Name your project (e.g., `JMOS Operations Calendar`) and click **Create**.

### Step 2: Enable Google Calendar API
1. In the left navigation menu, go to **APIs & Services** → **Library**.
2. Search for **Google Calendar API**.
3. Click on it and click **Enable**.

### Step 3: Create Credentials (API Key & OAuth 2.0)
1. Go to **APIs & Services** → **Credentials**.
2. Click **+ Create Credentials** → **API Key**:
   - Copy the generated API Key (e.g., `AIzaSy...`).
   - (Optional) Restrict the API key to the *Google Calendar API* for enhanced security.
3. Click **+ Create Credentials** → **OAuth Client ID**:
   - If prompted, configure the **OAuth Consent Screen** (User Type: *External* or *Internal*, App Name: `JMOS`, Developer Email: `admin@jeotamedia.co.ke`).
   - Application Type: Select **Web application**.
   - **Authorized JavaScript origins**:
     - `https://jmos.jeotamedia.co.ke`
     - `http://127.0.0.1:8000` (for local development)
   - **Authorized redirect URIs**:
     - `https://jmos.jeotamedia.co.ke/oauth/google/callback`
   - Click **Create** and copy your **Client ID** and **Client Secret**.

### Step 4: Configure in JMOS
1. Log in to JMOS as an **Owner** or **Manager**.
2. Navigate to **Settings** in the sidebar.
3. Scroll to **Google Calendar & Task API**:
   - **Target Google Calendar ID**: `primary` (or your dedicated calendar address like `production@jeotamedia.co.ke` / Google Group Calendar ID).
   - **OAuth Client ID**: Paste your Google OAuth Client ID.
   - **Client Secret**: Paste your Google Client Secret.
   - **Google API Key**: Paste your Google API Key.
4. Click **Save all settings** at the top right.
5. Click **Sync Now** to verify the live connection.

> **Note on Meet Links:** When Google Cloud credentials are configured, JMOS requests conference data via `conferenceDataVersion=1`. If Google API is momentarily offline or credentials are not yet entered, JMOS uses its built-in meeting algorithm to generate standard `https://meet.google.com/xxx-yyyy-zzz` URLs so workflows are never blocked.

---

## 2. Kenya Revenue Authority (KRA) eTIMS & iTax ("Connect Gava")

JMOS is architected for strict compliance with Kenyan tax statutes for media production, film agencies, and creative service providers.

### Professional Creative Services Tax Rules:
- **Value Added Tax (VAT)**: **0% Exempt / Zero-Rated** for professional creative and production services.
- **Withholding Tax (WHT)**: **5% WHT** deducted by corporate clients on professional service invoices (with WHT certificates credited against annual corporate tax returns).
- **Direct Project Expenses**: 100% tax-deductible when supported by electronic tax register (ETR / eTIMS) receipts.

### Step 1: Obtain KRA PIN & eTIMS Device ID
1. Log in to the [KRA iTax Portal](https://itax.kra.go.ke/).
2. Retrieve your company's **KRA PIN** (e.g., `P051782390X`).
3. For eTIMS integration, obtain your registered **eTIMS Branch / Device ID** (default is `00` for primary headquarters).

### Step 2: Configure in JMOS
1. Open **Settings** in JMOS.
2. Locate the **Connect Gava (KRA / eTIMS & iTax Gateway)** card:
   - **KRA PIN**: Enter your company's registered PIN (e.g., `P051782390X`).
   - **Taxpayer Name**: Registered company entity (e.g., `Jeota Media Ltd`).
   - **eTIMS Branch / Device ID**: e.g., `00`.
   - **VAT Rate (%)**: `0` (Exempt for professional creative services).
   - **Withholding Tax (WHT %)**: `5` (Statutory 5% professional services withholding tax).
   - **Gava Connection Status**: Select `Connected & Live (iTax / eTIMS)`.
3. Click **Save all settings** and click **Sync with Gava**.

### Step 3: Daily eTIMS Operational Compliance
- **Issuing Invoices**: When issuing invoices in JMOS, ensure **eTIMS Compliant?** is toggled to **Yes**.
- **Logging Expenses**: When recording production expenses (gear rental, locations, catering, talent), enter the vendor's **eTIMS / KRA CU Invoice Number** (e.g., `CU01-08123`) and attach the scanned receipt image/PDF.

---

## 3. SMTP Email Gateway & Automated Notifications

JMOS uses a centralized notification dispatch engine to send branded HTML emails for task assignments, calendar meetings, invoice statements, and closed deals.

### Supported Mail Providers:
- **cPanel / DirectAdmin / Custom Domain Webmail** (`mail.yourdomain.co.ke`)
- **Zoho Mail** (`smtppro.zoho.com`)
- **Google Workspace / Gmail** (`smtp.gmail.com`)
- **Amazon SES / Sendgrid / Mailgun**

### Step 1: Recommended SMTP Server Settings
| Setting | Webmail / cPanel | Zoho Mail | Google Workspace |
| :--- | :--- | :--- | :--- |
| **SMTP Host** | `mail.jeotamedia.co.ke` | `smtppro.zoho.com` | `smtp.gmail.com` |
| **SMTP Port** | `587` | `587` | `587` |
| **Encryption** | `TLS` | `TLS` | `TLS` |
| **Username** | `jmos@jeotamedia.co.ke` | `jmos@jeotamedia.co.ke` | `you@jeotamedia.co.ke` |
| **Password** | *Your Mailbox Password* | *App-Specific Password* | *App-Specific Password* |
| **From Address** | `jmos@jeotamedia.co.ke` | `jmos@jeotamedia.co.ke` | `jmos@jeotamedia.co.ke` |
| **From Name** | `JMOS — Jeota Media` | `JMOS — Jeota Media` | `JMOS — Jeota Media` |

### Step 2: Configure in JMOS
1. Go to **Settings** → **SMTP Email Gateway**.
2. Enter the **Host**, **Port**, **Username**, **Password**, **Encryption**, **From Address**, and **Display Name**.
3. Click **Save all settings**.

### Step 3: Test Connection & Dispatch
1. In the **Test SMTP Connectivity** box:
   - Enter your test recipient email address (e.g., `admin@jeotamedia.co.ke`).
   - Click **Send Test Email**.
2. In the **Branded Email Notifications & Reminders** card:
   - Click **Send Sample** on any template (*1. Task Assignment*, *2. Calendar Reminder*, *3. Invoice Statement*, *4. Deal-Won Alert*) to verify HTML email formatting on desktop and mobile clients.

---

## 4. Web Push Notifications & PWA Background Alerts

JMOS features native **Progressive Web App (PWA)** capabilities and **VAPID Web Push Alerts** delivering real-time desktop and mobile push alerts.

### Step 1: Enabling in Browser
1. In JMOS, click the **🔔 Notifications** icon in the top navigation bar or go to **Settings** → **App Download & Push Notifications**.
2. Click **Enable Browser Notifications**.
3. In the browser prompt, select **Allow**.

### Step 2: Installing as a Standalone App (PC / Android / iOS)
- **Windows PC & Mac (Chrome / Edge / Brave)**:
  - Click **Download & Install JMOS App** in Settings or click the install icon in your browser's address bar.
- **Android**:
  - Open JMOS in Chrome, tap the 3-dot menu → **Install App** / **Add to Home screen**.
- **iPhone & iPad (Safari iOS)**:
  - Open JMOS in Safari, tap the **Share** button <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" style="display:inline-block;vertical-align:middle"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>, scroll down and tap **Add to Home Screen**.

---

## 5. WhatsApp Direct Dispatch (Quotes & Invoices)

JMOS includes zero-friction **WhatsApp Direct Dispatch** for instant client quote delivery and payment reminders via WhatsApp Web and the WhatsApp mobile app.

### How it Works:
- When creating a quotation in **Quote Generator** or viewing an invoice, clicking **Send WhatsApp** encodes the formatted commercial breakdown, deliverables list, and total amount into a direct `https://wa.me/{phone}?text={message}` deep-link.
- The phone number automatically formats country codes (e.g., `+254 7...` → `2547...`).

---

## 6. Banking & M-Pesa Payment Gateways

Invoice and quote documents generated by JMOS display payment coordinates for electronic settlement.

### Payment Coordinates Displayed:
1. **Bank Wire Transfer**:
   - **Bank Name**: NCBA Bank Kenya
   - **Account Name**: Jeota Media Limited
   - **Account Number**: *Configured in Finance Settings*
   - **Branch**: Westlands Branch, Nairobi
2. **M-Pesa Settlement**:
   - **Paybill Number / Buy Goods Till**: Configured in Finance Settings
   - **Account Reference**: Invoice Number (e.g., `JM-0146`)

---

## 7. Environment Variables Reference

For server-level configurations, the following `.env` variables can be populated in production:

```ini
# Application Configuration
APP_NAME="JMOS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jmos.jeotamedia.co.ke

# Database Connection (MySQL / MariaDB)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD="your_database_password"

# Mail / SMTP Configuration
MAIL_MAILER=smtp
MAIL_HOST=mail.jeotamedia.co.ke
MAIL_PORT=587
MAIL_USERNAME=jmos@jeotamedia.co.ke
MAIL_PASSWORD="your_email_password"
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="jmos@jeotamedia.co.ke"
MAIL_FROM_NAME="JMOS — Jeota Media"

# Google Calendar & Meet Integration
GOOGLE_CALENDAR_ID=primary
GOOGLE_CLIENT_ID=your_oauth_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_oauth_client_secret
GOOGLE_API_KEY=AIzaSy_your_google_api_key

# Kenya Revenue Authority (KRA / eTIMS)
KRA_PIN=P051782390X
KRA_TAXPAYER_NAME="Jeota Media Ltd"
KRA_ETIMS_BRANCH=00
KRA_VAT_RATE=0
KRA_WHT_RATE=5
```

---

## Technical Support & Verification

To verify all system settings and integrations in one command:
```bash
# Run system verification test suite
php artisan test

# Verify database migrations
php artisan migrate:status

# Clear and optimize configuration cache
php artisan optimize:clear
```
