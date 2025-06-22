<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $subjects = [
            'arabic', 'english', 'math', 'physics', 'chemistry',
            'biology', 'french', 'history', 'geography', 'philosophy', 'religion',
            'general sciences' , 'social studies'
        ];

        foreach ($subjects as $name) {
            Subject::create(['name' => $name]);
        }
    }
}
