<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $parent = User::create([
            'name' => 'Parent One',
            'phone_number' => '0990000001',
            'password' => Hash::make('password'),
        ]);
        $parent->parentProfile()->create([
            'occupation' => 'Lawyer',
        ]);
        $parent->assignRole('parent');

         $student = User::create([
            'name' => 'Student One',
            'phone_number' => '0910000001',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '2009-05-12'
        ]);
        $student->assignRole('student');

        $student->studentProfile()->create([
            'parent_id' => $parent->parentProfile->id,
            'level' => '9',
            'enrollment_year' => '2024',
            'classroom_id' => null, 
        ]);
    }
}
