# GeoCasa Bohol API Documentation

## Overview

The GeoCasa Bohol API provides RESTful endpoints for mobile app integration using Laravel Sanctum for authentication. This API allows users to manage properties, inquiries, seller requests, and user accounts.

## Base URL

```
http://your-domain.com/api
```

## Authentication

The API uses Laravel Sanctum for authentication. Most endpoints require a Bearer token in the Authorization header.

### Headers

```
Content-Type: application/json
Accept: application/json
Authorization: Bearer {your-token}
```

## Authentication Endpoints

### Register User

**POST** `/auth/register`

Register a new user account.

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+639123456789",
    "address": "123 Main St, Bohol",
    "role": "buyer",
    "device_name": "iPhone 12",
    "broker_license": "file" // Required if role is 'broker'
}
```

**Response:**
```json
{
    "success": true,
    "message": "Registration successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "buyer",
        "phone": "+639123456789",
        "address": "123 Main St, Bohol",
        "profile_picture": null,
        "email_verified_at": null,
        "created_at": "2024-01-01T00:00:00.000000Z",
        "updated_at": "2024-01-01T00:00:00.000000Z"
    },
    "token": "1|abc123..."
}
```

### Login User

**POST** `/auth/login`

Authenticate a user and receive an access token.

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123",
    "device_name": "iPhone 12"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "buyer"
    },
    "token": "1|abc123..."
}
```

### Logout User

**POST** `/auth/logout`

*Requires Authentication*

Revoke the current access token.

**Response:**
```json
{
    "success": true,
    "message": "Logged out successfully"
}
```

### Get Current User

**GET** `/auth/user`

*Requires Authentication*

Get the authenticated user's information.

**Response:**
```json
{
    "success": true,
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "role": "buyer"
    }
}
```

## Property Endpoints

### List Properties

**GET** `/properties`

Get a paginated list of approved properties.

**Query Parameters:**
- `page` (integer): Page number (default: 1)
- `per_page` (integer): Items per page (default: 15, max: 50)
- `property_type` (string): Filter by property type (house, condo, lot, commercial)
- `min_price` (number): Minimum price filter
- `max_price` (number): Maximum price filter
- `location` (string): Filter by location

**Response:**
```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "title": "Beautiful House in Tagbilaran",
            "description": "A lovely 3-bedroom house...",
            "price": 2500000,
            "property_type": "house",
            "status": "approved",
            "bedrooms": 3,
            "bathrooms": 2,
            "area": 120,
            "location": "Tagbilaran City, Bohol",
            "latitude": 9.6496,
            "longitude": 123.8547,
            "featured": false,
            "images": [
                "http://your-domain.com/storage/properties/image1.jpg"
            ],
            "amenities": ["parking", "garden"],
            "broker": {
                "id": 2,
                "name": "Jane Smith",
                "email": "jane@example.com",
                "role": "broker"
            },
            "created_at": "2024-01-01T00:00:00.000000Z",
            "updated_at": "2024-01-01T00:00:00.000000Z"
        }
    ],
    "meta": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75
    }
}
```

### Get Property Details

**GET** `/properties/{id}`

Get detailed information about a specific property.

**Response:**
```json
{
    "success": true,
    "data": {
        "id": 1,
        "title": "Beautiful House in Tagbilaran",
        // ... full property details
    }
}
```

### Search Properties

**GET** `/properties/search`

Search properties with advanced filters.

**Query Parameters:**
- `q` (string): Search query
- `property_type` (string): Property type filter
- `min_price` (number): Minimum price
- `max_price` (number): Maximum price
- `bedrooms` (integer): Number of bedrooms
- `bathrooms` (integer): Number of bathrooms
- `location` (string): Location filter

### Get Featured Properties

**GET** `/properties/featured`

Get a list of featured properties.

### Create Property

**POST** `/properties`

*Requires Authentication (Broker Role)*

Create a new property listing.

**Request Body (multipart/form-data):**
```
title: "Beautiful House in Tagbilaran"
description: "A lovely 3-bedroom house..."
price: 2500000
property_type: "house"
bedrooms: 3
bathrooms: 2
area: 120
location: "Tagbilaran City, Bohol"
latitude: 9.6496
longitude: 123.8547
images[]: file1.jpg
images[]: file2.jpg
amenities[]: "parking"
amenities[]: "garden"
```

## Inquiry Endpoints

### List User Inquiries

**GET** `/inquiries`

*Requires Authentication*

Get inquiries for the authenticated user.

### Create Inquiry

**POST** `/inquiries`

*Requires Authentication*

Create a new property inquiry.

**Request Body:**
```json
{
    "property_id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+639123456789",
    "message": "I'm interested in this property..."
}
```

### Broker Inquiries

**GET** `/inquiries/broker`

*Requires Authentication (Broker Role)*

Get inquiries for the broker's properties.

### Admin Inquiries

**GET** `/inquiries/admin`

*Requires Authentication (Admin Role)*

Get all inquiries (admin only).

## User Management Endpoints

### Update Profile

**PUT** `/user/profile`

*Requires Authentication*

Update the authenticated user's profile.

**Request Body:**
```json
{
    "name": "John Doe Updated",
    "phone": "+639123456789",
    "address": "456 New St, Bohol"
}
```

### Change Password

**PUT** `/user/password`

*Requires Authentication*

Change the authenticated user's password.

**Request Body:**
```json
{
    "current_password": "oldpassword",
    "password": "newpassword",
    "password_confirmation": "newpassword"
}
```

## Seller Request Endpoints

### List Broker Requests

**GET** `/seller-requests/broker`

*Requires Authentication (Broker Role)*

Get seller requests assigned to the broker.

### Submit Seller Request

**POST** `/seller-requests`

Submit a new seller request (public endpoint).

**Request Body (multipart/form-data):**
```
name: "John Seller"
email: "seller@example.com"
phone: "+639123456789"
property_type: "house"
location: "Tagbilaran City, Bohol"
description: "I want to sell my house..."
expected_price: 2000000
documents[]: deed.pdf
documents[]: tax_declaration.pdf
```

### Get Seller Request

**GET** `/seller-requests/{id}`

Get details of a specific seller request.

## Error Responses

All endpoints return consistent error responses:

### Validation Error (422)
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."]
    }
}
```

### Authentication Error (401)
```json
{
    "success": false,
    "message": "Unauthenticated"
}
```

### Authorization Error (403)
```json
{
    "success": false,
    "message": "Insufficient permissions. Required role: broker"
}
```

### Not Found Error (404)
```json
{
    "success": false,
    "message": "Resource not found"
}
```

### Server Error (500)
```json
{
    "success": false,
    "message": "Internal server error"
}
```

## Rate Limiting

- Authentication endpoints: 5 requests per minute
- File upload endpoints: 10 requests per minute
- General API endpoints: 60 requests per minute

## File Upload Security

All file uploads are protected by:
- File type validation (MIME type and extension)
- File size limits (5MB for images, 10MB for documents)
- Virus scanning (if configured)
- Malicious content detection

## CORS Configuration

The API supports CORS for web applications. Configure allowed origins in your environment:

```
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000,127.0.0.1,127.0.0.1:8000
```

## Testing

Use tools like Postman or curl to test the API endpoints. Remember to include the proper headers and authentication tokens.

### Example curl request:

```bash
curl -X GET \
  http://your-domain.com/api/properties \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json'
```

### Authenticated request:

```bash
curl -X GET \
  http://your-domain.com/api/auth/user \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer 1|abc123...'
```