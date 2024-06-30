<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $token = $request->bearerToken();
        if ($token) {
            try {
                $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
                $time = Carbon::createFromTimestamp($decoded->exp);
                if ($decoded and $time > Carbon::now() ) {
                    return $next($request);
                }
                return response()->json([
                    "status" => false,
                    "message" => "Token invalid or token expired",
                ],403);
            } catch (ExpiredException $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token expired",
                    "error" => $e->getMessage()
                ],401);
            } catch (BeforeValidException $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token not yet valid",
                    "error" => $e->getMessage()
                ],401);
            } catch (\Exception $e) {
                return response()->json([
                    "status" => false,
                    "message" => "Token is invalid",
                    "error" => $e->getMessage()
                ],403);
            }
        }
        return response()->json([
            "status" => false,
            "message" => "Authorization header cannot be empty",
        ]);
    }
}
