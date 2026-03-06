<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'images',
        'files',
        'category',
        'tags',
        'url',
        'status',
        'order',
        'is_featured',
    ];

    protected $casts = [
        'images' => 'array',
        'files' => 'array',
        'is_featured' => 'boolean',
        'order' => 'integer',
    ];

    protected $appends = ['tags_array', 'categories_array'];

    /**
     * Get tags as an array
     */
    public function getTagsArrayAttribute(): array
    {
        if (empty($this->tags)) {
            return [];
        }
        
        return array_map('trim', explode(',', $this->tags));
    }

    /**
     * Get categories as an array
     */
    public function getCategoriesArrayAttribute(): array
    {
        if (empty($this->category)) {
            return [];
        }
        
        return array_map('trim', explode(',', $this->category));
    }

    /**
     * Set tags from array or string
     */
    public function setTagsAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['tags'] = implode(', ', array_filter(array_map('trim', $value)));
        } else {
            $this->attributes['tags'] = $value;
        }
    }

    /**
     * Set category from array or string
     */
    public function setCategoryAttribute($value): void
    {
        if (is_array($value)) {
            $this->attributes['category'] = implode(', ', array_filter(array_map('trim', $value)));
        } else {
            $this->attributes['category'] = $value;
        }
    }

    /**
     * Get the user that owns the portfolio
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter published portfolios
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to filter featured portfolios
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to filter by user
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', 'like', "%{$category}%");
    }

    /**
     * Scope to filter by tag
     */
    public function scopeByTag($query, $tag)
    {
        return $query->where('tags', 'like', "%{$tag}%");
    }
}
