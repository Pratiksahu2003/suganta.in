# Portfolio API

Auth user's data only. All endpoints require `Authorization: Bearer {token}`.

## Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/v1/portfolios/options` | Get dropdown options (auth user's categories/tags) |
| GET | `/api/v1/portfolios` | Show auth user's portfolios |
| POST | `/api/v1/portfolios` | Create portfolio |
| PUT/PATCH | `/api/v1/portfolios/{id}` | Update portfolio |

## Request Examples

**Create:**
```bash
POST /api/v1/portfolios
Content-Type: multipart/form-data

title, description, category, tags, url, status, images[], files[]
```

**Update:**
```bash
PUT /api/v1/portfolios/{id}
Content-Type: multipart/form-data
```
