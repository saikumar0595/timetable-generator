# Linux VPS Deployment Guide (Ubuntu/Debian)

This guide provides step-by-step instructions to host **ChronoGen** on a standard Linux VPS.

## 1. System Requirements
- Ubuntu 22.04 LTS or Debian 11+
- Public IP Address
- Domain name (optional, but recommended for SSL)

## 2. Install Dependencies

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx php-fpm php-mysqli php-mbstring python3 python3-pip git certbot python3-certbot-nginx
```

## 3. Clone and Prepare the Project

```bash
cd /var/www
sudo git clone <your-repository-url> chronogen
sudo chown -R www-data:www-data /var/www/chronogen
sudo chmod -R 755 /var/www/chronogen
```

## 4. Configure Nginx

Create a new site configuration:
`sudo nano /etc/nginx/sites-available/chronogen`

Paste the following (replace `your-domain.com` or use your IP):

```nginx
server {
    listen 80;
    server_name your-domain.com; # Or your server IP
    root /var/www/chronogen/saoo;
    index index.php index.html login.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock; # Adjust version if needed
    }

    location ~ /\.ht {
        deny all;
    }
}
```

Enable the site and test Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/chronogen /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 5. Set Up the Alert Daemon (Background Process)

Create a systemd service to keep the alert daemon running:
`sudo nano /etc/systemd/system/chronogen-alert.service`

Paste the following:

```unit
[Unit]
Description=ChronoGen Alert Daemon
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/chronogen/saoo
ExecStart=/usr/bin/php /var/www/chronogen/saoo/alert_daemon.php
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

Enable and start the service:
```bash
sudo systemctl daemon-reload
sudo systemctl enable chronogen-alert
sudo systemctl start chronogen-alert
```

## 6. (Optional) Enable SSL with Certbot

```bash
sudo certbot --nginx -d your-domain.com
```

## 7. Troubleshooting

- **Logs**:
  - Nginx: `/var/log/nginx/error.log`
  - Alert Daemon: `journalctl -u chronogen-alert`
  - App Logs: `/var/www/chronogen/logs/`
- **Permissions**: Ensure `www-data` has write access to `logs/`, `uploads/`, and `timetable-generator/input.json`.

```bash
sudo chown -R www-data:www-data /var/www/chronogen/logs
sudo chown -R www-data:www-data /var/www/chronogen/uploads
sudo chown www-data:www-data /var/www/chronogen/timetable-generator/input.json
```
