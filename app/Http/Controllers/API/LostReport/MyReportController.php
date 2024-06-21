<?php

namespace App\Http\Controllers\API\LostReport;

use App\Http\Controllers\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyReportController extends Controller
{
    private $tokenKey;
    private $refreshTokenKey;

    public function __construct()
    {
        $this->tokenKey = config('services.jwt.token_key');
        $this->refreshTokenKey = config('services.jwt.refresh_token_key');
    }

    public function getByStatus(Request $request,$id)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $reports = DB::select("SELECT 
                a.*,
                i.name,
                h.url AS user_url,
                g.name AS category,
                GROUP_CONCAT(b.url SEPARATOR ', ') AS url,
                CONCAT(d.name, ', ', c.name, ', ', e.name, ', ', f.name) AS address,
                j.reward,
                j.expired
            FROM
                lost_reports a
            LEFT JOIN
                media b ON a.id = b.model_id AND b.media_type_id = 4
            LEFT JOIN
                media h ON a.user_id = h.model_id AND h.media_type_id = 2
            LEFT JOIN
                users i ON a.user_id = i.id
            LEFT JOIN
                lost_categories g ON a.lost_category_id = g.id
            LEFT JOIN
                villages d ON a.village_code = d.village_code
            LEFT JOIN
                districts c ON d.district_code = c.district_code
            LEFT JOIN
                cities e ON c.city_code = e.city_code
            LEFT JOIN
                provinces f ON e.province_code = f.province_code
            LEFT JOIN
                transactions j ON a.id = j.lost_report_id
            WHERE
                j.user_id = ?
            AND
                j.transaction_status_id = ?
            GROUP BY
            a.id, g.name, h.url, i.name, j.reward, j.expired", [$decoded->id,$id]);
            return response()->json([
                "status" => true,
                "message" => "Get lost report is successful",
                'data' => $reports
            ]);
        } catch (\Exception $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ]);
        }
    }

    public function savedReports(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $reports = DB::select("SELECT 
                a.*,
                i.name,
                h.url AS user_url,
                g.name AS category,
                GROUP_CONCAT(b.url SEPARATOR ', ') AS url,
                CONCAT(d.name, ', ', c.name, ', ', e.name, ', ', f.name) AS address,
                j.reward,
                j.expired
            FROM
                lost_reports a
            LEFT JOIN
                media b ON a.id = b.model_id AND b.media_type_id = 4
            LEFT JOIN
                media h ON a.user_id = h.model_id AND h.media_type_id = 2
            LEFT JOIN
                users i ON a.user_id = i.id
            LEFT JOIN
                lost_categories g ON a.lost_category_id = g.id
            LEFT JOIN
                villages d ON a.village_code = d.village_code
            LEFT JOIN
                districts c ON d.district_code = c.district_code
            LEFT JOIN
                cities e ON c.city_code = e.city_code
            LEFT JOIN
                provinces f ON e.province_code = f.province_code
            LEFT JOIN
                transactions j ON a.id = j.lost_report_id
            JOIN
                saved_reports k ON a.id = k.report_id AND k.report_type_id = 2
            WHERE
                k.user_id = ?
            GROUP BY
            a.id, g.name, h.url, i.name, j.reward, j.expired", [$decoded->id]);
            return response()->json([
                "status" => true,
                "message" => "Get lost report is successful",
                'data' => $reports
            ]);
        } catch (\Exception $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ]);
        }
    }
}
