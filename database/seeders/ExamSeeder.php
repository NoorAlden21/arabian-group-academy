<?php

namespace Database\Seeders;

use App\Models\Exam;
use App\Models\ExamTerm;
use App\Models\ClassTypeSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExamSeeder extends Seeder
{
    public function run(): void
    {
        // terms we just seeded
        $terms = ExamTerm::whereIn('term', ['midterm', 'final'])
            ->where('academic_year', '2024/2025')
            ->get()
            ->keyBy('term');

        if ($terms->isEmpty()) {
            $this->command->warn('⚠️ No exam_terms found. Run ExamTermSeeder first.');
            return;
        }

        // subjects per class type (from your pivot)
        $cts = ClassTypeSubject::with('subject')
            ->get()
            ->groupBy('class_type_id'); // class_type_id => [ClassTypeSubject,...]

        if ($cts->isEmpty()) {
            $this->command->warn('⚠️ class_type_subjects is empty. Run ClassTypeSubjectSeeder first.');
            return;
        }

        DB::transaction(function () use ($terms, $cts) {
            foreach (['midterm', 'final'] as $kind) {
                if (!isset($terms[$kind])) {
                    Log::warning("ExamSeeder: term {$kind} not found, skipping.");
                    continue;
                }

                $term = $terms[$kind];

                // pick a base date (fallback if null)
                $base = $term->start_date
                    ? Carbon::parse($term->start_date)->setTime(9, 0)
                    : Carbon::parse($kind === 'midterm' ? '2025-01-10 09:00' : '2025-05-20 09:00');

                foreach ($cts as $classTypeId => $subjects) {
                    // schedule one subject per day to avoid overlap for same class type
                    $day = 0;

                    foreach ($subjects as $pivot) {
                        $subjectId = $pivot->subject_id;

                        $scheduledAt = (clone $base)->addDays($day);
                        $day++;

                        Exam::updateOrCreate(
                            [
                                'exam_term_id' => $term->id,
                                'class_type_id' => $classTypeId,
                                'subject_id'   => $subjectId,
                            ],
                            [
                                'scheduled_at'     => $scheduledAt,
                                'duration_minutes' => 90,
                                'max_score'        => 100,
                                'status'           => 'draft',   // keep draft; you can publish via API/UI
                                'notes'            => 'Seeded exam',
                                'published_at'     => null,
                                'results_published_at' => null,
                            ]
                        );
                    }
                }
            }
        });
    }
}
