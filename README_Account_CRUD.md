# Account CRUD System

A complete CRUD (Create, Read, Update, Delete) system for Account entities in the MoneyTrack API, built with Symfony 5.4.

## Features

✅ **Complete CRUD Operations**
- Create new accounts with validation
- Read accounts (single and paginated lists)
- Update accounts with partial update support
- Soft delete accounts (preserves data integrity)

✅ **Advanced Functionality**
- Pagination with customizable page size
- Advanced filtering (synced status, balance range, date range)
- Full-text search in name and description
- Account statistics with transaction counts
- Soft delete with restore capability

✅ **Consistent API Response**
- Uses `NogSystemResponse` for all endpoints
- Standardized error handling
- CORS headers included

✅ **Data Integrity**
- Soft delete preserves historical data
- Automatic timestamp management
- Unique online ID generation

## Quick Start

### 1. Database Setup

Make sure your database is set up and migrations are run:

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

### 2. Start the Server

```bash
symfony server:start
# or
php -S localhost:8000 -t public/
```

### 3. Test the API

Create a new account:
```bash
curl -X POST http://localhost:8000/api/accounts \
  -H "Content-Type: application/json" \
  -d '{
    "name": "My Savings Account",
    "balance": "5000.00",
    "description": "Emergency fund",
    "contact": "user@example.com"
  }'
```

Get all accounts:
```bash
curl http://localhost:8000/api/accounts
```

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `POST` | `/api/accounts` | Create new account |
| `GET` | `/api/accounts` | List accounts (paginated) |
| `GET` | `/api/accounts/{id}` | Get single account |
| `PUT` | `/api/accounts/{id}` | Update account |
| `DELETE` | `/api/accounts/{id}` | Soft delete account |
| `GET` | `/api/accounts/search` | Search accounts |
| `GET` | `/api/accounts/stats` | Get account statistics |
| `POST` | `/api/accounts/{id}/restore` | Restore deleted account |

## Request/Response Examples

### Create Account

**Request:**
```json
POST /api/accounts
{
    "name": "Main Checking",
    "balance": "1500.00",
    "description": "Primary checking account",
    "contact": "john@example.com",
    "emplacement": "Bank Branch A"
}
```

**Response:**
```json
{
    "statut": "success",
    "message": "Account created successfully",
    "data": {
        "id": 1,
        "onlineID": "GNLSHP01SYS_ACC64f8a1b2c3d4e5f6",
        "name": "Main Checking",
        "balance": "1500.00",
        "description": "Primary checking account",
        "contact": "john@example.com",
        "emplacement": "Bank Branch A",
        "synced": false,
        "created_at": "2025-01-28 21:30:45",
        "updated_at": "2025-01-28 21:30:45"
    }
}
```

### List Accounts with Filters

**Request:**
```
GET /api/accounts?page=1&limit=10&synced=false&search=checking&sort=name&order=ASC
```

**Response:**
```json
{
    "statut": "success",
    "message": "Accounts retrieved successfully",
    "data": {
        "accounts": [...],
        "pagination": {
            "current_page": 1,
            "total_pages": 3,
            "total_items": 25,
            "items_per_page": 10,
            "has_next": true,
            "has_previous": false
        }
    }
}
```

### Update Account

**Request:**
```json
PUT /api/accounts/1
{
    "balance": "2000.00",
    "description": "Updated description",
    "synced": true
}
```

**Response:**
```json
{
    "statut": "success",
    "message": "Account updated successfully",
    "data": {
        "id": 1,
        "name": "Main Checking",
        "balance": "2000.00",
        "description": "Updated description",
        "synced": true,
        "updated_at": "2025-01-28 21:45:30"
    }
}
```

## Query Parameters

### List Accounts (`GET /api/accounts`)

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `page` | integer | 1 | Page number |
| `limit` | integer | 10 | Items per page |
| `synced` | boolean | - | Filter by sync status |
| `deleted` | string | false | Include deleted (true/false/all) |
| `search` | string | - | Search in name/description |
| `balance_min` | decimal | - | Minimum balance |
| `balance_max` | decimal | - | Maximum balance |
| `created_after` | date | - | Created after date (Y-m-d) |
| `created_before` | date | - | Created before date (Y-m-d) |
| `sort` | string | create_at | Sort field |
| `order` | string | DESC | Sort order (ASC/DESC) |

### Search Accounts (`GET /api/accounts/search`)

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `q` | string | Yes | Search term |
| `include_deleted` | boolean | No | Include soft-deleted accounts |

## Validation Rules

### Required Fields
- `name`: Non-empty string (max 255 chars)
- `balance`: Numeric value

### Optional Fields
- `description`: Text field
- `contact`: String (max 255 chars)
- `emplacement`: String (max 255 chars)
- `synced`: Boolean (defaults to false)

### Auto-Generated Fields
- `id`: Primary key
- `onlineID`: Unique identifier using NogCustomedFunctions
- `create_at`: Creation timestamp
- `updated_at`: Last update timestamp
- `deleted_at`: Soft delete timestamp (null for active)

## Error Handling

All errors return the `NogSystemResponse` format:

```json
{
    "statut": "error",
    "message": "Descriptive error message",
    "data": null
}
```

### Common HTTP Status Codes
- `200 OK`: Success
- `201 Created`: Resource created
- `400 Bad Request`: Invalid data
- `404 Not Found`: Resource not found
- `500 Internal Server Error`: Server error

## Testing

### Manual Testing

Run the manual test script:
```bash
php tests/manual_crud_test.php
```

This will test all CRUD operations and provide a detailed report.

### Using cURL

Test individual endpoints:

```bash
# Create account
curl -X POST http://localhost:8000/api/accounts \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Account","balance":"1000.00"}'

# Get accounts
curl http://localhost:8000/api/accounts

# Get single account
curl http://localhost:8000/api/accounts/1

# Update account
curl -X PUT http://localhost:8000/api/accounts/1 \
  -H "Content-Type: application/json" \
  -d '{"balance":"1500.00"}'

# Search accounts
curl "http://localhost:8000/api/accounts/search?q=Test"

# Delete account
curl -X DELETE http://localhost:8000/api/accounts/1

# Restore account
curl -X POST http://localhost:8000/api/accounts/1/restore
```

## File Structure

```
src/
├── Controller/
│   └── AccountController.php      # Main CRUD controller
├── Entity/
│   └── Account.php               # Account entity
├── Repository/
│   └── AccountRepository.php     # Enhanced repository with custom methods
└── Modele/
    ├── NogSystemResponse.php     # Standardized response format
    └── NogCustomedFunctions.php  # Utility functions

docs/
└── Account_API_Documentation.md  # Complete API documentation

tests/
├── AccountCrudTest.php          # PHPUnit tests (needs PHPUnit setup)
└── manual_crud_test.php         # Manual testing script
```

## Key Features Explained

### Soft Delete
- Accounts are never permanently deleted
- `deleted_at` timestamp marks deletion
- Deleted accounts can be restored
- Maintains data integrity for historical records

### Pagination
- Default: 10 items per page
- Customizable page size
- Includes pagination metadata
- Efficient database queries

### Filtering & Search
- Multiple filter combinations
- Full-text search in name/description
- Date range filtering
- Balance range filtering

### Response Consistency
- All endpoints use `NogSystemResponse`
- Consistent error messages
- Standardized data format
- CORS headers for frontend integration

## Dependencies

- **Symfony 5.4**: Web framework
- **Doctrine ORM**: Database abstraction
- **PHP 7.2.5+**: Minimum PHP version

## Configuration

The system uses existing Symfony configuration. Key files:
- `config/doctrine.yaml`: Database configuration
- `config/services.yaml`: Service configuration
- `.env`: Environment variables

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check `.env` database configuration
   - Ensure database server is running
   - Run migrations: `php bin/console doctrine:migrations:migrate`

2. **404 Not Found**
   - Verify Symfony server is running
   - Check route configuration
   - Ensure proper URL format

3. **Validation Errors**
   - Check required fields (name, balance)
   - Ensure balance is numeric
   - Verify JSON format in requests

4. **Permission Errors**
   - Check file permissions
   - Ensure cache directory is writable
   - Clear Symfony cache: `php bin/console cache:clear`

## Contributing

When extending this CRUD system:

1. Follow existing patterns in `AccountController`
2. Add new methods to `AccountRepository` for complex queries
3. Update API documentation
4. Add tests for new functionality
5. Maintain `NogSystemResponse` format consistency

## License

This project follows the same license as the main MoneyTrack application.