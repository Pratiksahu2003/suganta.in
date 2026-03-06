# Portfolio API Documentation

Complete API documentation for managing user portfolios.

## Base URL

```
/api/v1/portfolios
```

## Public Access

**No authentication required** for viewing portfolios! Anyone can:
- View all published portfolios
- View a specific user's portfolios by user ID
- Filter and search portfolios
- View individual portfolio details

Authentication is only required for:
- Creating portfolios
- Updating your own portfolios
- Deleting your own portfolios
- Managing featured status and ordering

## Authentication

Protected endpoints require authentication using Laravel Sanctum. Include the bearer token in the Authorization header:

```
Authorization: Bearer {your-token}
```

---

## Endpoints

### 1. Get Portfolio Options

Get available options for dropdowns (statuses, categories, tags).

**Endpoint:** `GET /api/v1/portfolios/options`

**Auth Required:** No

**Response:**
```json
{
  "success": true,
  "message": "Portfolio options retrieved successfully.",
  "code": 200,
  "data": {
    "statuses": {
      "draft": "Draft",
      "published": "Published",
      "archived": "Archived"
    },
    "categories": ["Web Development", "Mobile Development", "Design"],
    "tags": ["Laravel", "React", "Vue", "PHP"]
  }
}
```

---

### 2. List Portfolios

Get a paginated list of portfolios with filtering options.

**Endpoint:** `GET /api/v1/portfolios`

**Auth Required:** No (public access - shows published portfolios only)

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `user_id` | integer | Filter by user ID |
| `category` | string | Filter by category (partial match) |
| `tag` | string | Filter by tag (partial match) |
| `status` | string | Filter by status (draft, published, archived) |
| `featured` | boolean | Filter featured portfolios only |
| `search` | string | Search in title, description, tags, category |
| `per_page` | integer | Items per page (default: 15) |
| `page` | integer | Page number |

**Example Request:**
```
GET /api/v1/portfolios?category=Web Development&featured=1&per_page=10
```

**Response:**
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
      "description": "A full-featured online store built with Laravel and Vue.js",
      "images": [
        {
          "path": "portfolios/portfolio_image_5_20260306120000_abc123_screenshot.jpg",
          "url": "http://localhost:8000/storage/portfolios/portfolio_image_5_20260306120000_abc123_screenshot.jpg"
        }
      ],
      "files": [
        {
          "path": "portfolios/portfolio_file_5_20260306120000_def456_documentation.pdf",
          "url": "http://localhost:8000/storage/portfolios/portfolio_file_5_20260306120000_def456_documentation.pdf",
          "name": "documentation.pdf"
        }
      ],
      "category": "Web Development, E-commerce",
      "categories": ["Web Development", "E-commerce"],
      "tags": "Laravel, Vue.js, MySQL, Stripe",
      "tags_array": ["Laravel", "Vue.js", "MySQL", "Stripe"],
      "url": "https://example.com/project",
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

### 3. Get User's Portfolios (Public)

Get all published portfolios for a specific user without authentication.

**Endpoint:** `GET /api/v1/portfolios/user/{userId}`

**Auth Required:** No (public access - shows published portfolios only)

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `category` | string | Filter by category (partial match) |
| `tag` | string | Filter by tag (partial match) |
| `featured` | boolean | Filter featured portfolios only |
| `search` | string | Search in title, description, tags, category |
| `per_page` | integer | Items per page (default: 15) |
| `page` | integer | Page number |

**Example Request:**
```
GET /api/v1/portfolios/user/5?featured=1&category=Web Development
```

**Response:**
```json
{
  "success": true,
  "message": "User portfolios retrieved successfully.",
  "code": 200,
  "data": [ ... ],
  "links": { ... },
  "meta": { ... }
}
```

---

### 4. Get Single Portfolio

Retrieve a specific portfolio by ID.

**Endpoint:** `GET /api/v1/portfolios/{id}`

**Auth Required:** No (public access for published portfolios)

**Response:**
```json
{
  "success": true,
  "message": "Portfolio retrieved successfully.",
  "code": 200,
  "data": {
    "id": 1,
    "user_id": 5,
    "title": "E-commerce Platform",
    ...
  }
}
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "You are not allowed to view this portfolio.",
  "code": 403
}
```

---

### 5. Create Portfolio

Create a new portfolio for the authenticated user.

**Endpoint:** `POST /api/v1/portfolios`

**Auth Required:** Yes

**Content-Type:** `multipart/form-data`

**Request Body:**

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `title` | string | Yes | Portfolio title (max 255 chars) |
| `description` | text | No | Detailed description |
| `images[]` | file | No | Image files (max 10, 5MB each, jpg/png/gif/webp) |
| `files[]` | file | No | Document files (max 10, 10MB each, pdf/doc/xls/ppt/txt/zip) |
| `category` | string | No | Comma-separated categories (max 500 chars) |
| `tags` | string | No | Comma-separated tags (max 500 chars) |
| `url` | string | No | Project URL (max 500 chars) |
| `status` | string | No | Status: draft, published, archived (default: draft) |
| `order` | integer | No | Display order (default: 0) |
| `is_featured` | boolean | No | Featured flag (default: false) |

**Example Request:**
```bash
curl -X POST http://localhost:8000/api/v1/portfolios \
  -H "Authorization: Bearer {token}" \
  -F "title=My Awesome Project" \
  -F "description=A detailed description of the project" \
  -F "category=Web Development, E-commerce" \
  -F "tags=Laravel, Vue.js, MySQL, Stripe" \
  -F "url=https://example.com/project" \
  -F "status=published" \
  -F "is_featured=1" \
  -F "images[]=@/path/to/image1.jpg" \
  -F "images[]=@/path/to/image2.jpg" \
  -F "files[]=@/path/to/document.pdf"
```

**Response (201):**
```json
{
  "success": true,
  "message": "Portfolio created successfully.",
  "code": 201,
  "data": {
    "id": 1,
    "user_id": 5,
    "title": "My Awesome Project",
    ...
  }
}
```

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "code": 422,
  "errors": {
    "title": ["Portfolio title is required."],
    "images.0": ["Each image must not exceed 5MB."]
  }
}
```

---

### 6. Update Portfolio

Update an existing portfolio. Only the owner can update their portfolio.

**Endpoint:** `PUT /api/v1/portfolios/{id}` or `PATCH /api/v1/portfolios/{id}`

**Auth Required:** Yes (must be owner)

**Content-Type:** `multipart/form-data`

**Request Body:**

All fields are optional. Only include fields you want to update.

| Field | Type | Description |
|-------|------|-------------|
| `title` | string | Portfolio title (max 255 chars) |
| `description` | text | Detailed description |
| `images[]` | file | New image files to add (max 10 total, 5MB each) |
| `remove_images[]` | string | Array of image paths to remove |
| `files[]` | file | New document files to add (max 10 total, 10MB each) |
| `remove_files[]` | string | Array of file paths to remove |
| `category` | string | Comma-separated categories (max 500 chars) |
| `tags` | string | Comma-separated tags (max 500 chars) |
| `url` | string | Project URL |
| `status` | string | Status: draft, published, archived |
| `order` | integer | Display order |
| `is_featured` | boolean | Featured flag |

**Example Request:**
```bash
curl -X PUT http://localhost:8000/api/v1/portfolios/1 \
  -H "Authorization: Bearer {token}" \
  -F "title=Updated Project Title" \
  -F "tags=Laravel, Vue.js, Redis" \
  -F "images[]=@/path/to/new-image.jpg" \
  -F "remove_images[]=portfolios/old-image.jpg"
```

**Response (200):**
```json
{
  "success": true,
  "message": "Portfolio updated successfully.",
  "code": 200,
  "data": { ... }
}
```

---

### 7. Delete Portfolio

Delete a portfolio and all associated files. Only the owner can delete their portfolio.

**Endpoint:** `DELETE /api/v1/portfolios/{id}`

**Auth Required:** Yes (must be owner)

**Response (204):**
```
No content (successful deletion)
```

**Error Response (403):**
```json
{
  "success": false,
  "message": "You are not allowed to delete this portfolio.",
  "code": 403
}
```

---

### 8. Get My Portfolios

Get all portfolios for the authenticated user (includes draft and archived).

**Endpoint:** `GET /api/v1/portfolios/my/portfolios`

**Auth Required:** Yes

**Query Parameters:**

| Parameter | Type | Description |
|-----------|------|-------------|
| `status` | string | Filter by status |
| `category` | string | Filter by category |
| `per_page` | integer | Items per page (default: 15) |

**Example Request:**
```
GET /api/v1/portfolios/my/portfolios?status=draft
```

**Response:**
```json
{
  "success": true,
  "message": "Your portfolios retrieved successfully.",
  "code": 200,
  "data": [ ... ],
  "links": { ... },
  "meta": { ... }
}
```

---

### 9. Toggle Featured Status

Toggle the featured status of a portfolio.

**Endpoint:** `POST /api/v1/portfolios/{id}/toggle-featured`

**Auth Required:** Yes (must be owner)

**Response (200):**
```json
{
  "success": true,
  "message": "Portfolio featured status updated successfully.",
  "code": 200,
  "data": {
    "id": 1,
    "is_featured": true,
    ...
  }
}
```

---

### 10. Reorder Portfolios

Batch update the order of multiple portfolios.

**Endpoint:** `POST /api/v1/portfolios/reorder`

**Auth Required:** Yes

**Request Body:**
```json
{
  "portfolios": [
    { "id": 1, "order": 0 },
    { "id": 2, "order": 1 },
    { "id": 3, "order": 2 }
  ]
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Portfolios reordered successfully.",
  "code": 200
}
```

---

## Usage Examples

### Example 1: Get All Portfolios for a User (No Auth Required)

```bash
# Get all published portfolios for user ID 5
curl -X GET "http://localhost:8000/api/v1/portfolios/user/5"

# Get featured portfolios for user ID 5
curl -X GET "http://localhost:8000/api/v1/portfolios/user/5?featured=1"

# Get portfolios filtered by category
curl -X GET "http://localhost:8000/api/v1/portfolios/user/5?category=Web Development"

# Get portfolios with search
curl -X GET "http://localhost:8000/api/v1/portfolios/user/5?search=Laravel"
```

### Example 2: Get All Portfolios with User Filter

```bash
# Using the general endpoint with user_id parameter
curl -X GET "http://localhost:8000/api/v1/portfolios?user_id=5"
```

### Example 3: Create a Portfolio with Tags and Categories

```bash
curl -X POST http://localhost:8000/api/v1/portfolios \
  -H "Authorization: Bearer your-token-here" \
  -F "title=Modern Dashboard Application" \
  -F "description=A real-time analytics dashboard with beautiful visualizations" \
  -F "category=Web Development, Dashboard, Analytics" \
  -F "tags=React, TypeScript, D3.js, WebSocket, Node.js" \
  -F "url=https://github.com/username/dashboard" \
  -F "status=published" \
  -F "is_featured=true" \
  -F "images[]=@screenshot1.png" \
  -F "images[]=@screenshot2.png"
```

### Example 4: Search Portfolios by Tag

```bash
curl -X GET "http://localhost:8000/api/v1/portfolios?tag=Laravel&per_page=20"
```

### Example 5: Filter by Multiple Categories

```bash
curl -X GET "http://localhost:8000/api/v1/portfolios?search=Web Development"
```

### Example 6: Update Portfolio Tags

```bash
curl -X PUT http://localhost:8000/api/v1/portfolios/1 \
  -H "Authorization: Bearer your-token-here" \
  -F "tags=Laravel, PHP, MySQL, Redis, Docker"
```

---

## Tags and Categories Format

Both `tags` and `category` fields accept comma-separated strings:

**Input:**
```
tags: "Laravel, Vue.js, MySQL, Redis"
category: "Web Development, E-commerce, SaaS"
```

**Output in API Response:**
```json
{
  "tags": "Laravel, Vue.js, MySQL, Redis",
  "tags_array": ["Laravel", "Vue.js", "MySQL", "Redis"],
  "category": "Web Development, E-commerce, SaaS",
  "categories": ["Web Development", "E-commerce", "SaaS"]
}
```

The API automatically:
- Trims whitespace from each tag/category
- Provides both string and array formats in responses
- Dynamically generates available options from existing portfolios

---

## File Uploads

### Images
- **Formats:** jpg, jpeg, png, gif, webp
- **Max Size:** 5MB per image
- **Max Count:** 10 images per portfolio

### Files
- **Formats:** pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar
- **Max Size:** 10MB per file
- **Max Count:** 10 files per portfolio

### Storage

**Images are stored in:**
```
storage/app/public/portfolios/images/
```

**Files (documents) are stored in:**
```
storage/app/public/portfolios/
```

**Naming pattern:**
```
portfolio_{type}_{user_id}_{timestamp}_{random}_{original_name}.{ext}
```

**Example paths:**
- Image: `portfolios/images/portfolio_image_5_20260306120000_abc123_screenshot.jpg`
- File: `portfolios/portfolio_file_5_20260306120000_def456_document.pdf`

---

## Error Responses

### 401 Unauthorized
```json
{
  "success": false,
  "message": "Unauthorized",
  "code": 401
}
```

### 403 Forbidden
```json
{
  "success": false,
  "message": "You are not allowed to update this portfolio.",
  "code": 403
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Resource not found",
  "code": 404
}
```

### 422 Validation Error
```json
{
  "success": false,
  "message": "Validation failed",
  "code": 422,
  "errors": {
    "title": ["Portfolio title is required."],
    "images.0": ["Each image must not exceed 5MB."]
  }
}
```

### 500 Server Error
```json
{
  "success": false,
  "message": "Failed to create portfolio. Please try again.",
  "code": 500
}
```

---

## Business Rules

1. **Visibility:**
   - Published portfolios are visible to everyone
   - Draft/archived portfolios are only visible to the owner

2. **Authorization:**
   - Only the portfolio owner can update or delete their portfolios
   - Only the portfolio owner can toggle featured status
   - Only the portfolio owner can reorder their portfolios

3. **File Management:**
   - When updating, new files are added to existing ones
   - Use `remove_images[]` and `remove_files[]` to delete specific files
   - Deleting a portfolio automatically deletes all associated files

4. **Ordering:**
   - Portfolios are ordered by `order` field (ascending), then by creation date (descending)
   - Use the reorder endpoint to batch update multiple portfolio orders

5. **Featured:**
   - Featured portfolios can be filtered using `?featured=1`
   - Toggle featured status without updating other fields

---

## Testing with Postman/Insomnia

### Create Portfolio
1. Set method to `POST`
2. URL: `http://localhost:8000/api/v1/portfolios`
3. Headers: `Authorization: Bearer {token}`
4. Body type: `form-data`
5. Add fields:
   - `title`: "My Project"
   - `category`: "Web Development, SaaS"
   - `tags`: "Laravel, React, PostgreSQL"
   - `images[]`: (file) select image
   - `status`: "published"

### Update Portfolio
1. Set method to `PUT` or `PATCH`
2. URL: `http://localhost:8000/api/v1/portfolios/1`
3. Headers: `Authorization: Bearer {token}`
4. Body type: `form-data`
5. Add fields to update (all optional):
   - `tags`: "Laravel, Vue.js, MySQL"
   - `remove_images[]`: "portfolios/old-image.jpg"
   - `images[]`: (file) select new image
