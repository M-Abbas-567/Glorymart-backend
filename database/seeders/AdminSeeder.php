<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run()
    {
        User::updateOrCreate(
            ['email' => 'admin@glorymart.local'],
            [
                'name' => 'Admin Prime',
                'password' => bcrypt('admin123'),
                'role' => 'admin',
                'status' => 'active',
            ],
        );
    }
}
