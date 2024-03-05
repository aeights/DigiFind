<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class ApiAuth
{
    private $tokenKey;
    private $refreshTokenKey;

    public function __construct()
    {
        $this->tokenKey = config('services.jwt.token_key');
        $this->refreshTokenKey = config('services.jwt.refresh_token_key');
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authorizationHeader = $request->header('Authorization');
        $token = str_replace('Bearer ', '', $authorizationHeader);
        if ($authorizationHeader) {
            try {
                $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
                if ($decoded) {
                    $userToken = DB::select('SELECT * FROM tokens WHERE token = ?', [$token]);
                    if ($userToken and $userToken[0]->expired > Carbon::now()) {
                        return $next($request);
                    }
                    return response()->json([
                        "status" => false,
                        "message" => "Token not found or token expired",
                    ]);
                }
            } catch (ExpiredException $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token expired",
                    "error" => $e
                ]);
            } catch (BeforeValidException $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token not yet valid",
                    "error" => $e
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token is invalid",
                    "error" => $e
                ]);
            }
        }
        return response()->json([
            "status" => false,
            "message" => "Authorization header cannot be empty",
        ]);
    }
}
