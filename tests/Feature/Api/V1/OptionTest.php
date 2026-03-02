<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class OptionTest extends TestCase
{
    /**
     * Test fetching all options.
     */
    public function test_can_fetch_all_options(): void
    {
        $response = $this->getJson('/api/v1/options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'gender',
                    'institute_type',
                    // Add other keys as needed
                ]
            ]);
    }

    /**
     * Test fetching a specific option.
     */
    public function test_can_fetch_specific_option(): void
    {
        $response = $this->getJson('/api/v1/options?key=gender');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'gender'
                ]
            ])
            ->assertJsonMissing([
                'data' => [
                    'institute_type'
                ]
            ]);
    }

    /**
     * Test fetching multiple options.
     */
    public function test_can_fetch_multiple_options(): void
    {
        $response = $this->getJson('/api/v1/options?key=gender,institute_type');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'gender',
                    'institute_type'
                ]
            ]);
    }

    /**
     * Test fetching invalid option.
     */
    public function test_fetch_invalid_option_returns_404(): void
    {
        $response = $this->getJson('/api/v1/options?key=invalid_key_123');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No valid options found for the provided keys.'
            ]);
    }

    /**
     * Test response has cache headers.
     */
    public function test_response_has_cache_headers(): void
    {
        $response = $this->getJson('/api/v1/options');

        $response->assertStatus(200);
        $this->assertTrue($response->headers->has('Cache-Control'));
        $this->assertTrue($response->headers->has('ETag'));
        
        // Check content, order might differ
        $cacheControl = $response->headers->get('Cache-Control');
        $this->assertStringContainsString('max-age=86400', $cacheControl);
        $this->assertStringContainsString('public', $cacheControl);
    }

    /**
     * Test ETag caching (304 Not Modified).
     */
    public function test_etag_caching_returns_304(): void
    {
        // First request to get the ETag
        $response = $this->getJson('/api/v1/options');
        $etag = $response->headers->get('ETag');

        // Second request with If-None-Match header
        $response2 = $this->getJson('/api/v1/options', [
            'If-None-Match' => $etag
        ]);

        $response2->assertStatus(304);
    }
}
