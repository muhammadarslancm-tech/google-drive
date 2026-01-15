# Error Handling & Logging

## Overview

The application now has comprehensive error handling that:
- ✅ Catches all PHP errors, exceptions, and fatal errors
- ✅ Returns JSON responses (never blank)
- ✅ Logs all errors to files
- ✅ Provides detailed error information

## Error Logs Location

All errors are logged to:
- **Application Errors:** `backend/storage/logs/error.log`
- **PHP Errors:** `backend/storage/logs/php-errors.log`

## Error Handler

The `ErrorHandler` class (`backend/app/Helpers/ErrorHandler.php`) handles:

1. **PHP Errors** (E_ERROR, E_WARNING, E_NOTICE, etc.)
2. **Exceptions** (uncaught exceptions)
3. **Fatal Errors** (E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE)

## Error Response Format

All errors return JSON:

```json
{
  "success": false,
  "message": "Error message",
  "error": {
    "message": "Detailed error",
    "file": "/path/to/file.php",
    "line": 123
  }
}
```

## Log Format

```
[2026-01-15 12:30:45] Exception: Error message in /path/to/file.php:123
Stack trace:
#0 /path/to/file.php(45): function()
#1 /path/to/file.php(67): otherFunction()
--------------------------------------------------------------------------------
```

## Viewing Logs

```bash
# Real-time log monitoring
tail -f backend/storage/logs/error.log

# Last 50 errors
tail -n 50 backend/storage/logs/error.log

# Search for specific errors
grep "Database" backend/storage/logs/error.log

# Count errors
wc -l backend/storage/logs/error.log
```

## Error Types Handled

### 1. PHP Errors
- Syntax errors
- Runtime errors
- Warnings
- Notices
- Deprecated warnings

### 2. Exceptions
- Uncaught exceptions
- Controller exceptions
- Model exceptions
- Database exceptions

### 3. Fatal Errors
- Fatal PHP errors
- Core errors
- Compile errors
- Parse errors

## Output Buffering

The application uses output buffering to:
- Prevent blank responses
- Catch unexpected output
- Ensure clean JSON responses

## Configuration

Error logging is configured in `backend/bootstrap.php`:
- `error_reporting(E_ALL)` - Report all errors
- `ini_set('display_errors', 0)` - Don't display errors
- `ini_set('log_errors', 1)` - Log errors
- Custom error handler registered

## Testing Error Handling

### Test PHP Error
```php
// This will be caught and logged
trigger_error("Test error", E_USER_ERROR);
```

### Test Exception
```php
// This will be caught and logged
throw new Exception("Test exception");
```

### Test Fatal Error
```php
// This will be caught on shutdown
callUndefinedFunction();
```

## Production Considerations

1. **Log Rotation:** Set up log rotation to prevent large log files
2. **Permissions:** Ensure logs directory is writable
3. **Monitoring:** Monitor error logs for issues
4. **Debug Mode:** Set `APP_DEBUG=false` in production

## Troubleshooting

### If errors still return blank:
1. Check log files exist and are writable
2. Verify ErrorHandler is loaded in bootstrap.php
3. Check PHP error_log configuration
4. Verify output buffering is working

### If logs aren't being written:
1. Check directory permissions: `chmod 755 backend/storage/logs`
2. Check file permissions: `chmod 666 backend/storage/logs/*.log`
3. Verify disk space
4. Check PHP error_log setting
