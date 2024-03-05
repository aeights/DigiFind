<?php

namespace App\Http\Controllers\API\Home;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $authorizationHeader = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $authorizationHeader);
        $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
        $user = User::find($decoded->id);
        return response()->json([
            "status" => true,
            "message" => "Get user profile successfully",
            "data" => $user,
        ]);

        // $authorizationHeader = $request->header('Authorization');
        // $token = str_replace('Bearer ', '', $authorizationHeader);
        // if ($authorizationHeader) {
        //     try {
        //         $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
        //         if ($decoded) {
        //             $userToken = DB::select('SELECT * FROM tokens WHERE token = ?', [$token]);
                    
        //             if ($userToken and $userToken[0]->expired > Carbon::now()) {
        //                 $user = User::find($decoded->id);
        //                 return response()->json([
        //                     "status" => true,
        //                     "message" => "Get user profile successfully",
        //                     "data" => $user,
        //                 ]);
        //             }
        //             return response()->json([
        //                 "status" => false,
        //                 "message" => "Token expired",
        //             ]);
        //         }
        //     } catch (ExpiredException $e) {
        //         return response()->json([
        //             "status" => false,
        //             "message" => "Token expired",
        //             "error" => $e
        //         ]);
        //     } catch (BeforeValidException $e) {
        //         return response()->json([
        //             "status" => false,
        //             "message" => "Token not yet valid",
        //             "error" => $e
        //         ]);
        //     } catch (\Exception $e) {
        //         return response()->json([
        //             "status" => false,
        //             "message" => "Token is invalid",
        //             "error" => $e
        //         ]);
        //     }
        // }
        // return response()->json([
        //     "status" => false,
        //     "message" => "Authorization header cannot be empty",
        // ]);
    }

    public function logout(Request $request)
    {
        $authorizationHeader = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $authorizationHeader);
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

        // $authorizationHeader = $request->header('Authorization');
        // $token = str_replace('Bearer ', '', $authorizationHeader);
        // if ($authorizationHeader) {
        //     try {
        //         $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
        //         if ($decoded) {
        //             $userToken = Token::where('token',$token)->first();
        //             $userRefreshToken = RefreshToken::where('user_id',$decoded->id)->first();
                    
        //             if ($userToken and $userToken->expired > Carbon::now()) {
        //                 $userToken->delete();
        //                 $userRefreshToken->delete();
        //                 return response()->json([
        //                     "status" => true,
        //                     "message" => "User logout successfully",
        //                 ]);
        //             }
        //             return response()->json([
        //                 "status" => false,
        //                 "message" => "Token expired",
        //             ]);
        //         }
        //     } catch (ExpiredException $e) {
        //         return response()->json([
        //             "status" => false,
        //             "message" => "Token expired",
        //             "error" => $e
        //         ]);
        //     } catch (BeforeValidException $e) {
        //         return response()->json([
        //             "status" => false,
        //             "message" => "Token not yet valid",
        //             "error" => $e
        //         ]);
        //     } catch (\Exception $e) {
        //         return response()->json([
        //             "status" => false,
        //             "message" => "Token is invalid",
        //             "error" => $e
        //         ]);
        //     }
        // }
        // return response()->json([
        //     "status" => false,
        //     "message" => "Authorization header cannot be empty",
        // ]);
    }

    public function updateProfile()
    {
        
    }
}
