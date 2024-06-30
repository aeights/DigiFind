<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\RefreshToken;
use App\Models\Token;
use App\Models\User;
use Carbon\Carbon;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private $tokenKey;
    private $refreshTokenKey;

    public function __construct()
    {
        $this->tokenKey = config('services.jwt.token_key');
        $this->refreshTokenKey = config('services.jwt.refresh_token_key');
    }

    public function login(LoginRequest $loginRequest)
    {
        try {
            $validated = $loginRequest->validated();
    
            if ($validated ) {
                $auth = Auth::attempt($validated);
                if ($auth) {
                    $user = Auth::user();
                    $payloadToken = [
                        'id' => $user->id,
                        'iat' => Carbon::now()->timestamp,
                        'exp' => Carbon::now()->addDay()->timestamp,
                    ];
    
                    $payloadRefreshToken = [
                        'id' => $user->id,
                        'iat' => Carbon::now()->timestamp,
                        'exp' => Carbon::now()->addMonth(3)->timestamp,
                    ];
    
                    $token = JWT::encode($payloadToken, $this->tokenKey, 'HS256');
                    $refreshToken = JWT::encode($payloadRefreshToken, $this->refreshTokenKey, 'HS256');
                    
                    // $userToken = Token::updateOrCreate(
                    //     [
                    //         'user_id' => $user->id
                    //     ],
                    //     [
                    //         'user_id' => $user->id,
                    //         'token' => $token,
                    //         'expired' => Carbon::now()->addDay()
                    //     ]
                    // );
                    
                    $userRefreshToken = RefreshToken::updateOrCreate(
                        [
                            'user_id' => $user->id
                        ],
                        [
                            'user_id' => $user->id,
                            'token' => $refreshToken,
                            'expired' => Carbon::now()->addMonth(3)
                        ]
                    );
                    // $userRefreshToken = RefreshToken::create(
                    //     [
                    //         'user_id' => $user->id,
                    //         'token' => $refreshToken,
                    //         'expired' => Carbon::now()->addMonth(3)
                    //     ]
                    // );
    
                    if(!empty($token)){
                        return response()->json([
                            "status" => true,
                            "message" => "User login successful",
                            "token" => $token,
                            "refresh_token" => $refreshToken,
                        ]);
                    }
                }
    
                return response()->json([
                    "status" => false,
                    "message" => "Invalid email or password",
                ],400);
            }
        } catch (ValidationException $ex) {
            return response()->json([
                "status" => false,
                "message" => "Validation fails",
                "error" => $ex->errors(),
            ],400);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ],500);
        }
    }

    public function generateNewToken(Request $request)
    {
        $token = $request->bearerToken();
        if ($token) {
            try {
                $decoded = JWT::decode($token, new Key($this->refreshTokenKey, 'HS256'));
                if ($decoded) {
                    $refreshToken = DB::select('SELECT * FROM refresh_tokens WHERE token = ?', [$token]);
                    
                    if ($refreshToken and $refreshToken[0]->expired > Carbon::now()) {
                        $payloadToken = [
                            'id' => $decoded->id,
                            'iat' => Carbon::now()->timestamp,
                            'exp' => Carbon::now()->addDay()->timestamp,
                        ];
        
                        $payloadRefreshToken = [
                            'id' => $decoded->id,
                            'iat' => Carbon::now()->timestamp,
                            'exp' => Carbon::now()->addMonth(3)->timestamp,
                        ];
        
                        $newToken = JWT::encode($payloadToken, $this->tokenKey, 'HS256');
                        $newRefreshToken = JWT::encode($payloadRefreshToken, $this->refreshTokenKey, 'HS256');
                        
                        // $userToken = Token::where('user_id',$decoded->id)->update(
                        //     [
                        //         'token' => $newToken,
                        //         'expired' => Carbon::now()->addDay()
                        //     ]
                        // );
                        
                        $userRefreshToken = RefreshToken::where('user_id',$decoded->id)->update(
                            [
                                'token' => $newRefreshToken,
                                'expired' => Carbon::now()->addMonth(3)
                            ]
                        );
                        return response()->json([
                            "status" => true,
                            "message" => "Generate new token successfully",
                            "token" => $newToken,
                            "refresh_token" => $newRefreshToken,
                        ]);
                    }
                    return response()->json([
                        "status" => false,
                        "message" => "Token expired",
                        "token" => $refreshToken[0]->expired
                    ]);
                }
            } catch (ExpiredException $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token expired",
                    "error" => $e
                ],401);
            } catch (BeforeValidException $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token not yet valid",
                    "error" => $e
                ],401);
            } catch (\Exception $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token is invalid",
                    "error" => $e
                ],401);
            }
        }
        return response()->json([
            "status" => false,
            "message" => "Authorization header cannot be empty",
        ]);
    }
}
