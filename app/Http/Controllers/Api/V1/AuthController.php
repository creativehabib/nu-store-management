<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Controllers\Controller;
use App\Models\ApiUserToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request, CreateNewUser $createNewUser): JsonResponse
    {
        $user = $createNewUser->create($request->all());

        return response()->json([
            'message' => 'Registration successful. Please wait for admin approval before logging in.',
            'data' => [
                'user' => $this->userPayload($user->load(['department', 'designation'])),
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::query()
            ->with(['department', 'designation'])
            ->where('email', $credentials['login'])
            ->orWhere('pf_no', $credentials['login'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => __('The provided credentials are incorrect.'),
            ]);
        }

        if (! $user->is_approved) {
            return response()->json([
                'message' => 'Your account is pending admin approval.',
            ], 403);
        }

        $plainTextToken = Str::random(80);

        ApiUserToken::query()->create([
            'user_id' => $user->id,
            'name' => $credentials['device_name'] ?? 'mobile-app',
            'token_hash' => hash('sha256', $plainTextToken),
        ]);

        return response()->json([
            'message' => 'Login successful.',
            'data' => [
                'token_type' => 'Bearer',
                'token' => $plainTextToken,
                'user' => $this->userPayload($user),
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        return response()->json([
            'data' => [
                'user' => $this->userPayload($user->loadMissing(['department', 'designation'])),
            ],
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)->ignore($user->id)],
            'mobile_no' => ['required', 'string', 'max:20', Rule::unique(User::class, 'mobile_no')->ignore($user->id)],
            'department_id' => ['required', 'integer', Rule::exists('departments', 'id')],
            'designation_id' => ['required', 'integer', Rule::exists('designations', 'id')],
            'picture' => ['nullable', 'image', 'max:2048'],
            'current_password' => ['required_with:password', 'string'],
            'password' => ['nullable', 'string', Password::default(), 'confirmed'],
        ]);

        if (filled($validated['password'] ?? null) && ! Hash::check((string) $validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The provided password does not match your current password.'),
            ]);
        }

        $profileData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'mobile_no' => $validated['mobile_no'],
            'department_id' => $validated['department_id'],
            'designation_id' => $validated['designation_id'],
        ];

        if ($request->hasFile('picture')) {
            if ($user->picture) {
                Storage::disk('public')->delete($user->picture);
            }

            $profileData['picture'] = $request->file('picture')->store('profile-images', 'public');
        }

        if (filled($validated['password'] ?? null)) {
            $profileData['password'] = $validated['password'];
        }

        $user->update($profileData);

        return response()->json([
            'message' => 'Profile updated successfully.',
            'data' => [
                'user' => $this->userPayload($user->refresh()->load(['department', 'designation'])),
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $apiUserToken = $request->attributes->get('api_user_token');

        if ($apiUserToken instanceof ApiUserToken) {
            $apiUserToken->delete();
        }

        return response()->json([
            'message' => 'Logout successful.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function userPayload(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'pf_no' => $user->pf_no,
            'mobile_no' => $user->mobile_no,
            'picture' => $user->picture,
            'picture_url' => $user->picture ? url(Storage::disk('public')->url($user->picture)) : null,
            'role' => $user->role,
            'is_approved' => $user->is_approved,
            'department' => $user->department,
            'designation' => $user->designation,
        ];
    }
}
