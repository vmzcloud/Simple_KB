# Server Configuration Guide

This document explains how to configure your web server for optimal performance with the Simple Knowledge Base.

## Apache Configuration

### Enabling mod_rewrite

If you're getting the error "Invalid command 'RewriteEngine'", you need to enable mod_rewrite.

#### On Ubuntu/Debian:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

#### On CentOS/RHEL:
```bash
# mod_rewrite is usually enabled by default
# If not, uncomment this line in /etc/httpd/conf/httpd.conf:
# LoadModule rewrite_module modules/mod_rewrite.so
sudo systemctl restart httpd
```

#### On shared hosting:
Contact your hosting provider to enable mod_rewrite for your account.

### Virtual Host Configuration

Add this to your Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    DocumentRoot /var/www/html/Simple_KB
    
    <Directory "/var/www/html/Simple_KB">
        AllowOverride All
        Require all granted
    </Directory>
    
    # Optional: Enable additional modules
    LoadModule rewrite_module modules/mod_rewrite.so
    LoadModule headers_module modules/mod_headers.so
    LoadModule expires_module modules/mod_expires.so
    LoadModule deflate_module modules/mod_deflate.so
</VirtualHost>
```

### Directory Permissions

Make sure your directories have the correct permissions:

```bash
# Make directories writable by web server
sudo chown -R www-data:www-data /var/www/html/Simple_KB
sudo chmod -R 755 /var/www/html/Simple_KB
sudo chmod -R 777 /var/www/html/Simple_KB/data
sudo chmod -R 777 /var/www/html/Simple_KB/uploads
```

## Nginx Configuration

If you're using Nginx instead of Apache, here's a basic configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/html/Simple_KB;
    index index.php;
    
    # Handle PHP files
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Pretty URLs (optional)
    location ~ ^/article/([0-9]+)/?$ {
        try_files $uri /view.php?id=$1;
    }
    
    location ~ ^/search/(.+)/?$ {
        try_files $uri /search.php?q=$1;
    }
    
    location ~ ^/tag/(.+)/?$ {
        try_files $uri /search.php?tag=$1;
    }
    
    # Protect sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ \.(md|txt|log|db)$ {
        deny all;
    }
    
    location ~ config\.php$ {
        deny all;
    }
    
    # Cache static files
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }
    
    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy "strict-origin-when-cross-origin";
}
```

## PHP Configuration

### Required PHP Extensions

Make sure these PHP extensions are installed:
- `sqlite3` (required)
- `pdo_sqlite` (required)
- `gd` (optional, for image processing)
- `fileinfo` (optional, for better file type detection)

### PHP Settings

Update your `php.ini` file with these recommended settings:

```ini
; File upload settings
upload_max_filesize = 10M
post_max_size = 10M
max_file_uploads = 20

; Execution settings
max_execution_time = 300
max_input_vars = 3000
memory_limit = 256M

; Security settings
expose_php = Off
allow_url_fopen = On
allow_url_include = Off

; Session settings
session.cookie_httponly = 1
session.use_strict_mode = 1
```

## Troubleshooting

### Common Issues

1. **"Invalid command 'RewriteEngine'" Error**
   - Enable mod_rewrite (see instructions above)
   - Use the basic `.htaccess` file provided (without rewrite rules)

2. **File Upload Not Working**
   - Check directory permissions for `uploads/` folder
   - Verify PHP upload settings
   - Ensure web server can write to the uploads directory

3. **Database Errors**
   - Check that `data/` directory is writable
   - Verify SQLite3 PHP extension is installed
   - Run the installation script: `install.php`

4. **CSS/JavaScript Not Loading**
   - Check file permissions
   - Verify web server can serve static files
   - Check browser console for 404 errors

5. **Search Not Working**
   - Ensure SQLite FTS5 is available
   - Rebuild search index by running install script
   - Check database file permissions

### Performance Optimization

1. **Enable Gzip Compression**
   - Use mod_deflate (Apache) or gzip module (Nginx)
   - Reduces bandwidth usage significantly

2. **Enable Caching**
   - Set proper cache headers for static files
   - Consider using mod_expires (Apache)

3. **Optimize Images**
   - Compress uploaded images
   - Consider implementing automatic image resizing

4. **Database Optimization**
   - Run `VACUUM` on SQLite database periodically
   - Consider using WAL mode for better concurrency

### Security Checklist

- [ ] Hide sensitive files (.db, config.php, etc.)
- [ ] Set proper directory permissions
- [ ] Enable security headers
- [ ] Keep PHP and web server updated
- [ ] Regularly backup your database
- [ ] Consider using HTTPS in production

## Production Deployment

For production use, consider:

1. **Use HTTPS**
   - Get an SSL certificate
   - Redirect HTTP to HTTPS

2. **Regular Backups**
   - Backup the SQLite database
   - Backup uploaded files

3. **Monitoring**
   - Set up error logging
   - Monitor disk space usage
   - Monitor application performance

4. **Security**
   - Regular security updates
   - Consider using a Web Application Firewall (WAF)
   - Implement rate limiting for API endpoints
