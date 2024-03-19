<?php

namespace App\Http\Controllers\API\Content;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index()
    {
        try {
            $onboarding = About::where('about_type_id',1)->get();
            if (count($onboarding) >= 1) {
                $media = [];
                foreach ($onboarding as $key => $value) {
                    $media[] = [
                        'title' => $value['title'],
                        'description' => $value['description'],
                        'url' =>$value->getFirstMediaUrl('onboarding')
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
