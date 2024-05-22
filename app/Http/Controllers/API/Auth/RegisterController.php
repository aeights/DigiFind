<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\Media;
use App\Models\User;
use Illuminate\Support\Facades\File;

class RegisterController extends Controller
{
    private $tokenKey;
    private $refreshTokenKey;

    public function __construct()
    {
        $this->tokenKey = config('services.jwt.token_key');
        $this->refreshTokenKey = config('services.jwt.refresh_token_key');
    }

    public function getRandomAvatar($gender)
    {
        $avatarsPath = '';
        if ($gender == 'male') {
            $avatarsPath = public_path('media/avatar/male');
        }
        if ($gender == 'female') {
            $avatarsPath = public_path('media/avatar/female');
        }
        $files = glob($avatarsPath . '/*.{jpg,png,gif}', GLOB_BRACE);
        $file = array_rand(array_flip($files));
        return $file;
    }

    public function register(RegisterRequest $registerRequest)
    {
        $validated = $registerRequest->validated();
        try {
            if ($validated) {
                $user = User::create([
                    'nik' => $registerRequest->nik,
                    'name' => $registerRequest->name,
                    'gender' => $registerRequest->gender,
                    'address' => $registerRequest->address,
                    'email' => $registerRequest->email,
                    'password' => $registerRequest->password,
                    'phone' => $registerRequest->phone,
                ]);

                $avatar = self::getRandomAvatar($registerRequest->gender);
                $extension = pathinfo($avatar, PATHINFO_EXTENSION);
                $fileName = time().'-'.$user->id.'.'.$extension;
                $path = 'media/profile';
                $size = File::size($avatar);
                Media::updateOrCreate(
                    [
                        'model_id' => $user->id
                    ],
                    [
                        'model_id' => $user->id,
                        'media_type_id' => 2,
                        'file_name' => $fileName,
                        'path' => $path,
                        'url' => $path.'/'.$fileName,
                        'mime_type' => $extension,
                        'size' => $size,
                    ]
                );
                copy($avatar,public_path().'/'.$path.'/'.$fileName);
                return response()->json([
                    "status" => true,
                    "message" => "User registration is successful"
                ]);
            }
    
            return response()->json([
                "status" => false,
                "message" => "Validation error"
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
