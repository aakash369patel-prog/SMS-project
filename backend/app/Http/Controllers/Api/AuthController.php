<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'admin',
        ]);

        $token = $user->createToken('school-management')->plainTextToken;

        return response()->json([
            'user' => $this->presentUser($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('school-management')->plainTextToken;

        return response()->json([
            'user' => $this->presentUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'user' => $this->presentUser($request->user()),
        ]);
    }

    private function presentUser(User $user): array
    {
        $user->load(['parentProfile', 'teacherProfile']);

        return [
            ...$user->toArray(),
            'permissions' => Permission::allows($user, '*') ? ['*'] : Permission::abilitiesFor($user),
        ];
    }
}
