<?php

namespace App\Http\Controllers\API\PublicReport;

use App\Http\Controllers\Controller;
use App\Models\LostReport;
use App\Models\PublicComment;
use App\Models\PublicReport;
use App\Models\ReportedReport;
use App\Models\SavedPublicReport;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

use function Laravel\Prompts\select;

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
                $reports = PublicReport::orderBy('created_at','desc')->offset($request->offset)->limit($request->limit)->get();
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
                'location' => 'required',
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
            $report = PublicReport::findOrFail($id)->delete;
            return response()->json([
                "status" => true,
                "message" => "Delete public report is successful",
                "data" => $report
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
            // $savedId = DB::select("SELECT public_report_id FROM saved_public_reports WHERE user_id = ?",[$decoded->id]);
            // $reports = PublicReport::whereIn
            $savedReports = DB::select("SELECT a.*, m.file_name, m.model_id as model_id, m.collection_name FROM saved_public_reports a LEFT OUTER JOIN media m ON a.public_report_id = m.model_id WHERE a.user_id = ".$decoded->id);
            // $savedReports = DB::table('public_reports as a')
            //     ->join('saved_public_reports as b', 'a.id', '=', 'b.public_report_id')
            //     ->where('b.user_id', $decoded->id)
            //     ->get();
            // foreach ($savedReports as $key => $value) {
            //     $value->getMedia('public_report');
            // }
                
            $media = [];

            foreach ($savedReports as $value) {
                if(array_key_exists($value->public_report_id, $media)){
                    if($value->file_name != null){
                        array_push($media[$value->public_report_id], $value->file_name);
                    }
                }else{
                    if($value->file_name != null){
                        $media[$value->public_report_id] = [
                            $value->file_name,
                        ];
                    }else{
                        $media[$value->public_report_id] = [];
                    }
                }
            }


            return response()->json([
                "status" => true,
                "message" => "Get user saved public report is successful",
                "media" => $media,
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
                $report = null;

                if ($request->report_type_id == 1) {
                    $public_report = PublicReport::findOrFail($request->report_id);
                    $report = $public_report;
                } 
                if ($request->report_type_id == 2) {
                    $lost_report = LostReport::findOrFail($request->report_id);
                    $report = $lost_report;
                }
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
}
