# Google Drive Manager - Production MVP

A production-ready Google Drive-like file management system built with Core PHP, MongoDB, and strict OOP principles.

## Features

- ✅ Authentication & Authorization (Custom token-based)
- ✅ Files CRUD operations
- ✅ Folders CRUD operations (nested folders supported)
- ✅ File sharing with permissions (read/write)
- ✅ Trash system (soft delete)
- ✅ Restore and permanent delete
- ✅ Token-based API authentication (database-driven)
- ✅ Complete separation of backend and frontend

## Tech Stack

### Backend
- **Core PHP** (No frameworks)
- **MongoDB** (PHP MongoDB extension)
- **MVC Architecture**
- **Strict OOP** principles
- **RESTful APIs** with JSON responses

### Frontend
- **HTML5** + **Bootstrap 5**
- **JavaScript** (Vanilla JS)
- **Axios** for API communication
- **Material Design Icons**

## Project Structure

```
google-drive/
├── backend/
│   ├── app/
│   │   ├── Core/           # Core classes (Controller, Model, Router, Database, Request)
│   │   ├── Controllers/    # Application controllers
│   │   ├── Models/         # Data models
│   │   ├── Middleware/     # Authentication middleware
│   │   ├── Traits/         # Reusable traits
│   │   ├── Helpers/         # Helper functions
│   │   └── Services/       # Business logic services
│   ├── config/             # Configuration files
│   ├── routes/              # API routes
│   ├── public/              # Public entry point
│   └── storage/             # File storage
│       ├── uploads/         # Uploaded files
│       └── trash/           # Trashed files
├── frontend/                # Frontend files
└── login.php, register.php  # Auth pages
```

## Installation

### Prerequisites

1. **PHP 7.4+** with MongoDB extension
2. **MongoDB** server running
3. **Web server** (Apache/Nginx)

### PHP MongoDB Extension

Install the MongoDB PHP extension:

```bash
# Ubuntu/Debian
sudo pecl install mongodb
sudo apt-get install php-mongodb

# Or via PECL
pecl install mongodb
```

Add to `php.ini`:
```ini
extension=mongodb.so
```

### Configuration

1. **Database Configuration**

Edit `backend/config/database.php`:

```php
return [
    'host' => 'localhost',
    'port' => 27017,
    'database' => 'google_drive',
    'username' => '',  // Leave empty if no auth
    'password' => '',   // Leave empty if no auth
];
```

2. **Application Configuration**

Edit `backend/config/app.php` if needed:

```php
return [
    'app_name' => 'Google Drive Manager',
    'app_url' => 'http://localhost',
    'storage_path' => __DIR__ . '/../storage',
    'uploads_path' => __DIR__ . '/../storage/uploads',
    'trash_path' => __DIR__ . '/../storage/trash',
    'token_expiry' => 86400 * 7, // 7 days
    'max_file_size' => 100 * 1024 * 1024, // 100MB
];
```

3. **Web Server Configuration**

#### Apache (.htaccess)

Create `.htaccess` in project root:

```apache
RewriteEngine On
RewriteBase /

# API routes
RewriteCond %{REQUEST_URI} ^/api/
RewriteRule ^api/(.*)$ /backend/public/index.php/api/$1 [L,QSA]

# Frontend routes
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ /frontend/$1 [L]
```

#### Nginx

```nginx
location /api/ {
    rewrite ^/api/(.*)$ /backend/public/index.php/api/$1 last;
}

location / {
    try_files $uri $uri/ /frontend/$uri;
}
```

4. **Set Permissions**

```bash
chmod -R 755 /var/www/google-drive
chmod -R 777 /var/www/google-drive/backend/storage
```

## API Endpoints

### Authentication

- `POST /api/auth/register` - Register new user
- `POST /api/auth/login` - Login user
- `POST /api/auth/logout` - Logout user
- `GET /api/auth/me` - Get current user

### Files

- `POST /api/files/upload` - Upload file
- `GET /api/files` - List files
- `GET /api/files/{id}` - Get file details
- `GET /api/files/{id}/download` - Download file
- `PUT /api/files/{id}` - Update file
- `DELETE /api/files/{id}` - Delete file (trash)
- `POST /api/files/{id}/restore` - Restore from trash
- `DELETE /api/files/{id}/permanent` - Permanent delete
- `GET /api/files/storage/stats` - Get storage stats

### Folders

- `POST /api/folders` - Create folder
- `GET /api/folders` - List folders
- `GET /api/folders/{id}` - Get folder details
- `PUT /api/folders/{id}` - Update folder
- `DELETE /api/folders/{id}` - Delete folder (trash)
- `POST /api/folders/{id}/restore` - Restore from trash
- `DELETE /api/folders/{id}/permanent` - Permanent delete
- `GET /api/folders/{id}/path` - Get folder path

### Sharing

- `POST /api/shares` - Share resource
- `GET /api/shares/resource/{resourceId}/{resourceType}` - Get resource shares
- `GET /api/shares/with-me` - Get shared with me
- `GET /api/shares/by-me` - Get shared by me
- `DELETE /api/shares/{id}` - Remove share

## Authentication

The system uses custom token-based authentication:

1. On login, a cryptographically secure token is generated
2. Token is stored in MongoDB with:
   - User ID
   - IP address
   - User agent
   - Expiration date
3. Token is sent in `Authorization: Bearer {token}` header
4. Middleware validates token on each request

## Usage

1. **Register/Login**: Visit `/register.php` or `/login.php`
2. **Dashboard**: After login, access `/frontend/dashboard.html`
3. **Upload Files**: Click "Upload" button
4. **Create Folders**: Click "New Folder" button
5. **Share Files**: Use the share option in file menu
6. **Manage Trash**: Access trash from sidebar

## Security Features

- Password hashing (bcrypt)
- Token-based authentication
- IP and User-Agent tracking
- Token expiration
- File ownership validation
- Share permission system

## Development

### Code Standards

- **Strict OOP**: All code must be object-oriented
- **MVC Pattern**: Clear separation of concerns
- **No Global Variables**: Everything in classes
- **No Procedural Code**: All functions in classes
- **PSR-4 Autoloading**: Namespace-based autoloading

### Adding New Features

1. Create Model in `app/Models/`
2. Create Controller in `app/Controllers/`
3. Add routes in `routes/api.php`
4. Update frontend in `frontend/`

## License

This is a production MVP built for demonstration purposes.
