# 🚀 FileFusion REST API & Developer Hub (v1)

Welcome to the **FileFusion Developer Documentation**. This folder contains the complete REST API specification, ready-to-import Postman collections, Personal Access Token (PAT) guides, and integration tutorials for client applications and AI Model Context Protocol (MCP) servers.

---

## 📁 Repository Structure

```text
api_documentation/
├── README.md                                  # API Overview, Quickstart & Environment Setup
├── API_DOCUMENTATION.md                       # Comprehensive API Reference & Endpoint Docs
└── FileFusion_API_v1.postman_collection.json  # Complete Postman / Thunder Client / Insomnia Collection
```

---

## ⚡ Quick Start: Testing the API

### 1. In-Browser API Playground
Log into your FileFusion Admin / Developer account and open the live testing console:
👉 **`/api-tester`** (or **`/api-console`**)

### 2. Postman / Thunder Client Collection
1. Open **Postman** (or **Thunder Client** in VS Code).
2. Click **Import** $\to$ Select `api_documentation/FileFusion_API_v1.postman_collection.json`.
3. Set your collection variables:
   - `base_url`: `http://127.0.0.1:8000/api/v1` (or `https://your-domain.com/api/v1`)
   - `filefusion_token`: `ff_live_YOUR_TOKEN_HERE`

### 3. Generate Personal Access Tokens (PAT)
1. Go to **Settings $\to$ Developer & API Tokens** (`/panel/settings`).
2. Click **Create Personal Access Token**.
3. Select your token abilities/scopes (`*`, `files:read`, `vault:reveal`, `links:write`, etc.).
4. Copy your secret key (`ff_live_...`).

---

## 📑 API Highlights & Capabilities

- **🔐 Zero-Knowledge Envelope Encryption**: Files and credentials remain AES-256 encrypted at rest; decrypted streams are served on-demand via authenticated tokens.
- **🛡️ Anti-Enumeration Identifiers**: All resource IDs are tamper-proof Laravel-encrypted strings (anti-IDOR).
- **🌐 Universal Sharing Engine**: Programmatically generate 1-time self-destructing links, password-protected guest links, anonymous transfers, and duration-expiring URLs.
- **🤖 MCP & AI Agent Ready**: Seamlessly bridge your cloud storage and password vault to Cursor, Claude Desktop, and Antigravity agents.

For full endpoint documentation and request/response payloads, see [API_DOCUMENTATION.md](file:///d:/Ashish/Web/filesystem/New%20Filesystem/laravel/api_documentation/API_DOCUMENTATION.md).
