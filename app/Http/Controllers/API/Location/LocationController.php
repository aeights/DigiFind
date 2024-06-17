<?php

namespace App\Http\Controllers\API\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LocationController extends Controller
{
    public function provinces()
    {
        try {
            $data = DB::select('SELECT * FROM provinces');
            return response()->json([
                "status" => true,
                "message" => "Showing all provinces",
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
    public function cities($id)
    {
        try {
            if ($id == 0) {
                $data = DB::select('SELECT * FROM cities');
                return response()->json([
                    "status" => true,
                    "message" => "Showing all city",
                    "data" => $data
                ]);
            }
            $data = DB::select('SELECT * FROM cities WHERE province_code = ?',[$id]);
            return response()->json([
                "status" => true,
                "message" => "Showing all selected city",
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
    public function districts($id)
    {
        try {
            if ($id == 0) {
                $data = DB::select('SELECT * FROM districts');
                return response()->json([
                    "status" => true,
                    "message" => "Showing all districts",
                    "data" => $data
                ]);
            }
            $data = DB::select('SELECT * FROM districts WHERE city_code = ?',[$id]);
            return response()->json([
                "status" => true,
                "message" => "Showing all selected districts",
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
    public function villages($id)
    {
        try {
            if ($id == 0) {
                $data = DB::select('SELECT * FROM villages');
                return response()->json([
                    "status" => true,
                    "message" => "Showing all village",
                    "data" => $data
                ]);
            }
            $data = DB::select('SELECT * FROM villages WHERE district_code = ?',[$id]);
            return response()->json([
                "status" => true,
                "message" => "Showing all selected village",
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
}
