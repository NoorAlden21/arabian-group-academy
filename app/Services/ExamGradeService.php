<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Grade;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ExamGradeService
{
    // جلب طلاب الـ ClassType (اختياري تصفية بـ class_id)
    public function studentsForExam(int $examId, ?int $classId = null)
    {
        $exam = Exam::with('classType')->findOrFail($examId);

        // الصفوف التابعة لنفس class_type
        $classes = DB::table('classrooms')
            ->where('class_type_id', $exam->class_type_id)
            ->when($classId, fn($q) => $q->where('id', $classId))
            ->pluck('id');

        if ($classId && !$classes->contains($classId)) {
            throw ValidationException::withMessages(['class_id' => ['Class does not belong to the exam class type.']]);
        }

        // طلاب هذه الصفوف
        return DB::table('student_profiles as sp')
            ->join('users as u', 'u.id', '=', 'sp.user_id')
            ->whereIn('sp.classroom_id', $classes)
            ->select('sp.id as student_profile_id', 'u.name', 'u.phone_number')
            ->orderBy('u.name')
            ->get();
    }

    // إدخال/تحديث درجات الطلاب (Admin)
    public function bulkUpsert(int $examId, array $records): int
    {
        $exam = Exam::findOrFail($examId);

        return DB::transaction(function () use ($exam, $records) {
            $count = 0;

            foreach ($records as $r) {
                // إذا status != present، تجاهل score
                $status = $r['status'];
                $score  = ($status === 'present') ? ($r['score'] ?? null) : null;

                Grade::updateOrCreate(
                    [
                        'student_profile_id' => $r['student_profile_id'],
                        'gradable_type'      => Exam::class,
                        'gradable_id'        => $exam->id,
                    ],
                    [
                        'status'     => $status,
                        'score'      => $score,
                        'max_score'  => $exam->max_score,
                        'remark'     => $r['remark'] ?? null,
                        'graded_at'  => now(),
                    ]
                );

                $count++;
            }

            return $count;
        });
    }

    public function publishResults(int $examId): Exam
    {
        $exam = Exam::findOrFail($examId);
        $exam->update([
            'results_published_at' => now(),
            'status' => $exam->status === 'published' ? 'done' : $exam->status,
        ]);
        return $exam->fresh();
    }
}
