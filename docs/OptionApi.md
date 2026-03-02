# Option API Documentation

## Introduction

This documentation provides details about the API endpoints available in the SuGanta API.

## Endpoints

### 1. Get Options

Retrieve configuration options used for dropdowns and select boxes in the application.

- **URL**: `/api/v1/options`
- **Method**: `GET`
- **Authentication**: Not required (currently)

#### Query Parameters

| Parameter | Type   | Required | Description                                                                 |
| :-------- | :----- | :------- | :-------------------------------------------------------------------------- |
| `key`     | string | No       | Comma-separated list of keys to filter the options (e.g., `gender,board`). |

#### Success Response

**Code**: `200 OK`

**Content Example (All Options)**:

```json
{
    "message": "All options retrieved successfully.",
    "success": true,
    "code": 200,
    "data": {
        "gender": {
            "1": "Male",
            "2": "Female",
            "3": "Other",
            "4": "Prefer not to say"
        },
        "institute_type": {
            "1": "School",
            "2": "College",
            // ...
        },
        // ... other options
    }
}
```

**Content Example (Specific Keys: `gender,board`)**:

```json
{
    "message": "Options retrieved successfully.",
    "success": true,
    "code": 200,
    "data": {
        "gender": {
            "1": "Male",
            "2": "Female",
            "3": "Other",
            "4": "Prefer not to say"
        },
        "board": {
            "1": "CBSE",
            "2": "ICSE",
            "3": "State Board",
            "4": "IB",
            "5": "IGCSE"
        }
    }
}
```

#### Error Response

**Condition**: No valid keys found.

**Code**: `404 Not Found`

**Content Example**:

```json
{
    "message": "No valid options found for the provided keys.",
    "success": false,
    "code": 404
}
```
