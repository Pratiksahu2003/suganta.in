<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use App\Models\User;
use App\Traits\HandlesFileStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PortfolioController extends BaseApiController
{
    use HandlesFileStorage;
    /**
     * Get dropdown option values for portfolios (auth user's data only).
     */
    public function options(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $allCategories = Portfolio::forUser($user->id)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->pluck('category')
            ->flatMap(function ($category) {
                return array_map('trim', explode(',', $category));
            })
            ->unique()
            ->values()
            ->toArray();

        $allTags = Portfolio::forUser($user->id)
            ->whereNotNull('tags')
            ->where('tags', '!=', '')
            ->pluck('tags')
            ->flatMap(function ($tags) {
                return array_map('trim', explode(',', $tags));
            })
            ->unique()
            ->values()
            ->toArray();

        return $this->success('Portfolio options retrieved successfully.', [
            'statuses' => [
                'draft' => 'Draft',
                'published' => 'Published',
                'archived' => 'Archived',
            ],
            'categories' => $allCategories,
            'tags' => $allTags,
        ]);
    }

    /**
     * Create a new portfolio.
     */
    public function store(StorePortfolioRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $validated = $request->validated();

            $imagePaths = [];
            if ($request->hasFile('images')) {
                $imagePaths = $this->uploadMultipleFiles(
                    $request->file('images'),
                    $user->id,
                    'image',
                    'portfolio'
                );
            }

            $filePaths = [];
            if ($request->hasFile('files')) {
                $filePaths = $this->uploadMultipleFiles(
                    $request->file('files'),
                    $user->id,
                    'file',
                    'portfolio'
                );
            }

            $portfolio = Portfolio::create([
                'user_id' => $user->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'images' => $imagePaths,
                'files' => $filePaths,
                'category' => $validated['category'] ?? null,
                'tags' => $validated['tags'] ?? null,
                'url' => $validated['url'] ?? null,
                'status' => $validated['status'] ?? 'draft',
                'order' => $validated['order'] ?? 0,
                'is_featured' => $validated['is_featured'] ?? false,
            ]);

            $portfolio->load('user');

            return $this->created(
                new PortfolioResource($portfolio),
                'Portfolio created successfully.'
            );
        } catch (Exception $e) {
            Log::error('Failed to create portfolio', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->serverError('Failed to create portfolio. Please try again.');
        }
    }

    /**
     * Show auth user's portfolios (fetched by Auth::id(), no ID in URL).
     */
    public function show(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $portfolios = Portfolio::forUser($user->id)
            ->with('user')
            ->orderBy('order')
            ->latest()
            ->get();

        return $this->success(
            'Portfolios retrieved successfully.',
            PortfolioResource::collection($portfolios)
        );
    }

    /**
     * Update an existing portfolio.
     */
    public function update(UpdatePortfolioRequest $request, Portfolio $portfolio): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($portfolio->user_id !== $user->id) {
                return $this->forbidden('You are not allowed to update this portfolio.');
            }

            $validated = $request->validated();

            $imagePaths = $portfolio->images ?? [];
            if ($request->hasFile('images')) {
                $newImages = $this->uploadMultipleFiles(
                    $request->file('images'),
                    $user->id,
                    'image',
                    'portfolio'
                );
                $imagePaths = array_merge($imagePaths, $newImages);
            }

            if ($request->has('remove_images')) {
                $removeImages = $request->input('remove_images', []);
                $this->deleteMultipleFiles($removeImages);
                $imagePaths = array_values(array_diff($imagePaths, $removeImages));
            }

            $filePaths = $portfolio->files ?? [];
            if ($request->hasFile('files')) {
                $newFiles = $this->uploadMultipleFiles(
                    $request->file('files'),
                    $user->id,
                    'file',
                    'portfolio'
                );
                $filePaths = array_merge($filePaths, $newFiles);
            }

            if ($request->has('remove_files')) {
                $removeFiles = $request->input('remove_files', []);
                $this->deleteMultipleFiles($removeFiles);
                $filePaths = array_values(array_diff($filePaths, $removeFiles));
            }

            $portfolio->update([
                'title' => $validated['title'] ?? $portfolio->title,
                'description' => $validated['description'] ?? $portfolio->description,
                'images' => $imagePaths,
                'files' => $filePaths,
                'category' => $validated['category'] ?? $portfolio->category,
                'tags' => $validated['tags'] ?? $portfolio->tags,
                'url' => $validated['url'] ?? $portfolio->url,
                'status' => $validated['status'] ?? $portfolio->status,
                'order' => $validated['order'] ?? $portfolio->order,
                'is_featured' => $validated['is_featured'] ?? $portfolio->is_featured,
            ]);

            return $this->success(
                'Portfolio updated successfully.',
                new PortfolioResource($portfolio->fresh('user'))
            );
        } catch (Exception $e) {
            Log::error('Failed to update portfolio', [
                'portfolio_id' => $portfolio->id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->serverError('Failed to update portfolio. Please try again.');
        }
    }
}
