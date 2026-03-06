# Support Ticket API - Quick Reference

## 🚀 Quick Start

### 1. Setup (One-time)
```bash
php artisan storage:link
mkdir storage/app/public/support-tickets
```

### 2. Test Upload
```bash
curl -X POST http://localhost:8000/api/v1/support-tickets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "subject=Test" \
  -F "message=Testing" \
  -F "priority=low" \
  -F "category=technical" \
  -F "attachment=@test.png"
```

---

## 📍 Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/support-tickets/options` | Get dropdown options |
| `GET` | `/api/v1/support-tickets` | List tickets |
| `POST` | `/api/v1/support-tickets` | Create ticket |
| `GET` | `/api/v1/support-tickets/{id}` | View ticket |
| `PUT/PATCH` | `/api/v1/support-tickets/{id}` | Update ticket |
| `DELETE` | `/api/v1/support-tickets/{id}` | Delete ticket |
| `POST` | `/api/v1/support-tickets/{id}/reply` | Reply to ticket |
| `GET` | `/api/v1/support-tickets/{id}/attachment` | Download ticket attachment |
| `GET` | `/api/v1/support-tickets/{id}/replies/{replyId}/attachment` | Download reply attachment |

---

## 📝 Create Ticket

### Request
```http
POST /api/v1/support-tickets
Content-Type: multipart/form-data
Authorization: Bearer {token}

subject: Cannot login
message: I'm getting an error...
priority: high
category: technical
attachment: [FILE]
```

### Response
```json
{
  "success": true,
  "message": "Support ticket created successfully.",
  "data": {
    "id": 1,
    "ticket_number": "TKT2026030001",
    "subject": "Cannot login",
    "status": "open",
    "attachment_path": "support-tickets/ticket_5_20260306143022_a1b2c3d4_screenshot.png"
  }
}
```

---

## 💬 Reply to Ticket

### Request
```http
POST /api/v1/support-tickets/1/reply
Content-Type: multipart/form-data
Authorization: Bearer {token}

message: Here's the error log
attachment: [FILE]
```

### Response
```json
{
  "success": true,
  "message": "Reply added successfully.",
  "data": {
    "id": 1,
    "message": "Here's the error log",
    "is_admin_reply": false,
    "attachment_path": "support-tickets/reply_5_20260306153045_x9y8z7w6_error.txt"
  }
}
```

---

## 📥 Download Attachment

### Request
```bash
curl -X GET http://localhost:8000/api/v1/support-tickets/1/attachment \
  -H "Authorization: Bearer TOKEN" \
  -o downloaded-file.png
```

---

## 🎨 Client Examples

### JavaScript/React
```javascript
// Create ticket
const formData = new FormData();
formData.append('subject', 'Help needed');
formData.append('message', 'Description');
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

### Flutter/Dart
```dart
// Create ticket
final formData = FormData.fromMap({
  'subject': 'Help needed',
  'message': 'Description',
  'priority': 'high',
  'category': 'technical',
});

if (file != null) {
  formData.files.add(MapEntry(
    'attachment',
    await MultipartFile.fromFile(file.path),
  ));
}

final response = await dio.post('/api/v1/support-tickets', data: formData);
```

---

## 🔐 Options Values

### Priorities
- `low` - Low
- `medium` - Medium
- `high` - High
- `urgent` - Urgent

### Statuses
- `open` - Open
- `in_progress` - In Progress
- `waiting_for_user` - Waiting for User
- `resolved` - Resolved
- `closed` - Closed

### Categories
- `technical` - Technical Issue
- `billing` - Billing & Payment
- `account` - Account Issue
- `subject` - Subject Related
- `exam` - Exam Related
- `new_subject_request` - Request New Subject
- `new_exam_request` - Request New Exam
- `new_exam_category_request` - Request New Exam Category
- `feature_request` - Feature Request
- `bug_report` - Bug Report
- `general` - General Inquiry

---

## 📋 Validation Rules

### Create/Update Ticket
| Field | Type | Required | Max Size | Allowed Types |
|-------|------|----------|----------|---------------|
| `subject` | string | Yes | 255 chars | - |
| `message` | string | Yes | - | - |
| `priority` | string | Yes | - | low, medium, high, urgent |
| `category` | string | Yes | - | See categories above |
| `attachment` | file | No | 10MB | jpg, jpeg, png, pdf, doc, docx, txt, zip |
| `user_notes` | string | No | 1000 chars | - |

### Admin-Only Fields
| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `status` | string | No | Change ticket status |
| `admin_notes` | string | No | Internal notes (max 2000 chars) |
| `assigned_to` | integer | No | Assign to admin user ID |

---

## ⚠️ Common Errors

### 422 - Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "attachment": ["The attachment must be a file of type: jpg, png, pdf."]
  }
}
```

### 403 - Forbidden
```json
{
  "success": false,
  "message": "You are not allowed to access this ticket."
}
```

### 404 - Not Found
```json
{
  "success": false,
  "message": "Support ticket not found."
}
```

---

## 🔧 Troubleshooting

### "Storage link not found"
```bash
php artisan storage:link
```

### "Permission denied"
```bash
# Linux/Mac
chmod -R 775 storage
chmod -R 775 public/storage
```

### "File too large"
Check `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 12M
```

---

## 📱 Notifications

### Automatic notifications sent for:
✅ Ticket created  
✅ Ticket updated (status/priority/assignment changes)  
✅ Reply added  

### Notification payload:
```json
{
  "title": "Support Ticket Created",
  "message": "Your ticket #TKT2026030001 has been created",
  "type": "support",
  "resource_type": "support_ticket",
  "resource_id": 1,
  "action": "view"
}
```

---

## 🎯 Best Practices

### For Clients:
1. ✅ Validate file size before upload (< 10MB)
2. ✅ Check file type before upload
3. ✅ Show upload progress
4. ✅ Handle errors gracefully
5. ✅ Cache ticket list

### For API:
1. ✅ Monitor storage usage
2. ✅ Implement cleanup for old files
3. ✅ Add rate limiting
4. ✅ Consider cloud storage (S3)
5. ✅ Add virus scanning

---

## 📊 Storage Info

**Location**: `storage/app/public/support-tickets/`  
**Public URL**: `/storage/support-tickets/{filename}`  
**Max Size**: 10MB per file  
**Allowed Types**: Images, PDFs, Documents, Archives  

**Filename Format**:
```
ticket_5_20260306143022_a1b2c3d4_screenshot.png
└─┬──┘ └┬┘ └────┬─────┘ └──┬───┘ └────┬─────┘
  │     │       │           │          │
  type  user   timestamp   random   original name
```

---

## 📚 Full Documentation

- **Complete API Reference**: `SUPPORT_TICKET_API.md`
- **Setup Guide**: `SETUP_STORAGE.md`
- **Optimization Summary**: `SUPPORT_TICKET_OPTIMIZATION_SUMMARY.md`
- **This Quick Reference**: `SUPPORT_TICKET_QUICK_REFERENCE.md`

---

## ✅ Checklist

### Before Going Live:
- [ ] Run `php artisan storage:link`
- [ ] Create `storage/app/public/support-tickets` directory
- [ ] Set proper permissions
- [ ] Test file upload
- [ ] Test file download
- [ ] Configure PHP upload limits
- [ ] Configure web server limits
- [ ] Set up backup strategy
- [ ] Implement cleanup cron job
- [ ] Test notifications
- [ ] Update client applications

---

**Ready to use! 🚀**
