# Public Teacher Controller API Documentation

Complete API documentation for `App\Http\Controllers\Api\V1\PublicTeacherController`. Public teacher listing and profile endpoints. **No authentication required.** Uses **User ID only** for show endpoint (no slug).

**Base path:** `/api/v1`

**Controller:** `App\Http\Controllers\Api\V1\PublicTeacherController`

---

## Table of Contents

1. [Overview](#overview)
2. [Endpoints Summary](#endpoints-summary)
3. [Get Filter Options](#1-get-filter-options)
4. [List Teachers](#2-list-teachers)
5. [Filter Parameters Reference](#filter-parameters-reference) *(complete list of all filters)*
6. [Show Teacher by ID](#3-show-teacher-by-id)
7. [Response Format](#response-format)
8. [Error Codes](#error-codes)
9. [Internal Behavior & Notes](#internal-behavior--notes)
10. [cURL Examples](#curl-examples)

---

## Overview

The Public Teacher API provides:

- **Filter options** for building search/filter UIs (gender, teaching mode, availability, rates, experience, subjects, cities)
- **Paginated teacher listing** with location, subject, rate, experience, and search filters
- **Single teacher profile** by numeric User ID, including related teachers recommendation

### Eligibility Criteria

Teachers are included only if:

- `role` = `teacher`
- `email_verified_at` is not null
- `registration_fee_status` ∈ `paid`, `not_required`
- `is_active` = true
- User ID ≠ 40 (excluded user)

### Routes

| Method | Route | Action |
|--------|-------|--------|
| GET | `/api/v1/teachers/options` | `options` |
| GET | `/api/v1/teachers` | `index` |
| GET | `/api/v1/teachers/{id}` | `show` |

---

## Endpoints Summary

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/teachers/options` | Get filter options and dropdown data |
| GET | `/api/v1/teachers` | List teachers (paginated, filtered, sorted) |
| GET | `/api/v1/teachers/{id}` | Show single teacher profile by User ID |

---

## 1. Get Filter Options

Retrieve all filter options needed for teacher search/filter UIs. Data comes from `config/options.php` and active subjects/cities. Options are cached for 1 hour; cities are cached for 1 hour.

- **Endpoint**: `GET /api/v1/teachers/options`
- **Access**: Public (no auth)

### Success Response (200 OK)

```json
{
  "message": "Teacher filter options retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "options": {
      "gender": [{ "id": 1, "label": "Male" }, { "id": 2, "label": "Female" }],
      "teaching_mode": [{ "id": 1, "label": "Online Only" }, { "id": 2, "label": "Offline Only" }, { "id": 3, "label": "Both" }],
      "availability_status": [{ "id": 1, "label": "Available" }, ...],
      "hourly_rate_range": [{ "id": 1, "label": "₹0-200" }, ...],
      "monthly_rate_range": [{ "id": 1, "label": "₹0-2500" }, ...],
      "teaching_experience_years": [{ "id": 1, "label": "0-1 years" }, ...],
      "travel_radius_km": [{ "id": 1, "label": "5 km" }, ...],
      "highest_qualification": [{ "id": 1, "label": "Bachelor's" }, ...]
    },
    "subjects": [
      { "id": 1, "name": "Mathematics", "slug": "mathematics" },
      { "id": 2, "name": "Physics", "slug": "physics" }
    ],
    "cities": [
      { "value": "Mumbai", "count": 15 },
      { "value": "Delhi", "count": 12 }
    ]
  }
}
```

| Field | Type | Description |
|-------|------|-------------|
| `options` | object | Keys from config; values are `[{id, label}]` arrays |
| `subjects` | array | Active subjects: `id`, `name`, `slug` |
| `cities` | array | Cities with teacher count: `value`, `count` |

---

## 2. List Teachers

Paginated list of teachers with optional filters and sorting. When no teachers match filters, the API **falls back to up to 10 online-capable teachers** (excludes offline-only).

- **Endpoint**: `GET /api/v1/teachers`
- **Access**: Public (no auth)

### Query Parameters

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | int | 12 | Items per page (min: 1, max: 50) |
| `sort` | string | `created_at` | Sort field |
| `order` | string | `desc` | `asc` or `desc` |
| `location` | string | - | Search city OR area (overrides `city`/`area` if set) |
| `city` | string | - | Filter by city (exact or partial match) |
| `area` | string | - | Filter by area (only if `location` not set) |
| `pincode` | string | - | Filter by exact pincode |
| `subject_id` | int/string | - | Filter by subject ID (also accepts `subject`) |
| `subject` | int/string | - | Alias for `subject_id` |
| `hourly_rate_range` | int | - | Option ID from `hourly_rate_range` |
| `monthly_rate_range` | int | - | Option ID from `monthly_rate_range` |
| `experience` | int | - | Teaching experience option ID |
| `teaching_mode` | int | - | Teaching mode option ID |
| `availability` | int/string | - | ID or label (e.g. `Available`) — mapped via ProfileOptionsHelper |
| `verified` | bool | - | `true`/`1` = verified only; `false`/`0` = all |
| `search` | string | - | Search by teacher name or display name |

### Sort Options

| `sort` value | Description |
|--------------|-------------|
| `created_at` | Sort by registration date |
| `recent` | Alias for `created_at` |
| `rating` | Sort by rating, then by total_reviews desc |
| `price_low` | Sort by hourly rate ascending |
| `price_high` | Sort by hourly rate descending |
| `name` | Sort by user name |

### Success Response (200 OK)

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
        "bio": "Experienced mathematics tutor with 10 years of...",
        "avatar_url": "http://localhost:8000/storage/profile-images/profile_1.jpg",
        "qualification": "M.Sc. Mathematics",
        "experience_years": { "id": 10, "label": "10 years" },
        "rating": 4.8,
        "total_reviews": 25,
        "hourly_rate": 500.00,
        "city": "Mumbai",
        "state": "Maharashtra",
        "location": {
          "address_line_1": null,
          "address_line_2": null,
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
        "subjects": [
          { "id": 1, "name": "Mathematics", "slug": "mathematics", "category": "Science" }
        ],
        "institute": null,
        "verified": true,
        "is_featured": false
      }
    ],
    "pagination": {
      "current_page": 1,
      "per_page": 12,
      "total": 42,
      "last_page": 4
    }
  }
}
```

### List Item Fields

| Field | Type | Description |
|-------|------|-------------|
| `id` | int | User ID |
| `name` | string | Name or display name |
| `bio` | string | Truncated to 120 chars |
| `avatar_url` | string\|null | Full storage URL |
| `qualification` | string\|null | Highest qualification text |
| `experience_years` | object\|null | `{ id, label }` from config |
| `rating` | float | 0–5 |
| `total_reviews` | int | Number of reviews |
| `hourly_rate` | float\|null | Hourly rate |
| `city` | string\|null | City |
| `state` | string\|null | State |
| `location` | object | Full location structure (see below) |
| `teaching_mode` | object\|null | `{ id, label }` |
| `availability_status` | object\|null | `{ id, label }` |
| `subjects` | array | `[{ id, name, slug, category }]` |
| `institute` | null | Always null in current implementation |
| `verified` | bool | Verification status |
| `is_featured` | bool | Always false in current implementation |

---

## Filter Parameters Reference

Complete reference for all query parameters used with `GET /api/v1/teachers`. Get valid option IDs from `GET /api/v1/teachers/options` unless noted otherwise.

### Location Filters

| Parameter | Type | Source | Description |
|-----------|------|--------|-------------|
| `location` | string | free text | Search in both `city` and `area` (LIKE). **Overrides** `city` and `area` when set. |
| `city` | string | `data.cities[].value` or free text | Filter by city (LIKE). Ignored if `location` is set. |
| `area` | string | free text | Filter by area (LIKE). Ignored if `location` is set. |
| `pincode` | string | free text | Exact match on `profile.pincode`. |

**Example:** `?location=Mumbai` or `?city=Mumbai&area=Andheri`

---

### Subject Filter

| Parameter | Type | Source | Description |
|-----------|------|--------|-------------|
| `subject_id` | int/string | `data.subjects[].id` | Filter teachers who teach this subject (JSON contains). |
| `subject` | int/string | same as `subject_id` | Alias for `subject_id`. |

**Example:** `?subject_id=1` or `?subject=1`

---

### Rate Filters

| Parameter | Type | Source | Description |
|-----------|------|--------|-------------|
| `hourly_rate_range` | int | `data.options.hourly_rate_range[].id` | Filter by `profile_teaching_info.hourly_rate_id`. |
| `monthly_rate_range` | int | `data.options.monthly_rate_range[].id` | Filter by `profile_teaching_info.monthly_rate_id`. |

**Example:** `?hourly_rate_range=5`

---

### Experience Filter

| Parameter | Type | Source | Description |
|-----------|------|--------|-------------|
| `experience` | int | `data.options.teaching_experience_years[].id` | Filter by `profile_teaching_info.teaching_experience_years`. |

**Example:** `?experience=10`

---

### Teaching Mode Filter

| Parameter | Type | Source | Description |
|-----------|------|--------|-------------|
| `teaching_mode` | int | `data.options.teaching_mode[].id` | Filter by `profile_teaching_info.teaching_mode_id`. 1=Online, 2=Offline, 3=Both. |

**Example:** `?teaching_mode=1`

---

### Availability Filter

| Parameter | Type | Source | Description |
|-----------|------|--------|-------------|
| `availability` | int or string | `data.options.availability_status[].id` or label | Filter by availability. Accepts numeric ID or label (e.g. `Available`). Uses `ProfileOptionsHelper::getValue`. |

**Example:** `?availability=1` or `?availability=Available`

---

### Verified Filter

| Parameter | Type | Source | Description |
|-----------|------|--------|-------------|
| `verified` | bool | `true`/`false`/`1`/`0` | Filter by `profile_teaching_info.verified`. `true`/`1` = verified only. |

**Example:** `?verified=true`

---

### Search Filter

| Parameter | Type | Source | Description |
|-----------|------|--------|-------------|
| `search` | string | free text | Search in `users.name` or `profile.display_name` (LIKE). Trimmed. |

**Example:** `?search=John`

---

### Pagination & Sort

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `per_page` | int | 12 | Items per page. Clamped 1–50. |
| `sort` | string | `created_at` | `created_at`, `recent`, `rating`, `price_low`, `price_high`, `name`. |
| `order` | string | `desc` | `asc` or `desc` (used for `rating`, `name`). |

---

### Filter Combination Examples

```bash
# Mumbai + Mathematics + verified + sort by price
?city=Mumbai&subject_id=1&verified=true&sort=price_low

# Search by name + availability
?search=Kumar&availability=Available

# Location (city or area) + experience + teaching mode
?location=Delhi&experience=5&teaching_mode=3
```

---

## 3. Show Teacher by ID

Retrieve a single teacher's full profile by User ID. Includes related teachers (similar subjects/city) cached for 5 minutes.

- **Endpoint**: `GET /api/v1/teachers/{id}`
- **Access**: Public (no auth)
- **Parameter**: `id` — integer (required), User ID

### Success Response (200 OK)

```json
{
  "message": "Teacher profile retrieved successfully.",
  "success": true,
  "code": 200,
  "data": {
    "id": 1,
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com"
    },
    "profile": {
      "bio": "Experienced mathematics tutor with 10 years...",
      "profile_image_url": "http://localhost:8000/storage/profile-images/profile_1.jpg",
      "phone_primary": "9876543210",
      "whatsapp": "9876543210",
      "city": "Mumbai",
      "state": "Maharashtra",
      "pincode": "400058",
      "gender": { "id": 1, "label": "Male" },
      "highest_qualification": { "id": 4, "label": "Master's Degree" }
    },
    "location": {
      "address_line_1": "123 Main Street",
      "address_line_2": null,
      "area": "Andheri West",
      "city": "Mumbai",
      "state": "Maharashtra",
      "pincode": "400058",
      "country_id": 1,
      "latitude": 19.1136,
      "longitude": 72.8697
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
    "institute": null,
    "verified": true,
    "is_featured": false,
    "reviews_sample": [
      {
        "id": 1,
        "rating": 5,
        "comment": "Excellent teacher!",
        "created_at": "2025-03-01T10:00:00.000000Z"
      }
    ],
    "related_teachers": [
      {
        "id": 2,
        "name": "Jane Smith",
        "bio": "Physics tutor...",
        "avatar_url": "http://...",
        "qualification": "M.Sc. Physics",
        "experience_years": { "id": 5, "label": "5 years" },
        "rating": 4.5,
        "total_reviews": 10,
        "hourly_rate": 450.00,
        "city": "Mumbai",
        "state": "Maharashtra",
        "location": { ... },
        "teaching_mode": { "id": 3, "label": "Both" },
        "availability_status": { "id": 1, "label": "Available" },
        "subjects": [...],
        "institute": null,
        "verified": false,
        "is_featured": false
      }
    ]
  }
}
```

### Show Response Fields

| Section | Fields |
|--------|--------|
| `user` | `id`, `name`, `email` |
| `profile` | `bio`, `profile_image_url`, `phone_primary`, `whatsapp`, `city`, `state`, `pincode`, `gender`, `highest_qualification` |
| `location` | `address_line_1`, `address_line_2`, `area`, `city`, `state`, `pincode`, `country_id`, `latitude`, `longitude` |
| `teaching` | `qualification`, `experience_years`, `specialization`, `languages`, `hourly_rate`, `hourly_rate_range`, `monthly_rate`, `monthly_rate_range`, `teaching_mode`, `availability_status`, `travel_radius_km`, `online_classes`, `home_tuition`, `institute_classes` |
| `reviews_sample` | Up to 5 published reviews: `id`, `rating`, `comment`, `created_at` |
| `related_teachers` | Same structure as list items (see above) |

### Error Response (404 Not Found)

```json
{
  "message": "Teacher not found.",
  "success": false,
  "code": 404,
  "errors": null
}
```

---

## Response Format

All endpoints use the standard API envelope:

```json
{
  "message": "Success or error message",
  "success": true,
  "code": 200,
  "data": { ... }
}
```

For errors: `success` is `false` and `errors` may contain validation/details.

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 404 | Teacher not found (`show` only) |
| 422 | Validation failed (if used with validation middleware) |
| 500 | Internal server error |

---

## Internal Behavior & Notes

### Dependencies

| Helper/Class | Purpose |
|--------------|---------|
| `FilterOptionsHelper` | `buildFromConfig`, `getActiveSubjects`, `paginationMeta` |
| `ProfileOptionsHelper` | `getValue` for `availability` string→ID mapping |
| `PublicProfileOptionsMapper` | Map option IDs to `{ id, label }` for public responses |

### Caching

| Key | TTL | Description |
|-----|-----|-------------|
| `teacher_show_related_{id}` | 300 s (5 min) | Related teachers for show page |
| `teacher_cities_profile` | 3600 s (1 h) | Cities with teacher counts |
| `filter_options:*` | 3600 s | Config-based option lists |
| `filter_subjects` | 3600 s | Active subjects |

### Filters Logic

- **`location`** — If present, filters by `city` OR `area`; `city`/`area` are ignored.
- **`city`** / **`area`** — Used only when `location` is not set.
- **`subject_id`** / **`subject`** — Uses JSON contains on `profile_teaching_info.subjects_taught`.
- **`availability`** — Accepts numeric ID or string label (e.g. `"Available"`); mapped to ID via `ProfileOptionsHelper::getValue`.

### Empty Result Fallback

When no teachers match filters, the list endpoint returns up to **10 online-capable teachers** (teaching mode ≠ Offline Only). These are merged with the empty result, keeping the pagination metadata based on the original query.

### Related Teachers

- Same subjects taught (from `profile_teaching_info.subjects_taught`) **or** same city.
- Excludes the current teacher and user ID 40.
- Limited to 6 teachers.
- Cached per teacher ID for 5 minutes.

### Option Structures

Where options appear, format is `{ "id": <int|string>, "label": "<string>" }` or `null`.

---

## cURL Examples

```bash
# Get filter options
curl -X GET "http://localhost:8000/api/v1/teachers/options" \
  -H "Accept: application/json"

# List teachers with filters
curl -X GET "http://localhost:8000/api/v1/teachers?per_page=20&city=Mumbai&subject_id=1&sort=price_low" \
  -H "Accept: application/json"

# Search by name
curl -X GET "http://localhost:8000/api/v1/teachers?search=John&per_page=12" \
  -H "Accept: application/json"

# Show teacher by ID
curl -X GET "http://localhost:8000/api/v1/teachers/1" \
  -H "Accept: application/json"
```

---

*Last Updated: March 2026*
