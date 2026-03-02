<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class TwoFactorLoginTest extends TestCase
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
                // Create user_sessions table
        if (!\Schema::hasTable('user_sessions')) {
            \Schema::create('user_sessions', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->id();
                $table->string('session_id')->unique()->nullable();
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
                $table->string('browser_version')->nullable();
                $table->string('platform')->nullable();
                $table->string('platform_version')->nullable();
                $table->string('device_model')->nullable();
                $table->string('device_name')->nullable();
                $table->string('location')->nullable();
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->string('postal_code')->nullable();
                $table->string('latitude')->nullable();
                $table->string('longitude')->nullable();
                $table->string('timezone')->nullable();
                $table->string('device_fingerprint')->nullable();
                $table->string('device_family')->nullable();
                $table->timestamps();
            });
        }
        
        // Create notifications table
        if (!\Schema::hasTable('notifications')) {
            \Schema::create('notifications', function (\Illuminate\Database\Schema\Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }
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
    }

    public function test_login_triggers_otp_for_untrusted_device()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
            'role' => 'student',
        ]);

        $response = $this->postJson('auth/login', [
            'email' => 'test@example.com',
            'password' => 'password',
            'device_name' => 'Test Device',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'requires_otp' => true,
                    'identifier' => 'test@example.com',
                    'type' => 'email',
                ]
            ]);

        $this->assertDatabaseHas('otps', [
            'identifier' => 'test@example.com',
            'is_used' => false,
        ]);
    }

    public function test_verify_login_otp_issues_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
            'role' => 'student',
        ]);

        // Create OTP manually
        $otpCode = '123456';
        Otp::create([
            'user_id' => $user->id,
            'identifier' => 'test@example.com',
            'type' => 'email',
            'otp' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
            'attempt_count' => 0,
        ]);

        $response = $this->postJson('auth/login/verify', [
            'identifier' => 'test@example.com',
            'otp' => $otpCode,
            'device_name' => 'Test Device',
        ]);

        if ($response->status() !== 200) {
             $response->dump();
        }

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Login successful',
            ])
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'user',
                ]
            ]);
    }

    public function test_rate_limiting_otp_requests()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
            'email_verified_at' => now(),
            'role' => 'student',
        ]);

        // 1st request
        $this->postJson('auth/login', ['email' => 'test@example.com', 'password' => 'password']);
        
        // 2nd request (should be allowed after cooldown? No, first 3 are allowed immediately in my implementation logic? 
        // Wait, my implementation logic: 
        // if attempts=0 -> allow, next wait 30s.
        // So 2nd request immediately after 1st should be blocked by cooldown key.
        
        $response = $this->postJson('auth/login', ['email' => 'test@example.com', 'password' => 'password']);
        
        if ($response->status() !== 429) {
             $response->dump();
        }
        
        $response->assertStatus(429); // Too Many Requests (Cooldown)
    }
}
