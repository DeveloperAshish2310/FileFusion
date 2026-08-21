# 🚀 FileFusion REST API Documentation (v1)

Welcome to the **FileFusion REST API & Developer Interface**. This API allows you to programmatically manage files, links, encrypted password vault items, and administrator telemetry. It also acts as the backend bridge for AI assistants (Cursor, Claude Desktop, Antigravity MCP).

---

## 📑 Table of Contents
1. [Authentication & Authorization](#1-authentication--authorization)
2. [Base URL & Headers](#2-base-url--headers)
3. [Token Ability Scopes](#3-token-ability-scopes)
4. [Identity & Token Telemetry](#4-identity--token-telemetry)
5. [File Management API](#5-file-management-api)
6. [Link & Bookmark API](#6-link--bookmark-api)
7. [Password & Credential Vault API](#7-password--credential-vault-api)
8. [Admin & System Health API](#8-admin--system-health-api)
9. [Error Handling & Status Codes](#9-error-handling--status-codes)
10. [AI / MCP Server Configuration](#10-ai--mcp-server-configuration)

---

## 1. Authentication & Authorization

All API requests require a **Personal Access Token (PAT)**. 
- Generated tokens begin with the prefix `ff_live_` followed by the token ID and the secret hash.
- **Admin Approval**: Standard users must be granted API access by an Administrator in the User Management Console (`/panel/admin/users`) before generating or using tokens.

### Providing the Token
Pass your token in the `Authorization` HTTP header:
```http
Authorization: Bearer ff_live_ff_tok_xxxxxxxxxx.yyyyyyyyyyyyyyyyyyyyyyyyyyyyyy
```
*Alternatively, you can pass it via `X-API-TOKEN: <TOKEN>`.*

---

## 2. Base URL & Headers

- **Base URL**: `http://127.0.0.1:8000/api/v1` (or your production domain `https://your-domain.com/api/v1`)
- **Required Headers**:
  ```http
  Accept: application/json
  Authorization: Bearer <YOUR_API_TOKEN>
  ```
- **Content-Type**:
  - For JSON requests: `Content-Type: application/json`
  - For file uploads: `Content-Type: multipart/form-data`

### 🔒 Encrypted Resource Identifiers (Anti-Enumeration / Anti-IDOR)
To prevent ID enumeration attacks and protect sensitive resource IDs:
- All returned resource IDs (`user.id`, `file.id`, `link.id`, `credential.id`) are **Laravel-encrypted strings** (e.g. `eyJpdiI6...`).
- When making requests to parameterized endpoints (e.g. `/files/{id}`, `/links/{id}`, `/vault/reveal/{id}`), provide the encrypted ID string returned by the API (or standard numeric IDs during local debugging).

---

## 3. Token Ability Scopes

When creating a token in **Settings > Developer & API Tokens**, you can grant granular abilities:

| Scope | Description |
| :--- | :--- |
| `*` | Full Access across all resources |
| `files:read` | List files, view metadata, stream and download decrypted files |
| `files:write` | Upload and envelope-encrypt new files |
| `files:delete` | Soft delete or permanently delete files |
| `links:read` | List and retrieve saved bookmark links |
| `links:write` | Create and update bookmark links (triggers scraper) |
| `links:delete` | Soft delete or permanently delete bookmark links |
| `categories:read` | List and retrieve categories and classification tags |
| `categories:write` | Create and update categories and subcategory tags |
| `categories:delete` | Delete categories |
| `todos:read` | List tasks, overdue indicators, and collections |
| `todos:write` | Create tasks, update notes, and toggle completion |
| `todos:delete` | Delete tasks and checklists |
| `vault:read` | List encrypted passwords and metadata (passwords masked) |
| `vault:reveal` | **JIT Decrypt**: Decrypt and reveal cleartext passwords |
| `vault:write` | Store and edit encrypted passwords |
| `vault:delete` | Delete passwords from the vault |
| `admin:users` | (Admin only) List and provision users |
| `admin:system` | (Admin only) System health, disk storage, and PHP telemetry |

---

## 4. Identity & Token Telemetry

### 4.1 Get Authenticated User & Token Scopes
Verify your token and inspect user storage consumption.

- **Endpoint**: `GET /api/v1/auth/me`
- **Required Scope**: Any valid token

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/auth/me" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "user": {
    "id": 1,
    "name": "Super Admin",
    "email": "test@filefusion.io",
    "username": "superadmin",
    "role": "Super Admin",
    "storage": {
      "used_bytes": 1048576,
      "used_formatted": "1 MB",
      "quota_bytes": 5368709120000,
      "quota_formatted": "5 TB",
      "usage_percentage": 0.02
    }
  },
  "token": {
    "name": "CLI Script",
    "token_id": "ff_tok_84920491",
    "abilities": ["*"],
    "expires_at": null,
    "last_used_at": "2026-08-18T23:20:00Z"
  }
}
```

---

## 5. File Management API

### 5.1 List Files
List encrypted files with search and type filters.

- **Endpoint**: `GET /api/v1/files`
- **Required Scope**: `files:read`
- **Query Parameters**:
  - `q` *(optional)*: Search filename or title.
  - `type` *(optional)*: Filter by file type (`image`, `pdf`, `video`, `audio`, `document`, `archive`).
  - `page` *(optional)*: Page number (default: `1`).
  - `per_page` *(optional)*: Results per page (default: `20`, max: `100`).

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/files?q=invoice&type=pdf" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

---

### 5.2 Upload & Encrypt File
Upload a file. The server automatically generates an envelope-encrypted AES-256-CBC cipher and stores it securely.

- **Endpoint**: `POST /api/v1/files/upload`
- **Required Scope**: `files:write`
- **Content-Type**: `multipart/form-data`
- **Form Body**:
  - `file` *(required)*: Binary file (max 500 MB per file or user quota limit).
  - `title` *(optional)*: Custom display title.
  - `notes` *(optional)*: Description or notes.

#### cURL Example:
```bash
curl -X POST "http://127.0.0.1:8000/api/v1/files/upload" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -F "file=@/path/to/my_document.pdf" \
  -F "title=Q3 Financial Report" \
  -F "notes=Confidential investor presentation"
```

#### Success Response (`201 Created`):
```json
{
  "success": true,
  "message": "File uploaded and encrypted successfully.",
  "file": {
    "id": 42,
    "title": "Q3 Financial Report",
    "name": "my_document.pdf",
    "size": "2.4 MB",
    "size_bytes": 2516582,
    "type": "application/pdf",
    "created_at": "2026-08-18T23:25:00Z"
  }
}
```

---

### 5.3 Get File Metadata
- **Endpoint**: `GET /api/v1/files/{id}/metadata`
- **Required Scope**: `files:read`

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/files/42/metadata" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

---

### 5.4 Download Decrypted File Stream
Stream or download the file decrypted on-the-fly.

- **Endpoint**: `GET /api/v1/files/{id}/download`
- **Required Scope**: `files:read`

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/files/42/download" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  --output "downloaded_document.pdf"
```

---

### 5.5 Generate Expiring & 1-Time Download Share Links
Generate a public share URL with automated download limits (e.g. single-use 1-time valid link) or strict time-bound expiration.

- **Endpoint**: `POST /api/v1/files/{id}/share`
- **Required Scope**: `files:write`
- **Request Body (JSON)**:
  - `expires_in_minutes` *(optional)*: Expiration time in minutes (e.g. `60` for 1 hour, `1440` for 24 hours).
  - `max_downloads` *(optional)*: Maximum allowed downloads. Set `max_downloads: 1` for **1-time single-use links**.
  - `password` *(optional)*: Optional PIN/passcode protection for guests.
  - `is_anonymous` *(optional)*: Boolean, hides owner identity from guests (default: `true`).

#### cURL Example (1-Time Single Use Link):
```bash
curl -X POST "http://127.0.0.1:8000/api/v1/files/eyJpdiI6.../share" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "max_downloads": 1,
    "expires_in_minutes": 120
  }'
```

#### Success Response (`201 Created`):
```json
{
  "success": true,
  "message": "Share link generated successfully.",
  "share": {
    "id": "eyJpdiI6...",
    "file_id": "eyJpdiI6...",
    "share_token": "enRM13kYPnvaOI72nSWDsbMhI4Q8ZZvK",
    "public_url": "http://127.0.0.1:8000/s/enRM13kYPnvaOI72nSWDsbMhI4Q8ZZvK",
    "direct_download_url": "http://127.0.0.1:8000/s/enRM13kYPnvaOI72nSWDsbMhI4Q8ZZvK/download",
    "max_downloads": 1,
    "is_one_time": true,
    "download_count": 0,
    "expires_at": "2026-08-19T23:34:35Z",
    "is_password_protected": false
  }
}
```

---

### 5.6 Delete File
- **Endpoint**: `DELETE /api/v1/files/{id}`
- **Required Scope**: `files:delete`
- **Query Parameters**:
  - `force` *(optional)*: `true` for permanent purge from disk, or `false` for soft trash (default: `false`).

---

## 6. Universal Share Management API

Manage and monitor all file sharing links, private user delegations, and anonymous transfers programmatically.

### 6.1 List All Shares
List all active, expired, or download-locked shares created by your account.

- **Endpoint**: `GET /api/v1/shares`
- **Required Scope**: `files:read`
- **Query Parameters**:
  - `status` *(optional)*: Filter by status (`all`, `active`, `expired`, `locked`).
  - `page` *(optional)*: Page number.
  - `per_page` *(optional)*: Results per page (default: `20`).

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/shares?status=active" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

---

### 6.2 Create a Share (Public, Private, or Anonymous)
Create a new share link with fine-grained access policies.

- **Endpoint**: `POST /api/v1/shares`
- **Required Scope**: `files:write`
- **Request Body (JSON)**:
```json
{
  "file_id": "eyJpdiI6...",
  "share_type": "public_link",
  "max_downloads": 5,
  "expires_in_minutes": 1440,
  "password": "PIN9876Password",
  "is_anonymous": true,
  "recipient_email": "user@example.com"
}
```

---

### 6.3 Get Share Details
Inspect live telemetry, download counts, expiration, and lock status for a share.

- **Endpoint**: `GET /api/v1/shares/{id}`
- **Required Scope**: `files:read`

---

### 6.4 Update Active Share Settings
Modify duration, adjust download limits, reset download counters, or update passcodes on an active share.

- **Endpoint**: `PUT /api/v1/shares/{id}`
- **Required Scope**: `files:write`
- **Request Body (JSON)**:
```json
{
  "max_downloads": 10,
  "reset_download_count": true,
  "expires_in_minutes": 60,
  "password": "NewSecretPIN",
  "clear_password": false,
  "is_anonymous": true
}
```

---

### 6.5 Revoke / Delete Share
Immediately revoke access to a share link so all existing recipients lose access.

- **Endpoint**: `DELETE /api/v1/shares/{id}`
- **Required Scope**: `files:delete`

---

## 7. Link & Bookmark API

### 6.1 List Links
- **Endpoint**: `GET /api/v1/links`
- **Required Scope**: `links:read`
- **Query Parameters**: `q`, `category_id`, `tag`, `page`, `per_page`.

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/links?tag=dev" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

---

### 6.2 Create Bookmark Link
Creates a bookmark. Automatically scrapes page metadata and favicons.

- **Endpoint**: `POST /api/v1/links`
- **Required Scope**: `links:write`
- **JSON Body**:
```json
{
  "url": "https://laravel.com/docs",
  "title": "Laravel Documentation",
  "notes": "Framework reference guide",
  "category_id": 1,
  "tags": "php, laravel, backend"
}
```

#### cURL Example:
```bash
curl -X POST "http://127.0.0.1:8000/api/v1/links" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "url": "https://laravel.com/docs",
    "title": "Laravel Documentation",
    "tags": "php, laravel"
  }'
```

---

### 6.3 Update Bookmark Link
- **Endpoint**: `PUT /api/v1/links/{id}`
- **Required Scope**: `links:write`

---

### 6.4 Delete Bookmark Link
- **Endpoint**: `DELETE /api/v1/links/{id}`
- **Required Scope**: `links:delete`

---

## 7. Category & Tag Classification API

### 7.1 List Categories
Retrieve categories and subcategory tags.

- **Endpoint**: `GET /api/v1/categories`
- **Required Scope**: `categories:read`
- **Query Parameters**: `search` (or `q`), `type`, `only_hidden`, `all`, `per_page`, `page`.

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/categories?all=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

---

### 7.2 Create Category
- **Endpoint**: `POST /api/v1/categories`
- **Required Scope**: `categories:write`
- **JSON Body**:
```json
{
  "title": "Cloud Infrastructure",
  "type": "development",
  "description": "DevOps configurations and scripts",
  "categories": ["AWS", "Docker", "Terraform"],
  "is_hidden": false
}
```

---

### 7.3 Update Category
- **Endpoint**: `PUT /api/v1/categories/{id}`
- **Required Scope**: `categories:write`

---

### 7.4 Delete Category
- **Endpoint**: `DELETE /api/v1/categories/{id}`
- **Required Scope**: `categories:delete`

---

## 8. To-Dos & Task Management API

### 8.1 List To-Do Tasks
- **Endpoint**: `GET /api/v1/todos`
- **Required Scope**: `todos:read`
- **Query Parameters**: `search` (or `q`), `pending_only`, `completed_only`, `starred_only`, `collection_id`, `page`, `per_page`.

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/todos?pending_only=1" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

---

### 8.2 Create Task
- **Endpoint**: `POST /api/v1/todos`
- **Required Scope**: `todos:write`
- **JSON Body**:
```json
{
  "title": "Review REST API integration endpoints",
  "notes": "Verify scopes, token validation, and latency",
  "due_date": "2026-08-25",
  "is_starred": true,
  "is_hidden": false
}
```

---

### 8.3 Update / Toggle Task Completion
- **Endpoint**: `PUT /api/v1/todos/{id}`
- **Required Scope**: `todos:write`
- **JSON Body**:
```json
{
  "is_completed": true
}
```

---

### 8.4 Delete Task
- **Endpoint**: `DELETE /api/v1/todos/{id}`
- **Required Scope**: `todos:delete`

---

## 9. Password & Credential Vault API

> 🔒 **Security Notice**: All passwords in FileFusion are stored with military-grade AES-256 encryption. Passwords returned by index endpoints are always masked (`••••••••`).

### 7.1 List Vault Items (Masked)
- **Endpoint**: `GET /api/v1/vault`
- **Required Scope**: `vault:read`

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/vault" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

#### Success Response:
```json
{
  "success": true,
  "data": [
    {
      "id": 12,
      "title": "AWS Production Root",
      "url": "https://aws.amazon.com",
      "username": "aws-admin",
      "email": "devops@company.com",
      "password": "••••••••",
      "notes": "Main production account"
    }
  ]
}
```

---

### 7.2 Just-In-Time (JIT) Password Reveal
Decrypts and reveals the cleartext password.

- **Endpoint**: `POST /api/v1/vault/reveal/{id}`
- **Required Scope**: `vault:reveal`

#### cURL Example:
```bash
curl -X POST "http://127.0.0.1:8000/api/v1/vault/reveal/12" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

#### Success Response (`200 OK`):
```json
{
  "success": true,
  "id": 12,
  "title": "AWS Production Root",
  "username": "aws-admin",
  "password": "SecretPlainPassword#2026",
  "revealed_at": "2026-08-18T23:30:00Z"
}
```

---

### 7.3 Store New Password in Vault
- **Endpoint**: `POST /api/v1/vault`
- **Required Scope**: `vault:write`
- **JSON Body**:
```json
{
  "title": "GitHub Enterprise",
  "url": "https://github.com",
  "username": "octocat",
  "email": "octo@github.com",
  "password": "SuperSecretPassword123!",
  "notes": "Personal access token stored in note"
}
```

#### cURL Example:
```bash
curl -X POST "http://127.0.0.1:8000/api/v1/vault" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -d '{
    "title": "GitHub Enterprise",
    "username": "octocat",
    "password": "SuperSecretPassword123!"
  }'
```

---

### 7.4 Delete Vault Item
- **Endpoint**: `DELETE /api/v1/vault/{id}`
- **Required Scope**: `vault:delete`

---

## 8. Admin & System Health API

*(Restricted to Administrator tokens)*

### 8.1 System Health & Storage Pool Telemetry
- **Endpoint**: `GET /api/v1/admin/system/health`
- **Required Scope**: `admin:system`

#### cURL Example:
```bash
curl -X GET "http://127.0.0.1:8000/api/v1/admin/system/health" \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_API_TOKEN"
```

#### Success Response:
```json
{
  "success": true,
  "system": {
    "php_version": "8.3.14",
    "laravel_version": "11.x",
    "environment": "production",
    "database": "mysql",
    "total_users": 5,
    "active_users": 5,
    "storage_used_bytes": 1048576,
    "storage_used_formatted": "1 MB",
    "timestamp": "2026-08-18T23:32:00Z"
  }
}
```

---

### 8.2 List & Filter Users (Admin)
- **Endpoint**: `GET /api/v1/admin/users`
- **Required Scope**: `admin:users`

---

### 8.3 Provision New User (Admin)
- **Endpoint**: `POST /api/v1/admin/users`
- **Required Scope**: `admin:users`
- **JSON Body**:
```json
{
  "name": "Jane Developer",
  "email": "jane@company.com",
  "username": "janedev",
  "password": "SecurePassword123!",
  "role": "pro_user",
  "quota_gb": 100,
  "api_access_enabled": true
}
```

---

## 9. Error Handling & Status Codes

All responses follow standard HTTP semantics with structured JSON:

```json
{
  "success": false,
  "message": "Human readable error description."
}
```

| HTTP Code | Description | Reason |
| :--- | :--- | :--- |
| `400 Bad Request` | Missing or invalid request parameters | Validation error |
| `401 Unauthorized` | Missing, invalid, or expired API token | Bad Token header |
| `403 Forbidden` | API access revoked by Admin, or missing required token scope | `canUseApi() === false` or insufficient scope |
| `404 Not Found` | Requested resource not found or belongs to another user | Target does not exist |
| `413 Payload Too Large`| Uploaded file exceeds quota or system max file size | Storage limit reached |
| `500 Server Error` | Unexpected backend error | Check system logs |

---

## 10. AI / MCP Server Configuration

You can connect FileFusion directly to **Cursor**, **Claude Desktop**, or **Antigravity** using Model Context Protocol (MCP).

### `claude_desktop_config.json` / `mcp_config.json`:
```json
{
  "mcpServers": {
    "filefusion": {
      "command": "node",
      "args": ["/path/to/filefusion-mcp-server/index.js"],
      "env": {
        "FILEFUSION_BASE_URL": "http://127.0.0.1:8000/api/v1",
        "FILEFUSION_API_TOKEN": "YOUR_PERSONAL_ACCESS_TOKEN"
      }
    }
  }
}
```
