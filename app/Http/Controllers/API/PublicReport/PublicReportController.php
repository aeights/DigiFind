<?php

namespace App\Http\Controllers\API\PublicReport;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\PublicComment;
use App\Models\PublicReport;
use App\Models\ReportedComment;
use App\Models\ReportedReport;
use App\Models\SavedReport;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class PublicReportController extends Controller
{
    private $tokenKey;
    private $refreshTokenKey;

    public function __construct()
    {
        $this->tokenKey = config('services.jwt.token_key');
        $this->refreshTokenKey = config('services.jwt.refresh_token_key');
    }

    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'limit' => 'required',
                'offset' => 'required'
            ]);
            if ($validated) {
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
                LIMIT ? OFFSET ?", [$request->limit, $request->offset]);
                return response()->json([
                    "status" => true,
                    "message" => "Get list public report is successful",
                    'data' => $reports
                ]);
                // a.id, g.name, h.url, i.name
            }
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
                "error" => $ex
            ]);
        }
    }

    public function show($id)
    {
        try {
            $report = DB::select("SELECT 
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
                WHERE a.id = ?
                GROUP BY
                    a.id, g.name, h.url, i.name", [$id]);
            if (count($report) > 0) {
                return response()->json([
                    "status" => true,
                    "message" => "Get public report is successful",
                    'data' => $report[0]
                ]);
            }
            return response()->json([
                "status" => false,
                "message" => "Public report not found",
            ]);
        } catch (\Exception $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ]);
        }
    }
    
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'public_category_id' => 'required|exists:public_categories,id',
                'public_sub_category_id' => 'required|exists:public_sub_categories,id',
                'title' => 'required',
                'date' => 'required|date',
                'village_code' => 'required',
                'location_detail' => 'required',
                'description' => 'required',
            ]);
            if ($validated) {
                DB::beginTransaction();
                $publicReport = PublicReport::create($validated);

                if ($request->hasFile('media')) {
                    $file = $request->file('media');
                    foreach ($file as $key => $value) {
                        $extension = $value->getClientOriginalExtension();
                        $fileName = time().'-'.$publicReport->id.'-'.$key.'.'.$extension;
                        $path = 'media/public-report';
                        $size = File::size($value);
                        Media::create(
                            [
                                'model_id' => $publicReport->id,
                                'media_type_id' => 3,
                                'file_name' => $fileName,
                                'path' => $path,
                                'url' => $path.'/'.$fileName,
                                'mime_type' => $extension,
                                'size' => $size,
                            ]
                        );
                        $value->move($path, $fileName);
                    }
                }
                DB::commit();
                return response()->json([
                    "status" => true,
                    "message" => 'Add public report is successful',
                ]);
            }
            return response()->json([
                "status" => false,
                "message" => "Validation error"
            ]);
        } catch (ValidationException $ex) {
            return response()->json([
                "status" => false,
                "message" => "Validation fails",
                "error" => $ex->errors(),
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ]);
        }
    }
    
    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'exists:users,id',
                'public_category_id' => 'exists:public_categories,id',
                'public_sub_category_id' => 'exists:public_sub_categories,id',
                // 'title' => 'required',
                // 'date' => 'required|date',
                // 'village_code' => 'required',
                // 'location_detail' => 'required',
                // 'description' => 'required',
            ]);
            if ($validated) {
                DB::beginTransaction();
                $publicReport = PublicReport::findOrFail($id)->update($request->all());
                DB::commit();
                return response()->json([
                    "status" => true,
                    "message" => 'Update public report is successful',
                ]);
            }
            return response()->json([
                "status" => false,
                "message" => "Validation error"
            ]);
        } catch (ValidationException $ex) {
            return response()->json([
                "status" => false,
                "message" => "Validation fails",
                "error" => $ex->errors(),
            ]);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ]);
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();
            $report = PublicReport::findOrFail($id)->delete();
            $media = Media::where('model_id',$id)->where('media_type_id',3);

            if (count($media->get()) > 0) {
                foreach ($media->get() as $key => $value) {
                    $file = public_path($value->url);
                    if (file_exists($file)) {
                        File::delete($value->url);
                    }
                }
            }
            $media->delete();

            DB::commit();
            return response()->json([
                "status" => true,
                "message" => "Delete public report is successful",
            ]);
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ]);
        }
    }

    public function userReports(Request $request)
    {
        try {
            $validated = $request->validate([
                'limit' => 'required',
                'offset' => 'required'
            ]);
            if ($validated) {
                $token = $request->bearerToken();
                $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));

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
                WHERE a.user_id = ?
                GROUP BY
                    a.id, g.name, h.url, i.name", [$decoded->id]);
                return response()->json([
                    "status" => true,
                    "message" => "Get user public report is successful",
                    "data" => $reports,
                ]);
            }
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
                "error" => $ex
            ]);
        }
    }

    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'keyword' => 'required'
            ]);
            if ($validated) {
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
                WHERE a.title LIKE ?
                GROUP BY
                    a.id, g.name, h.url, i.name",["%".$request->keyword."%"]);
                return response()->json([
                    "status" => true,
                    "message" => "Search public report is successful",
                    "data" => $reports
                ]);
            }
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

    public function save(Request $request, $id)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $report = PublicReport::findOrFail($id);
            if ($report) {
                SavedReport::create([
                    'report_id' => $report->id,
                    'report_type_id' => 1,
                    'user_id' => $decoded->id
                ]);
                return response()->json([
                    "status" => true,
                    "message" => "Save public report is successful",
                    "data" => $report
                ]);
            }
            return response()->json([
                "status" => true,
                "message" => "Public report not found",
            ],404);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ]);
        }
    }

    public function userSavedReports(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));

            $savedReports = DB::select("SELECT 
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
                JOIN
                    saved_reports j ON a.id = j.report_id AND j.report_type_id = 1
                WHERE j.user_id = ?
                GROUP BY
                    a.id, g.name, h.url, i.name",[$decoded->id]);

            return response()->json([
                "status" => true,
                "message" => "Get user saved public report is successful",
                "data" => $savedReports,
            ]);
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
                "error" => $ex
            ]);
        }
    }

    public function comment(Request $request)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $report = PublicReport::findOrFail($request->public_report_id);
            if ($report) {
                PublicComment::create([
                    'public_report_id' => $report->id,
                    'user_id' => $decoded->id,
                    'body' => $request->body
                ]);
                return response()->json([
                    "status" => true,
                    "message" => "Add comment public report is successful",
                    "data" => $report
                ]);
            }
            return response()->json([
                "status" => true,
                "message" => "Public report not found",
            ],404);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ]);
        }
    }

    public function report(Request $request)
    {
        try {
            $validated = $request->validate([
                'report_id' => 'required',
                'reason' => 'required'
            ]);
            if ($validated) {
                $token = $request->bearerToken();
                $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
                $report = PublicReport::findOrFail($request->report_id);

                if ($report) {
                    ReportedReport::create([
                        'user_id' => $decoded->id,
                        'report_id' => $report->id,
                        'report_type_id' => 1,
                        'reason' => $request->reason
                    ]);
                    return response()->json([
                        "status" => true,
                        "message" => "Report submitted",
                        "data" => $report
                    ]);
                }
                return response()->json([
                    "status" => true,
                    "message" => "Public report not found",
                ],404);
            }
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

    public function reportComment(Request $request)
    {
        try {
            $validated = $request->validate([
                'public_comment_id' => 'required|exists:public_comments,id',
                'reason' => 'required'
            ]);
            if ($validated) {
                $token = $request->bearerToken();
                $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));

                $comment = PublicComment::findOrFail($request->public_comment_id);
                if ($comment) {
                    ReportedComment::create([
                        'user_id' => $decoded->id,
                        'public_comment_id' => $request->public_comment_id,
                        'reason' => $request->reason
                    ]);
                    return response()->json([
                        "status" => true,
                        "message" => "Report comment submitted",
                        "data" => $comment
                    ]);
                }
            }
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

    public function categories()
    {
        try {
            $data = DB::select("SELECT 
                a.id AS category_id, 
                a.name AS category_name, 
                GROUP_CONCAT(b.id SEPARATOR ', ') AS sub_category_id,
                GROUP_CONCAT(b.name SEPARATOR ', ') AS sub_category_names
            FROM public_categories a 
            LEFT JOIN 
                public_sub_categories b 
            ON 
                a.id = b.public_category_id 
            GROUP BY a.id, a.name");
            if ($data) {
                return response()->json([
                    "status" => true,
                    "message" => "Showing all category list",
                    "data" => $data
                ]);
            }
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ]);
        }
    }

    public function getComments($id)
    {
        try {
            $data = DB::select("SELECT b.id AS user_id, b.name, c.url, a.id AS comment_id, a.body, a.created_at
                FROM public_comments a 
                LEFT JOIN users b ON a.user_id = b.id
                LEFT JOIN media c ON b.id = c.model_id AND c.media_type_id = 2
                LEFT JOIN public_reports d ON a.public_report_id = d.id
                WHERE d.id = ?
                ORDER BY a.created_at desc",[$id]);
            return response()->json([
                "status" => true,
                "message" => "Showing all comment",
                "data" => $data
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ]);
        }
    }

    public function relatedReport($id)
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
                WHERE a.public_category_id = ?
                GROUP BY
                    a.id, g.name, h.url, i.name",[$id]);
            return response()->json([
                "status" => true,
                "message" => "Get related reports successfully",
                "data" => $reports
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
