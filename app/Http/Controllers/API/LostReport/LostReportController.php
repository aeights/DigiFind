<?php

namespace App\Http\Controllers\API\LostReport;

use App\Http\Controllers\Controller;
use App\Models\LostCategory;
use App\Models\LostReport;
use App\Models\PublicationPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
                $reports = DB::select("SELECT a.*, GROUP_CONCAT(b.url SEPARATOR ', ') AS url FROM lost_reports a JOIN media b ON a.id = b.model_id WHERE b.media_type_id = 4 GROUP BY a.id LIMIT ? OFFSET ?", [$request->limit, $request->offset]);
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
            $report = LostReport::findOrFail($id);
            $report->getMedia('lost_report');
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
                    foreach ($request->file('media') as $key => $value) {
                        $lostReport->addMedia($value)->toMediaCollection('lost_report');
                    }
                }
                DB::commit();
                return response()->json([
                    "status" => true,
                    "message" => 'Add lost report is successful',
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
            $report = LostReport::findOrFail($id)->delete();
            return response()->json([
                "status" => true,
                "message" => "Delete lost report is successful",
            ]);
        } catch (\Exception $th) {
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
}
