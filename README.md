# L2D
L2D (Link-to-Download) is a secure and lightweight PHP tool that automatically downloads files from a given URL and saves them to a predefined directory on your server. It includes URL validation, safe filename handling, size limits, and optional CLI or browser-based execution. Perfect for servers, automation workflows, and reliable file fetching.

📦 L2D — Link-to-Download

A secure, lightweight PHP script for downloading files from a URL directly into your server.

🚀 About

L2D (Link-to-Download) is a simple but secure PHP tool that automatically downloads any file from a given URL and stores it safely inside a predefined directory.
It’s ideal for automation, cron jobs, backend processing, or any scenario where you need a server-side file fetcher.

🔐 Features

✔ Secure URL validation (HTTP/HTTPS only)

✔ Safe filename sanitizing

✔ File size limit for protection

✔ Prevents directory traversal

✔ Supports both CLI & Browser execution

✔ Custom download directory

✔ cURL-based lightweight downloader

🛠 Usage
1. Run from browser
https://yourdomain.com/secure_downloader.php?url=https://example.com/file.zip

2. Run from CLI
php secure_downloader.php "https://example.com/file.zip"


The file will be automatically saved inside your configured download directory.

⚙ Configuration

Inside the script, you can edit:

const DOWNLOAD_DIR = __DIR__ . '/downloads';
const MAX_FILE_SIZE_BYTES = 50 * 1024 * 1024; // 50MB


You may also restrict allowed hosts or enable additional protections.

📁 Folder Structure
/
├── downloads/       # stored files
└── secure_downloader.php

📄 License

This project is free to use. You may add a license of your choice (MIT recommended).

🤝 Contributions

Pull requests are welcome! Feel free to submit improvements or open issues.

⭐ Support

If you like this project, please ⭐ the repo to support development.
