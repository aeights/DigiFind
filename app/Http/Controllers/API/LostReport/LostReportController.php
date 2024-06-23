<?php

namespace App\Http\Controllers\API\LostReport;

use App\Http\Controllers\Controller;
use App\Models\LostCategory;
use App\Models\LostReport;
use App\Models\Media;
use App\Models\PublicationPackage;
use App\Models\ReportedReport;
use App\Models\SavedReport;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class LostReportController extends Controller
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
                    GROUP BY
                        a.id, g.name, h.url, i.name, j.reward, j.expired, k.duration
                    LIMIT ? OFFSET ?", [$request->limit, $request->offset]);
                return response()->json([
                    "status" => true,
                    "message" => "Get list lost report is successful",
                    'data' => $reports
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

    public function show($id)
    {
        try {
            $report = DB::select("SELECT 
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
                a.id = ?
            GROUP BY
            a.id, g.name, h.url, i.name, j.reward, j.expired, k.duration", [$id]);
            return response()->json([
                "status" => true,
                "message" => "Get lost report is successful",
                'data' => $report
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
                'lost_category_id' => 'required|exists:lost_categories,id',
                'name' => 'required',
                'unique_number' => 'nullable',
                'description' => 'required|max:1000',
                'date' => 'required|date',
                'village_code' => 'required',
                'location_detail' => 'required',
            ]);
            if ($validated) {
                DB::beginTransaction();
                $lostReport = LostReport::create($validated);

                if ($request->hasFile('media')) {
                    $file = $request->file('media');
                    foreach ($file as $key => $value) {
                        $extension = $value->getClientOriginalExtension();
                        $fileName = time().'-'.$lostReport->id.'-'.$key.'.'.$extension;
                        $path = 'media/lost-report';
                        $size = File::size($value);
                        Media::create(
                            [
                                'model_id' => $lostReport->id,
                                'media_type_id' => 4,
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
                    "message" => 'Add lost report is successful',
                    "id" => $lostReport->id
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
                'user_id' => 'required|exists:users,id',
                'lost_category_id' => 'required|exists:lost_categories,id',
                'name' => 'required',
                'unique_number' => 'nullable',
                'description' => 'required|max:1000',
                'date' => 'required|date',
                'village_code' => 'required',
                'location_detail' => 'required',
            ]);
            if ($validated) {
                $lostReport = LostReport::findOrFail($id)->update($validated);
                return response()->json([
                    "status" => true,
                    "message" => 'Update lost report is successful',
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
            $report = LostReport::findOrFail($id)->delete();
            $media = Media::where('model_id',$id)->where('media_type_id',4);

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
                "message" => "Delete lost report is successful",
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

    public function categories()
    {
        try {
            $data = LostCategory::all();
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

    public function reportSummary()
    {

    }

    public function publicationPackage()
    {
        try {
            $data = PublicationPackage::all();
            if ($data) {
                return response()->json([
                    "status" => true,
                    "message" => "Showing all package list",
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

    public function save(Request $request, $id)
    {
        try {
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $report = LostReport::findOrFail($id);
            if ($report) {
                SavedReport::create([
                    'report_id' => $report->id,
                    'report_type_id' => 2,
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
                $report = LostReport::findOrFail($request->report_id);

                if ($report) {
                    ReportedReport::create([
                        'user_id' => $decoded->id,
                        'report_id' => $report->id,
                        'report_type_id' => 2,
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
                    "message" => "Lost report not found",
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

    public function search(Request $request)
    {
        try {
            $validated = $request->validate([
                'keyword' => 'required'
            ]);
            if ($validated) {
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
                    a.name LIKE ?
                GROUP BY
                    a.id, g.name, h.url, i.name, j.reward, j.expired, k.duration",["%".$request->keyword."%"]);
                return response()->json([
                    "status" => true,
                    "message" => "Search lost report is successful",
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

    public function relatedReport($id)
    {
        try {
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
            WHERE g.id = ?
            GROUP BY
                a.id, g.name, h.url, i.name, j.reward, j.expired, k.duration",[$id]);
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
