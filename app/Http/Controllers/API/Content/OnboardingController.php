<?php

namespace App\Http\Controllers\API\Content;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    public function index()
    {
        try {
            $onboarding = DB::select("SELECT a.id AS about_id, a.title, a.description, b.url FROM abouts a JOIN media b ON a.id = b.model_id");
            
            if ($onboarding) {
                return response()->json([
                    'status' => true,
                    'data' => $onboarding
                ]);
            }
            
            return response()->json([
                "status" => false,
                "message" => 'Data not found'
            ]);
            
            // $onboarding = About::where('about_type_id',1)->get();
            // if (count($onboarding) >= 1) {
            //     $media = [];
            //     foreach ($onboarding as $key => $value) {
            //         $media[] = [
            //             'title' => $value['title'],
            //             'description' => $value['description'],
            //             'url' =>$value->getFirstMediaUrl('onboarding')
            //         ];
            //     }
            //     return response()->json([
            //         'status' => true,
            //         'data' => $media
            //     ]);
            // }
        } catch (\Throwable $th) {
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
                "error" => $th
            ]);
        }
    }
}
