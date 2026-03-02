<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Auth\ForgotPasswordRequest;
use App\Http\Requests\Api\V1\Auth\LoginRequest;
use App\Http\Requests\Api\V1\Auth\RegisterRequest;
use App\Http\Requests\Api\V1\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Register a new user
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated(), $request);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => $result
            ], 201);
        } catch (\Exception $e) {
            Log::error('API Registration failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Registration failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Login user and return token
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated(), $request);

            if (isset($result['requires_registration_payment']) && $result['requires_registration_payment']) {
                return response()->json(array_merge([
                    'success' => false,
                ], $result), 200);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $result
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Login failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getCode() === 403 ? $e->getMessage() : 'Login failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], $e->getCode() === 403 ? 403 : 500);
        }
    }

    /**
     * Logout user and revoke token
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->authService->logout($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Logout from all devices
     */
    public function logoutFromAllDevices(Request $request): JsonResponse
    {
        try {
            $this->authService->logoutFromAllDevices($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Logged out from all devices successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Refresh user token
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $token = $this->authService->refreshToken($request->user());

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => [
                    'token' => $token,
                    'token_type' => 'Bearer'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Token refresh failed',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Send password reset link
     */
    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        try {
            $this->authService->forgotPassword($request->validated()['email']);

            return response()->json([
                'success' => true,
                'message' => 'If an account with that email exists, a password reset link has been sent.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset request failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $status = $this->authService->resetPassword($request->validated());

            if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
                return response()->json([
                    'success' => true,
                    'message' => 'Password has been reset successfully. Please login with your new password.'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Password reset failed. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
