<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class OptionController extends BaseApiController
{
    /**
     * Get options from config based on keys.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        // Generate a cache key based on the requested keys
        $keys = $request->query('key');
        $cacheKey = 'options_api_' . ($keys ? md5($keys) : 'all');
        $cacheDuration = 60 * 24; // Cache for 24 hours (in minutes)

        // Try to retrieve from cache or store it
        $options = Cache::remember($cacheKey, $cacheDuration, function () use ($keys) {
            if (!$keys) {
                // Return all options
                return config('options');
            }

            // Handle specific keys
            $requestedKeys = explode(',', $keys);
            $filteredOptions = [];

            foreach ($requestedKeys as $key) {
                $key = trim($key);
                if ($value = config("options.{$key}")) {
                    $filteredOptions[$key] = $value;
                }
            }

            return $filteredOptions;
        });

        if (empty($options)) {
            return $this->error('No valid options found for the provided keys.', 404);
        }

        // Generate ETag for client-side caching
        $etag = md5(json_encode($options));
        
        // Check if the client's ETag matches the current one
        $requestEtag = $request->header('If-None-Match');
        if ($requestEtag && strpos($requestEtag, $etag) !== false) {
            return response()->json(null, 304); // Not Modified
        }

        // Return the response with caching headers
        return $this->success($keys ? 'Options retrieved successfully.' : 'All options retrieved successfully.', $options)
            ->header('Cache-Control', 'public, max-age=86400') // Cache for 24 hours
            ->header('ETag', '"' . $etag . '"');
    }
}
