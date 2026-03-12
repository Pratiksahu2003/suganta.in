# Lead API

Endpoints for managing leads. Users see only leads they **own** (lead_owner_id), **created** (user_id), or are **assigned to** (assigned_to). All endpoints require authentication.

**Base path**: `/api/v1`  
**Auth**: All endpoints require Bearer token (Sanctum)

---

## Endpoints Summary

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/leads` | List leads (paginated; owner, assigned, or creator only) | Authenticated user |
| POST | `/leads` | Create a new lead | Authenticated user |
| GET | `/leads/{lead}` | Get a single lead | Authenticated (owner, assigned, or creator) |

---

## 1. List Leads

| | |
|---|---|
| **Endpoint** | `GET /api/v1/leads` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Paginated list of leads. Returns leads where the authenticated user is either the **owner** (lead_owner_id), **assigned** (assigned_to), or **creator** (user_id). Ordered by created_at descending.

### Query Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| status | string | No | — | See status options | Filter by status |
| search | string | No | — | — | Search in name, email, subject_interest, message |
| start_date | string | No | — | date (YYYY-MM-DD) | Filter leads created on or after this date |
| end_date | string | No | — | date (YYYY-MM-DD) | Filter leads created on or before this date |
| per_page | integer | No | 15 | max: 50 | Items per page |

### Example Request

```
GET /api/v1/leads
GET /api/v1/leads?status=new&per_page=20
GET /api/v1/leads?search=john&status=qualified
GET /api/v1/leads?start_date=2025-03-01&end_date=2025-03-31
```

### Success (200)

```json
{
  "message": "Leads retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "data": [
      {
        "id": 1,
        "lead_id": "SUG-20250311-000123",
        "user_id": 5,
        "lead_owner_id": 3,
        "assigned_to": 7,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "+911234567890",
        "type": "student",
        "source": "website",
        "subject_interest": "Mathematics",
        "grade_level": "Class 10",
        "location": "Mumbai, India",
        "message": "Interested in online tutoring for Class 10 Maths.",
        "status": "new",
        "priority": "high",
        "estimated_value": "5000.00",
        "utm_source": null,
        "utm_medium": null,
        "utm_campaign": null,
        "last_contacted_at": null,
        "next_follow_up_at": null,
        "contact_history": null,
        "notes": null,
        "created_at": "2025-03-11T10:00:00.000000Z",
        "updated_at": "2025-03-11T10:00:00.000000Z",
        "user": {
          "id": 5,
          "name": "Jane Smith",
          "email": "jane@example.com"
        },
        "lead_owner": {
          "id": 3,
          "name": "Teacher One",
          "email": "teacher@example.com"
        },
        "assigned_to": {
          "id": 7,
          "name": "Sales Rep",
          "email": "sales@example.com"
        }
      }
    ],
    "meta": {
      "current_page": 1,
      "last_page": 5,
      "per_page": 15,
      "total": 67,
      "from": 1,
      "to": 15
    },
    "links": {
      "first": "http://localhost/api/v1/leads?page=1",
      "last": "http://localhost/api/v1/leads?page=5",
      "prev": null,
      "next": "http://localhost/api/v1/leads?page=2"
    }
  }
}
```

---

## 2. Create Lead

| | |
|---|---|
| **Endpoint** | `POST /api/v1/leads` |
| **Content-Type** | `application/json` |
| **Access** | Protected (auth:sanctum) |

Creates a new lead. The authenticated user's ID is stored as `user_id` (creator). A unique `lead_id` (e.g. `SUG-20250311-000123`) is auto-generated.

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| name | string | **Yes** | max:255 | Lead name |
| phone | string | **Yes** | max:30 | Lead phone number |
| lead_owner_id | integer | **Yes** | exists:users,id | User ID of the lead owner (teacher) |
| email | string | No | email, max:255 | Lead email |
| type | string | No | in:student,parent,institute,teacher | Lead type |
| source | string | No | in:website,social_media,referral,advertisement,direct | Lead source |
| subject_interest | string | No | max:255 | Subject of interest |
| grade_level | string | No | max:100 | Grade or class level |
| location | string | No | max:255 | Location |
| message | string | No | max:5000 | Additional message |
| status | string | No | in:new,contacted,qualified,converted,closed | Default: `new` |
| priority | string | No | in:low,medium,high,urgent | Lead priority |
| assigned_to | integer | No | exists:users,id | User ID to assign the lead to |
| estimated_value | number | No | min:0 | Estimated value (decimal) |
| utm_source | string | No | max:255 | UTM source for tracking |
| utm_medium | string | No | max:255 | UTM medium for tracking |
| utm_campaign | string | No | max:255 | UTM campaign for tracking |

### Success (201)

```json
{
  "message": "Lead created successfully.",
  "success": true,
  "code": 201,
  "data": {
    "id": 1,
    "lead_id": "SUG-20250311-000123",
    "user_id": 5,
    "lead_owner_id": 3,
    "assigned_to": 7,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+911234567890",
    "type": "student",
    "source": "website",
    "subject_interest": "Mathematics",
    "grade_level": "Class 10",
    "location": "Mumbai, India",
    "message": "Interested in online tutoring.",
    "status": "new",
    "priority": "high",
    "estimated_value": "5000.00",
    "utm_source": null,
    "utm_medium": null,
    "utm_campaign": null,
    "last_contacted_at": null,
    "next_follow_up_at": null,
    "contact_history": null,
    "notes": null,
    "created_at": "2025-03-11T10:00:00.000000Z",
    "updated_at": "2025-03-11T10:00:00.000000Z",
    "user": {
      "id": 5,
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "lead_owner": {
      "id": 3,
      "name": "Teacher One",
      "email": "teacher@example.com"
    },
    "assigned_to": {
      "id": 7,
      "name": "Sales Rep",
      "email": "sales@example.com"
    }
  }
}
```

### Error (422) – Validation

```json
{
  "message": "Validation failed.",
  "success": false,
  "code": 422,
  "errors": {
    "name": ["The name field is required."],
    "phone": ["The phone field is required."],
    "lead_owner_id": ["The selected lead owner id is invalid."],
    "type": ["The selected type is invalid."]
  }
}
```

---

## 3. Get Lead

| | |
|---|---|
| **Endpoint** | `GET /api/v1/leads/{lead}` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Returns a single lead with creator, owner, and assigned user. Access only if the user is owner, assigned, or creator. Returns 404 if the lead does not exist or access is denied.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| lead | integer | **Yes** | Lead ID |

### Query Parameters

None.

### Success (200)

```json
{
  "message": "Lead retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "lead_id": "SUG-20250311-000123",
    "user_id": 5,
    "lead_owner_id": 3,
    "assigned_to": 7,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "+911234567890",
    "type": "student",
    "source": "website",
    "subject_interest": "Mathematics",
    "grade_level": "Class 10",
    "location": "Mumbai, India",
    "message": "Interested in online tutoring for Class 10 Maths.",
    "status": "new",
    "priority": "high",
    "estimated_value": "5000.00",
    "utm_source": null,
    "utm_medium": null,
    "utm_campaign": null,
    "last_contacted_at": null,
    "next_follow_up_at": null,
    "contact_history": null,
    "notes": null,
    "created_at": "2025-03-11T10:00:00.000000Z",
    "updated_at": "2025-03-11T10:00:00.000000Z",
    "user": {
      "id": 5,
      "name": "Jane Smith",
      "email": "jane@example.com"
    },
    "lead_owner": {
      "id": 3,
      "name": "Teacher One",
      "email": "teacher@example.com"
    },
    "assigned_to": {
      "id": 7,
      "name": "Sales Rep",
      "email": "sales@example.com"
    }
  }
}
```

### Error (404)

```json
{
  "message": "Lead not found or access denied.",
  "success": false,
  "code": 404
}
```

---

## Valid Values Reference

### Type

| Value | Description |
|-------|-------------|
| student | Student |
| parent | Parent |
| institute | Institute |
| teacher | Teacher |

### Source

| Value | Description |
|-------|-------------|
| website | Website |
| social_media | Social Media |
| referral | Referral |
| advertisement | Advertisement |
| direct | Direct |

### Status

| Value | Description |
|-------|-------------|
| new | New |
| contacted | Contacted |
| qualified | Qualified |
| converted | Converted |
| closed | Closed |

### Priority

| Value | Description |
|-------|-------------|
| low | Low |
| medium | Medium |
| high | High |
| urgent | Urgent |

---

## Access Rules

A user can access a lead if **any** of the following applies:

- **Creator**: `user_id` matches the authenticated user (user who created the lead)
- **Owner**: `lead_owner_id` matches the authenticated user (teacher who owns the lead)
- **Assigned**: `assigned_to` matches the authenticated user (user assigned to work on the lead)

---

## Example Requests

### List Leads (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/leads?status=new&per_page=20" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Create Lead (cURL)

```bash
curl -X POST "https://api.example.com/api/v1/leads" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "phone": "+911234567890",
    "email": "john@example.com",
    "lead_owner_id": 3,
    "type": "student",
    "source": "website",
    "subject_interest": "Mathematics",
    "grade_level": "Class 10",
    "location": "Mumbai, India",
    "message": "Interested in online tutoring.",
    "status": "new",
    "priority": "high",
    "assigned_to": 7,
    "estimated_value": 5000
  }'
```

### Get Lead (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/leads/1" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### JavaScript (Fetch – List)

```javascript
const response = await fetch('/api/v1/leads?search=john&status=new', {
  method: 'GET',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
});
```

### JavaScript (Fetch – Create)

```javascript
const response = await fetch('/api/v1/leads', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    name: 'John Doe',
    phone: '+911234567890',
    email: 'john@example.com',
    lead_owner_id: 3,
    type: 'student',
    source: 'website',
  }),
});
```

---

## Error Codes Summary

| Code | Condition |
|------|-----------|
| 401 | Unauthenticated |
| 404 | Lead not found or access denied (on show) |
| 422 | Validation failed |
| 500 | Server error |
