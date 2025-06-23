<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{

    public function login(string $phoneNumber, string $password, ?string $deviceName = 'mobile-device'): array
    {
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'phone_number' => ['رقم الهاتف أو كلمة المرور غير صحيحة.'],
            ]);
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }


    public function logout(User $user): bool
    {
        $user->currentAccessToken()->delete();
        return true;
    }

    public function logoutAllDevices(User $user): bool
    {
        $user->tokens()->delete();
        return true;
    }
}
