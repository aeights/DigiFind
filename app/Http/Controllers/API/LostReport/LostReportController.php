<?php

namespace App\Http\Controllers\API\LostReport;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\DiscoveredItem;
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
                    WHERE
                        j.transaction_status_id = 2
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
            ],400);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ],500);
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
                'data' => $report[0]
            ]);
        } catch (\Exception $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ],500);
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
            ],400);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ],500);
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
            ],400);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ],500);
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
            ],500);
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
            ],500);
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
            ],500);
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
            ],500);
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
            ],400);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ],500);
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
            ],400);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ],500);
        }
    }

    public function searchAndFilter(Request $request)
    {
        try {
            $validated = $request->validate([
                'keyword' => 'required',
                'village_code' => 'nullable|string',
                'city_code' => 'nullable|string',
                'district_code' => 'nullable|string',
                'province_code' => 'nullable|string',
                'category_id' => 'nullable|string',
                'sort' => 'nullable|string|in:newest,oldest,a-z,z-a'
            ]);

            if ($validated) {
                $query = "
                    SELECT 
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
                ";

                $params = ["%".$request->keyword."%"];

                if ($request->has('village_code')) {
                    $query .= " AND d.village_code = ?";
                    $params[] = $request->village_code;
                }

                if ($request->has('city_code')) {
                    $query .= " AND e.city_code = ?";
                    $params[] = $request->city_code;
                }

                if ($request->has('district_code')) {
                    $query .= " AND c.district_code = ?";
                    $params[] = $request->district_code;
                }

                if ($request->has('province_code')) {
                    $query .= " AND f.province_code = ?";
                    $params[] = $request->province_code;
                }

                if ($request->has('category_id')) {
                    $query .= " AND g.id = ?";
                    $params[] = $request->category_id;
                }

                $query .= "
                    GROUP BY
                        a.id, g.name, h.url, i.name, j.reward, j.expired, k.duration
                ";

                if ($request->has('sort')) {
                    switch ($request->sort) {
                        case 'newest':
                            $query .= " ORDER BY a.created_at DESC";
                            break;
                        case 'oldest':
                            $query .= " ORDER BY a.created_at ASC";
                            break;
                        case 'a-z':
                            $query .= " ORDER BY a.name ASC";
                            break;
                        case 'z-a':
                            $query .= " ORDER BY a.name DESC";
                            break;
                    }
                }

                $reports = DB::select($query, $params);

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
            ],400);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ],500);
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
            ],500);
        }
    }

    public function addDiscoveredItem(Request $request)
    {
        try {
            $validated = $request->validate([
                'lost_report_id' => 'required|exists:lost_reports,id',
                'discoverer_id' => 'required|exists:users,id',
                'description' => 'required',
                'discovered_date' => 'required|date',
                'village_code' => 'required|exists:villages,village_code',
                'location_detail' => 'required',
            ]);
            if ($validated) {
                DB::beginTransaction();
                $item = DiscoveredItem::create($validated);
                if ($request->hasFile('media')) {
                    $file = $request->file('media');
                    foreach ($file as $key => $value) {
                        $extension = $value->getClientOriginalExtension();
                        $fileName = time().'-'.$item->id.'-'.$key.'.'.$extension;
                        $path = 'media/discovered-item';
                        $size = File::size($value);
                        Media::create(
                            [
                                'model_id' => $item->id,
                                'media_type_id' => 5,
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
                    "message" => "Item successfully added",
                ]);
            }
        } catch (ValidationException $ex) {
            return response()->json([
                "status" => false,
                "message" => "Validation fails",
                "error" => $ex->errors(),
            ],400);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ],500);
        }
    }

    public function getDiscoverer($id)
    {
        try {
            $data = DB::select("SELECT
                a.*,
                b.name AS user_name,
                c.url AS user_url,
                GROUP_CONCAT(d.url SEPARATOR ', ') AS url
            FROM
                discovered_items a
            LEFT JOIN
                users b ON a.discoverer_id = b.id
            LEFT JOIN
                media c ON b.id = c.model_id AND c.media_type_id = 2
            LEFT JOIN
                media d ON a.id = d.model_id AND d.media_type_id = 5
            WHERE
                a.lost_report_id = ?
            GROUP BY
                a.id, b.name, c.url",[$id]);
            return response()->json([
                "status" => true,
                "message" => "Get related reports successfully",
                "data" => $data
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ],500);
        }
    }

    public function sendChat(Request $request)
    {
        try {
            $validated = $request->validate([
                'discovered_item_id' => 'required|exists:discovered_items,id',
                'sender_id' => 'required|exists:users,id',
                'receiver_id' => 'required|exists:users,id',
                'body' => 'required',
            ]);
            if ($validated) {
                $chat = Chat::create($validated);
                return response()->json([
                    "status" => true,
                    "message" => "Message sent!",
                    "data" => $chat
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

    public function getChat($id)
    {
        try {
            $chat = DB::select("SELECT
                a.*
            FROM
                chats a
            LEFT JOIN
                discovered_items b ON a.discovered_item_id = b.id
            WHERE
                b.id = ?",[$id]);
            return response()->json([
                "status" => true,
                "message" => "Get messages successful",
                "data" => $chat
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex,
            ],500);
        }
    }
}
