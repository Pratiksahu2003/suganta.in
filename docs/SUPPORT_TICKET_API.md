# Support Ticket API Documentation

## Overview
Complete support ticket system with file attachments, replies, and admin management.

## Storage Configuration
- **Attachments stored in**: `storage/app/public/support-tickets/`
- **Public URL**: `/storage/support-tickets/{filename}`
- **Max file size**: 10MB (10240 KB)
- **Allowed formats**: JPG, JPEG, PNG, PDF, DOC, DOCX, TXT, ZIP

## Setup Instructions

### 1. Create Storage Symlink
```bash
php artisan storage:link
```

This creates a symbolic link from `public/storage` to `storage/app/public`.

### 2. Set Permissions (Linux/Mac)
```bash
chmod -R 775 storage
chmod -R 775 public/storage
```

### 3. Verify Directory Structure
```
storage/
  app/
    public/
      support-tickets/  ← Attachments stored here
public/
  storage/  ← Symlink to storage/app/public
```

## API Endpoints

### Base URL
```
/api/v1/support-tickets
```

### Authentication
All endpoints require authentication via Sanctum:
```
Authorization: Bearer {token}
```

---

## 1. Get Options (Dropdown Values)

### Endpoint
```http
GET /api/v1/support-tickets/options
```

### Response
```json
{
  "success": true,
  "message": "Support ticket options retrieved successfully.",
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

### Endpoint
```http
GET /api/v1/support-tickets
```

### Query Parameters
| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status (open, in_progress, etc.) |
| `priority` | string | Filter by priority (low, medium, high, urgent) |
| `category` | string | Filter by category |
| `user_id` | integer | Filter by user (admin only) |
| `per_page` | integer | Items per page (default: 15) |
| `page` | integer | Page number |

### Example Request
```http
GET /api/v1/support-tickets?status=open&priority=high&per_page=20
```

### Response
```json
{
  "success": true,
  "message": "Support tickets retrieved successfully.",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "ticket_number": "TKT2026030001",
        "user_id": 5,
        "subject": "Cannot access my sessions",
        "message": "I'm unable to view my scheduled sessions...",
        "priority": "high",
        "status": "open",
        "category": "technical",
        "attachment_path": "support-tickets/ticket_5_20260306143022_a1b2c3d4_screenshot.png",
        "user_notes": null,
        "admin_notes": null,
        "assigned_to": null,
        "resolved_at": null,
        "created_at": "2026-03-06T14:30:22.000000Z",
        "updated_at": "2026-03-06T14:30:22.000000Z",
        "user": {
          "id": 5,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "assigned_admin": null
      }
    ],
    "per_page": 15,
    "total": 1
  }
}
```

---

## 3. Create Support Ticket

### Endpoint
```http
POST /api/v1/support-tickets
Content-Type: multipart/form-data
```

### Request Body (Form Data)
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `subject` | string | Yes | Ticket subject (max 255 chars) |
| `message` | string | Yes | Detailed description |
| `priority` | string | Yes | low, medium, high, urgent |
| `category` | string | Yes | See categories in options |
| `attachment` | file | No | File attachment (max 10MB) |
| `user_notes` | string | No | Additional notes (max 1000 chars) |

### Example Request (cURL)
```bash
curl -X POST https://api.example.com/api/v1/support-tickets \
  -H "Authorization: Bearer {token}" \
  -F "subject=Cannot login to my account" \
  -F "message=I'm getting an error when trying to log in..." \
  -F "priority=high" \
  -F "category=technical" \
  -F "attachment=@/path/to/screenshot.png"
```

### Example Request (JavaScript/Axios)
```javascript
const formData = new FormData();
formData.append('subject', 'Cannot login to my account');
formData.append('message', 'I\'m getting an error...');
formData.append('priority', 'high');
formData.append('category', 'technical');
formData.append('attachment', fileInput.files[0]);

const response = await axios.post('/api/v1/support-tickets', formData, {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'multipart/form-data'
  }
});
```

### Response
```json
{
  "success": true,
  "message": "Support ticket created successfully.",
  "data": {
    "id": 1,
    "ticket_number": "TKT2026030001",
    "user_id": 5,
    "subject": "Cannot login to my account",
    "message": "I'm getting an error when trying to log in...",
    "priority": "high",
    "status": "open",
    "category": "technical",
    "attachment_path": "support-tickets/ticket_5_20260306143022_a1b2c3d4_screenshot.png",
    "user_notes": null,
    "admin_notes": null,
    "assigned_to": null,
    "resolved_at": null,
    "created_at": "2026-03-06T14:30:22.000000Z",
    "updated_at": "2026-03-06T14:30:22.000000Z",
    "user": {
      "id": 5,
      "name": "John Doe",
      "email": "john@example.com"
    }
  }
}
```

---

## 4. View Support Ticket

### Endpoint
```http
GET /api/v1/support-tickets/{id}
```

### Response
```json
{
  "success": true,
  "message": "Support ticket retrieved successfully.",
  "data": {
    "id": 1,
    "ticket_number": "TKT2026030001",
    "subject": "Cannot login to my account",
    "message": "I'm getting an error...",
    "priority": "high",
    "status": "in_progress",
    "category": "technical",
    "attachment_path": "support-tickets/ticket_5_20260306143022_a1b2c3d4_screenshot.png",
    "user": {
      "id": 5,
      "name": "John Doe"
    },
    "assigned_admin": {
      "id": 1,
      "name": "Admin User"
    },
    "replies": [
      {
        "id": 1,
        "message": "We're looking into this issue...",
        "is_admin_reply": true,
        "attachment_path": null,
        "created_at": "2026-03-06T15:00:00.000000Z",
        "user": {
          "id": 1,
          "name": "Admin User"
        }
      }
    ]
  }
}
```

---

## 5. Update Support Ticket

### Endpoint
```http
PUT /api/v1/support-tickets/{id}
PATCH /api/v1/support-tickets/{id}
Content-Type: multipart/form-data
```

### Request Body (User)
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `subject` | string | No | Update subject |
| `message` | string | No | Update message |
| `priority` | string | No | Update priority |
| `category` | string | No | Update category |
| `attachment` | file | No | Replace attachment |
| `user_notes` | string | No | Update notes |

### Request Body (Admin Only)
Additional fields for admins:
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `status` | string | No | Update status |
| `admin_notes` | string | No | Internal admin notes (max 2000 chars) |
| `assigned_to` | integer | No | Assign to admin user ID |
| `resolved_at` | date | No | Resolution timestamp |

### Example Request
```bash
curl -X PUT https://api.example.com/api/v1/support-tickets/1 \
  -H "Authorization: Bearer {token}" \
  -F "status=in_progress" \
  -F "assigned_to=1" \
  -F "admin_notes=Investigating the login issue"
```

### Response
```json
{
  "success": true,
  "message": "Support ticket updated successfully.",
  "data": {
    "id": 1,
    "status": "in_progress",
    "assigned_to": 1,
    "admin_notes": "Investigating the login issue",
    "updated_at": "2026-03-06T15:00:00.000000Z"
  }
}
```

---

## 6. Reply to Support Ticket

### Endpoint
```http
POST /api/v1/support-tickets/{id}/reply
Content-Type: multipart/form-data
```

### Request Body
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `message` | string | Yes | Reply message |
| `attachment` | file | No | File attachment (max 10MB) |
| `internal_notes` | string | No | Internal notes (max 1000 chars) |

### Example Request
```bash
curl -X POST https://api.example.com/api/v1/support-tickets/1/reply \
  -H "Authorization: Bearer {token}" \
  -F "message=Thank you for your response. I tried the solution..." \
  -F "attachment=@/path/to/error-log.txt"
```

### Response
```json
{
  "success": true,
  "message": "Reply added successfully.",
  "data": {
    "id": 2,
    "support_ticket_id": 1,
    "user_id": 5,
    "message": "Thank you for your response. I tried the solution...",
    "is_admin_reply": false,
    "attachment_path": "support-tickets/reply_5_20260306153045_x9y8z7w6_error-log.txt",
    "internal_notes": null,
    "created_at": "2026-03-06T15:30:45.000000Z",
    "user": {
      "id": 5,
      "name": "John Doe"
    }
  }
}
```

---

## 7. Download Ticket Attachment

### Endpoint
```http
GET /api/v1/support-tickets/{id}/attachment
```

### Response
- **Success**: File download (binary)
- **Error**: JSON error response

### Example Request
```bash
curl -X GET https://api.example.com/api/v1/support-tickets/1/attachment \
  -H "Authorization: Bearer {token}" \
  -o downloaded-file.png
```

---

## 8. Download Reply Attachment

### Endpoint
```http
GET /api/v1/support-tickets/{ticketId}/replies/{replyId}/attachment
```

### Response
- **Success**: File download (binary)
- **Error**: JSON error response

### Example Request
```bash
curl -X GET https://api.example.com/api/v1/support-tickets/1/replies/2/attachment \
  -H "Authorization: Bearer {token}" \
  -o reply-attachment.txt
```

---

## 9. Delete Support Ticket

### Endpoint
```http
DELETE /api/v1/support-tickets/{id}
```

### Response
```http
HTTP/1.1 204 No Content
```

---

## File Upload Specifications

### Filename Format
```
{type}_{userId}_{timestamp}_{random}_{originalName}.{extension}

Examples:
- ticket_5_20260306143022_a1b2c3d4e5f6g7h8_screenshot.png
- reply_5_20260306153045_x9y8z7w6v5u4t3s2_error-log.txt
```

### Security Features
- ✅ User ID included in filename
- ✅ Timestamp for uniqueness
- ✅ Random string to prevent guessing
- ✅ Sanitized original filename (special chars removed)
- ✅ File type validation
- ✅ Size limit enforcement
- ✅ Permission checks before download

### Storage Path
```
storage/app/public/support-tickets/
  ├── ticket_5_20260306143022_a1b2c3d4_screenshot.png
  ├── reply_5_20260306153045_x9y8z7w6_error-log.txt
  └── ticket_7_20260306160000_b2c3d4e5_invoice.pdf
```

---

## Permissions & Access Control

### User Permissions
| Action | User (Owner) | Admin | Other Users |
|--------|--------------|-------|-------------|
| Create Ticket | ✅ | ✅ | ✅ |
| View Own Tickets | ✅ | ✅ | ❌ |
| View All Tickets | ❌ | ✅ | ❌ |
| Update Own Ticket | ✅ | ✅ | ❌ |
| Update Status | ❌ | ✅ | ❌ |
| Assign Ticket | ❌ | ✅ | ❌ |
| Reply to Ticket | ✅ | ✅ | ❌ |
| Download Attachments | ✅ | ✅ | ❌ |
| Delete Ticket | ✅ | ✅ | ❌ |

---

## Notifications

### Automatic Notifications Sent:

#### 1. Ticket Created
- **To User**: Confirmation with ticket number
- **To Admins**: New ticket alert with priority

#### 2. Ticket Updated
- **To User**: Update notification with changes
- **To Assignee**: Assignment notification (if assigned)

#### 3. Reply Added
- **To Ticket Owner**: Reply notification
- **To Assigned Admin**: Reply notification (if different from replier)

### Notification Data Structure
```json
{
  "title": "Support Ticket Created",
  "message": "Your support ticket has been created successfully",
  "type": "support",
  "priority": "normal",
  "resource_type": "support_ticket",
  "resource_id": 1,
  "action": "view",
  "ticket_id": 1,
  "subject": "Cannot login",
  "priority": "high",
  "status": "open"
}
```

---

## Error Responses

### Validation Error (422)
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "subject": ["The subject field is required."],
    "attachment": ["The attachment must be a file of type: jpg, png, pdf."]
  }
}
```

### Forbidden (403)
```json
{
  "success": false,
  "message": "You are not allowed to access this ticket."
}
```

### Not Found (404)
```json
{
  "success": false,
  "message": "Support ticket not found."
}
```

### Server Error (500)
```json
{
  "success": false,
  "message": "Failed to create support ticket. Please try again."
}
```

---

## Usage Examples

### React/Next.js Example
```javascript
import axios from 'axios';

// Create ticket with attachment
async function createTicket(data, file) {
  const formData = new FormData();
  formData.append('subject', data.subject);
  formData.append('message', data.message);
  formData.append('priority', data.priority);
  formData.append('category', data.category);
  
  if (file) {
    formData.append('attachment', file);
  }
  
  try {
    const response = await axios.post('/api/v1/support-tickets', formData, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    });
    
    return response.data;
  } catch (error) {
    console.error('Failed to create ticket:', error.response.data);
    throw error;
  }
}

// Reply to ticket
async function replyToTicket(ticketId, message, file) {
  const formData = new FormData();
  formData.append('message', message);
  
  if (file) {
    formData.append('attachment', file);
  }
  
  const response = await axios.post(
    `/api/v1/support-tickets/${ticketId}/reply`,
    formData,
    {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'multipart/form-data'
      }
    }
  );
  
  return response.data;
}

// Download attachment
async function downloadAttachment(ticketId) {
  const response = await axios.get(
    `/api/v1/support-tickets/${ticketId}/attachment`,
    {
      headers: { 'Authorization': `Bearer ${token}` },
      responseType: 'blob'
    }
  );
  
  // Create download link
  const url = window.URL.createObjectURL(new Blob([response.data]));
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', 'attachment.png');
  document.body.appendChild(link);
  link.click();
  link.remove();
}
```

### Flutter/Dart Example
```dart
import 'package:dio/dio.dart';

// Create ticket with attachment
Future<Map<String, dynamic>> createTicket({
  required String subject,
  required String message,
  required String priority,
  required String category,
  File? attachment,
}) async {
  final formData = FormData.fromMap({
    'subject': subject,
    'message': message,
    'priority': priority,
    'category': category,
  });
  
  if (attachment != null) {
    formData.files.add(MapEntry(
      'attachment',
      await MultipartFile.fromFile(
        attachment.path,
        filename: attachment.path.split('/').last,
      ),
    ));
  }
  
  final response = await dio.post(
    '/api/v1/support-tickets',
    data: formData,
    options: Options(
      headers: {'Authorization': 'Bearer $token'},
    ),
  );
  
  return response.data;
}

// Download attachment
Future<void> downloadAttachment(int ticketId, String savePath) async {
  await dio.download(
    '/api/v1/support-tickets/$ticketId/attachment',
    savePath,
    options: Options(
      headers: {'Authorization': 'Bearer $token'},
    ),
  );
}
```

---

## Testing

### Manual Testing with Postman
1. Create a new request
2. Set method to `POST`
3. URL: `http://localhost:8000/api/v1/support-tickets`
4. Headers: `Authorization: Bearer {your-token}`
5. Body → form-data:
   - `subject`: "Test ticket"
   - `message`: "This is a test"
   - `priority`: "medium"
   - `category`: "technical"
   - `attachment`: Select file

### Testing File Upload
```bash
# Create test ticket with attachment
curl -X POST http://localhost:8000/api/v1/support-tickets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "subject=Test Ticket" \
  -F "message=Testing file upload" \
  -F "priority=medium" \
  -F "category=technical" \
  -F "attachment=@test-image.png"

# Download attachment
curl -X GET http://localhost:8000/api/v1/support-tickets/1/attachment \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o downloaded-file.png
```

---

## Best Practices

### For API Consumers:
1. Always validate file size before upload (max 10MB)
2. Check file type before upload (only allowed formats)
3. Handle download errors gracefully
4. Show upload progress for better UX
5. Cache ticket list and refresh on updates

### For API Developers:
1. Monitor storage usage regularly
2. Implement file cleanup for deleted tickets
3. Consider moving to cloud storage (S3) for production
4. Add virus scanning for uploaded files
5. Implement rate limiting for file uploads

---

## Troubleshooting

### Issue: "Storage link not found"
**Solution**: Run `php artisan storage:link`

### Issue: "Permission denied" when uploading
**Solution**: 
```bash
chmod -R 775 storage
chmod -R 775 public/storage
```

### Issue: "File not found" when downloading
**Solution**: Check if symlink exists:
```bash
ls -la public/storage
```

### Issue: "File too large"
**Solution**: 
1. Check `php.ini` settings:
   - `upload_max_filesize = 10M`
   - `post_max_size = 12M`
2. Check nginx/apache config if applicable

---

## Security Considerations

1. ✅ **File Type Validation** - Only allowed MIME types
2. ✅ **File Size Limit** - Max 10MB to prevent abuse
3. ✅ **Permission Checks** - Users can only access their own tickets
4. ✅ **Sanitized Filenames** - Special characters removed
5. ✅ **Random Strings** - Prevents filename guessing
6. ✅ **Secure Storage** - Files stored outside public root
7. ✅ **Audit Logging** - All actions logged

### Recommended Enhancements:
- Add virus scanning (ClamAV)
- Implement file encryption at rest
- Add watermarking for images
- Implement CDN for file delivery
- Add file retention policies
