<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Models\ReportedUser;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    private $tokenKey;
    private $refreshTokenKey;

    public function __construct()
    {
        $this->tokenKey = config('services.jwt.token_key');
        $this->refreshTokenKey = config('services.jwt.refresh_token_key');
    }

    public function report(Request $request)
    {
        try {
            $validated = $request->validate([
                'reported_user_id' => 'required|exists:users,id',
                'reason' => 'required'
            ]);

            if ($validated) {
                $token = $request->bearerToken();
                $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));

                $report = ReportedUser::create([
                    'user_id' => $decoded->id,
                    'reported_user_id' => $request->reported_user_id,
                    'reason' => $request->reason
                ]);

                return response()->json([
                    "status" => true,
                    "message" => "Report user is successful",
                ]);
            }

            return response()->json([
                "status" => true,
                "message" => "User not found",
            ],404);
        } catch (ValidationException $ex) {
            return response()->json([
                "status" => false,
                "message" => "Validation fails",
                "error" => $ex->errors(),
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ]);
        }
    }
}
