# Public API Documentation

Public listing and profile pages for **Teachers** and **Institutes**. No authentication required. Uses **ID only** for show endpoints (no slug).

**Base path:** `/api/v1`

Structure aligned with [ProfileApi.md](ProfileApi.md) for consistency.

---

## Table of Contents

1. [Teachers](#teachers)
   - [List Teachers](#1-list-teachers)
   - [Filter Options](#2-teacher-filter-options)
   - [Show Teacher by ID](#3-show-teacher-by-id)
2. [Institutes](#institutes)
   - [List Institutes](#4-list-institutes)
   - [Filter Options](#5-institute-filter-options)
   - [Show Institute by ID](#6-show-institute-by-id)
3. [Response Format](#response-format)
4. [Error Codes](#error-codes)

---

## Teachers

### Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/teachers` | List teachers (paginated) |
| GET | `/api/v1/teachers/options` | Filter options for teacher listing |
| GET | `/api/v1/teachers/{id}` | Show single teacher profile by **ID** |

---

### 1. List Teachers

- **Endpoint**: `GET /api/v1/teachers`
- **Access**: Public (no auth)

#### Query Parameters

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| per_page | int | 15 | Items per page (max 50) |
| location | string | - | Filter by city or area |
| city | string | - | Filter by city |
| pincode | string | - | Filter by pincode |
| subject_id | int | - | Filter by subject ID |
| hourly_rate_range | int | - | Option ID from config |
| monthly_rate_range | int | - | Option ID from config |
| experience | int | - | Teaching experience option ID |
| teaching_mode | int | - | Teaching mode option ID |
| availability | int | - | Availability option ID |
| verified | 0\|1 | 1 | 1 = verified only, 0 = all |
| featured | 0\|1 | 0 | 1 = featured only |
| search | string | - | Search by teacher name |
| sort | string | rating | created_at, rating, price_low, price_high, name |
| order | string | desc | asc, desc |

#### Success Response (200 OK)

```json
{
  "message": "Teachers retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "teachers": [
      {
        "id": 1,
        "name": "John Doe",
        "bio": "Experienced mathematics tutor...",
        "avatar_url": "http://localhost:8000/storage/...",
        "qualification": "M.Sc. Mathematics",
        "experience_years": { "id": 10, "label": "10 years" },
        "rating": 4.8,
        "total_reviews": 25,
        "hourly_rate": 500.00,
        "city": "Mumbai",
        "state": "Maharashtra",
        "location": {
          "address_line_1": "123 Main Street",
          "address_line_2": "Apartment 4",
          "area": "Andheri West",
          "city": "Mumbai",
          "state": "Maharashtra",
          "pincode": "400058",
          "country_id": 1,
          "latitude": 19.1136,
          "longitude": 72.8697
        },
        "teaching_mode": { "id": 1, "label": "Online Only" },
        "availability_status": { "id": 1, "label": "Available" },
        "subjects": [{ "id": 1, "name": "Mathematics", "slug": "mathematics" }],
        "institute": { "id": 5, "name": "ABC Academy", "city": "Mumbai" },
        "verified": true,
        "is_featured": false
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 42,
      "last_page": 3
    }
  }
}
```

---

### 2. Teacher Filter Options

- **Endpoint**: `GET /api/v1/teachers/options`
- **Access**: Public

#### Success Response (200 OK)

```json
{
  "message": "Teacher filter options retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "options": {
      "gender": [{ "id": 1, "label": "Male" }, ...],
      "teaching_mode": [...],
      "availability_status": [...],
      "hourly_rate_range": [...],
      "monthly_rate_range": [...],
      "teaching_experience_years": [...],
      "travel_radius_km": [...],
      "highest_qualification": [...]
    },
    "subjects": [{ "id": 1, "name": "Mathematics", "slug": "mathematics" }, ...],
    "cities": [{ "value": "Mumbai", "count": 15 }, ...]
  }
}
```

---

### 3. Show Teacher by ID

- **Endpoint**: `GET /api/v1/teachers/{id}`
- **Access**: Public
- **Parameter**: `id` — integer (required)

#### Success Response (200 OK)

Structure mirrors Profile API (user, profile, teaching sections):

```json
{
  "message": "Teacher profile retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user": {
      "id": 10,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "profile": {
      "bio": "Experienced mathematics tutor...",
      "profile_image_url": "http://localhost:8000/storage/...",
      "phone_primary": "9876543210",
      "whatsapp": "9876543210",
      "city": "Mumbai",
      "state": "Maharashtra",
      "pincode": "400058",
      "gender": { "id": 1, "label": "Male" },
      "highest_qualification": { "id": 4, "label": "Master's Degree" }
    },
    "teaching": {
      "qualification": "M.Sc. Mathematics",
      "experience_years": { "id": 10, "label": "10 years" },
      "specialization": "Calculus, Algebra",
      "languages": ["English", "Hindi"],
      "hourly_rate": 500.00,
      "hourly_rate_range": { "id": 5, "label": "₹500-1000" },
      "monthly_rate": 8000.00,
      "monthly_rate_range": { "id": 4, "label": "₹5001-7500" },
      "teaching_mode": { "id": 1, "label": "Online Only" },
      "availability_status": { "id": 1, "label": "Available" },
      "travel_radius_km": { "id": 10, "label": "10 km" },
      "online_classes": true,
      "home_tuition": true,
      "institute_classes": false
    },
    "rating": 4.8,
    "total_reviews": 25,
    "total_students": 50,
    "subjects": [
      { "id": 1, "name": "Mathematics", "slug": "mathematics", "category": "Science" }
    ],
    "institute": {
      "id": 5,
      "name": "ABC Academy",
      "city": "Mumbai",
      "address": "123 Main St",
      "website": "https://abc.edu"
    },
    "verified": true,
    "is_featured": false,
    "reviews_sample": [
      {
        "id": 1,
        "rating": 5,
        "comment": "Excellent teacher!",
        "created_at": "2025-03-01T10:00:00.000000Z"
      }
    ]
  }
}
```

---

## Institutes

### Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/institutes` | List institutes (paginated) |
| GET | `/api/v1/institutes/options` | Filter options for institute listing |
| GET | `/api/v1/institutes/{id}` | Show single institute profile by **ID** |

---

### 4. List Institutes

- **Endpoint**: `GET /api/v1/institutes`
- **Access**: Public

#### Query Parameters

| Param | Type | Default | Description |
|-------|------|---------|-------------|
| per_page | int | 15 | Items per page (max 50) |
| city | string | - | Filter by city |
| verified | 0\|1 | 0 | 1 = verified only |
| search | string | - | Search by institute name |
| featured | 0\|1 | 0 | 1 = featured only |

#### Success Response (200 OK)

```json
{
  "message": "Institutes retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "institutes": [
      {
        "id": 1,
        "name": "ABC Academy",
        "description": "Premier educational institute...",
        "logo_url": "http://localhost:8000/storage/...",
        "city": "Mumbai",
        "state": "Maharashtra",
        "rating": 4.5,
        "teachers_count": 25,
        "subjects": [{ "id": 1, "name": "Mathematics", "slug": "mathematics" }],
        "verified": true,
        "is_featured": true
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 15,
      "total": 20,
      "last_page": 2
    }
  }
}
```

---

### 5. Institute Filter Options

- **Endpoint**: `GET /api/v1/institutes/options`
- **Access**: Public

#### Success Response (200 OK)

```json
{
  "message": "Institute filter options retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "options": {
      "institute_type": [{ "id": 1, "label": "School" }, ...],
      "institute_category": [...],
      "establishment_year_range": [...],
      "total_students_range": [...],
      "total_teachers_range": [...]
    },
    "subjects": [{ "id": 1, "name": "Mathematics", "slug": "mathematics" }, ...],
    "cities": [{ "value": "Mumbai", "count": 8 }, ...]
  }
}
```

---

### 6. Show Institute by ID

- **Endpoint**: `GET /api/v1/institutes/{id}`
- **Access**: Public
- **Parameter**: `id` — integer (required)

#### Success Response (200 OK)

Structure mirrors Profile API institute section:

```json
{
  "message": "Institute profile retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user": {
      "id": 12,
      "name": "ABC Academy",
      "email": "contact@abc.edu"
    },
    "profile": {
      "name": "ABC Academy",
      "description": "Premier educational institute...",
      "specialization": "Science & Mathematics",
      "affiliation": "CBSE",
      "registration_number": "REG123",
      "website": "https://abc.edu",
      "contact_person": "Principal",
      "contact_phone": "9876543210",
      "contact_email": "contact@abc.edu",
      "address": "123 Main Street",
      "city": "Mumbai",
      "state": "Maharashtra",
      "pincode": "400058",
      "established_year": 2010,
      "institute_type": { "id": 1, "label": "School" },
      "institute_category": { "id": 2, "label": "Private" },
      "establishment_year": { "id": 7, "label": "2001-2010" },
      "total_students": 500,
      "total_students_range": { "id": 4, "label": "201-500" },
      "total_teachers_range": { "id": 3, "label": "21-30" },
      "logo_url": "http://localhost:8000/storage/...",
      "gallery_urls": ["http://localhost:8000/storage/...", ...],
      "facilities": ["Library", "Lab", "Sports"]
    },
    "rating": 4.5,
    "teachers_count": 25,
    "subjects": [
      { "id": 1, "name": "Mathematics", "slug": "mathematics", "category": "Science" }
    ],
    "branches": [
      {
        "id": 2,
        "name": "Andheri Branch",
        "address": "456 Branch St",
        "city": "Mumbai",
        "state": "Maharashtra",
        "phone": "9123456789",
        "email": "andheri@abc.edu"
      }
    ],
    "teachers_preview": [
      { "id": 1, "name": "John Doe" },
      { "id": 2, "name": "Jane Smith" }
    ],
    "verified": true,
    "is_featured": true
  }
}
```

---

## Response Format

All endpoints use the standard API response structure (same as ProfileApi.md):

```json
{
  "message": "Success message",
  "success": true,
  "code": 200,
  "data": { }
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 404 | Teacher/Institute not found |
| 422 | Validation failed (query params) |
| 500 | Internal server error |

---

## Quick Reference (cURL)

```bash
# List teachers
curl -X GET "http://localhost:8000/api/v1/teachers?per_page=15&city=Mumbai"

# Teacher options
curl -X GET "http://localhost:8000/api/v1/teachers/options"

# Show teacher by ID
curl -X GET "http://localhost:8000/api/v1/teachers/1"

# List institutes
curl -X GET "http://localhost:8000/api/v1/institutes?verified=1"

# Institute options
curl -X GET "http://localhost:8000/api/v1/institutes/options"

# Show institute by ID
curl -X GET "http://localhost:8000/api/v1/institutes/5"
```

---

*Last Updated: March 2026*
