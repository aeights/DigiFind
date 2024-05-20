<?php

namespace App\Http\Controllers\API\PublicReport;

use App\Http\Controllers\Controller;
use App\Models\LostReport;
use App\Models\PublicCategory;
use App\Models\PublicComment;
use App\Models\PublicReport;
use App\Models\ReportedComment;
use App\Models\ReportedReport;
use App\Models\SavedPublicReport;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                $reports = PublicReport::select()->orderBy('created_at','desc')->offset($request->offset)->limit($request->limit)->get();
                foreach ($reports as $key => $value) {
                    $value->getMedia('public_report');
                }
                return response()->json([
                    "status" => true,
                    "message" => "Get list public report is successful",
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
            $report = PublicReport::findOrFail($id);
            $report->getMedia('public_report');
            return response()->json([
                "status" => true,
                "message" => "Get public report is successful",
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
                'public_category_id' => 'required|exists:public_categories,id',
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
                    foreach ($request->file('media') as $key => $value) {
                        $publicReport->addMedia($value)->toMediaCollection('public_report');
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
                'user_id' => 'required|exists:users,id',
                'public_category_id' => 'required|exists:public_categories,id',
                'title' => 'required',
                'date' => 'required|date',
                'location' => 'required',
                'description' => 'required',
            ]);
            if ($validated) {
                $publicReport = PublicReport::findOrFail($id)->update($validated);
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
            $report = PublicReport::findOrFail($id)->delete();
            return response()->json([
                "status" => true,
                "message" => "Delete public report is successful",
            ]);
        } catch (\Exception $th) {
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
                $reports = PublicReport::where('user_id',$decoded->id)->orderBy('created_at','desc')->offset($request->offset)->limit($request->limit)->get();
                foreach ($reports as $key => $value) {
                    $value->getMedia('public_report');
                }
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
                $reports = PublicReport::where('title','like',"%$request->keyword%")->get();
                foreach ($reports as $key => $value) {
                    $value->getMedia('public_report');
                }
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
                SavedPublicReport::create([
                    'public_report_id' => $report->id,
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
            $savedReports = PublicReport::join('saved_public_reports', 'public_reports.id', '=', 'saved_public_reports.public_report_id')
                ->where('saved_public_reports.user_id', $decoded->id)
                ->select('public_reports.*')
                ->get();
            // $savedReports = DB::select("SELECT a.* FROM public_reports a JOIN saved_public_reports b ON a.id = b.public_report_id WHERE b.user_id = ?",[$decoded->id]);
            foreach ($savedReports as $value) {
                $value->getMedia('public_report');
            }

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
                'report_type_id' => 'required|exists:report_types,id',
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
                        'report_type_id' => $request->report_type_id,
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
                'comment_id' => 'required|exists:public_comments,id',
                'reason' => 'required'
            ]);
            if ($validated) {
                $token = $request->bearerToken();
                $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));

                $comment = PublicComment::findOrFail($request->comment_id);
                if ($comment) {
                    ReportedComment::create([
                        'user_id' => $decoded->id,
                        'comment_id' => $request->comment_id,
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
            $data = PublicCategory::all();
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
}
