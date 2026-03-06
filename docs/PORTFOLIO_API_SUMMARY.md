# Portfolio API - Quick Reference

## 🌐 Public Access (No Authentication Required)

### View Any User's Portfolios
```bash
GET /api/v1/portfolios/user/{userId}
```

**Examples:**
```bash
# Get all portfolios for user 5
GET /api/v1/portfolios/user/5

# Get featured portfolios only
GET /api/v1/portfolios/user/5?featured=1

# Filter by category
GET /api/v1/portfolios/user/5?category=Web Development

# Filter by tag
GET /api/v1/portfolios/user/5?tag=Laravel

# Search
GET /api/v1/portfolios/user/5?search=ecommerce
```

### View All Published Portfolios
```bash
GET /api/v1/portfolios
```

**Examples:**
```bash
# Get all published portfolios
GET /api/v1/portfolios

# Filter by specific user
GET /api/v1/portfolios?user_id=5

# Filter by category
GET /api/v1/portfolios?category=Design

# Search across all portfolios
GET /api/v1/portfolios?search=Laravel
```

### View Single Portfolio
```bash
GET /api/v1/portfolios/{id}
```

---

## 🔒 Protected Routes (Authentication Required)

### Get My Portfolios (All Statuses)
```bash
GET /api/v1/portfolios/my/portfolios
Authorization: Bearer {token}
```

### Create Portfolio
```bash
POST /api/v1/portfolios
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "title": "My Project",
  "description": "Project description",
  "category": "Web Development, E-commerce",
  "tags": "Laravel, Vue.js, MySQL",
  "url": "https://example.com",
  "status": "published",
  "is_featured": true,
  "images[]": [file],
  "files[]": [file]
}
```

### Update Portfolio
```bash
PUT /api/v1/portfolios/{id}
Authorization: Bearer {token}
```

### Delete Portfolio
```bash
DELETE /api/v1/portfolios/{id}
Authorization: Bearer {token}
```

### Toggle Featured
```bash
POST /api/v1/portfolios/{id}/toggle-featured
Authorization: Bearer {token}
```

### Reorder Portfolios
```bash
POST /api/v1/portfolios/reorder
Authorization: Bearer {token}

{
  "portfolios": [
    { "id": 1, "order": 0 },
    { "id": 2, "order": 1 }
  ]
}
```

---

## 📝 Tags and Categories

Both accept **comma-separated strings**:

```json
{
  "category": "Web Development, Mobile App, SaaS",
  "tags": "Laravel, React, PostgreSQL, Redis, AWS"
}
```

**API Response includes both formats:**
```json
{
  "category": "Web Development, Mobile App",
  "categories": ["Web Development", "Mobile App"],
  "tags": "Laravel, React, Redis",
  "tags_array": ["Laravel", "React", "Redis"]
}
```

---

## 🔍 Filtering Options

| Parameter | Description | Example |
|-----------|-------------|---------|
| `user_id` | Filter by user ID | `?user_id=5` |
| `category` | Filter by category (partial match) | `?category=Web Development` |
| `tag` | Filter by tag (partial match) | `?tag=Laravel` |
| `status` | Filter by status | `?status=published` |
| `featured` | Show only featured | `?featured=1` |
| `search` | Search in title, description, tags, category | `?search=ecommerce` |
| `per_page` | Items per page | `?per_page=20` |
| `page` | Page number | `?page=2` |

---

## 📊 Response Format

```json
{
  "success": true,
  "message": "Portfolios retrieved successfully.",
  "code": 200,
  "data": [
    {
      "id": 1,
      "user_id": 5,
      "user": {
        "id": 5,
        "name": "John Doe",
        "email": "john@example.com"
      },
      "title": "E-commerce Platform",
      "description": "Full-featured online store",
      "images": [
        {
          "path": "portfolios/image.jpg",
          "url": "http://localhost:8000/storage/portfolios/image.jpg"
        }
      ],
      "files": [
        {
          "path": "portfolios/doc.pdf",
          "url": "http://localhost:8000/storage/portfolios/doc.pdf",
          "name": "doc.pdf"
        }
      ],
      "category": "Web Development, E-commerce",
      "categories": ["Web Development", "E-commerce"],
      "tags": "Laravel, Vue.js, MySQL",
      "tags_array": ["Laravel", "Vue.js", "MySQL"],
      "url": "https://example.com",
      "status": "published",
      "order": 1,
      "is_featured": true,
      "created_at": "2026-03-06T10:30:00.000Z",
      "updated_at": "2026-03-06T12:00:00.000Z"
    }
  ],
  "links": { ... },
  "meta": { ... }
}
```

---

## 🎯 Key Features

✅ **Public Access** - No authentication needed to view portfolios
✅ **User-Specific Filtering** - Get portfolios by user ID
✅ **Flexible Tags & Categories** - Comma-separated strings
✅ **File Uploads** - Images (5MB) and documents (10MB)
✅ **Search & Filter** - Multiple filtering options
✅ **Pagination** - Efficient data loading
✅ **Featured Portfolios** - Highlight important work
✅ **Custom Ordering** - Control display order
✅ **Privacy Control** - Draft/Published/Archived statuses

---

## 🚀 Quick Start Examples

### Frontend Integration (JavaScript)

```javascript
// Get user's portfolios (no auth needed)
async function getUserPortfolios(userId) {
  const response = await fetch(`/api/v1/portfolios/user/${userId}`);
  const data = await response.json();
  return data.data;
}

// Create portfolio (auth required)
async function createPortfolio(token, formData) {
  const response = await fetch('/api/v1/portfolios', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData // FormData with files
  });
  return await response.json();
}

// Search portfolios (no auth needed)
async function searchPortfolios(query) {
  const response = await fetch(`/api/v1/portfolios?search=${query}`);
  const data = await response.json();
  return data.data;
}
```

---

## 📁 File Storage Structure

### Portfolio Files
- **Images**: `storage/app/public/portfolios/images/`
- **Documents**: `storage/app/public/portfolios/`

### Support Tickets
- **Attachments**: `storage/app/public/support-tickets/`

### Public URLs
```
http://domain.com/storage/portfolios/images/filename.jpg
http://domain.com/storage/portfolios/filename.pdf
http://domain.com/storage/support-tickets/filename.pdf
```

### Setup Storage Link
```bash
php artisan storage:link
```

---

## 📖 Full Documentation

- [Portfolio API](docs/PortfolioApi.md) - Complete API documentation
- [Storage Structure](docs/StorageStructure.md) - File storage details
