# Storage Directory Permissions

## Current Setup

The storage directories require write permissions for the web server user (www-data).

### Directory Structure
```
backend/storage/
├── uploads/     - User uploaded files (777)
├── trash/        - Deleted files (777)
└── logs/         - Application logs (775)
```

## Permissions

### Current Permissions (Development)
- `storage/`: 777 (readable, writable, executable by all)
- `storage/uploads/`: 777
- `storage/trash/`: 777
- `storage/logs/`: 775

### Recommended Production Permissions

For better security in production, use:

```bash
# Set ownership to web server user
sudo chown -R www-data:www-data backend/storage/

# Set permissions
sudo chmod 755 backend/storage/
sudo chmod 775 backend/storage/uploads/
sudo chmod 775 backend/storage/trash/
sudo chmod 755 backend/storage/logs/
```

Or if running as a specific user:

```bash
# Add www-data to your user's group
sudo usermod -a -G www-data arslan

# Set group ownership
sudo chgrp -R www-data backend/storage/

# Set permissions with group write
sudo chmod 775 backend/storage/
sudo chmod 775 backend/storage/uploads/
sudo chmod 775 backend/storage/trash/
sudo chmod 755 backend/storage/logs/
```

## Troubleshooting

### Permission Denied Errors

If you get "Permission denied" errors:

1. **Check current permissions:**
   ```bash
   ls -la backend/storage/
   ```

2. **Check web server user:**
   ```bash
   ps aux | grep php-fpm | head -1
   ```

3. **Fix permissions (quick fix):**
   ```bash
   chmod 777 backend/storage/uploads/
   chmod 777 backend/storage/trash/
   ```

4. **Verify writability:**
   ```bash
   php -r "echo is_writable('backend/storage/uploads/') ? 'Writable' : 'Not writable';"
   ```

### SELinux (if enabled)

If SELinux is blocking access:

```bash
# Check SELinux context
ls -Z backend/storage/

# Set proper context
sudo chcon -R -t httpd_sys_rw_content_t backend/storage/uploads/
sudo chcon -R -t httpd_sys_rw_content_t backend/storage/trash/
```

## Notes

- The FileController automatically creates directories if they don't exist
- The FileController checks writability and provides error messages
- Uploaded files are set to 644 permissions (readable by all, writable by owner)
