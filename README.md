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
