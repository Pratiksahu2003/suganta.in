# Portfolio API

Endpoints for managing the authenticated user's portfolio. **One portfolio per user only.** All data is user-scoped.

**Base path**: `/api/v1`  
**Auth**: All endpoints require Bearer token (Sanctum)

---

## Endpoints Summary

| Method | Endpoint | Description | Access |
|--------|----------|-------------|--------|
| GET | `/portfolios/options` | Get dropdown options (statuses, categories, tags) | Authenticated user |
| GET | `/portfolios` | Get auth user's portfolio (single object or null) | Authenticated user |
| POST | `/portfolios` | Create portfolio (only if user has none) | Authenticated user |
| PUT/PATCH | `/portfolios` | Update auth user's portfolio | Authenticated user |

---

## 1. Get Options

| | |
|---|---|
| **Endpoint** | `GET /api/v1/portfolios/options` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Returns dropdown options for statuses, and the user's existing categories and tags from their portfolio.

### Query Parameters

None.

### Success (200)

```json
{
  "message": "Portfolio options retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "statuses": {
      "draft": "Draft",
      "published": "Published",
      "archived": "Archived"
    },
    "categories": ["Web Design", "Mobile App", "Branding"],
    "tags": ["React", "Laravel", "UI/UX"]
  }
}
```

---

## 2. Get Portfolio

| | |
|---|---|
| **Endpoint** | `GET /api/v1/portfolios` |
| **Content-Type** | — |
| **Access** | Protected (auth:sanctum) |

Returns the authenticated user's single portfolio. Returns `null` if the user has not created one yet.

### Query Parameters

None.

### Success (200) – Portfolio exists

```json
{
  "message": "Portfolio retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user_id": 1,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "title": "E-commerce Website",
    "description": "Full-stack e-commerce platform.",
    "images": [
      {
        "path": "users/1/portfolio/image_xxx.jpg",
        "url": "https://api.example.com/storage/users/1/portfolio/image_xxx.jpg"
      }
    ],
    "files": [
      {
        "path": "users/1/portfolio/doc.pdf",
        "url": "https://api.example.com/storage/users/1/portfolio/doc.pdf",
        "name": "doc.pdf"
      }
    ],
    "category": "Web Design, E-commerce",
    "categories": ["Web Design", "E-commerce"],
    "tags": "React, Laravel, Stripe",
    "tags_array": ["React", "Laravel", "Stripe"],
    "url": "https://example.com/project",
    "status": "published",
    "order": 1,
    "is_featured": true,
    "created_at": "2025-03-11T10:00:00.000000Z",
    "updated_at": "2025-03-11T12:00:00.000000Z"
  }
}
```

### Success (200) – No portfolio yet

```json
{
  "message": "Portfolio retrieved successfully.",
  "success": true,
  "code": 200
}
```

*Note: When no portfolio exists, `data` is omitted. Use POST /portfolios to create one.*

---

## 3. Create Portfolio

| | |
|---|---|
| **Endpoint** | `POST /api/v1/portfolios` |
| **Content-Type** | `multipart/form-data` |
| **Access** | Protected (auth:sanctum) |

Creates a new portfolio for the authenticated user. **One portfolio per user only.** Fails if the user already has a portfolio (use PUT/PATCH to update instead).

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| title | string | **Yes** | max:255 | Portfolio title |
| description | string | No | — | Portfolio description |
| category | string | No | max:500 | Comma-separated categories (e.g. `Web Design, Mobile`) |
| tags | string | No | max:500 | Comma-separated tags (e.g. `React, Laravel`) |
| url | string | No | url, max:500 | Project or demo URL |
| status | string | No | in:draft,published,archived | Default: `draft` |
| order | integer | No | min:0 | Display order. Default: `0` |
| is_featured | boolean | No | — | Feature this portfolio. Default: `false` |
| images[] | file[] | No | array, max:10 items | Image files. Max 5MB each. Allowed: jpg, jpeg, png, gif, webp |
| files[] | file[] | No | array, max:10 items | Document files. Max 10MB each. Allowed: pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar |

### File Upload Constraints

**Images:**
- Max 10 images per request
- Max 5MB per image
- MIME types: `jpg`, `jpeg`, `png`, `gif`, `webp`

**Files:**
- Max 10 files per request
- Max 10MB per file
- MIME types: `pdf`, `doc`, `docx`, `xls`, `xlsx`, `ppt`, `pptx`, `txt`, `zip`, `rar`

### Success (201)

```json
{
  "message": "Portfolio created successfully.",
  "success": true,
  "code": 201,
  "data": {
    "id": 1,
    "user_id": 1,
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com" },
    "title": "E-commerce Website",
    "description": "Full-stack e-commerce platform.",
    "images": [],
    "files": [],
    "category": "Web Design",
    "categories": ["Web Design"],
    "tags": "React, Laravel",
    "tags_array": ["React", "Laravel"],
    "url": "https://example.com/project",
    "status": "draft",
    "order": 0,
    "is_featured": false,
    "created_at": "2025-03-11T10:00:00.000000Z",
    "updated_at": "2025-03-11T10:00:00.000000Z"
  }
}
```

### Error (422) – Already Has Portfolio

```json
{
  "message": "You already have a portfolio. Use PUT or PATCH /portfolios to update it.",
  "success": false,
  "code": 422
}
```

### Error (422) – Validation

```json
{
  "message": "Validation failed.",
  "success": false,
  "code": 422,
  "errors": {
    "title": ["Portfolio title is required."],
    "images.*": ["Each image must not exceed 5MB."],
    "url": ["Please provide a valid URL."]
  }
}
```

---

## 4. Update Portfolio

| | |
|---|---|
| **Endpoint** | `PUT /api/v1/portfolios` or `PATCH /api/v1/portfolios` |
| **Content-Type** | `multipart/form-data` or `application/json` |
| **Access** | Protected (auth:sanctum) |

Updates the authenticated user's portfolio. No ID in URL — updates the user's single portfolio. All parameters are optional (partial update).

### Path Parameters

None.

### Request Parameters

| Parameter | Type | Required | Validation | Description |
|-----------|------|----------|------------|-------------|
| title | string | No | max:255 | Portfolio title |
| description | string | No | — | Portfolio description |
| category | string | No | max:500 | Comma-separated categories |
| tags | string | No | max:500 | Comma-separated tags |
| url | string | No | url, max:500 | Project or demo URL |
| status | string | No | in:draft,published,archived | Status |
| order | integer | No | min:0 | Display order |
| is_featured | boolean | No | — | Feature this portfolio |
| images[] | file[] | No | array, max:10 items | New images to add (appended to existing) |
| files[] | file[] | No | array, max:10 items | New files to add (appended to existing) |
| remove_images | array | No | array of strings | Paths of images to remove (e.g. `users/1/portfolio/img.jpg`) |
| remove_files | array | No | array of strings | Paths of files to remove |

### File Upload Constraints

Same as Create (images: max 10, 5MB each; files: max 10, 10MB each).

### Success (200)

```json
{
  "message": "Portfolio updated successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user_id": 1,
    "user": { "id": 1, "name": "John Doe", "email": "john@example.com" },
    "title": "Updated Title",
    "description": "Updated description.",
    "images": [...],
    "files": [...],
    "category": "Web Design, Mobile",
    "categories": ["Web Design", "Mobile"],
    "tags": "React, Laravel, Flutter",
    "tags_array": ["React", "Laravel", "Flutter"],
    "url": "https://example.com/updated",
    "status": "published",
    "order": 2,
    "is_featured": true,
    "created_at": "2025-03-11T10:00:00.000000Z",
    "updated_at": "2025-03-11T14:00:00.000000Z"
  }
}
```

### Error (404) – No Portfolio Yet

```json
{
  "message": "You do not have a portfolio yet. Use POST /portfolios to create one.",
  "success": false,
  "code": 404
}
```

---

## Response Field Reference

### Portfolio Object

| Field | Type | Description |
|-------|------|-------------|
| id | integer | Portfolio ID |
| user_id | integer | Owner user ID |
| user | object | `{ id, name, email }` |
| title | string | Title |
| description | string \| null | Description |
| images | array | `[{ path, url }]` – full URLs |
| files | array | `[{ path, url, name }]` – full URLs |
| category | string | Raw category string (comma-separated) |
| categories | array | Parsed category array |
| tags | string | Raw tags string (comma-separated) |
| tags_array | array | Parsed tags array |
| url | string \| null | Project URL |
| status | string | `draft`, `published`, `archived` |
| order | integer | Display order |
| is_featured | boolean | Featured flag |
| created_at | string | ISO 8601 datetime |
| updated_at | string | ISO 8601 datetime |

---

## Example Requests

### Get Options (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/portfolios/options" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Get Portfolio (cURL)

```bash
curl -X GET "https://api.example.com/api/v1/portfolios" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

### Create Portfolio (cURL)

```bash
curl -X POST "https://api.example.com/api/v1/portfolios" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -F "title=E-commerce Website" \
  -F "description=Full-stack e-commerce platform built with React and Laravel" \
  -F "category=Web Design, E-commerce" \
  -F "tags=React, Laravel, Stripe" \
  -F "url=https://example.com/project" \
  -F "status=published" \
  -F "order=1" \
  -F "is_featured=1" \
  -F "images[]=@screenshot1.png" \
  -F "images[]=@screenshot2.png" \
  -F "files[]=@case-study.pdf"
```

### Update Portfolio (cURL)

```bash
curl -X PATCH "https://api.example.com/api/v1/portfolios" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json" \
  -F "title=Updated E-commerce Website" \
  -F "status=published" \
  -F "remove_images[]=users/1/portfolio/old-image.jpg" \
  -F "images[]=@new-screenshot.png"
```

### JavaScript (Fetch – Create)

```javascript
const formData = new FormData();
formData.append('title', 'E-commerce Website');
formData.append('description', 'Full-stack platform');
formData.append('category', 'Web Design, E-commerce');
formData.append('tags', 'React, Laravel');
formData.append('url', 'https://example.com/project');
formData.append('status', 'published');
formData.append('is_featured', true);
formData.append('images[]', imageFile1);
formData.append('images[]', imageFile2);
formData.append('files[]', pdfFile);

const response = await fetch('/api/v1/portfolios', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json',
  },
  body: formData,
});
```

### JavaScript (Fetch – Update with remove)

```javascript
const formData = new FormData();
formData.append('title', 'Updated Title');
formData.append('remove_images[]', 'users/1/portfolio/old.jpg');
formData.append('images[]', newImageFile);

const response = await fetch('/api/v1/portfolios', {
  method: 'PATCH',
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
| 401 | Unauthenticated |
| 404 | No portfolio yet (on update — use POST to create first) |
| 422 | Validation failed, or user already has a portfolio (on create) |
| 500 | Server error (e.g. upload failure) |
