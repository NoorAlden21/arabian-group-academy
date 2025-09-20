<?php

namespace Database\Seeders;

use App\Models\ClassType;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClassTypeSubjectSeeder extends Seeder
{
    public function run(): void
    {
        // slug(name) => id
        $subjects = Subject::select('id', 'name')
            ->get()
            ->mapWithKeys(fn ($s) => [Str::slug($s->name) => $s->id]);

        $classTypes = ClassType::select('id', 'name')
            ->get()
            ->mapWithKeys(fn ($ct) => [Str::slug($ct->name) => $ct->id]);

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

        $aliases = [
            'math' => 'mathematics', // map short to canonical
        ];

        $rows = [];
        $now  = now();

        foreach ($mapping as $typeName => $subjectList) {
            $ctSlug = Str::slug($typeName);
            $ctId   = $classTypes[$ctSlug] ?? null;
            if (!$ctId) continue;

            foreach ($subjectList as $subjectName) {
                $subSlug = Str::slug($subjectName);
                $subSlug = $aliases[$subSlug] ?? $subSlug;

                $subId = $subjects[$subSlug] ?? null;
                if (!$subId) continue;

                $rows[] = [
                    'class_type_id' => $ctId,
                    'subject_id'    => $subId,
                    'created_at'    => $now,
                    'updated_at'    => $now,
                ];
            }
        }

        if (!empty($rows)) {
            DB::table('class_type_subjects')->insert($rows);
        }
    }
}
