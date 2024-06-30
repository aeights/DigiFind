<?php

namespace App\Http\Controllers\API\LostReport;

use App\Http\Controllers\Controller;
use App\Models\RewardRecipient;
use App\Models\Transaction;
use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
                i.name AS user_name,
                h.url AS user_url,
                g.name AS category,
                GROUP_CONCAT(b.url SEPARATOR ', ') AS url,
                CONCAT(d.name, ', ', c.name, ', ', e.name, ', ', f.name) AS address,
                j.reward,
                j.expired,
                k.duration
            FROM
                transactions j
            LEFT JOIN
                lost_reports a ON a.id = j.lost_report_id
            LEFT JOIN
                media b ON a.id = b.model_id AND b.media_type_id = 4
            LEFT JOIN
                media h ON a.user_id = h.model_id AND h.media_type_id = 2
            LEFT JOIN
                users i ON a.user_id = i.id
            LEFT JOIN
                lost_categories g ON a.lost_category_id = g.id
            LEFT JOIN
                publication_packages k ON j.publication_package_id = k.id
            LEFT JOIN
                villages d ON a.village_code = d.village_code
            LEFT JOIN
                districts c ON d.district_code = c.district_code
            LEFT JOIN
                cities e ON c.city_code = e.city_code
            LEFT JOIN
                provinces f ON e.province_code = f.province_code
            WHERE
                j.user_id = ?
            AND
                j.transaction_status_id = ?
            GROUP BY
                a.id, g.name, h.url, i.name, j.reward, j.expired, k.duration", [$decoded->id,$id]);
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
            ],500);
        }
    }

    public function savedReports(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $reports = DB::select("SELECT 
                a.*,
                i.name AS user_name,
                h.url AS user_url,
                g.name AS category,
                GROUP_CONCAT(b.url SEPARATOR ', ') AS url,
                CONCAT(d.name, ', ', c.name, ', ', e.name, ', ', f.name) AS address,
                j.reward,
                j.expired,
                k.duration
            FROM
                transactions j
            LEFT JOIN
                lost_reports a ON a.id = j.lost_report_id
            LEFT JOIN
                media b ON a.id = b.model_id AND b.media_type_id = 4
            LEFT JOIN
                media h ON a.user_id = h.model_id AND h.media_type_id = 2
            LEFT JOIN
                users i ON a.user_id = i.id
            LEFT JOIN
                lost_categories g ON a.lost_category_id = g.id
            LEFT JOIN
                publication_packages k ON j.publication_package_id = k.id
            LEFT JOIN
                villages d ON a.village_code = d.village_code
            LEFT JOIN
                districts c ON d.district_code = c.district_code
            LEFT JOIN
                cities e ON c.city_code = e.city_code
            LEFT JOIN
                provinces f ON e.province_code = f.province_code
            LEFT JOIN
                saved_reports l ON a.id = l.report_id AND l.report_type_id = 2
            WHERE
                l.user_id = ?
            GROUP BY
                a.id, g.name, h.url, i.name, j.reward, j.expired, k.duration", [$decoded->id]);
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
            ],500);
        }
    }

    public function myHelp(Request $request, $id)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $data = DB::select("SELECT 
                a.*,
                i.name AS user_name,
                h.url AS user_url,
                g.name AS category,
                GROUP_CONCAT(b.url SEPARATOR ', ') AS url,
                CONCAT(d.name, ', ', c.name, ', ', e.name, ', ', f.name) AS address,
                j.reward,
                j.expired,
                k.duration,
                l.id AS discovered_item_id,
                l.discoverer_id
            FROM
                transactions j
            LEFT JOIN
                lost_reports a ON a.id = j.lost_report_id
            LEFT JOIN
                media b ON a.id = b.model_id AND b.media_type_id = 4
            LEFT JOIN
                media h ON a.user_id = h.model_id AND h.media_type_id = 2
            LEFT JOIN
                users i ON a.user_id = i.id
            LEFT JOIN
                lost_categories g ON a.lost_category_id = g.id
            LEFT JOIN
                publication_packages k ON j.publication_package_id = k.id
            LEFT JOIN
                villages d ON a.village_code = d.village_code
            LEFT JOIN
                districts c ON d.district_code = c.district_code
            LEFT JOIN
                cities e ON c.city_code = e.city_code
            LEFT JOIN
                provinces f ON e.province_code = f.province_code
            LEFT JOIN
                discovered_items l ON a.id = l.lost_report_id
            WHERE
                l.discoverer_id = ?
            AND
                j.transaction_status_id = ?
            GROUP BY
                a.id, g.name, h.url, i.name, j.reward, j.expired, k.duration, l.id, l.discoverer_id",[$decoded->id, $id]);
            
            return response()->json([
                "status" => true,
                "message" => "Get lost report is successful",
                'data' => $data
            ]);
        } catch (\Exception $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ],500);
        }
    }

    public function completeReport($id)
    {
        try {
            $transaction = Transaction::findOrFail($id)->update([
                'transaction_status_id' => 4
            ]);
            return response()->json([
                "status" => true,
                "message" => "Report completed"
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ],500);
        }
    }

    public function chooseDiscoverer(Request $request)
    {
        try {
            $validated = $request->validate([
                'discovered_item_id' => 'required|exists:discovered_items,id',
                'reward' => 'required|numeric',
            ]);
            if ($validated) {
                $reward = RewardRecipient::create($validated);
                return response()->json([
                    "status" => true,
                    "message" => "Reward gifted"
                ]);
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
                "error" => $ex,
            ],500);
        }
    }
}
