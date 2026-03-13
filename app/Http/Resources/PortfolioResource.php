<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortfolioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'title' => $this->title,
            'description' => $this->description,
            'images' => $this->formatImages($this->images),
            'files' => $this->formatFiles($this->files),
            'category' => $this->category,
            'categories' => $this->categories_array,
            'tags' => $this->tags,
            'tags_array' => $this->tags_array,
            'url' => $this->url,
            'status' => $this->status,
            'order' => $this->order,
            'is_featured' => $this->is_featured,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Format images with full URLs.
     */
    protected function formatImages(?array $images): array
    {
        if (!$images) {
            return [];
        }

        return array_map(function ($path) {
            return [
                'path' => $path,
                'url' => storage_file_url($path),
            ];
        }, $images);
    }

    /**
     * Format files with full URLs.
     */
    protected function formatFiles(?array $files): array
    {
        if (!$files) {
            return [];
        }

        return array_map(function ($path) {
            return [
                'path' => $path,
                'url' => storage_file_url($path),
                'name' => basename($path),
            ];
        }, $files);
    }
}
