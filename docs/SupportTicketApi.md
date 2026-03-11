# Support Ticket API

Endpoints for managing support tickets. Users can create and manage their own tickets; admins can view all tickets, assign, change status, and add admin notes. All endpoints require authentication.

**Base path**: `/api/v1`  
**Auth**: All endpoints require Bearer token (Sanctum)

---

## Endpoints Summary

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/support-tickets/options` | Get dropdown options (priorities, statuses, categories) | Authenticated user |
| GET | `/support-tickets` | List tickets (paginated; admins see all, users see own) | Authenticated user |
| POST | `/support-tickets` | Create a new ticket | Authenticated user |
| GET | `/support-tickets/{supportTicket}` | Get a single ticket with replies | Authenticated (owner or admin) |
| PUT/PATCH | `/support-tickets/{supportTicket}` | Update a ticket | Authenticated (owner or admin) |
| DELETE | `/support-tickets/{supportTicket}` | Soft-delete a ticket | Authenticated (owner or admin) |
| POST | `/support-tickets/{supportTicket}/reply` | Add a reply to a ticket | Authenticated (owner or admin) |
| GET | `/support-tickets/{supportTicket}/attachment` | Download ticket attachment | Authenticated (owner or admin) |
| GET | `/support-tickets/{supportTicket}/replies/{reply}/attachment` | Download reply attachment | Authenticated (owner or admin) |

---

## 1. Get Options

| | |
|---|---|
| **Endpoint** | `GET /api/v1/support-tickets/options` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Returns dropdown options for priorities, statuses, and categories. Use when building create/edit forms and filters.

### Query Parameters

None.

### Success (200)

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

## 2. List Support Tickets

| | |
|---|---|
| **Endpoint** | `GET /api/v1/support-tickets` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Paginated list of support tickets. Regular users see only their own tickets; admins see all tickets and can filter by `user_id`.

### Query Parameters

| Parameter | Type | Required | Default | Validation | Description |
|-----------|------|----------|---------|------------|-------------|
| status | string | No | — | See status options | Filter by status |
| priority | string | No | — | See priority options | Filter by priority |
| category | string | No | — | See category options | Filter by category |
| user_id | integer | No | — | — | **Admin only**: Filter by user ID |
| per_page | integer | No | 15 | — | Items per page |

### Example Request

```
GET /api/v1/support-tickets
GET /api/v1/support-tickets?status=open&priority=high
GET /api/v1/support-tickets?category=technical&per_page=20
GET /api/v1/support-tickets?user_id=5
```

### Success (200)

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
        "attachment_path": null,
        "user_notes": null,
        "admin_notes": null,
        "assigned_to": null,
        "assigned_admin_id": null,
        "resolved_at": null,
        "created_at": "2025-03-06T10:00:00.000000Z",
        "updated_at": "2025-03-06T10:00:00.000000Z",
        "user": {
          "id": 5,
          "name": "John Doe",
          "email": "john@example.com"
        },
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

## 3. Create Support Ticket

| | |
|---|---|
| **Endpoint** | `POST /api/v1/support-tickets` |
| **Content-Type** | `multipart/form-data` or `application/json` |
| **Access** | Protected (auth:sanctum) |

Creates a new support ticket for the authenticated user.

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| subject | string | **Yes** | max:255 | Ticket subject |
| message | string | **Yes** | — | Ticket message/description |
| priority | string | **Yes** | in:low,medium,high,urgent | Priority level |
| category | string | **Yes** | in:category keys from options | Category (e.g. technical, billing) |
| attachment | file | No | file, max:10MB | Supported: jpg, jpeg, png, pdf, doc, docx, txt, zip |
| user_notes | string | No | max:1000 | Optional internal notes |

### Success (201)

```json
{
  "message": "Support ticket created successfully.",
  "success": true,
  "code": 201,
  "data": {
    "id": 2,
    "user_id": 5,
    "subject": "Cannot access exam after payment",
    "message": "I paid yesterday but the exam section still shows locked.",
    "priority": "high",
    "status": "open",
    "category": "billing",
    "ticket_number": "TKT2025030002",
    "attachment_path": "users/5/ticket/abc123.pdf",
    "user_notes": "Paid via UPI.",
    "assigned_to": null,
    "resolved_at": null,
    "admin_notes": null,
    "created_at": "2025-03-06T11:00:00.000000Z",
    "updated_at": "2025-03-06T11:00:00.000000Z",
    "user": { "id": 5, "name": "John Doe", "email": "john@example.com" },
    "assigned_admin": null
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
    "subject": ["The subject field is required."],
    "priority": ["The selected priority is invalid."]
  }
}
```

---

## 4. Get Support Ticket

| | |
|---|---|
| **Endpoint** | `GET /api/v1/support-tickets/{supportTicket}` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Returns a single ticket with user, assigned admin, and replies. Access only if user owns the ticket or is admin.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| supportTicket | integer | **Yes** | Ticket ID |

### Query Parameters

None.

### Success (200)

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
    "assigned_admin_id": null,
    "resolved_at": null,
    "created_at": "2025-03-06T10:00:00.000000Z",
    "updated_at": "2025-03-06T10:00:00.000000Z",
    "user": { "id": 5, "name": "John Doe", "email": "john@example.com" },
    "assigned_admin": null,
    "replies": [
      {
        "id": 1,
        "support_ticket_id": 1,
        "user_id": 2,
        "message": "We are looking into this.",
        "is_admin_reply": true,
        "attachment_path": null,
        "internal_notes": null,
        "created_at": "2025-03-06T11:00:00.000000Z",
        "user": { "id": 2, "name": "Support Admin", "email": "support@example.com" }
      }
    ]
  }
}
```

### Error (403)

```json
{
  "message": "You are not allowed to access this ticket.",
  "success": false,
  "code": 403
}
```

---

## 5. Update Support Ticket

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/support-tickets/{supportTicket}` or `PATCH /api/v1/support-tickets/{supportTicket}` |
| **Content-Type** | `multipart/form-data` or `application/json` |
| **Access** | Protected (auth:sanctum) |

Partial update. Users can update subject, message, priority, category, attachment, user_notes. **Admins only** can also update status, admin_notes, assigned_to, assigned_admin_id, resolved_at.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| supportTicket | integer | **Yes** | Ticket ID |

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| subject | string | No | max:255 | Subject |
| message | string | No | — | Message |
| priority | string | No | in:low,medium,high,urgent | Priority |
| category | string | No | in:category keys | Category |
| attachment | file | No | file, max:10MB | Replace ticket attachment (jpg, jpeg, png, pdf, doc, docx, txt, zip) |
| user_notes | string | No | max:1000 | User notes |
| status | string | No | in:status keys | **Admin only** |
| admin_notes | string | No | max:2000 | **Admin only** |
| assigned_to | integer | No | exists:users,id | **Admin only** – assignee user ID |
| assigned_admin_id | integer | No | exists:users,id | **Admin only** – assignee user ID |
| resolved_at | string | No | date | **Admin only** – ISO date |

### Success (200)

```json
{
  "message": "Support ticket updated successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user_id": 5,
    "subject": "Login issue",
    "message": "Updated: I have tried clearing cache as well.",
    "priority": "high",
    "status": "in_progress",
    "category": "technical",
    "ticket_number": "TKT2025030001",
    "assigned_to": 2,
    "admin_notes": "Assigned to support team.",
    "user_notes": "Browser: Chrome on Windows.",
    "created_at": "2025-03-06T10:00:00.000000Z",
    "updated_at": "2025-03-06T12:00:00.000000Z",
    "user": { },
    "assigned_admin": { "id": 2, "name": "Support Admin", "email": "support@example.com" }
  }
}
```

### Error (403)

```json
{
  "message": "You are not allowed to update this ticket.",
  "success": false,
  "code": 403
}
```

---

## 6. Delete Support Ticket

| | |
|---|---|
| **Endpoint** | `DELETE /api/v1/support-tickets/{supportTicket}` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Soft-deletes a ticket. Allowed only if user owns the ticket or is admin.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| supportTicket | integer | **Yes** | Ticket ID |

### Success (204)

No content (empty body).

### Error (403)

```json
{
  "message": "You are not allowed to delete this ticket.",
  "success": false,
  "code": 403
}
```

---

## 7. Reply to Support Ticket

| | |
|---|---|
| **Endpoint** | `POST /api/v1/support-tickets/{supportTicket}/reply` |
| **Content-Type** | `multipart/form-data` or `application/json` |
| **Access** | Protected (auth:sanctum) |

Adds a reply to a ticket. Ticket status is updated automatically: admin reply → `waiting_for_user`; user reply → `in_progress`.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| supportTicket | integer | **Yes** | Ticket ID |

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| message | string | **Yes** | — | Reply message |
| attachment | file | No | file, max:10MB | Supported: jpg, jpeg, png, pdf, doc, docx, txt, zip |
| internal_notes | string | No | max:1000 | Internal notes (admin only, not shown to user) |

### Success (201)

```json
{
  "message": "Reply added successfully.",
  "success": true,
  "code": 201,
  "data": {
    "id": 1,
    "support_ticket_id": 1,
    "user_id": 2,
    "message": "We are looking into this. Please try again in a few minutes.",
    "is_admin_reply": true,
    "attachment_path": null,
    "internal_notes": null,
    "created_at": "2025-03-06T11:00:00.000000Z",
    "updated_at": "2025-03-06T11:00:00.000000Z",
    "user": { "id": 2, "name": "Support Admin", "email": "support@example.com" }
  }
}
```

### Error (403)

```json
{
  "message": "You are not allowed to reply to this ticket.",
  "success": false,
  "code": 403
}
```

---

## 8. Download Ticket Attachment

| | |
|---|---|
| **Endpoint** | `GET /api/v1/support-tickets/{supportTicket}/attachment` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Downloads the main ticket attachment (from create/update). Returns the file as a download response.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| supportTicket | integer | **Yes** | Ticket ID |

### Query Parameters

None.

### Success (200)

Binary file download (Content-Disposition: attachment).

### Error (403)

```json
{
  "message": "You are not allowed to download attachments from this ticket.",
  "success": false,
  "code": 403
}
```

### Error (404)

```json
{
  "message": "No attachment found for this ticket.",
  "success": false,
  "code": 404
}
```

---

## 9. Download Reply Attachment

| | |
|---|---|
| **Endpoint** | `GET /api/v1/support-tickets/{supportTicket}/replies/{reply}/attachment` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Downloads an attachment from a specific reply.

### Path Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| supportTicket | integer | **Yes** | Ticket ID |
| reply | integer | **Yes** | Reply ID |

### Query Parameters

None.

### Success (200)

Binary file download (Content-Disposition: attachment).

### Error (403)

```json
{
  "message": "You are not allowed to download attachments from this ticket.",
  "success": false,
  "code": 403
}
```

```json
{
  "message": "Reply does not belong to this ticket.",
  "success": false,
  "code": 403
}
```

### Error (404)

```json
{
  "message": "No attachment found for this reply.",
  "success": false,
  "code": 404
}
```

---

## Valid Values Reference

### Priority

| Value | Label |
|-------|-------|
| low | Low |
| medium | Medium |
| high | High |
| urgent | Urgent |

### Status

| Value | Label |
|-------|-------|
| open | Open |
| in_progress | In Progress |
| waiting_for_user | Waiting for User |
| resolved | Resolved |
| closed | Closed |

### Category

| Value | Label |
|-------|-------|
| technical | Technical Issue |
| billing | Billing & Payment |
| account | Account Issue |
| subject | Subject Related |
| exam | Exam Related |
| new_subject_request | Request New Subject |
| new_exam_request | Request New Exam |
| new_exam_category_request | Request New Exam Category |
| feature_request | Feature Request |
| bug_report | Bug Report |
| general | General Inquiry |

### Allowed Attachment Types

- **Images**: jpg, jpeg, png  
- **Documents**: pdf, doc, docx, txt  
- **Archives**: zip  
- **Max size**: 10 MB per file

---

## Example Requests

### Get Options (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/support-tickets/options" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### List Tickets (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/support-tickets?status=open&per_page=20" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Create Ticket (cURL – with attachment)

```bash
curl -X POST "https://api.example.com/api/v1/support-tickets" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -F "subject=Cannot access exam after payment" \
  -F "message=I paid yesterday but the exam section still shows locked." \
  -F "priority=high" \
  -F "category=billing" \
  -F "user_notes=Paid via UPI" \
  -F "attachment=@screenshot.png"
```

### Create Ticket (cURL – JSON, no attachment)

```bash
curl -X POST "https://api.example.com/api/v1/support-tickets" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"subject":"Login issue","message":"Cannot login.","priority":"high","category":"technical"}'
```

### Reply (cURL)

```bash
curl -X POST "https://api.example.com/api/v1/support-tickets/1/reply" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -F "message=Here is the screenshot you requested." \
  -F "attachment=@screenshot.png"
```

### Download Ticket Attachment (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/support-tickets/1/attachment" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -o attachment.pdf
```

### Download Reply Attachment (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/support-tickets/1/replies/3/attachment" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -o reply-attachment.pdf
```

### JavaScript (Fetch – Create with FormData)

```javascript
const formData = new FormData();
formData.append('subject', 'Cannot access exam');
formData.append('message', 'I paid yesterday but exam is locked.');
formData.append('priority', 'high');
formData.append('category', 'billing');
formData.append('attachment', fileInput.files[0]);

const response = await fetch('/api/v1/support-tickets', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
  body: formData,
});
```

### JavaScript (Fetch – Reply)

```javascript
const formData = new FormData();
formData.append('message', 'Here is the screenshot.');
formData.append('attachment', fileInput.files[0]);

const response = await fetch('/api/v1/support-tickets/1/reply', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
  body: formData,
});
```

---

## Error Codes Summary

| Code | Condition |
|------|-----------|
| 204 | No content (delete success) |
| 401 | Unauthenticated |
| 403 | Forbidden (not owner/admin, or reply not in ticket) |
| 404 | Ticket/reply not found, or no attachment |
| 422 | Validation failed |
| 500 | Server error (e.g. upload failure) |
