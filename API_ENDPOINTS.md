# API Endpoints Reference

## Base URL
All API endpoints are accessed via: `http://google-drive.test/api/`

## Important Notes
- **All endpoints require `/api/` prefix**
- **Authentication endpoints do NOT require token**
- **All other endpoints require Bearer token in Authorization header**

---

## Authentication Endpoints

### Register User
- **URL:** `POST /api/auth/register`
- **Auth:** Not required
- **Body:**
```json
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "password": "password123"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "User registered successfully",
  "data": {
    "user": {...},
    "token": "abc123...",
    "token_type": "Bearer"
  }
}
```

### Login
- **URL:** `POST /api/auth/login`
- **Auth:** Not required
- **Body:**
```json
{
  "email": "john@example.com",
  "password": "password123"
}
```
- **Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "user": {...},
    "token": "abc123...",
    "token_type": "Bearer"
  }
}
```

### Logout
- **URL:** `POST /api/auth/logout`
- **Auth:** Required (Bearer token)

### Get Current User
- **URL:** `GET /api/auth/me`
- **Auth:** Required (Bearer token)

---

## Health & Debug

### Health Check
- **URL:** `GET /api/health`
- **Auth:** Not required

### Debug Info
- **URL:** `GET /api/debug`
- **Auth:** Not required
- **Shows:** Server variables and request info

---

## Common Issues

### ❌ Wrong: `/api/login`
### ✅ Correct: `/api/auth/login`

### ❌ Wrong: `GET /api/auth/login`
### ✅ Correct: `POST /api/auth/login`

### ❌ Wrong: Missing `/api/` prefix
### ✅ Correct: Always use `/api/` prefix

---

## Postman Setup

1. **Base URL:** `http://google-drive.test`
2. **For Auth endpoints:** No headers needed (except Content-Type)
3. **For Protected endpoints:** Add header:
   - Key: `Authorization`
   - Value: `Bearer {your_token_here}`

## Example Postman Request

**Register:**
```
POST http://google-drive.test/api/auth/register
Content-Type: application/json

{
  "first_name": "Test",
  "last_name": "User",
  "email": "test@example.com",
  "password": "test1234"
}
```

**Login:**
```
POST http://google-drive.test/api/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "test1234"
}
```
