# Account CRUD API Documentation

## Overview

This API provides complete CRUD (Create, Read, Update, Delete) operations for Account entities in the MoneyTrack system. All endpoints use the `NogSystemResponse` format for consistent JSON responses.

## Base URL
```
/api/accounts
```

## Response Format

All API responses follow the `NogSystemResponse` structure:

```json
{
    "statut": "success|error",
    "message": "Descriptive message",
    "data": "Response data or null"
}
```

## Authentication

Currently, no authentication is required. Future versions may implement token-based authentication.

---

## Endpoints

### 1. Create Account

**POST** `/api/accounts`

Creates a new account with the provided data.

#### Request Body
```json
{
    "name": "Account Name (required)",
    "balance": "1500.00 (required, numeric)",
    "description": "Optional description",
    "contact": "Optional contact info",
    "emplacement": "Optional location"
}
```

#### Response (201 Created)
```json
{
    "statut": "success",
    "message": "Account created successfully",
    "data": {
        "id": 1,
        "onlineID": "GNLSHP01SYS_ACC64f8a1b2c3d4e5f6",
        "name": "Account Name",
        "balance": "1500.00",
        "description": "Optional description",
        "contact": "Optional contact info",
        "emplacement": "Optional location",
        "synced": false,
        "created_at": "2025-01-28 21:30:45",
        "updated_at": "2025-01-28 21:30:45"
    }
}
```

#### Error Responses
- **400 Bad Request**: Missing required fields or invalid data
- **500 Internal Server Error**: Database or system error

---

### 2. List Accounts

**GET** `/api/accounts`

Retrieves a paginated list of accounts with optional filtering.

#### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number for pagination |
| `limit` | integer | 10 | Number of items per page |
| `synced` | boolean | - | Filter by sync status (true/false) |
| `deleted` | string | false | Include deleted accounts (true/false/all) |
| `search` | string | - | Search in name and description |
| `balance_min` | decimal | - | Minimum balance filter |
| `balance_max` | decimal | - | Maximum balance filter |
| `created_after` | date | - | Filter accounts created after date (Y-m-d) |
| `created_before` | date | - | Filter accounts created before date (Y-m-d) |
| `sort` | string | create_at | Sort field (name, balance, create_at, updated_at) |
| `order` | string | DESC | Sort order (ASC/DESC) |

#### Example Request
```
GET /api/accounts?page=1&limit=5&synced=false&search=checking&sort=name&order=ASC
```

#### Response (200 OK)
```json
{
    "statut": "success",
    "message": "Accounts retrieved successfully",
    "data": {
        "accounts": [
            {
                "id": 1,
                "onlineID": "GNLSHP01SYS_ACC64f8a1b2c3d4e5f6",
                "name": "Main Checking",
                "balance": "1500.00",
                "description": "Primary account",
                "contact": "john@example.com",
                "emplacement": "Bank A",
                "synced": false,
                "created_at": "2025-01-28 21:30:45",
                "updated_at": "2025-01-28 21:30:45",
                "deleted_at": null
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 3,
            "total_items": 15,
            "items_per_page": 5,
            "has_next": true,
            "has_previous": false
        }
    }
}
```

---

### 3. Get Single Account

**GET** `/api/accounts/{id}`

Retrieves a single account by its ID.

#### Path Parameters
- `id` (integer, required): Account ID

#### Response (200 OK)
```json
{
    "statut": "success",
    "message": "Account retrieved successfully",
    "data": {
        "id": 1,
        "onlineID": "GNLSHP01SYS_ACC64f8a1b2c3d4e5f6",
        "name": "Main Checking",
        "balance": "1500.00",
        "description": "Primary account",
        "contact": "john@example.com",
        "emplacement": "Bank A",
        "synced": false,
        "created_at": "2025-01-28 21:30:45",
        "updated_at": "2025-01-28 21:30:45",
        "deleted_at": null,
        "online_transactions_count": 5,
        "offline_transactions_count": 3,
        "total_transactions_count": 8
    }
}
```

#### Error Responses
- **404 Not Found**: Account not found or deleted
- **500 Internal Server Error**: Database or system error

---

### 4. Update Account

**PUT** `/api/accounts/{id}`

Updates an existing account. Only provided fields will be updated.

#### Path Parameters
- `id` (integer, required): Account ID

#### Request Body (partial update supported)
```json
{
    "name": "Updated Account Name",
    "balance": "2000.00",
    "description": "Updated description",
    "contact": "newemail@example.com",
    "emplacement": "New Location",
    "synced": true
}
```

#### Response (200 OK)
```json
{
    "statut": "success",
    "message": "Account updated successfully",
    "data": {
        "id": 1,
        "onlineID": "GNLSHP01SYS_ACC64f8a1b2c3d4e5f6",
        "name": "Updated Account Name",
        "balance": "2000.00",
        "description": "Updated description",
        "contact": "newemail@example.com",
        "emplacement": "New Location",
        "synced": true,
        "created_at": "2025-01-28 21:30:45",
        "updated_at": "2025-01-28 21:45:30",
        "deleted_at": null
    }
}
```

#### Error Responses
- **400 Bad Request**: Invalid data (empty name, non-numeric balance)
- **404 Not Found**: Account not found or deleted
- **500 Internal Server Error**: Database or system error

---

### 5. Delete Account (Soft Delete)

**DELETE** `/api/accounts/{id}`

Soft deletes an account by setting the `deleted_at` timestamp.

#### Path Parameters
- `id` (integer, required): Account ID

#### Response (200 OK)
```json
{
    "statut": "success",
    "message": "Account deleted successfully",
    "data": {
        "id": 1,
        "name": "Account Name",
        "deleted_at": "2025-01-28 21:50:15"
    }
}
```

#### Error Responses
- **404 Not Found**: Account not found or already deleted
- **500 Internal Server Error**: Database or system error

---

### 6. Search Accounts

**GET** `/api/accounts/search`

Searches accounts by name and description.

#### Query Parameters
- `q` (string, required): Search term
- `include_deleted` (boolean, default: false): Include soft-deleted accounts

#### Example Request
```
GET /api/accounts/search?q=checking&include_deleted=false
```

#### Response (200 OK)
```json
{
    "statut": "success",
    "message": "Search completed successfully",
    "data": {
        "search_term": "checking",
        "results_count": 2,
        "accounts": [
            {
                "id": 1,
                "name": "Main Checking",
                "balance": "1500.00",
                "description": "Primary checking account",
                "synced": false,
                "created_at": "2025-01-28 21:30:45"
            }
        ]
    }
}
```

---

### 7. Account Statistics

**GET** `/api/accounts/stats`

Retrieves accounts with their transaction counts for statistical purposes.

#### Query Parameters
- `include_deleted` (boolean, default: false): Include soft-deleted accounts

#### Response (200 OK)
```json
{
    "statut": "success",
    "message": "Account statistics retrieved successfully",
    "data": {
        "accounts": [
            {
                "id": 1,
                "name": "Main Checking",
                "balance": "1500.00",
                "online_transaction_count": 5,
                "offline_transaction_count": 3,
                "total_transaction_count": 8,
                "synced": false,
                "created_at": "2025-01-28 21:30:45"
            }
        ],
        "total_accounts": 1
    }
}
```

---

### 8. Restore Account

**POST** `/api/accounts/{id}/restore`

Restores a soft-deleted account by clearing the `deleted_at` timestamp.

#### Path Parameters
- `id` (integer, required): Account ID

#### Response (200 OK)
```json
{
    "statut": "success",
    "message": "Account restored successfully",
    "data": {
        "id": 1,
        "onlineID": "GNLSHP01SYS_ACC64f8a1b2c3d4e5f6",
        "name": "Restored Account",
        "balance": "1500.00",
        "synced": false,
        "created_at": "2025-01-28 21:30:45",
        "updated_at": "2025-01-28 21:55:20",
        "deleted_at": null
    }
}
```

#### Error Responses
- **404 Not Found**: Account not found
- **400 Bad Request**: Account is not deleted
- **500 Internal Server Error**: Database or system error

---

## Data Validation Rules

### Account Creation/Update
- **name**: Required, non-empty string (max 255 characters)
- **balance**: Required numeric value, stored as decimal(10,0)
- **description**: Optional text field
- **contact**: Optional string (max 255 characters)
- **emplacement**: Optional string (max 255 characters)
- **synced**: Boolean, defaults to false

### Automatic Fields
- **id**: Auto-generated primary key
- **onlineID**: Auto-generated using NogCustomedFunctions::generateID('ACC')
- **create_at**: Set automatically on creation
- **updated_at**: Updated automatically on modification
- **deleted_at**: Set on soft delete, null for active accounts

---

## Error Handling

All errors return appropriate HTTP status codes with `NogSystemResponse` format:

```json
{
    "statut": "error",
    "message": "Descriptive error message",
    "data": null
}
```

### Common HTTP Status Codes
- **200 OK**: Successful operation
- **201 Created**: Resource created successfully
- **400 Bad Request**: Invalid request data
- **404 Not Found**: Resource not found
- **500 Internal Server Error**: Server/database error

---

## Usage Examples

### Create a new account
```bash
curl -X POST /api/accounts \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Savings Account",
    "balance": "5000.00",
    "description": "Emergency fund",
    "contact": "user@example.com"
  }'
```

### Get paginated accounts with filters
```bash
curl -X GET "/api/accounts?page=1&limit=10&synced=false&search=savings"
```

### Update an account
```bash
curl -X PUT /api/accounts/1 \
  -H "Content-Type: application/json" \
  -d '{
    "balance": "5500.00",
    "description": "Updated emergency fund"
  }'
```

### Soft delete an account
```bash
curl -X DELETE /api/accounts/1
```

### Search accounts
```bash
curl -X GET "/api/accounts/search?q=checking&include_deleted=false"
```

---

## Notes

1. **Soft Delete**: All delete operations are soft deletes using the `deleted_at` field
2. **Pagination**: Default page size is 10, maximum recommended is 100
3. **Filtering**: Multiple filters can be combined in a single request
4. **Sorting**: Default sort is by `create_at DESC`
5. **Search**: Searches both `name` and `description` fields using LIKE queries
6. **Timestamps**: All timestamps are in 'Y-m-d H:i:s' format
7. **Balance**: Stored as string to maintain precision, should be numeric for calculations