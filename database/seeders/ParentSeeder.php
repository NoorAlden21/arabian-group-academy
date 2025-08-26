<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ParentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $parent = User::create([
                'name' => "Parent {$i}",
                'phone_number' => "0990000" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
            ]);

            $parent->parentProfile()->create([
                'occupation' => ['Lawyer', 'Engineer', 'Doctor', 'Teacher', 'Merchant'][array_rand(['Lawyer', 'Engineer', 'Doctor', 'Teacher', 'Merchant'])],
            ]);

            $parent->assignRole('parent');
        }
    }
}
