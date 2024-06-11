<?php

namespace Database\Seeders;

use App\Models\Media;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
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

    public function run(): void
    {
        $data = [
            [
                'nik' => '1231',
                'name' => 'User 1',
                'gender' => 'male',
                'address' => 'Jember',
                'email' => 'user1@mail.com',
                'phone' => '081',
                'password' => '111111',
            ],
            [
                'nik' => '1232',
                'name' => 'User 2',
                'gender' => 'female',
                'address' => 'Jember',
                'email' => 'user2@mail.com',
                'phone' => '082',
                'password' => '111111',
            ],
            [
                'nik' => '1233',
                'name' => 'User 3',
                'gender' => 'male',
                'address' => 'Jember',
                'email' => 'user3@mail.com',
                'phone' => '083',
                'password' => '111111',
            ],
            [
                'nik' => '1234',
                'name' => 'User 4',
                'gender' => 'female',
                'address' => 'Jember',
                'email' => 'user4@mail.com',
                'phone' => '084',
                'password' => '111111',
            ],
            [
                'nik' => '1235',
                'name' => 'User 5',
                'gender' => 'male',
                'address' => 'Jember',
                'email' => 'user5@mail.com',
                'phone' => '085',
                'password' => '111111',
            ],
            [
                'nik' => '1236',
                'name' => 'User 6',
                'gender' => 'female',
                'address' => 'Jember',
                'email' => 'user6@mail.com',
                'phone' => '086',
                'password' => '111111',
            ],
            [
                'nik' => '1237',
                'name' => 'User 7',
                'gender' => 'male',
                'address' => 'Jember',
                'email' => 'user7@mail.com',
                'phone' => '087',
                'password' => '111111',
            ],
            [
                'nik' => '1238',
                'name' => 'User 8',
                'gender' => 'female',
                'address' => 'Jember',
                'email' => 'user8@mail.com',
                'phone' => '088',
                'password' => '111111',
            ],
            [
                'nik' => '1239',
                'name' => 'User 9',
                'gender' => 'male',
                'address' => 'Jember',
                'email' => 'user9@mail.com',
                'phone' => '089',
                'password' => '111111',
            ],
            [
                'nik' => '12310',
                'name' => 'User 10',
                'gender' => 'female',
                'address' => 'Jember',
                'email' => 'user10@mail.com',
                'phone' => '0810',
                'password' => '111111',
            ],
        ];

        foreach ($data as $key => $value) {
            $user = User::create($value);
            $image = self::getRandomAvatar($value['gender']);
            $relativePath = 'media/avatar/'.$value['gender'];
            $avatarsPath = public_path($relativePath);
            $path = str_replace($avatarsPath, $relativePath, $image);
            $size = File::size($image);
            Media::create([
                'model_id' => $user->id,
                'media_type_id' => 2,
                'file_name' => $path,
                'path' => $path,
                'url' => $path,
                'mime_type' => 'jpg',
                'size' => $size,
            ]);
        }
    }
}
