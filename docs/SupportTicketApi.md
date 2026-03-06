# Support Ticket API Documentation

## Introduction

This documentation describes the Support Ticket API endpoints in the SuGanta API. All endpoints require **Bearer token** authentication (Sanctum).

**Base URL**: `{{base_url}}/api/v1`  
**Authentication**: `Authorization: Bearer {{token}}`

---

## Endpoints

### 1. Get Support Ticket Dropdown Options

Returns priorities, statuses, and categories for building dropdowns (create/edit forms, filters).

| Item | Value |
|------|--------|
| **URL** | `GET /api/v1/support-tickets/options` |
| **Auth** | Required (Bearer) |

#### Postman

- **Method**: `GET`
- **URL**: `{{base_url}}/api/v1/support-tickets/options`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer {{token}}`
- **Body**: None

#### Success Response (200)

```json
{
  "message": "Support ticket options retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "priorities": {
      "low": "Low",
      "medium": "Medium",
      "high": "High",
      "urgent": "Urgent"
    },
    "statuses": {
      "open": "Open",
      "in_progress": "In Progress",
      "waiting_for_user": "Waiting for User",
      "resolved": "Resolved",
      "closed": "Closed"
    },
    "categories": {
      "technical": "Technical Issue",
      "billing": "Billing & Payment",
      "account": "Account Issue",
      "subject": "Subject Related",
      "exam": "Exam Related",
      "new_subject_request": "Request New Subject",
      "new_exam_request": "Request New Exam",
      "new_exam_category_request": "Request New Exam Category",
      "feature_request": "Feature Request",
      "bug_report": "Bug Report",
      "general": "General Inquiry"
    }
  }
}
```

---

### 2. List Support Tickets

Paginated list. Regular users see only their tickets; admins see all and can filter by `user_id`.

| Item | Value |
|------|--------|
| **URL** | `GET /api/v1/support-tickets` |
| **Auth** | Required (Bearer) |

#### Query Parameters

| Parameter   | Type   | Required | Description |
|------------|--------|----------|-------------|
| `per_page` | integer| No       | Items per page (default: 15) |
| `status`   | string | No       | Filter by status (see options) |
| `priority` | string | No       | Filter by priority (see options) |
| `category` | string | No       | Filter by category (see options) |
| `user_id`  | integer| No       | Admin only: filter by user ID |

#### Postman

- **Method**: `GET`
- **URL**: `{{base_url}}/api/v1/support-tickets`
- **Query (optional)**: `?per_page=15&status=open&priority=high&category=technical`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer {{token}}`
- **Body**: None

#### Success Response (200)

```json
{
  "message": "Support tickets retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user_id": 5,
        "subject": "Login issue",
        "message": "Cannot login after password reset.",
        "priority": "high",
        "status": "open",
        "category": "technical",
        "ticket_number": "TKT2025030001",
        "assigned_to": null,
        "resolved_at": null,
        "created_at": "2025-03-06T10:00:00.000000Z",
        "updated_at": "2025-03-06T10:00:00.000000Z",
        "user": { "id": 5, "name": "John Doe", "email": "john@example.com" },
        "assigned_admin": null
      }
    ],
    "first_page_url": "http://localhost/api/v1/support-tickets?page=1",
    "from": 1,
    "last_page": 1,
    "last_page_url": "http://localhost/api/v1/support-tickets?page=1",
    "links": [],
    "next_page_url": null,
    "path": "http://localhost/api/v1/support-tickets",
    "per_page": 15,
    "prev_page_url": null,
    "to": 1,
    "total": 1
  }
}
```

---

### 3. Create Support Ticket

Creates a new ticket for the authenticated user.

| Item | Value |
|------|--------|
| **URL** | `POST /api/v1/support-tickets` |
| **Auth** | Required (Bearer) |

#### Request Body (JSON)

| Field            | Type   | Required | Description |
|------------------|--------|----------|-------------|
| `subject`        | string | Yes      | Max 255 chars |
| `message`        | string | Yes      | Ticket message |
| `priority`       | string | Yes      | `low`, `medium`, `high`, `urgent` |
| `category`       | string | Yes      | One of category keys from options |
| `attachment_path`| string | No       | Max 255 chars |
| `user_notes`     | string | No       | Optional notes |

#### Postman

- **Method**: `POST`
- **URL**: `{{base_url}}/api/v1/support-tickets`
- **Headers**:
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {{token}}`
- **Body (raw JSON)**:

```json
{
  "subject": "Cannot access exam after payment",
  "message": "I paid yesterday but the exam section still shows locked. Transaction ID: TXN123.",
  "priority": "high",
  "category": "billing",
  "user_notes": "Paid via UPI."
}
```

#### Success Response (201)

```json
{
  "message": "Support ticket created successfully.",
  "success": true,
  "code": 201,
  "data": {
    "id": 2,
    "user_id": 5,
    "subject": "Cannot access exam after payment",
    "message": "I paid yesterday but the exam section still shows locked. Transaction ID: TXN123.",
    "priority": "high",
    "status": "open",
    "category": "billing",
    "ticket_number": "TKT2025030002",
    "attachment_path": null,
    "user_notes": "Paid via UPI.",
    "assigned_to": null,
    "resolved_at": null,
    "admin_notes": null,
    "created_at": "2025-03-06T11:00:00.000000Z",
    "updated_at": "2025-03-06T11:00:00.000000Z"
  }
}
```

#### Validation Error (422)

```json
{
  "message": "The subject field is required. (and other validation messages)",
  "success": false,
  "code": 422,
  "errors": {
    "subject": ["The subject field is required."],
    "priority": ["The selected priority is invalid."]
  }
}
```

---

### 4. Get Single Support Ticket

Returns one ticket with user, assigned admin, and replies. Access allowed only if user owns the ticket or is admin.

| Item | Value |
|------|--------|
| **URL** | `GET /api/v1/support-tickets/{id}` |
| **Auth** | Required (Bearer) |

#### Postman

- **Method**: `GET`
- **URL**: `{{base_url}}/api/v1/support-tickets/1`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer {{token}}`
- **Body**: None

#### Success Response (200)

```json
{
  "message": "Support ticket retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user_id": 5,
    "subject": "Login issue",
    "message": "Cannot login after password reset.",
    "priority": "high",
    "status": "open",
    "category": "technical",
    "ticket_number": "TKT2025030001",
    "attachment_path": null,
    "user_notes": null,
    "admin_notes": null,
    "assigned_to": null,
    "resolved_at": null,
    "created_at": "2025-03-06T10:00:00.000000Z",
    "updated_at": "2025-03-06T10:00:00.000000Z",
    "user": { "id": 5, "name": "John Doe", "email": "john@example.com" },
    "assigned_admin": null,
    "replies": []
  }
}
```

#### Forbidden (403)

```json
{
  "message": "You are not allowed to access this ticket.",
  "success": false,
  "code": 403
}
```

#### Not Found (404)

```json
{
  "message": "Resource not found",
  "success": false,
  "code": 404
}
```

---

### 5. Update Support Ticket

Partial update. Users can update subject, message, priority, category, attachment_path, user_notes. Admins can also update status, admin_notes, assigned_to, assigned_admin_id, resolved_at.

| Item | Value |
|------|--------|
| **URL** | `PUT /api/v1/support-tickets/{id}` or `PATCH /api/v1/support-tickets/{id}` |
| **Auth** | Required (Bearer) |

#### Request Body (JSON) – User

| Field             | Type   | Required | Description |
|-------------------|--------|----------|-------------|
| `subject`         | string | No       | Max 255 chars |
| `message`         | string | No       | |
| `priority`        | string | No       | `low`, `medium`, `high`, `urgent` |
| `category`        | string | No       | One of category keys |
| `attachment_path` | string | No       | Nullable, max 255 |
| `user_notes`      | string | No       | Nullable |

#### Request Body (JSON) – Admin only (in addition to above)

| Field               | Type   | Required | Description |
|---------------------|--------|----------|-------------|
| `status`            | string | No       | open, in_progress, waiting_for_user, resolved, closed |
| `admin_notes`       | string | No       | Nullable |
| `assigned_to`       | integer| No       | User ID, nullable |
| `assigned_admin_id` | integer| No       | User ID, nullable |
| `resolved_at`       | string | No       | ISO date, nullable |

#### Postman – User updating own ticket

- **Method**: `PUT`
- **URL**: `{{base_url}}/api/v1/support-tickets/1`
- **Headers**:
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {{token}}`
- **Body (raw JSON)**:

```json
{
  "message": "Updated: I have tried clearing cache as well, still same issue.",
  "user_notes": "Browser: Chrome on Windows."
}
```

#### Postman – Admin updating status/assignment

- **Method**: `PATCH`
- **URL**: `{{base_url}}/api/v1/support-tickets/1`
- **Headers**:
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {{token}}`
- **Body (raw JSON)**:

```json
{
  "status": "in_progress",
  "assigned_to": 2,
  "admin_notes": "Assigned to support team. Checking server logs."
}
```

#### Success Response (200)

```json
{
  "message": "Support ticket updated successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user_id": 5,
    "subject": "Login issue",
    "message": "Updated: I have tried clearing cache as well, still same issue.",
    "priority": "high",
    "status": "in_progress",
    "category": "technical",
    "ticket_number": "TKT2025030001",
    "assigned_to": 2,
    "resolved_at": null,
    "admin_notes": "Assigned to support team. Checking server logs.",
    "user_notes": "Browser: Chrome on Windows.",
    "created_at": "2025-03-06T10:00:00.000000Z",
    "updated_at": "2025-03-06T12:00:00.000000Z",
    "user": { "id": 5, "name": "John Doe", "email": "john@example.com" },
    "assigned_admin": { "id": 2, "name": "Support Admin", "email": "support@example.com" }
  }
}
```

#### Forbidden (403)

```json
{
  "message": "You are not allowed to update this ticket.",
  "success": false,
  "code": 403
}
```

---

### 6. Delete Support Ticket (Soft Delete)

Soft-deletes a ticket. Allowed only if the user can close the ticket (owner or admin).

| Item | Value |
|------|--------|
| **URL** | `DELETE /api/v1/support-tickets/{id}` |
| **Auth** | Required (Bearer) |

#### Postman

- **Method**: `DELETE`
- **URL**: `{{base_url}}/api/v1/support-tickets/1`
- **Headers**:
  - `Accept: application/json`
  - `Authorization: Bearer {{token}}`
- **Body**: None

#### Success Response (204)

No content (empty body).

#### Forbidden (403)

```json
{
  "message": "You are not allowed to delete this ticket.",
  "success": false,
  "code": 403
}
```

---

## Postman Collection Variables

Use these in Postman for `{{base_url}}` and `{{token}}`:

| Variable   | Example                    | Description |
|-----------|----------------------------|-------------|
| `base_url`| `http://localhost` or your API host | API base URL |
| `token`   | (Sanctum token from login) | Bearer token for protected routes |

---

## Valid Values Reference

- **priority**: `low`, `medium`, `high`, `urgent`
- **status**: `open`, `in_progress`, `waiting_for_user`, `resolved`, `closed`
- **category**: `technical`, `billing`, `account`, `subject`, `exam`, `new_subject_request`, `new_exam_request`, `new_exam_category_request`, `feature_request`, `bug_report`, `general`

For the full list of option labels, call `GET /api/v1/support-tickets/options`.
