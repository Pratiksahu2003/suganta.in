# Registration API Documentation

## Introduction

This documentation provides details about the registration-related API endpoints available in the SuGanta API.

## Endpoints

### 1. Get Registration Charges

Retrieve registration charges for different user roles (Student, Teacher, Institute, University).

- **URL**: `/api/v1/registration/charges`
- **Method**: `GET`
- **Authentication**: Not required (currently)

#### Query Parameters

| Parameter | Type   | Required | Description                                                                 |
| :-------- | :----- | :------- | :-------------------------------------------------------------------------- |
| `role`    | string | No       | Specific role to filter charges (e.g., `student`, `teacher`).              |

#### Success Response

**Code**: `200 OK`

**Content Example (All Charges)**:

```json
{
    "message": "All registration charges retrieved successfully.",
    "success": true,
    "code": 200,
    "data": {
        "student": {
            "actual_price": 0,
            "discounted_price": 0,
            "currency": "INR",
            "description": "Student Registration Fee"
        },
        "teacher": {
            "actual_price": 1000,
            "discounted_price": 299,
            "currency": "INR",
            "description": "Teacher Registration Fee"
        },
        "institute": {
            "actual_price": 3000,
            "discounted_price": 599,
            "currency": "INR",
            "description": "Institute Registration Fee"
        },
        "university": {
            "actual_price": 5000,
            "discounted_price": 699,
            "currency": "INR",
            "description": "University Registration Fee"
        }
    }
}
```

**Content Example (Specific Role: `student`)**:

```json
{
    "message": "Registration charges for student retrieved successfully.",
    "success": true,
    "code": 200,
    "data": {
        "actual_price": 0,
        "discounted_price": 0,
        "currency": "INR",
        "description": "Student Registration Fee"
    }
}
```

#### Error Response

**Condition**: Invalid role specified.

**Code**: `404 Not Found`

**Content Example**:

```json
{
    "message": "Invalid role specified.",
    "success": false,
    "code": 404,
    "errors": null
}
```
