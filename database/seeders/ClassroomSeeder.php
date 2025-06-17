<?php

namespace Database\Seeders;

use App\Models\Classroom;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Classroom::firstOrCreate(
            ['name' => 'Grade 9A', 'level' => '9', 'year' => '2024'],
            ['students_count' => 25]
        );

        Classroom::firstOrCreate(
            ['name' => 'Grade 10B', 'level' => '10', 'year' => '2024'],
            ['students_count' => 28]
        );

        Classroom::firstOrCreate(
            ['name' => 'Grade 11C', 'level' => '11', 'year' => '2025'],
            ['students_count' => 22]
        );

    }
}
