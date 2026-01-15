# API Routing Fixes

## Issues Fixed

### 1. **Corrected Routes File Path**
- **Before:** `__DIR__ . '/../../routes/api.php'` (incorrect - 2 levels up)
- **After:** `__DIR__ . '/../routes/api.php'` (correct - 1 level up)
- **File:** `backend/public/index.php`

### 2. **Fixed BASE_PATH**
- **Before:** `dirname(__DIR__)` (pointed to project root)
- **After:** `__DIR__` (points to backend directory)
- **File:** `backend/bootstrap.php`
- **Reason:** App classes are in `backend/app/`, so BASE_PATH should be `backend/`

### 3. **Improved Error Handling**
- Added try-catch in Router dispatch
- Added try-catch in index.php entry point
- Better error messages with file/line information
- **File:** `backend/app/Core/Router.php`, `backend/public/index.php`

### 4. **Enhanced Path Processing**
- Improved Request::path() method
- Better handling of empty paths
- Normalized path processing in Router
- **Files:** `backend/app/Core/Request.php`, `backend/app/Core/Router.php`

### 5. **Added Health Check Endpoint**
- `GET /api/health` - Quick API status check
- **File:** `backend/routes/api.php`

### 6. **Made Routes Property Public**
- Changed `private array $routes` to `public array $routes`
- Allows debugging route registration
- **File:** `backend/app/Core/Router.php`

### 7. **Better 404 Error Messages**
- Includes available routes in 404 response
- Shows request path and method
- **File:** `backend/app/Core/Router.php`

## API Endpoints

All endpoints are accessible via `/api/{route}`:

### Authentication
- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user (requires auth)
- `GET /api/auth/me` - Get current user (requires auth)

### Health Check
- `GET /api/health` - API status check

### Files
- `POST /api/files/upload` - Upload file (requires auth)
- `GET /api/files` - List files (requires auth)
- `GET /api/files/{id}` - Get file (requires auth)
- `GET /api/files/{id}/download` - Download file (requires auth)
- `PUT /api/files/{id}` - Update file (requires auth)
- `DELETE /api/files/{id}` - Delete file (requires auth)
- `POST /api/files/{id}/restore` - Restore from trash (requires auth)
- `DELETE /api/files/{id}/permanent` - Permanent delete (requires auth)
- `GET /api/files/storage/stats` - Storage statistics (requires auth)

### Folders
- `POST /api/folders` - Create folder (requires auth)
- `GET /api/folders` - List folders (requires auth)
- `GET /api/folders/{id}` - Get folder (requires auth)
- `PUT /api/folders/{id}` - Update folder (requires auth)
- `DELETE /api/folders/{id}` - Delete folder (requires auth)
- `POST /api/folders/{id}/restore` - Restore from trash (requires auth)
- `DELETE /api/folders/{id}/permanent` - Permanent delete (requires auth)
- `GET /api/folders/{id}/path` - Get folder path (requires auth)

### Shares
- `POST /api/shares` - Share resource (requires auth)
- `GET /api/shares/resource/{resourceId}/{resourceType}` - Get shares (requires auth)
- `GET /api/shares/with-me` - Get shared with me (requires auth)
- `GET /api/shares/by-me` - Get shared by me (requires auth)
- `DELETE /api/shares/{id}` - Remove share (requires auth)

## Testing

### Test Health Endpoint
```bash
curl http://localhost/api/health
```

### Test Registration
```bash
curl -X POST http://localhost/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Test",
    "last_name": "User",
    "email": "test@example.com",
    "password": "test1234"
  }'
```

### Expected Response
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {
      "_id": "...",
      "first_name": "Test",
      "last_name": "User",
      "email": "test@example.com"
    }
  }
}
```

## File Structure

```
backend/
├── app/
│   ├── Core/
│   │   ├── Router.php      # Fixed error handling
│   │   ├── Request.php     # Improved path handling
│   │   └── ...
│   ├── Controllers/
│   │   └── AuthController.php
│   └── ...
├── bootstrap.php           # Fixed BASE_PATH
├── public/
│   └── index.php          # Fixed routes path, added error handling
└── routes/
    └── api.php            # Added health check endpoint
```

## Troubleshooting

### If API returns 404:
1. Check that request goes to `/api/{route}`
2. Verify .htaccess or Nginx is routing correctly
3. Check Router debug output in 404 response

### If API returns 500:
1. Check PHP error logs
2. Verify MongoDB connection
3. Check that all classes are autoloading correctly

### If routes not found:
1. Check `backend/routes/api.php` exists
2. Verify route definitions are correct
3. Check Router is loading routes properly
