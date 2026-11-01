<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Api\ApiController;
use App\Http\Requests\Api\Auth\GoogleLoginRequest;
use App\Http\Requests\Api\Auth\MobileLoginRequest;
use App\Models\User;
use App\Services\GoogleIdTokenVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends ApiController
{
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'username' => Str::slug($request->first_name . $request->last_name) . '_' . uniqid(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'email_verified' => (bool) $user->hasVerifiedEmail(),
                'phone_verified' => ! is_null($user->phone_verified_at),
                'phone_number' => $user->phone_number,
            ],
        ], 201);
    }

    /**
     * Login user and create token.
     *
     * @tags Authentication
     */
    public function login(MobileLoginRequest $request): JsonResponse
    {
        // The identifier is an email address or a username.
        $identifier = $request->identifier();
        $user = User::where(
            filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username',
            $identifier
        )->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        return $this->tokenResponse($user);
    }

    /**
     * Sign in (or sign up) with a Google ID token from the mobile app.
     *
     * @tags Authentication
     */
    public function google(GoogleLoginRequest $request, GoogleIdTokenVerifier $verifier): JsonResponse
    {
        $identity = $verifier->verify($request->string('id_token')->toString());

        if (! $identity) {
            return response()->json([
                'message' => 'Invalid Google token',
            ], 401);
        }

        // Google has verified this email address, so an existing account with
        // the same email is the same person (same rule as the web Google login).
        $user = User::where('email', $identity['email'])->first()
            ?? $this->createGoogleUser($identity);

        if (! $user->hasVerifiedEmail()) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        return $this->tokenResponse($user);
    }

    /**
     * @param  array{email: string, name: ?string, given_name: ?string, family_name: ?string}  $identity
     */
    private function createGoogleUser(array $identity): User
    {
        $fullName = $identity['name'] ?: explode('@', $identity['email'])[0];
        $firstName = $identity['given_name'] ?: explode(' ', $fullName, 2)[0];
        $lastName = $identity['family_name'] ?? (explode(' ', $fullName, 2)[1] ?? '');

        $baseUsername = Str::slug($fullName) ?: 'user';
        $username = $baseUsername;
        $count = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername.$count++;
        }

        $user = User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $identity['email'],
            'username' => $username,
            'password' => Hash::make(Str::random(32)),
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    private function tokenResponse(User $user): JsonResponse
    {
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'full_name' => $user->full_name,
                'email' => $user->email,
                'email_verified' => (bool) $user->hasVerifiedEmail(),
                'phone_verified' => ! is_null($user->phone_verified_at),
                'phone_number' => $user->phone_number,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token_type' => 'Bearer',
        ], 200);
    }

    /**
     * Get authenticated user.
     *
     * @tags Authentication
     */
    public function user(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'email_verified' => (bool) $user->hasVerifiedEmail(),
            'phone_verified' => ! is_null($user->phone_verified_at),
            'phone_number' => $user->phone_number,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ], 200);
    }

    /**
     * Logout user (revoke token).
     *
     * @tags Authentication
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }

    /**
     * Revoke all tokens for the authenticated user.
     *
     * @tags Authentication
     */
    public function revokeAll(Request $request): JsonResponse
    {
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'All tokens revoked successfully',
        ], 200);
    }

    /**
     * Refresh the current token — revoke it and issue a new one.
     * Mobile clients should call this before token expiry to stay authenticated.
     *
     * @tags Authentication
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();

        $user->currentAccessToken()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
        ], 200);
    }
}
