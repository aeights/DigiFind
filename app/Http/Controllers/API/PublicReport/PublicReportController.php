<?php

namespace App\Http\Controllers\API\PublicReport;

use App\Http\Controllers\Controller;
use App\Models\PublicReport;
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
            $reports = PublicReport::orderBy('created_at','desc')->offset($request->offset)->limit($request->limit)->get();
            return response()->json([
                "status" => true,
                "message" => "Get list public report is successful",
                'data' => $reports
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
            return response()->json([
                "status" => true,
                "message" => $request->file(),
            ]);
            if ($validated) {
                DB::beginTransaction();
                $publicReport = PublicReport::create($validated);
                if ($request->hasFile('media')) {
                    $publicReport->addMediaFromRequest($request->file('media'))->toMediaCollection('public_report');
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
            $token = $request->bearerToken();
            $decoded = JWT::decode($token, new Key($this->tokenKey, 'HS256'));
            $reports = PublicReport::where('user_id',$decoded->id)->orderBy('created_at','desc')->offset($request->offset)->limit($request->limit)->get();
            return response()->json([
                "status" => true,
                "message" => "Get user public report is successful",
                'data' => $reports
            ]);
        } catch (\Exception $ex) {
            return response()->json([
                "status" => false,
                "message" => $ex->getMessage(),
                "error" => $ex
            ]);
        }
    }
}
