# 📦 L2D — Link-to-Download  
🌐 Languages: [English](README.md) | [فارسی](README-FA.md)

**L2D** is a secure and lightweight PHP script that downloads files from a given URL and stores them in a predefined directory on your server.  
Perfect for automation, cron jobs, backend workflows, and server-side file fetching.

---

## 🚀 Features
- ✔ Secure URL validation (HTTP/HTTPS)  
- ✔ Safe filename handling  
- ✔ File size limit  
- ✔ No redirects (safer)  
- ✔ Optional allowed host restriction  
- ✔ Token-protected (browser + CLI)  
- ✔ Logging system (success & errors)  
- ✔ Works in both **CLI** and **Browser**  
- ✔ cURL-based with SSL verification  

---

## 📂 Folder Structure
```
/
├── downloads/              # downloaded files
├── l2d.log                 # log file
└── secure_downloader.php   # main script
```

---

## 🔧 Configuration
All settings can be edited inside `secure_downloader.php`:

```php
const DOWNLOAD_DIR = __DIR__ . '/downloads';
const MAX_FILE_SIZE_BYTES = 50 * 1024 * 1024; // 50 MB
const SECURITY_TOKEN = 'CHANGE_ME_TO_A_LONG_RANDOM_TOKEN';
const ENABLE_LOGGING = true;
```

⚠️ **Always replace SECURITY_TOKEN with a long random string.**

Generate a strong token:

```bash
openssl rand -hex 32
```

---

# 🔐 Token Security

### Web Requests
Add token to the URL:

```
https://yourdomain.com/secure_downloader.php?url=https://example.com/file.zip&token=YOUR_TOKEN
```

### CLI Requests (recommended)
```bash
L2D_TOKEN=YOUR_TOKEN php secure_downloader.php "https://example.com/file.zip"
```

---

# 🛠 Usage

### 1️⃣ Browser
```
secure_downloader.php?url=https://example.com/file.zip&token=YOUR_TOKEN
```

### 2️⃣ CLI
```
L2D_TOKEN=YOUR_TOKEN php secure_downloader.php "https://example.com/file.zip"
```

---

# 📝 Logging

If enabled, all events are logged in:

```
l2d.log
```

Example log entry:

```
[2025-02-01 11:23:54] [OK] [CLI] URL="https://example.com/file.zip" EXTRA="SavedTo=/path/file.zip" MESSAGE="Download successful"
```

Disable logging:

```php
const ENABLE_LOGGING = false;
```

---

# 🕒 Cron Job Example

Run every hour:

```bash
0 * * * * L2D_TOKEN=YOUR_TOKEN php /path/to/secure_downloader.php "https://example.com/file.zip"
```

---

# 🧰 Optional — Allowed Host List
Restrict downloads to specific hosts:

```php
const ALLOWED_HOSTS = ['example.com', 'cdn.example.com'];
```

Allow all:

```php
const ALLOWED_HOSTS = [];
```

---

# 🧩 Requirements
- PHP 7.4+  
- cURL enabled  
- SSL enabled  
- Writable download directory  

---

# 📄 License
You may use any license. MIT recommended.

---

# ⭐ Support
If you find this project useful, please ⭐ the repo.
