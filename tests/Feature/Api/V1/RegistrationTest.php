<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;

class RegistrationTest extends TestCase
{
    /**
     * Test fetching all registration charges.
     */
    public function test_can_fetch_all_registration_charges(): void
    {
        $response = $this->getJson('/api/v1/registration/charges');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'student',
                    'teacher',
                    'institute',
                    'university'
                ]
            ]);
    }

    /**
     * Test fetching specific role registration charges.
     */
    public function test_can_fetch_specific_role_charges(): void
    {
        $response = $this->getJson('/api/v1/registration/charges?role=student');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'actual_price',
                    'discounted_price',
                    'currency',
                    'description'
                ]
            ]);
    }

    /**
     * Test fetching invalid role returns 404.
     */
    public function test_fetch_invalid_role_returns_404(): void
    {
        $response = $this->getJson('/api/v1/registration/charges?role=invalid_role');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Invalid role specified.'
            ]);
    }
}
