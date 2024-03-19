<?php

namespace App\Http\Controllers\API\Content;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    public function index()
    {
        try {
            $about = About::where('about_type_id',3)->get();
            if (count($about) >= 1) {
                $media = [];
                foreach ($about as $key => $value) {
                    $media[] = [
                        'title' => $value['title'],
                        'description' => $value['description'],
                        'url' =>$value->getFirstMediaUrl('about')
                    ];
                }
                return response()->json([
                    'status' => true,
                    'data' => $media
                ]);
            }
            return response()->json([
                "status" => false,
                "message" => 'Data not found'
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ]);
        }
    }
}
