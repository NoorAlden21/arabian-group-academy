<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Admin One',
            'phone_number' => '0911111111',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '1990-01-01',
        ]);

        $admin->assignRole('admin');
    }
}
