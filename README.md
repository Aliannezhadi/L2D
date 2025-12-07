# L2D
L2D (Link-to-Download) is a secure and lightweight PHP tool that automatically downloads files from a given URL and saves them to a predefined directory on your server. It includes URL validation, safe filename handling, size limits, and optional CLI or browser-based execution. Perfect for servers, automation workflows, and reliable file fetching.

📦 L2D — Link-to-Download

A secure PHP tool for downloading files from a URL directly into your server.

🚀 About

L2D (Link-to-Download) is a lightweight and secure PHP script that fetches files from a URL and stores them inside a predefined directory.
Perfect for automation tasks, cron jobs, and backend workflows.

🛠 Usage
1. Run from browser
```https://yourdomain.com/secure_downloader.php?url=https://example.com/file.zip```

2. Run from CLI
```php secure_downloader.php "https://example.com/file.zip"```


The file will be saved in your configured download directory.

⚙ Configuration

Inside secure_downloader.php, you can edit:

```
const DOWNLOAD_DIR = __DIR__ . '/downloads';
const MAX_FILE_SIZE_BYTES = 50 * 1024 * 1024; // 50 MB
```
🔐 Usage (with Token & Logging)
1. Run in Browser

You must provide both url and token:
```
https://yourdomain.com/secure_downloader.php?url=https://example.com/file.zip&token=YOUR_TOKEN
```

If the token is missing or incorrect, the request will be rejected.

2. Run in CLI (Recommended for Cron Jobs)

You must pass the token through an environment variable:
```
L2D_TOKEN=YOUR_TOKEN php secure_downloader.php "https://example.com/file.zip"
```

This method is safer than exposing the token in the command line.

3. Log File

All events (successes and errors) are automatically written to:
```
l2d.log
```

Each entry includes:

Timestamp

Status (OK / ERROR)

Context (CLI / IP address)

File URL

Additional details

Message

Example log line:
```
[2025-01-01 14:33:12] [OK] [CLI] URL="https://example.com/file.zip" EXTRA="SavedTo=/path/file.zip" MESSAGE="Download successful"
```

To disable logging, set:
```
const ENABLE_LOGGING = false;
```
4. Security Token

The script requires a valid token for both Web and CLI usage.

Configure it inside the script:
```
const SECURITY_TOKEN = 'CHANGE_ME_TO_A_LONG_RANDOM_TOKEN';
```

⚠️ For security reasons, ALWAYS use a long random token.
Example token generator:
```
openssl rand -hex 32
```
5. Recommended Cron Usage
```
L2D_TOKEN=YOUR_TOKEN php /path/to/secure_downloader.php "https://example.com/file.zip"
```

Add to cron:
```
0 * * * * L2D_TOKEN=YOUR_TOKEN php /home/username/L2D/secure_downloader.php "https://site.com/file.zip"
```

You may also restrict allowed hosts or add extra security rules.

📁 Folder Structure/
```
├── downloads/            # stored files

└── secure_downloader.php
```

📄 License

This project is free to use. You may add any license you prefer (MIT recommended).

🤝 Contributions

Pull requests are welcome.
Feel free to submit issues, fixes, or improvements.

⭐ Support

If you find this useful, please ⭐ the repository.
