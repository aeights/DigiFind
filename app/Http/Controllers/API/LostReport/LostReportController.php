<?php

namespace App\Http\Controllers\API\LostReport;

use App\Http\Controllers\Controller;
use App\Models\LostReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class LostReportController extends Controller
{
    public function index()
    {
        
    }

    public function read($id)
    {

    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
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

    }

    public function delete($id)
    {

    }

    public function reportSummary()
    {

    }

    public function publicationPackage()
    {

    }
}
