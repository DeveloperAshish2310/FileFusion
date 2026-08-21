# ⚡ FileFusion — Modern Cloud Storage, Digital Asset & Encrypted Credential Vault

<p align="center">
  <img src="art/logo.png" width="180" height="180" alt="FileFusion Logo" />
</p>

<p align="center">
  <strong>An enterprise-grade, privacy-first personal cloud workspace, digital asset manager, task workspace, and zero-knowledge encrypted credential vault built with modern Laravel & rich UX aesthetics.</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+" />
  <img src="https://img.shields.io/badge/Laravel-11.x%20%2F%2012.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel" />
  <img src="https://img.shields.io/badge/Encryption-AES--256--CBC-blue?style=for-the-badge&logo=auth0&logoColor=white" alt="AES-256-CBC" />
  <img src="https://img.shields.io/badge/2FA-TOTP%20%26%20Email%20OTP-success?style=for-the-badge&logo=google-authenticator&logoColor=white" alt="2FA" />
  <img src="https://img.shields.io/badge/REST_API-v1_Ready-blueviolet?style=for-the-badge&logo=postman&logoColor=white" alt="REST API" />
  <img src="https://img.shields.io/badge/Vite-Bundled-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite" />
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License MIT" />
</p>

---

## 📖 Table of Contents
1. [Overview](#-overview)
2. [Key Highlights & Features](#-key-highlights--features)
   - [📁 File & Media Management](#-file--media-management)
   - [🌐 Universal Sharing Hub](#-universal-sharing-hub)
   - [📋 Tasks & To-Do Workspace](#-tasks--to-do-workspace)
   - [🔗 Web Bookmark & Link Hub](#-web-bookmark--link-hub)
   - [🔑 Encrypted Password & Credential Vault](#-encrypted-password--credential-vault)
   - [🚀 Developer REST API & Personal Access Tokens](#-developer-rest-api--personal-access-tokens)
   - [👑 Super Admin Console & CMS](#-super-admin-console--cms)
   - [🛡️ Security Architecture & 2FA](#-security-architecture--2fa)
   - [📦 Backup, Export & Disaster Recovery](#-backup-export--disaster-recovery)
   - [🎨 Design Engine & Theming](#-design-engine--theming)
3. [Technology Stack](#-technology-stack)
4. [Installation & Getting Started](#-installation--getting-started)
5. [Configuration & Environment](#-configuration--environment)
6. [API & Postman Collection](#-api--postman-collection)
7. [Project Structure](#-project-structure)
8. [Security Practices](#-security-practices)
9. [License](#-license)

---

## 🌟 Overview

**FileFusion** is a self-hosted cloud workspace that brings together file storage, media galleries, bookmark management, task scheduling, universal sharing, and confidential password storage under a unified, beautiful, and secure web interface.

Designed for speed, reliability, and security, FileFusion provides instant global search, per-category filtering, auto-locking hidden vaults, two-factor authentication (TOTP/Email), automated AES-256 backup creation, full REST API integration with developer tokens, and an administrative control suite with user quota management and audit activity logging.

---

## 🚀 Key Highlights & Features

### 📁 File & Media Management
* **Multi-Format Uploads**: High-performance asynchronous chunked/streamed uploads for documents, images, audio, video, archives, and code.
* **In-App Media Player & Previews**: Stream audio/video directly in the browser and preview PDF documents, code syntax, and high-resolution photos.
* **Smart Categorization & Tagging**: Organize items with customizable colored categories, nested tags, and custom metadata.
* **Bulk Operations**: Multi-select files for batch download, trash, permanent deletion, category assignment, or vault transfer.
* **Storage Quota Meters**: Visual progress bars and quota thresholds customized per user role (Free, Pro, Manager, Admin).
* **Storage Cleaner**: Dedicated orphaned file cleaner to recover storage space without database corruption.

### 🌐 Universal Sharing Hub
* **Multi-Resource Sharing**: Share single files, full categories, bookmark sets, or password vaults with fine-grained access policies.
* **1-Time Single-Use Links**: Self-destructing links that automatically expire after a single download or view.
* **Time-Bound Expiration & Passcodes**: Set custom expiration durations (minutes/hours/days) and optional guest PIN/passcode gates.
* **Private User-to-User Delegations**: Delegate resource access to other registered users directly within the platform.
* **Live Telemetry & Revocation**: Monitor download counters, visitor timestamps, and revoke links in real-time.

### 📋 Tasks & To-Do Workspace
* **Step-by-Step Task Breakdown**: Break complex tasks into bite-sized actionable checklists with interactive progress meters.
* **Visual Calendar View**: Track due dates, overdue milestones, and deadlines in an interactive monthly and weekly calendar.
* **Task Collections**: Organize tasks by project, collection, or sprint with custom cover images and category tags.
* **Priority & Hidden Vault**: Star critical tasks and secure confidential to-dos in an auto-locking hidden vault.

### 🔗 Web Bookmark & Link Hub
* **Metadata Scraper**: Automatically extracts page titles, descriptions, preview thumbnails, and favicons from pasted URLs.
* **Tagging & Filtering**: Quick-access tags, domain categorization, search filters, and favorite bookmark pins.
* **Sharing & QR Generation**: Generate instant QR codes and shareable landing links for any saved item.
* **Bulk Management**: Export bookmarks to standard JSON or HTML formats for easy portability across browsers.

### 🔑 Encrypted Password & Credential Vault
* **AES-256-CBC Field-Level Encryption**: All passwords, PINs, recovery codes, and custom fields are encrypted with distinct initialization vectors before storage.
* **JIT (Just-In-Time) On-Demand Reveal**: Secrets remain masked by default; clicking the eye icon triggers an authenticated reveal with configurable auto-mask timer.
* **Password Generator**: Configurable cryptographic generator with customizable character sets, lengths, and entropy strength score meter.
* **Custom Security Fields**: Attach custom key-value pairs (API keys, security questions, seed phrases, notes) to any credential item.

### 🚀 Developer REST API & Personal Access Tokens
* **Complete REST API (v1)**: Full JSON endpoints for Files, Universal Shares, Bookmarks, Categories, Tasks, Password Vault, and Admin telemetry.
* **Personal Access Tokens (PAT)**: Generate fine-grained API tokens (`ff_live_...`) with granular ability scopes (`files:read`, `vault:reveal`, `todos:write`, etc.).
* **In-Browser API Playground**: Test and explore all endpoints live at `/api-tester` with instant curl generator and response preview.
* **Postman / Thunder Client Ready**: Import the ready-to-use collection from `api_documentation/FileFusion_API_v1.postman_collection.json`.

### 👑 Super Admin Console & CMS
* **Live Overview Dashboard**: Global KPI telemetry (total storage consumed, active users, links, files, system health).
* **User Provisioning & Quota Manager**: Adjust individual user storage limits, assign roles, toggle API access, or reset credentials.
* **Landing Page CMS**: Real-time editor for homepage hero banners, typography, badge copy, feature grids, and SEO metadata.
* **Global Files Vault & Link Audit**: Centralized administrative search across all user files and decrypted bookmarks.
* **SMTP & Email Template CMS**: Customize system notifications (file shares, account welcome, 2FA OTP codes, security alerts).
* **Security & Audit Logs**: Real-time stream of all user actions, IP addresses, user agents, and security triggers.

### 🛡️ Security Architecture & 2FA
* **Independent Hidden Vaults**: Dedicated hidden zones for Files, Links, and Passwords protected by an independent Passcode / Master PIN.
* **Configurable Session Lifetimes**: Independent timeout controls for Hidden Files, Hidden Links, Password Vault, and Secret Reveals (`2m`, `5m`, `15m`, `30m`, `1h`, `2h`, `4h`, `8h`, `24h`, or `⚡ Immediate`).
* **Two-Factor Authentication (2FA)**:
  * **TOTP Authenticator Apps**: Google Authenticator, 1Password, Authy, Bitwarden.
  * **Email OTP Verification**: Fallback 6-digit one-time passcodes delivered via SMTP.
  * **Emergency Recovery Codes**: Printable single-use fallback codes.
* **Master Encryption Key Rotation**: Zero-downtime key rotation with backward compatibility for previous cipher keys.

### 📦 Backup, Export & Disaster Recovery
* **Three Flexible Backup Types**:
  1. **Database Only**: Complete SQL snapshot.
  2. **Files & Database**: Full user uploads directory combined with SQL database dump.
  3. **Full System Codebase**: Complete application backup including controllers, models, views, configurations, and uploads.
* **Zero-Knowledge Encryption**: Automatic password-protected AES-256 encrypted payload packaging.
* **Remote Offsite Backups**: Automated offsite transfer via SFTP / FTP storage.

### 🎨 Design Engine & Theming
* **Fluid Theme Engine**: Fast Light and Dark mode with persistent `localStorage` preference and zero layout shift on reload.
* **4 Curated Accent Palettes**:
  * 🧱 `Terracotta` (`#d97757`)
  * 🌲 `Emerald` (`#2f9e6e`)
  * 🌊 `Teal` (`#1e9ca8`)
  * 🔮 `Indigo` (`#5b6fcf`)
* **Dynamic Palette-Adaptive Shadows**: Primary buttons, badges, and focus rings dynamically match your selected accent palette.
* **Responsive Collapsible Sidebar**: Fluid sidebar collapsing to icon mode with seamless mobile drawer support.

---

## 🛠️ Technology Stack

| Layer | Technologies |
|---|---|
| **Backend** | PHP 8.2+, Laravel 11.x / 12.x, Eloquent ORM |
| **Frontend** | Blade Templates, ES6+ JavaScript, Vanilla CSS Design System, Vite |
| **Database** | MySQL 8.0+ / MariaDB / PostgreSQL / SQLite |
| **Icons & UI** | Lucide Icons, Custom Inline SVGs, Google Fonts (Outfit & Inter) |
| **Security & Crypto** | OpenSSL AES-256-CBC, PragmaRX Google2FA, Argon2id / Bcrypt |
| **Archival & Media** | PHP ZipArchive, Intervention Image, FFMpeg (optional) |

---

## 💻 Installation & Getting Started

### Prerequisites
* **PHP**: `>= 8.2` with extensions (`bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip`)
* **Composer**: `>= 2.x`
* **Node.js**: `>= 18.x` & **npm**
* **Database**: MySQL `>= 8.0` or MariaDB `>= 10.4`

### Step-by-Step Setup

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/your-username/filefusion.git
   cd filefusion/laravel
   ```

2. **Install PHP Dependencies**:
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Install & Compile Frontend Assets**:
   ```bash
   npm install
   npm run build
   ```

4. **Environment Configuration**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure Database & Mail in `.env`**:
   ```env
   APP_NAME="FileFusion"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=filefusion_db
   DB_USERNAME=root
   DB_PASSWORD=your_db_password

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_username
   MAIL_PASSWORD=your_password
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS="no-reply@filefusion.local"
   MAIL_FROM_NAME="${APP_NAME}"
   ```

6. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   ```

7. **Create Storage Symbolic Link**:
   ```bash
   php artisan storage:link
   ```

8. **Start the Application**:
   ```bash
   php artisan serve
   ```
   Visit `http://127.0.0.1:8000` in your web browser.

---

## 📚 API & Postman Collection

All API documentation and Postman collections are organized in the [`api_documentation/`](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/api_documentation/) directory:

- 📖 **[Complete API Documentation (Markdown)](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/api_documentation/API_DOCUMENTATION.md)**
- 🚀 **[API Quickstart Guide](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/api_documentation/README.md)**
- ⚡ **[Postman Collection v2.1 (JSON)](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/api_documentation/FileFusion_API_v1.postman_collection.json)**
- 🧪 **Live Browser Playground**: Accessible at `/api-tester` on any local/staging instance.

---

## 📂 Project Structure

```
laravel/
├── api_documentation/      # Full REST API specs & Postman collections
├── app/
│   ├── Helpers/            # Formatting, setting, and string helpers
│   ├── Http/
│   │   ├── Controllers/    # File, Link, Password, Share, Todo, Admin controllers
│   │   └── Middleware/     # Auth, SuperAdmin, 2FA, API token, and Session gates
│   ├── Models/             # User, File, Link, Password, Share, Todo, Category
│   └── Services/           # BackupService, AuditLogger, EncryptionEngine
├── config/                 # Application, auth, cache, and database configs
├── database/
│   ├── migrations/         # Schema definitions & lifetime enhancements
│   └── seeders/            # Super Admin & default category seeders
├── public/
│   ├── api-tester.html     # Live interactive API playground
│   ├── branding/           # Brand marks, favicon, and SVG logos
│   ├── build/              # Compiled Vite production CSS & JS bundles
│   └── uploads/            # Encrypted / isolated user file directories
├── resources/
│   ├── css/
│   │   └── filefusion.css  # Core design system tokens, themes & layout rules
│   ├── js/                 # Client scripts & theme bootstrap
│   └── views/
│       ├── errors/         # Custom HTTP error pages (401, 403, 404, 500, 502)
│       ├── layout/         # Master backend and public shell templates
│       ├── panel/          # Files, links, tasks, vault, settings, and admin views
│       └── public/         # Universal share gates, expired & download views
└── routes/
    ├── api.php             # REST API v1 endpoints
    └── web.php             # Authenticated, public share, and admin routes
```

---

## 🔒 Security Practices

* **Zero-Knowledge Field Storage**: Plaintext passwords and secret tokens are never written to disk or logs unencrypted.
* **Rate Limiting**: Critical endpoints (login, 2FA verify, password reveal, search queries, API calls) are protected with Laravel throttle middleware.
* **Auditability**: Every security-sensitive action creates an immutable event in the `activity_logs` table.
* **Anti-Enumeration (Anti-IDOR)**: Resource IDs are tamper-proof Laravel-encrypted strings.
* **Safe Content Serving**: Downloaded files are validated against MIME type mismatches with strict `Content-Disposition` headers to mitigate XSS vectors.

---

## 📄 License

This project is open-source software licensed under the **[MIT License](LICENSE)**. Feel free to use, modify, and distribute according to the terms.
