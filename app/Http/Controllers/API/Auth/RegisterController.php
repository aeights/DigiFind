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
        try {
            if ($validated) {
                $user = User::create([
                    'nik' => $registerRequest->nik,
                    'name' => $registerRequest->name,
                    'gender' => $registerRequest->gender,
                    'address' => $registerRequest->address,
                    'email' => $registerRequest->email,
                    'password' => $registerRequest->password,
                    'phone' => $registerRequest->phone,
                ]);
    
                return response()->json([
                    "status" => true,
                    "message" => "User registration is successful"
                ]);
            }
    
            return response()->json([
                "status" => false,
                "message" => "Validation error"
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ]);
        }
    }
}
