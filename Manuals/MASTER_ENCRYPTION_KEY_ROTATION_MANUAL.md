# Master Encryption Key Rotation & Leak Recovery Manual

This manual provides an in-depth operational and architectural guide for rotating compromised, leaked, or expired Master Encryption Keys (`APP_KEY` for database records and `FILE_ENCRYPTION_KEY` for envelope-encrypted physical files) with **zero data loss**, **zero downtime**, and **seamless cryptographic backward compatibility**.

---

## 📌 Executive Summary

| Feature | Specification |
| :--- | :--- |
| **Database Encryption** | AES-256-CBC with HMAC-SHA256 authentication |
| **Physical File Encryption** | AES-256-GCM Envelope Encryption (`FF_ENC` v2) |
| **Re-Keying Speed for Files** | Microseconds per file (60-byte binary header re-keying) |
| **Payload Integrity Risk** | 0% (Payloads remain intact and encrypted with unique DEK) |
| **Downtime** | Zero downtime (Multi-key fallback chain supported) |
| **Management Interfaces** | Web Admin UI (`/panel/admin/settings`) & Artisan CLI (`php artisan security:rotate-keys`) |

---

## 🔐 Cryptographic Architecture

### 1. Database Attribute Encryption (`Encryptor`)
- Encrypts sensitive database columns:
  - `files.name`
  - `categories.title`, `categories.description`
  - `links.title`, `links.url`, `links.description`, `links.tags`
  - `passwords.title`, `passwords.username`, `passwords.url`, `passwords.password`, `passwords.notes`, `passwords.auth_fields`
- **Fallback Chain**: Uses active `APP_KEY` first. If a record was encrypted under a previous key, `Encryptor::decrypt()` seamlessly checks `APP_PREVIOUS_KEYS` (comma-separated key list in `.env`).

### 2. Physical File Envelope Encryption (`FileEncryptor`)
- Each physical file on disk is protected with **AES-256-GCM Envelope Encryption**:
  ```
  +------------------------------------------------------------------------------------------------+
  |                                 BINARY HEADER (102 Bytes)                                      |
  | MAGIC (6B) | VER (1B) | ALG (1B) | KEYVER (4B) | NONCE (12B) | DEKLEN (2B) | ENC_DEK (60B) | TAG (16B) |
  +------------------------------------------------------------------------------------------------+
  |                                 ENCRYPTED PAYLOAD (Ciphertext)                                 |
  | Encrypted with random per-file 256-bit DEK                                                     |
  +------------------------------------------------------------------------------------------------+
  ```
- **Why File Re-Keying is Instant & 100% Safe**:
  - The actual file content (which may be gigabytes) is encrypted using a unique, random per-file 256-bit **Data Encryption Key (DEK)**.
  - The DEK is locked with the Master **Key Encryption Key (KEK)** derived from `FILE_ENCRYPTION_KEY`.
  - When rotating the Master Key, the engine **only decrypts and re-encrypts the 60-byte DEK in the header at byte offset 26**.
  - The underlying file ciphertext is never touched or rewritten, preventing disk space exhaustion and data corruption.

---

## 🚀 How to Rotate Keys

### Method A: Super Admin Web UI (Recommended)

1. Open your browser and log in as a **Super Admin**.
2. Navigate to **Super Admin Panel → System Settings** (`/panel/admin/settings`).
3. Locate the **🔐 Master Encryption Keys & Security Rotation** section.
4. **Step 1 (Diagnostic Dry Run)**:
   - Click the **`🧪 Pre-Flight Dry Run`** button.
   - The system tests decryption on 100% of database records and disk files and displays a real-time status summary.
5. **Step 2 (Execute Key Rotation)**:
   - Click **`🔐 Rotate Keys & Re-Encrypt`**.
   - A modal dialog appears allowing you to choose whether to rotate `APP_KEY`, `FILE_ENCRYPTION_KEY`, or both, and whether to preserve previous keys in the fallback chain.
   - Click **Confirm & Execute Rotation**.
   - The modal displays real-time progress and a detailed summary of rows and files re-encrypted.

---

### Method B: Artisan Command Line (CLI)

The CLI tool supports dry-run diagnostics, targeted rotations, and non-interactive scripted executions:

```bash
# 1. Run Pre-Flight Dry Run verification (no changes made)
php artisan security:rotate-keys --dry-run

# 2. Rotate BOTH Database Key (APP_KEY) and File Master Key (FILE_ENCRYPTION_KEY)
php artisan security:rotate-keys

# 3. Rotate ONLY Database APP_KEY
php artisan security:rotate-keys --app-key

# 4. Rotate ONLY File Master Key
php artisan security:rotate-keys --file-key

# 5. Provide custom specified keys
php artisan security:rotate-keys --new-app-key="base64:..." --new-file-key="base64:..."

# 6. Non-interactive execution for automated deployment pipelines
php artisan security:rotate-keys --force
```

---

## 🛠️ Configuration Parameters (`.env`)

| Variable | Description | Example |
| :--- | :--- | :--- |
| `APP_KEY` | Primary application and database attribute master encryption key | `base64:xnPh...WA0=` |
| `APP_PREVIOUS_KEYS` | Comma-separated list of previous application keys for zero-downtime decryption | `base64:itaH...,base64:JDsP...` |
| `FILE_ENCRYPTION_KEY` | Primary master key encryption key (KEK) for disk file envelopes | `base64:t3C+...nmE=` |
| `FILE_KEY_VERSION` | Sequential key version identifier | `v4` |
| `FILE_PREVIOUS_KEYS` | Comma-separated list of previous file master keys | `base64:Jwq2...,base64:+L+F...` |

---

## 🧪 Verification & Automated Testing

You can verify cryptographic health and rotation safety at any time by running the automated test suite:

```bash
php antigravity_testfiles/test_key_rotation_lifecycle.php
```

### What this test verifies:
1. Performs pre-flight dry-run check across all database tables (`files`, `categories`, `links`, `passwords`).
2. Creates sample envelope-encrypted files and tests initial decryption.
3. Executes live key rotation with newly generated 256-bit keys.
4. Verifies post-rotation decryption on sample disk files and database models.
5. Verifies `.env` file and runtime memory synchronization.

---

## ⚠️ Security Best Practices for Key Leak Emergencies

If an encryption key or `.env` file is accidentally leaked (e.g. pushed to a public repository or exposed via server log):

1. **Immediate Action**: Run `php artisan security:rotate-keys` immediately.
2. **Key Invalidation**:
   - Once all active database records and files are confirmed re-encrypted, you can prune old compromised keys from `APP_PREVIOUS_KEYS` and `FILE_PREVIOUS_KEYS` in `.env`.
3. **Cache Invalidation**:
   - Run `php artisan config:clear` and `php artisan cache:clear` after modifying `.env`.
