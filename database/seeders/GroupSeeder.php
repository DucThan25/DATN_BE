<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for($i = 1; $i <= 3; $i++) {
            for($j = 1; $j <= 50; $j++) {
                self::checkIssetBeforeCreate([
                    'user_id' => $j,
                    'name'=> 'Nhóm '.$i. ' của user '. $j,
                    'introduce'=>'Nhóm này của User '. $j . ' nha',
                    'type'=>Group::TYPE['PUBLIC'],
                    'avatar'=>'avatar/8NVzcocGUiUsvCDOfcyEMd02ce2OPNs45eti6EgO.jpg',
                    'cover_image'=>'cover_image/q8H04gxrAGj2YhmqdQPc6ePWTaSl9O9GmZuDgaHM.jpg'
                ]);
            }
        }
    }

    public function checkIssetBeforeCreate($data) {
        $admin = Group::where('name', $data['name'])->first();
        if (empty($admin)) {;
            Group::create($data);
        } else {
            $admin->update($data);
        }
    }
}
