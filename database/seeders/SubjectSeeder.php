<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            ['en' => 'Arabic',           'ar' => 'اللغة العربية'],
            ['en' => 'English',          'ar' => 'اللغة الإنجليزية'],
            ['en' => 'Mathematics',      'ar' => 'الرياضيات'],
            ['en' => 'Physics',          'ar' => 'الفيزياء'],
            ['en' => 'Chemistry',        'ar' => 'الكيمياء'],
            ['en' => 'Biology',          'ar' => 'الأحياء'],
            ['en' => 'French',           'ar' => 'اللغة الفرنسية'],
            ['en' => 'History',          'ar' => 'التاريخ'],
            ['en' => 'Geography',        'ar' => 'الجغرافيا'],
            ['en' => 'Philosophy',       'ar' => 'الفلسفة'],
            ['en' => 'Religion',         'ar' => 'التربية الدينية'],
            ['en' => 'General Sciences', 'ar' => 'العلوم العامة'],
            ['en' => 'Social Studies',   'ar' => 'الدراسات الاجتماعية'],
        ];

        foreach ($subjects as $s) {
            Subject::create([
                'name'    => $s['en'],
                'name_ar' => $s['ar'],
            ]);
        }
    }
}
