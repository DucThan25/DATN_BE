<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for($i = 1; $i <= 10; $i++) {
            self::checkIssetBeforeCreate([
                'name' => 'User'. $i,
                'email' => 'user'.$i.'@gmail.com',
                'role' => User::ROLE['USER'],
                'address'=> 'Ha Noi',
                'gender'=> User::GENDER['MALE'],
                'date'=> Carbon::now(),
                'password' => User::PASSWORD_DEFAULT,
                'type_account' => User::TYPE_ACCOUNT['BASIC'],
                'check_change_password' => User::CHANGE_PASSWORD['CHANGED'],
                'introduce' => '',
                'avatar'=>'',
                'cover_image'=> '',
            ]);
        }
    }
    public function checkIssetBeforeCreate($data) {
        $admin = User::where('email', $data['email'])->first();
        if (empty($admin)) {;
            User::create($data);
        } else {
            $admin->update($data);
        }
    }
}
