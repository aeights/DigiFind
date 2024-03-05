<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;

class RegisterController extends Controller
{
    private $tokenKey;
    private $refreshTokenKey;

    public function __construct()
    {
        $this->tokenKey = config('services.jwt.token_key');
        $this->refreshTokenKey = config('services.jwt.refresh_token_key');
    }

    public function register(RegisterRequest $registerRequest)
    {
        $validated = $registerRequest->validated();

        if ($validated) {
            $user = User::create([
                'nik' => $registerRequest->nik,
                'name' => $registerRequest->name,
                'email' => $registerRequest->email,
                'password' => $registerRequest->password,
                'phone' => $registerRequest->phone,
            ]);

            return response()->json([
                "status" => true,
                "message" => "User registered successfully"
            ]);
        }

        return response()->json([
            "status" => false,
            "message" => "error"
        ]);
    }
}
