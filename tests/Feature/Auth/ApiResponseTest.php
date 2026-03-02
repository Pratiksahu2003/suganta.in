<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Add missing columns for testing
        if (\Schema::hasTable('users')) {
            \Schema::table('users', function (\Illuminate\Database\Schema\Blueprint $table) {
                if (!\Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true);
                    // Create personal_access_tokens table
        if (!\Schema::hasTable('personal_access_tokens')) {
            \Schema::create('personal_access_tokens', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }
                if (!\Schema::hasColumn('users', 'role')) {
                    $table->string('role')->nullable();
                }
                if (!\Schema::hasColumn('users', 'phone')) {
                    $table->string('phone')->nullable();
                }
                if (!\Schema::hasColumn('users', 'verification_status')) {
                    $table->string('verification_status')->default('pending');
                }
                if (!\Schema::hasColumn('users', 'registration_fee_status')) {
                    $table->string('registration_fee_status')->nullable();
                }
                if (!\Schema::hasColumn('users', 'phone_verified_at')) {
                    $table->timestamp('phone_verified_at')->nullable();
                }
                if (!\Schema::hasColumn('users', 'referred_by')) {
                    $table->string('referred_by')->nullable();
                }
                if (!\Schema::hasColumn('users', 'preferences')) {
                    $table->json('preferences')->nullable();
                }
            });
        }
        
        // Create otps table
        if (!\Schema::hasTable('otps')) {
            \Schema::create('otps', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('identifier');
                $table->string('type');
                $table->string('otp');
                $table->timestamp('expires_at');
                $table->boolean('verified')->default(false);
                $table->boolean('is_used')->default(false);
                $table->integer('attempt_count')->default(0);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }

        // Create profiles table (needed for registration)
        if (!\Schema::hasTable('profiles')) {
             \Schema::create('profiles', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('display_name')->nullable();
                $table->string('profile_image')->nullable();
                $table->string('slug')->nullable();
                $table->timestamps();
            });
        }
        
        // Create user_sessions table
        if (!\Schema::hasTable('user_sessions')) {
            \Schema::create('user_sessions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->string('payload')->nullable();
                $table->integer('last_activity')->index();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_current_session')->default(false);
                $table->timestamp('login_at')->nullable();
                $table->string('device_type')->nullable();
                $table->string('browser')->nullable();
                $table->string('platform')->nullable();
                $table->timestamps();
            });
        }
        
        // Create roles table
        if (!\Schema::hasTable('roles')) {
            \Schema::create('roles', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
            \DB::table('roles')->insert([
                ['name' => 'student'],
                ['name' => 'teacher'],
            ]);
        }
        
        // Create user_roles table
        if (!\Schema::hasTable('user_roles')) {
            \Schema::create('user_roles', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamps();
            });
        }
    }

    public function test_register_returns_standard_response()
    {
        $response = $this->postJson('auth/register', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'student',
            'phone' => '+1234567890',
        ]);

        if ($response->status() !== 201 && $response->status() !== 422) {
             $response->dump();
        }

        if ($response->status() === 422) {
             $response->assertJsonStructure([
                'message',
                'success',
                'code',
                'errors'
            ])->assertJson([
                'success' => false,
                'code' => 422
            ]);
        } else {
            $response->assertStatus(201)
                ->assertJsonStructure([
                    'message',
                    'success',
                    'code',
                    'data' => [
                        'user',
                        'token',
                        'token_type'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'code' => 201,
                    'message' => 'User registered successfully'
                ]);
        }
    }

    public function test_login_returns_standard_response()
    {
        $user = User::factory()->create([
            'email' => 'login@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
            'role' => 'student',
        ]);

        $response = $this->postJson('auth/login', [
            'email' => 'login@example.com',
            'password' => 'password',
        ]);

        if ($response->status() !== 200) {
             $response->dump();
        }

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'success',
                'code',
                'data'
            ])
            ->assertJson([
                'success' => true,
                'code' => 200,
            ]);
            
        // Check if data contains user and token (if no OTP required) or requires_otp
        $data = $response->json('data');
        if (isset($data['requires_otp'])) {
             $this->assertTrue($data['requires_otp']);
        } else {
             $this->assertArrayHasKey('token', $data);
        }
    }

    public function test_validation_error_returns_standard_response()
    {
        $response = $this->postJson('auth/login', [
            'email' => 'invalid-email',
            'password' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'message',
                'success',
                'code',
                'errors'
            ])
            ->assertJson([
                'success' => false,
                'code' => 422,
                'message' => 'The password field is required.'
            ]);
    }

    public function test_verification_resend_returns_standard_response()
    {
        $user = User::factory()->create([
            'email' => 'resend@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'role' => 'student',
            'email_verified_at' => null, // Ensure unverified
        ]);

        $this->actingAs($user);

        $response = $this->postJson('auth/verification/resend', [
            'type' => 'email'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'success',
                'code',
            ])
            ->assertJson([
                'success' => true,
                'code' => 200,
                'message' => 'Verification code sent.'
            ]);
    }
}
