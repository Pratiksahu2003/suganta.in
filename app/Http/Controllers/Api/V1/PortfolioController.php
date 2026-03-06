<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StorePortfolioRequest;
use App\Http\Requests\UpdatePortfolioRequest;
use App\Http\Resources\PortfolioResource;
use App\Models\Portfolio;
use App\Models\User;
use App\Traits\HandlesFileStorage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PortfolioController extends BaseApiController
{
    use HandlesFileStorage;
    /**
     * Get dropdown option values for portfolios.
     */
    public function options(): JsonResponse
    {
        $allCategories = Portfolio::whereNotNull('category')
            ->where('category', '!=', '')
            ->pluck('category')
            ->flatMap(function ($category) {
                return array_map('trim', explode(',', $category));
            })
            ->unique()
            ->values()
            ->toArray();

        $allTags = Portfolio::whereNotNull('tags')
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
     * List portfolios with filtering and pagination.
     * Public access - anyone can view portfolios by user_id.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Portfolio::query()->with('user');

        if ($request->filled('user_id')) {
            $query->forUser($request->integer('user_id'));
            $query->where('status', 'published');
        } else {
            if ($request->filled('status')) {
                $query->where('status', $request->string('status'));
            } else {
                $query->published();
            }
        }

        if ($request->filled('category')) {
            $query->byCategory($request->string('category'));
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->filled('tag')) {
            $query->byTag($request->string('tag'));
        }

        if ($request->filled('search')) {
            $searchTerm = $request->string('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('tags', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
            });
        }

        $query->orderBy('order', 'asc')->latest();

        $portfolios = $query->paginate($request->integer('per_page', 15));

        return $this->paginated(
            PortfolioResource::collection($portfolios),
            'Portfolios retrieved successfully.'
        );
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
     * Show a specific portfolio.
     * Public access - anyone can view published portfolios.
     */
    public function show(Portfolio $portfolio): JsonResponse
    {
        $user = Auth::user();

        if ($portfolio->status !== 'published') {
            if (!$user || $portfolio->user_id !== $user->id) {
                return $this->forbidden('You are not allowed to view this portfolio.');
            }
        }

        $portfolio->load('user');

        return $this->success(
            'Portfolio retrieved successfully.',
            new PortfolioResource($portfolio)
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

    /**
     * Delete a portfolio.
     */
    public function destroy(Portfolio $portfolio): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($portfolio->user_id !== $user->id) {
                return $this->forbidden('You are not allowed to delete this portfolio.');
            }

            if ($portfolio->images) {
                $this->deleteMultipleFiles($portfolio->images);
            }

            if ($portfolio->files) {
                $this->deleteMultipleFiles($portfolio->files);
            }

            $portfolio->delete();

            return $this->noContent();
        } catch (Exception $e) {
            Log::error('Failed to delete portfolio', [
                'portfolio_id' => $portfolio->id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->serverError('Failed to delete portfolio. Please try again.');
        }
    }

    /**
     * Get portfolios for a specific user (public access).
     * Only shows published portfolios.
     */
    public function getUserPortfolios(Request $request, int $userId): JsonResponse
    {
        $query = Portfolio::query()
            ->with('user')
            ->forUser($userId)
            ->published();

        if ($request->filled('category')) {
            $query->byCategory($request->string('category'));
        }

        if ($request->filled('tag')) {
            $query->byTag($request->string('tag'));
        }

        if ($request->boolean('featured')) {
            $query->featured();
        }

        if ($request->filled('search')) {
            $searchTerm = $request->string('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%")
                  ->orWhere('tags', 'like', "%{$searchTerm}%")
                  ->orWhere('category', 'like', "%{$searchTerm}%");
            });
        }

        $query->orderBy('order', 'asc')->latest();

        $portfolios = $query->paginate($request->integer('per_page', 15));

        return $this->paginated(
            PortfolioResource::collection($portfolios),
            'User portfolios retrieved successfully.'
        );
    }

    /**
     * Get portfolios for the authenticated user.
     * Shows all statuses (draft, published, archived).
     */
    public function myPortfolios(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $query = Portfolio::query()->forUser($user->id);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category')) {
            $query->byCategory($request->string('category'));
        }

        $query->orderBy('order', 'asc')->latest();

        $portfolios = $query->paginate($request->integer('per_page', 15));

        return $this->paginated(
            PortfolioResource::collection($portfolios),
            'Your portfolios retrieved successfully.'
        );
    }

    /**
     * Toggle featured status of a portfolio.
     */
    public function toggleFeatured(Portfolio $portfolio): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            if ($portfolio->user_id !== $user->id) {
                return $this->forbidden('You are not allowed to modify this portfolio.');
            }

            $portfolio->update([
                'is_featured' => !$portfolio->is_featured,
            ]);

            return $this->success(
                'Portfolio featured status updated successfully.',
                new PortfolioResource($portfolio->fresh('user'))
            );
        } catch (Exception $e) {
            Log::error('Failed to toggle portfolio featured status', [
                'portfolio_id' => $portfolio->id ?? null,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->serverError('Failed to update portfolio. Please try again.');
        }
    }

    /**
     * Reorder portfolios.
     */
    public function reorder(Request $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $validated = $request->validate([
                'portfolios' => ['required', 'array'],
                'portfolios.*.id' => ['required', 'integer', 'exists:portfolios,id'],
                'portfolios.*.order' => ['required', 'integer', 'min:0'],
            ]);

            foreach ($validated['portfolios'] as $portfolioData) {
                $portfolio = Portfolio::find($portfolioData['id']);
                
                if ($portfolio && $portfolio->user_id === $user->id) {
                    $portfolio->update(['order' => $portfolioData['order']]);
                }
            }

            return $this->success('Portfolios reordered successfully.');
        } catch (Exception $e) {
            Log::error('Failed to reorder portfolios', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return $this->serverError('Failed to reorder portfolios. Please try again.');
        }
    }
}
