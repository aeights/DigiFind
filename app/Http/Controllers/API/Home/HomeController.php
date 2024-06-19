<?php

namespace App\Http\Controllers\API\Home;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function trendPublicReport()
    {
        try {
            $reports = DB::select("SELECT 
                a.*,
                i.name,
                h.url AS user_url,
                g.name AS category,
                GROUP_CONCAT(b.url SEPARATOR ', ') AS url,
                CONCAT(d.name, ', ', c.name, ', ', e.name, ', ', f.name) AS address
            FROM
                public_reports a
            LEFT JOIN
                media b ON a.id = b.model_id AND b.media_type_id = 3
            LEFT JOIN
                media h ON a.user_id = h.model_id AND h.media_type_id = 2
            LEFT JOIN
                users i ON a.user_id = i.id
            LEFT JOIN
                public_sub_categories g ON a.public_sub_category_id = g.id
            LEFT JOIN
                villages d ON a.village_code = d.village_code
            LEFT JOIN
                districts c ON d.district_code = c.district_code
            LEFT JOIN
                cities e ON c.city_code = e.city_code
            LEFT JOIN
                provinces f ON e.province_code = f.province_code
            GROUP BY
                a.id, g.name, h.url, i.name
            ORDER BY 
                RAND()
            LIMIT 3");
            return response()->json([
                "status" => true,
                "message" => "Get list public report is successful",
                'data' => $reports
            ]);
        }
        catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ]);
        }
    }
}
