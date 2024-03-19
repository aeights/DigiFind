<?php

namespace App\Http\Controllers\API\Content;

use App\Http\Controllers\Controller;
use App\Models\About;
use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    public function index()
    {
        try {
            $contact = About::where('about_type_id',2)->get();
            if (count($contact) >= 1) {
                $media = [];
                foreach ($contact as $key => $value) {
                    $media[] = [
                        'title' => $value['title'],
                        'description' => $value['description'],
                        'url' =>$value->getFirstMediaUrl('contact')
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
