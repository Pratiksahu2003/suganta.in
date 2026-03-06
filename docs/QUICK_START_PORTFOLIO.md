# Portfolio API - Quick Start Guide

Get started with the Portfolio API in 5 minutes!

---

## 🚀 Setup (One-Time)

### 1. Create Storage Symlink
```bash
php artisan storage:link
```

### 2. Start Server
```bash
php artisan serve
```

Server runs at: `http://localhost:8000`

---

## 📖 Basic Usage

### 1. View Someone's Portfolios (No Auth Needed!)

```bash
# Get all portfolios for user ID 5
curl http://localhost:8000/api/v1/portfolios/user/5
```

**Response:**
```json
{
  "success": true,
  "message": "User portfolios retrieved successfully.",
  "data": [...]
}
```

---

### 2. Create Your First Portfolio (Auth Required)

**Step 1: Get Auth Token**
```bash
# Login first
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "your@email.com",
    "password": "yourpassword"
  }'
```

**Step 2: Create Portfolio**
```bash
curl -X POST http://localhost:8000/api/v1/portfolios \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "title=My Awesome Project" \
  -F "description=This is my first portfolio project" \
  -F "category=Web Development, SaaS" \
  -F "tags=Laravel, Vue.js, MySQL" \
  -F "url=https://github.com/yourusername/project" \
  -F "status=published" \
  -F "is_featured=true" \
  -F "images[]=@/path/to/screenshot.jpg"
```

**Response:**
```json
{
  "success": true,
  "message": "Portfolio created successfully.",
  "code": 201,
  "data": {
    "id": 1,
    "title": "My Awesome Project",
    "category": "Web Development, SaaS",
    "categories": ["Web Development", "SaaS"],
    "tags": "Laravel, Vue.js, MySQL",
    "tags_array": ["Laravel", "Vue.js", "MySQL"],
    "images": [
      {
        "path": "portfolios/images/portfolio_image_5_20260306120000_abc123_screenshot.jpg",
        "url": "http://localhost:8000/storage/portfolios/images/portfolio_image_5_20260306120000_abc123_screenshot.jpg"
      }
    ],
    ...
  }
}
```

---

### 3. Search Portfolios

```bash
# Search by keyword
curl "http://localhost:8000/api/v1/portfolios?search=Laravel"

# Filter by tag
curl "http://localhost:8000/api/v1/portfolios?tag=Vue.js"

# Filter by category
curl "http://localhost:8000/api/v1/portfolios?category=Web Development"

# Get featured only
curl "http://localhost:8000/api/v1/portfolios?featured=1"
```

---

### 4. Update Portfolio

```bash
curl -X PUT http://localhost:8000/api/v1/portfolios/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "title=Updated Project Title" \
  -F "tags=Laravel, React, PostgreSQL, Redis"
```

---

### 5. Delete Portfolio

```bash
curl -X DELETE http://localhost:8000/api/v1/portfolios/1 \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

---

## 🎨 Frontend Integration

### React Example

```jsx
import { useState, useEffect } from 'react';

function UserPortfolios({ userId }) {
  const [portfolios, setPortfolios] = useState([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Fetch portfolios (no auth needed!)
    fetch(`http://localhost:8000/api/v1/portfolios/user/${userId}`)
      .then(res => res.json())
      .then(data => {
        setPortfolios(data.data);
        setLoading(false);
      });
  }, [userId]);

  if (loading) return <div>Loading...</div>;

  return (
    <div className="portfolios">
      {portfolios.map(portfolio => (
        <div key={portfolio.id} className="portfolio-card">
          <h2>{portfolio.title}</h2>
          <p>{portfolio.description}</p>
          
          {/* Display images */}
          <div className="images">
            {portfolio.images.map((img, idx) => (
              <img key={idx} src={img.url} alt={portfolio.title} />
            ))}
          </div>
          
          {/* Display tags */}
          <div className="tags">
            {portfolio.tags_array.map(tag => (
              <span key={tag} className="tag">{tag}</span>
            ))}
          </div>
          
          {/* Display categories */}
          <div className="categories">
            {portfolio.categories.map(cat => (
              <span key={cat} className="category">{cat}</span>
            ))}
          </div>
        </div>
      ))}
    </div>
  );
}

export default UserPortfolios;
```

### Vue.js Example

```vue
<template>
  <div class="portfolios">
    <div v-if="loading">Loading...</div>
    
    <div v-else>
      <div v-for="portfolio in portfolios" :key="portfolio.id" class="portfolio-card">
        <h2>{{ portfolio.title }}</h2>
        <p>{{ portfolio.description }}</p>
        
        <!-- Display images -->
        <div class="images">
          <img 
            v-for="(img, idx) in portfolio.images" 
            :key="idx"
            :src="img.url" 
            :alt="portfolio.title"
          />
        </div>
        
        <!-- Display tags -->
        <div class="tags">
          <span 
            v-for="tag in portfolio.tags_array" 
            :key="tag"
            class="tag"
          >
            {{ tag }}
          </span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['userId'],
  data() {
    return {
      portfolios: [],
      loading: true
    }
  },
  mounted() {
    this.fetchPortfolios();
  },
  methods: {
    async fetchPortfolios() {
      try {
        const response = await fetch(
          `http://localhost:8000/api/v1/portfolios/user/${this.userId}`
        );
        const data = await response.json();
        this.portfolios = data.data;
      } catch (error) {
        console.error('Error fetching portfolios:', error);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>
```

### JavaScript (Vanilla)

```javascript
// Fetch user portfolios
async function getUserPortfolios(userId) {
  const response = await fetch(`/api/v1/portfolios/user/${userId}`);
  const data = await response.json();
  
  if (data.success) {
    return data.data;
  }
  
  throw new Error(data.message);
}

// Create portfolio
async function createPortfolio(token, portfolioData) {
  const formData = new FormData();
  
  formData.append('title', portfolioData.title);
  formData.append('description', portfolioData.description);
  formData.append('category', portfolioData.category);
  formData.append('tags', portfolioData.tags);
  formData.append('status', 'published');
  
  // Add images
  portfolioData.images.forEach(image => {
    formData.append('images[]', image);
  });
  
  const response = await fetch('/api/v1/portfolios', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`
    },
    body: formData
  });
  
  return await response.json();
}

// Search portfolios
async function searchPortfolios(query) {
  const response = await fetch(`/api/v1/portfolios?search=${query}`);
  const data = await response.json();
  return data.data;
}

// Usage
const portfolios = await getUserPortfolios(5);
console.log(portfolios);
```

---

## 🎯 Common Use Cases

### Use Case 1: Portfolio Website

Display user's portfolios on their public profile:

```javascript
// Fetch portfolios
const portfolios = await fetch('/api/v1/portfolios/user/5?featured=1')
  .then(r => r.json())
  .then(d => d.data);

// Display featured portfolios
portfolios.forEach(portfolio => {
  console.log(`${portfolio.title} - ${portfolio.tags_array.join(', ')}`);
});
```

### Use Case 2: Portfolio Gallery

Show all portfolios with filtering:

```javascript
// Get all portfolios
const allPortfolios = await fetch('/api/v1/portfolios')
  .then(r => r.json())
  .then(d => d.data);

// Filter by category
const webProjects = await fetch('/api/v1/portfolios?category=Web Development')
  .then(r => r.json())
  .then(d => d.data);

// Search
const laravelProjects = await fetch('/api/v1/portfolios?search=Laravel')
  .then(r => r.json())
  .then(d => d.data);
```

### Use Case 3: Portfolio Management Dashboard

Manage user's own portfolios:

```javascript
// Get my portfolios (including drafts)
const myPortfolios = await fetch('/api/v1/portfolios/my/portfolios', {
  headers: { 'Authorization': `Bearer ${token}` }
}).then(r => r.json()).then(d => d.data);

// Toggle featured
await fetch(`/api/v1/portfolios/${portfolioId}/toggle-featured`, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` }
});

// Reorder
await fetch('/api/v1/portfolios/reorder', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    portfolios: [
      { id: 1, order: 0 },
      { id: 2, order: 1 },
      { id: 3, order: 2 }
    ]
  })
});
```

---

## 📱 Postman Collection

### Import this JSON into Postman:

```json
{
  "info": {
    "name": "Portfolio API",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Get User Portfolios",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/api/v1/portfolios/user/5"
      }
    },
    {
      "name": "Create Portfolio",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Authorization",
            "value": "Bearer {{token}}"
          }
        ],
        "body": {
          "mode": "formdata",
          "formdata": [
            {"key": "title", "value": "My Project", "type": "text"},
            {"key": "category", "value": "Web Development, SaaS", "type": "text"},
            {"key": "tags", "value": "Laravel, Vue.js", "type": "text"},
            {"key": "status", "value": "published", "type": "text"},
            {"key": "images[]", "type": "file", "src": ""}
          ]
        },
        "url": "{{base_url}}/api/v1/portfolios"
      }
    },
    {
      "name": "Search Portfolios",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/api/v1/portfolios?search=Laravel"
      }
    }
  ],
  "variable": [
    {
      "key": "base_url",
      "value": "http://localhost:8000"
    },
    {
      "key": "token",
      "value": "your-token-here"
    }
  ]
}
```

---

## 🔍 Testing Checklist

### Public Access (No Auth)
- [ ] Get all portfolios: `GET /api/v1/portfolios`
- [ ] Get user portfolios: `GET /api/v1/portfolios/user/5`
- [ ] View single portfolio: `GET /api/v1/portfolios/1`
- [ ] Search portfolios: `GET /api/v1/portfolios?search=Laravel`
- [ ] Filter by category: `GET /api/v1/portfolios?category=Design`
- [ ] Filter by tag: `GET /api/v1/portfolios?tag=React`
- [ ] Get featured: `GET /api/v1/portfolios?featured=1`

### Authenticated Operations
- [ ] Login and get token
- [ ] Create portfolio with images
- [ ] Create portfolio with files
- [ ] Update portfolio
- [ ] Add new images to existing portfolio
- [ ] Remove images from portfolio
- [ ] Toggle featured status
- [ ] Reorder portfolios
- [ ] Delete portfolio
- [ ] Verify files deleted from storage

### File Access
- [ ] Access image URL in browser
- [ ] Access file URL in browser
- [ ] Verify 404 for deleted files
- [ ] Check storage directories created

---

## ❓ Common Questions

### Q: Do I need authentication to view portfolios?
**A:** No! Viewing portfolios is public. Only creating/editing requires auth.

### Q: How do I add multiple tags?
**A:** Use comma-separated string: `"tags": "Laravel, Vue.js, MySQL, Redis"`

### Q: Can I add multiple categories?
**A:** Yes! Use comma-separated string: `"category": "Web Development, E-commerce, SaaS"`

### Q: What image formats are supported?
**A:** jpg, jpeg, png, gif, webp (max 5MB each, 10 images per portfolio)

### Q: What file formats are supported?
**A:** pdf, doc, docx, xls, xlsx, ppt, pptx, txt, zip, rar (max 10MB each, 10 files per portfolio)

### Q: Where are files stored?
**A:** 
- Images: `storage/app/public/portfolios/images/`
- Files: `storage/app/public/portfolios/`

### Q: How do I remove an image?
**A:** Send `remove_images[]` array with paths in PUT/PATCH request

### Q: Can users see draft portfolios?
**A:** Only the owner can see their draft portfolios. Public can only see published.

---

## 🎓 Advanced Examples

### Filter by Multiple Criteria

```bash
# User's featured web development portfolios
curl "http://localhost:8000/api/v1/portfolios/user/5?category=Web Development&featured=1"

# Search with pagination
curl "http://localhost:8000/api/v1/portfolios?search=Laravel&per_page=20&page=2"
```

### Update with File Management

```bash
curl -X PUT http://localhost:8000/api/v1/portfolios/1 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "title=Updated Title" \
  -F "tags=Laravel, React, PostgreSQL" \
  -F "images[]=@new-screenshot.jpg" \
  -F "remove_images[]=portfolios/images/old-screenshot.jpg" \
  -F "files[]=@new-document.pdf" \
  -F "remove_files[]=portfolios/old-document.pdf"
```

### Reorder Portfolios

```bash
curl -X POST http://localhost:8000/api/v1/portfolios/reorder \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "portfolios": [
      {"id": 1, "order": 0},
      {"id": 2, "order": 1},
      {"id": 3, "order": 2}
    ]
  }'
```

---

## 🐛 Troubleshooting

### Files not accessible
```bash
# Recreate storage link
php artisan storage:link

# Check permissions (Linux/Mac)
chmod -R 775 storage/app/public
```

### Upload fails
```bash
# Check PHP limits in php.ini
upload_max_filesize = 20M
post_max_size = 25M

# Restart server after changes
php artisan serve
```

### 404 on file URLs
```bash
# Verify symlink exists
ls -la public/storage  # Linux/Mac
dir public\storage     # Windows

# Check file exists
ls storage/app/public/portfolios/images/
```

---

## 📚 Next Steps

1. **Read Full Documentation**
   - [Portfolio API Documentation](docs/PortfolioApi.md)
   - [File Storage Architecture](docs/FileStorageArchitecture.md)

2. **Integrate with Frontend**
   - Use the JavaScript examples above
   - Build portfolio gallery
   - Create portfolio management dashboard

3. **Customize**
   - Add more categories
   - Customize validation rules
   - Add portfolio analytics
   - Implement comments/likes

4. **Deploy**
   - Set up production storage
   - Configure CDN for files
   - Set up backups
   - Monitor disk usage

---

## 🎉 You're Ready!

The Portfolio API is fully functional and ready to use. Start building your portfolio showcase!

**Key Endpoints:**
- View: `GET /api/v1/portfolios/user/{userId}`
- Create: `POST /api/v1/portfolios`
- Update: `PUT /api/v1/portfolios/{id}`
- Delete: `DELETE /api/v1/portfolios/{id}`

**Need Help?** Check the documentation in the `docs/` folder!
