<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Requests\Auth\GoogleLoginRequest;
use App\Http\Resources\UserResource;
use App\DTO\RegisterDTO;
use App\DTO\LoginDTO;
use App\DTO\ProfileDTO;
use App\Actions\Auth\RegisterAction;
use App\Actions\Auth\LoginAction;
use App\Actions\Auth\LogoutAction;
use App\Actions\Auth\RefreshTokenAction;
use App\Actions\Auth\ChangePasswordAction;
use App\Actions\Auth\ForgotPasswordAction;
use App\Actions\Auth\ResetPasswordAction;
use App\Actions\Auth\UpdateProfileAction;
use App\Actions\Auth\GoogleLoginAction;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Services\Auth\TokenService;
use Carbon\Carbon;

class AuthController extends Controller
{
    protected UserRepositoryInterface $userRepository;
    protected TokenService $tokenService;

    public function __construct(
        UserRepositoryInterface $userRepository,
        TokenService $tokenService
    ) {
        $this->userRepository = $userRepository;
        $this->tokenService = $tokenService;
    }

    /**
     * Register a new user
     * 
     * @param RegisterRequest $request
     * @param RegisterAction $action
     * @return JsonResponse
     */
    public function register(RegisterRequest $request, RegisterAction $action): JsonResponse
    {
        $dto = RegisterDTO::fromRequest($request->validated());
        $user = $action->execute($dto);

        // Generate tokens
        $tokens = $this->tokenService->generateTokens($user);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully',
            'data' => [
                'user' => new UserResource($user),
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => 'Bearer',
                'expires_in' => $tokens['expires_in'],
            ],
            'errors' => null,
        ], 201);
    }

    /**
     * Login user
     * 
     * @param LoginRequest $request
     * @param LoginAction $action
     * @return JsonResponse
     */
    public function login(LoginRequest $request, LoginAction $action): JsonResponse
    {
        $dto = LoginDTO::fromRequest($request->validated());
        $result = $action->execute($dto);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
                'data' => null,
                'errors' => [
                    'email' => ['The provided credentials are incorrect.'],
                ],
            ], 401);
        }

        $user = $result['user'];
        $tokens = $this->tokenService->generateTokens($user);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'user' => new UserResource($user),
                'access_token' => $tokens['access_token'],
                'refresh_token' => $tokens['refresh_token'],
                'token_type' => 'Bearer',
                'expires_in' => $tokens['expires_in'],
            ],
            'errors' => null,
        ]);
    }

    /**
     * Google OAuth Login
     * 
     * @param GoogleLoginRequest $request
     * @param GoogleLoginAction $action
     * @return JsonResponse
     */
    public function googleLogin(GoogleLoginRequest $request, GoogleLoginAction $action): JsonResponse
    {
        try {
            $dto = $request->validated();
            $result = $action->execute($dto['token']);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Google authentication failed',
                    'data' => null,
                    'errors' => [
                        'google' => ['Unable to authenticate with Google.'],
                    ],
                ], 401);
            }

            $user = $result['user'];
            $tokens = $this->tokenService->generateTokens($user);

            return response()->json([
                'success' => true,
                'message' => 'Google login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'token_type' => 'Bearer',
                    'expires_in' => $tokens['expires_in'],
                ],
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
                'data' => null,
                'errors' => [
                    'google' => [$e->getMessage()],
                ],
            ], 500);
        }
    }

    /**
     * Get Google OAuth URL
     * 
     * @return JsonResponse
     */
    public function getGoogleAuthUrl(): JsonResponse
    {
        try {
            $url = Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl();

            return response()->json([
                'success' => true,
                'message' => 'Google auth URL generated',
                'data' => [
                    'url' => $url,
                ],
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate Google auth URL',
                'data' => null,
                'errors' => [
                    'google' => [$e->getMessage()],
                ],
            ], 500);
        }
    }

    /**
     * Handle Google OAuth Callback
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function googleCallback(Request $request): JsonResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'email_verified_at' => now(),
                ]);
            }

            $tokens = $this->tokenService->generateTokens($user);

            return response()->json([
                'success' => true,
                'message' => 'Google login successful',
                'data' => [
                    'user' => new UserResource($user),
                    'access_token' => $tokens['access_token'],
                    'refresh_token' => $tokens['refresh_token'],
                    'token_type' => 'Bearer',
                    'expires_in' => $tokens['expires_in'],
                ],
                'errors' => null,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Google authentication failed',
                'data' => null,
                'errors' => [
                    'google' => [$e->getMessage()],
                ],
            ], 500);
        }
    }

    /**
     * Logout user
     * 
     * @param Request $request
     * @param LogoutAction $action
     * @return JsonResponse
     */
    public function logout(Request $request, LogoutAction $action): JsonResponse
    {
        $user = $request->user();
        $action->execute($user);

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Refresh token
     * 
     * @param Request $request
     * @param RefreshTokenAction $action
     * @return JsonResponse
     */
    public function refreshToken(Request $request, RefreshTokenAction $action): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $result = $action->execute($request->refresh_token);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid refresh token',
                'data' => null,
                'errors' => [
                    'refresh_token' => ['The refresh token is invalid or expired.'],
                ],
            ], 401);
        }

        return response()->json([
            'success' => true,
            'message' => 'Token refreshed successfully',
            'data' => [
                'access_token' => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
                'token_type' => 'Bearer',
                'expires_in' => $result['expires_in'],
            ],
            'errors' => null,
        ]);
    }

    /**
     * Get user profile
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'success' => true,
            'message' => 'Profile retrieved successfully',
            'data' => new UserResource($user),
            'errors' => null,
        ]);
    }

    /**
     * Update user profile
     * 
     * @param UpdateProfileRequest $request
     * @param UpdateProfileAction $action
     * @return JsonResponse
     */
    public function updateProfile(UpdateProfileRequest $request, UpdateProfileAction $action): JsonResponse
    {
        $user = $request->user();
        $dto = ProfileDTO::fromRequest($request->validated());
        $updatedUser = $action->execute($user, $dto);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => new UserResource($updatedUser),
            'errors' => null,
        ]);
    }

    /**
     * Change password
     * 
     * @param ChangePasswordRequest $request
     * @param ChangePasswordAction $action
     * @return JsonResponse
     */
    public function changePassword(ChangePasswordRequest $request, ChangePasswordAction $action): JsonResponse
    {
        $user = $request->user();
        $result = $action->execute($user, $request->current_password, $request->new_password);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'data' => null,
                'errors' => [
                    'current_password' => ['The current password is incorrect.'],
                ],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Send password reset link
     * 
     * @param ForgotPasswordRequest $request
     * @param ForgotPasswordAction $action
     * @return JsonResponse
     */
    public function forgotPassword(ForgotPasswordRequest $request, ForgotPasswordAction $action): JsonResponse
    {
        $result = $action->execute($request->email);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to send reset link',
                'data' => null,
                'errors' => [
                    'email' => [$result['message']],
                ],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset link sent to your email',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Reset password
     * 
     * @param ResetPasswordRequest $request
     * @param ResetPasswordAction $action
     * @return JsonResponse
     */
    public function resetPassword(ResetPasswordRequest $request, ResetPasswordAction $action): JsonResponse
    {
        $result = $action->execute($request->validated());

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
                'data' => null,
                'errors' => [
                    'email' => [$result['message']],
                ],
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Verify email
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyEmail(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|string',
            'hash' => 'required|string',
        ]);

        $user = User::findOrFail($request->id);

        if (!hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid verification link',
                'data' => null,
                'errors' => [
                    'verification' => ['The verification link is invalid.'],
                ],
            ], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified',
                'data' => null,
                'errors' => [
                    'verification' => ['Email is already verified.'],
                ],
            ], 400);
        }

        $user->markEmailAsVerified();

        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Resend verification email
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified',
                'data' => null,
                'errors' => [
                    'verification' => ['Email is already verified.'],
                ],
            ], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'success' => true,
            'message' => 'Verification email sent',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Revoke all tokens (logout from all devices)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function revokeAllTokens(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out from all devices',
            'data' => null,
            'errors' => null,
        ]);
    }

    /**
     * Check if user is authenticated
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function checkAuth(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'User is authenticated',
            'data' => [
                'authenticated' => $request->user() !== null,
                'user' => $request->user() ? new UserResource($request->user()) : null,
            ],
            'errors' => null,
        ]);
    }
}