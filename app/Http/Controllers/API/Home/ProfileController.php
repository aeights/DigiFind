<?php

namespace App\Http\Controllers\API\Home;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    private $tokenKey;
    private $refreshTokenKey;

    public function __construct()
    {
        $this->tokenKey = config('services.jwt.token_key');
        $this->refreshTokenKey = config('services.jwt.refresh_token_key');
    }
    
    public function profile(Request $request)
    {
        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
        $user = User::find($decoded->id);
        return response()->json([
            "status" => true,
            "message" => "Get user profile successfully",
            "data" => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));

        $userToken = Token::where('token',$token)->first();
        $userRefreshToken = RefreshToken::where('user_id',$decoded->id)->first();
        
        if ($userToken and $userToken->expired > Carbon::now()) {
            $userToken->delete();
            $userRefreshToken->delete();
            return response()->json([
                "status" => true,
                "message" => "User logout successfully",
            ]);
        }
        return response()->json([
            "status" => false,
            "message" => "Token expired",
        ]);
    }

    public function updateProfile(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $validated = $request->validate([
                'name' => 'nullable',
                'gender' => 'nullable|in:male,female',
                'email' => 'nullable|email|unique:users,email,'.$decoded->id,
                'address' => 'nullable',
                'phone' => 'nullable|numeric|unique:users,phone,'.$decoded->id,
            ]);
            
            if ($validated) {
                User::find($decoded->id)->update($validated);
                return response()->json([
                    "status" => true,
                    "message" => 'Update profile is successful',
                ]);
            }
        } catch (ValidationException $ex) {
            return response()->json([
                "status" => false,
                "message" => "Validation fails",
                "error" => $ex->errors()
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
            ]);
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'new_password' => 'required|min:6',
                'confirm_password' => 'required|min:6',
            ]);

            if ($validated and $request->new_password == $request->confirm_password) {
                $token = $request->bearerToken();
                $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
                $user = User::find($decoded->id)->update([
                    'password' => Hash::make($request->new_password)
                ]);
                $userToken = Token::where('token',$token)->first();
                $userRefreshToken = RefreshToken::where('user_id',$decoded->id)->first();
                $userToken->delete();
                $userRefreshToken->delete();
                return response()->json([
                    "status" => true,
                    "message" => 'Change password is successful',
                ]);
            }
        } catch (ValidationException $ex) {
            return response()->json([
                "status" => false,
                "message" => "Validation fails",
                "error" => $ex->errors()
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
            ]);
        }
    }
}
