# Logs Directory

This directory contains application error logs.

## Log Files

- `error.log` - Application errors, exceptions, and custom error logs
- `php-errors.log` - PHP native error log

## Log Format

```
[YYYY-MM-DD HH:MM:SS] Error Type: Error message in /path/to/file.php:123
Stack trace:
...
--------------------------------------------------------------------------------
```

## Viewing Logs

```bash
# View recent errors
tail -f backend/storage/logs/error.log

# View last 100 lines
tail -n 100 backend/storage/logs/error.log

# Search for specific errors
grep "Exception" backend/storage/logs/error.log
```

## Log Rotation

For production, consider setting up log rotation to prevent log files from growing too large.

## Permissions

Ensure the logs directory is writable by the web server:
```bash
chmod 755 backend/storage/logs
chmod 666 backend/storage/logs/*.log
```
