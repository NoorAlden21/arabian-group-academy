<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\ExamTerm;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class ExamService
{
    public function bulkUpsertForClassType(int $termId, int $classTypeId, array $items): array
    {
        $term = ExamTerm::findOrFail($termId);

        return DB::transaction(function () use ($term, $classTypeId, $items) {
            $result = [];

            foreach ($items as $item) {
                // تحقّق أن المادة ضمن هذا الـ ClassType عبر pivot class_type_subject
                $exists = DB::table('class_type_subjects')
                    ->where('class_type_id', $classTypeId)
                    ->where('subject_id', $item['subject_id'])
                    ->exists();

                if (!$exists) {
                    throw ValidationException::withMessages([
                        'subject_id' => ["Subject {$item['subject_id']} is not attached to class_type {$classTypeId}."]
                    ]);
                }

                $scheduledAt = Carbon::parse($item['scheduled_at']);
                $duration    = (int) $item['duration_minutes'];
                $endAt       = (clone $scheduledAt)->addMinutes($duration);

                // منع التعارض داخل نفس ClassType & Term
                $overlap = Exam::where('exam_term_id', $term->id)
                    ->where('class_type_id', $classTypeId)
                    ->where('id', '!=', ($item['id'] ?? 0))
                    ->where(function ($q) use ($scheduledAt, $endAt) {
                        $q->whereBetween('scheduled_at', [$scheduledAt, $endAt])
                          ->orWhere(function ($q2) use ($scheduledAt, $endAt) {
                              $q2->where('scheduled_at', '<', $scheduledAt)
                                 ->whereRaw('DATE_ADD(scheduled_at, INTERVAL duration_minutes MINUTE) > ?', [$scheduledAt]);
                          });
                    })
                    ->exists();

                if ($overlap) {
                    throw ValidationException::withMessages([
                        'scheduled_at' => ['Time overlaps with another exam for the same class type.']
                    ]);
                }

                $exam = Exam::updateOrCreate(
                    [
                        'exam_term_id' => $term->id,
                        'class_type_id'=> $classTypeId,
                        'subject_id'   => $item['subject_id'],
                    ],
                    [
                        'scheduled_at'     => $scheduledAt,
                        'duration_minutes' => $duration,
                        'max_score'        => $item['max_score'] ?? 100,
                        'notes'            => $item['notes'] ?? null,
                    ]
                );

                $result[] = $exam->fresh(['subject','classType','term']);
            }

            return $result;
        });
    }

    public function publish(int $examId): Exam
    {
        $exam = Exam::findOrFail($examId);
        $exam->update([
            'status' => 'published',
            'published_at' => now(),
        ]);
        return $exam->fresh();
    }

    public function publishTerm(int $termId): ExamTerm
    {
        $term = ExamTerm::findOrFail($termId);
        $term->update(['status' => 'published']);
        return $term->fresh();
    }
}
