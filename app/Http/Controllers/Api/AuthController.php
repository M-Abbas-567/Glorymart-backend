<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'nullable|in:customer,vendor',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'], ['rounds' => 4]),
            'role' => $data['role'] ?? 'customer',
            'status' => 'active',
        ]);

        if ($user->role === 'vendor') {
            Vendor::create([
                'user_id' => $user->id,
                'store_name' => $user->name . ' Store',
                'rating' => 0,
                'is_approved' => false,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $this->serializeUser($user->fresh('vendor')),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        $user->load('vendor');
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $this->serializeUser($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out successfully.']);
    }

    public function me(Request $request)
    {
        return response()->json(
            $this->serializeUser($request->user()->load('vendor'))
        );
    }

    private function serializeUser(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'status' => $user->status,
            'vendorId' => $user->vendor?->id,
            'vendorApproved' => $user->vendor?->is_approved,
        ];
    }
}
