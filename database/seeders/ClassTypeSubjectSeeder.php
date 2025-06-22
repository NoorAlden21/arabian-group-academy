<?php

namespace Database\Seeders;

use App\Models\ClassType;
use App\Models\ClassTypeSubject;
use App\Models\Subject;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use function PHPSTORM_META\map;

class ClassTypeSubjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subjects = Subject::all()->keyBy('name');
        $types = ClassType::all()->keyBy('name');

        $mapping = [
            '9th Grade' => [
                'arabic', 'english', 'math', 'french', 'general sciences', 'religion', 'social studies',
            ],
            'Baccalaureate Scientific' => [
                'math', 'physics', 'chemistry', 'biology', 'arabic', 'english', 'french', 'religion'
            ],
            'Baccalaureate Literature' => [
                'arabic', 'philosophy', 'history', 'geography', 'french', 'religion', 'english'
            ]
        ];

        foreach($mapping as $type => $subjectList){
            foreach($subjectList as $subjectName){
                ClassTypeSubject::create([
                    'class_type_id' => $types[$type]->id,
                    'subject_id' => $subjects[$subjectName]->id
                ]);
            }
        }
    }
}
