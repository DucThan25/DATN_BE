<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::all();
        for($i = 1; $i <= 5; $i++) {
            foreach ($users as $user) {
                Post::create([
                    'user_id' => $user->id,
                    'title' => 'Bài viết ' . $i . ' của User '.$user->id,
                    'content' => 'Nội dung bài viết ' . $i . ' của User '.$user->id,
                    'type' => Post::TYPE['HOME'],
                ]);
            }
        }
    }
}
