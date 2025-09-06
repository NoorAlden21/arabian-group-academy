<?php

namespace Database\Seeders;

use App\Models\ExamTerm;
use Illuminate\Database\Seeder;

class ExamTermSeeder extends Seeder
{
    public function run(): void
    {
        $year = '2024/2025';

        $terms = [
            [
                'name'          => "Midterm {$year}",
                'academic_year' => $year,
                'term'          => 'midterm',
                'start_date'    => '2025-01-10',
                'end_date'      => '2025-01-20',
                'status'        => 'draft',
            ],
            [
                'name'          => "Final {$year}",
                'academic_year' => $year,
                'term'          => 'final',
                'start_date'    => '2025-05-20',
                'end_date'      => '2025-05-30',
                'status'        => 'draft',
            ],
        ];

        foreach ($terms as $t) {
            ExamTerm::updateOrCreate(
                [
                    'academic_year' => $t['academic_year'],
                    'term'          => $t['term'],
                ],
                $t
            );
        }
    }
}
