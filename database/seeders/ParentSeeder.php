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
        $occupations = ['Lawyer', 'Engineer', 'Doctor', 'Teacher', 'Merchant'];
        $genders = ['male', 'female'];
        $motherNames = ['Fatima', 'Aisha', 'Khadija', 'Mariam', 'Zainab'];
        $birthDates = ['1980-05-15', '1982-08-22', '1978-12-10', '1985-03-30', '1983-07-18'];

        for ($i = 1; $i <= 5; $i++) {
            $parent = User::create([
                'name' => "Parent {$i}",
                'phone_number' => "0990000" . str_pad($i, 3, '0', STR_PAD_LEFT),
                'password' => Hash::make('password'),
                'gender' => $genders[$i % 2],
                'mother_name' => $motherNames[$i - 1],
                'birth_date' => $birthDates[$i - 1],
            ]);

            $parent->parentProfile()->create([
                'occupation' => $occupations[$i - 1],
            ]);

            $parent->assignRole('parent');

            // إضافة log لمراجعة البيانات
            \Illuminate\Support\Facades\Log::info("Created parent: {$parent->name}, Phone: {$parent->phone_number}, Occupation: {$occupations[$i - 1]}");
        }
    }
}
