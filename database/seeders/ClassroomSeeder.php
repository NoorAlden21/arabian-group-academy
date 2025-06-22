<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = ClassType::all()->keyBy('name');

        $classrooms = [
            ['name' => '9A', 'year' => '2024', 'class_type_id' => $types['9th Grade']->id],
            ['name' => '9B', 'year' => '2024', 'class_type_id' => $types['9th Grade']->id],
            ['name' => 'BacSci-A', 'year' => '2024', 'class_type_id' => $types['Baccalaureate Scientific']->id],
            ['name' => 'BacLit-A', 'year' => '2024', 'class_type_id' => $types['Baccalaureate Literature']->id],
        ];

        foreach ($classrooms as $c) {
            Classroom::create($c);
        }
    }
}
