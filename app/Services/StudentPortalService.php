<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\StudentProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StudentPortalService
{
    protected function getMyProfile(): StudentProfile
    {
        $profile = StudentProfile::with('classroom:id,class_type_id')
            ->where('user_id', auth()->id())
            ->first();

        if (!$profile) {
            throw ValidationException::withMessages(['profile' => ['Student profile not found for current user.']]);
        }
        if (!$profile->classroom?->class_type_id) {
            throw ValidationException::withMessages(['classroom' => ['Student classroom/class type is not set.']]);
        }
        return $profile;
    }

    /**
     * Fetch student's exams by his ClassType.
     *
     * @param int|null $termId  (optional) filter by exam_term_id
     * @param string|null $status  (optional) draft|published|done|cancelled (default: published)
     * @param string|null $from  (optional) ISO or 'Y-m-d H:i:s'
     * @param string|null $to    (optional)
     * @param bool $upcomingOnly (default: true) only future exams from now
     */
    public function myExams(?int $termId = null, ?string $status = 'published', ?string $from = null, ?string $to = null, bool $upcomingOnly = true)
    {
        $profile      = $this->getMyProfile();
        $classTypeId  = $profile->classroom->class_type_id;

        $q = Exam::query()
            ->with(['term:id,name,academic_year,term,status', 'subject:id,name', 'classType:id,name'])
            ->where('class_type_id', $classTypeId);

        if ($termId) {
            $q->where('exam_term_id', $termId);
        }

        if ($status) {
            $q->where('status', $status);
        } else {
            // default to published + done if not given
            $q->whereIn('status', ['published','done']);
        }

        if ($upcomingOnly) {
            $q->where('scheduled_at', '>=', now());
        }

        if ($from) {
            $q->where('scheduled_at', '>=', Carbon::parse($from));
        }
        if ($to) {
            $q->where('scheduled_at', '<=', Carbon::parse($to));
        }

        return $q->orderBy('scheduled_at')->get();
    }

    /**
     * Fetch student's exam grades (only visible after results are published).
     *
     * @param int|null $termId
     * @param int|null $subjectId
     */
    public function myExamGrades(?int $termId = null, ?int $subjectId = null)
    {
        $profile      = $this->getMyProfile();
        $classTypeId  = $profile->classroom->class_type_id;

        // ملاحظة: gradable_type قد تكون alias 'exam' لو مفعّل MorphMap،
        // أو اسم الكلاس الكامل \App\Models\Exam::class
        $types = [\App\Models\Exam::class, 'exam'];

        $q = DB::table('grades as g')
            ->join('exams as e', 'e.id', '=', 'g.gradable_id')
            ->join('subjects as s', 's.id', '=', 'e.subject_id')
            ->leftJoin('exam_terms as t', 't.id', '=', 'e.exam_term_id')
            ->where('g.student_profile_id', $profile->id)
            ->whereIn('g.gradable_type', $types)
            ->where('e.class_type_id', $classTypeId)
            // لا نُظهر الدرجات إلا بعد نشر نتائج الامتحان
            ->whereNotNull('e.results_published_at')
            ->selectRaw('
                g.id as grade_id, g.score, g.max_score, g.status as grade_status, g.remark,
                g.graded_at, g.verified_at,
                e.id as exam_id, e.scheduled_at, e.duration_minutes, e.max_score as exam_max_score,
                e.results_published_at,
                s.id as subject_id, s.name as subject_name,
                t.id as term_id, t.name as term_name, t.academic_year, t.term as term_kind
            ');

        if ($termId) {
            $q->where('e.exam_term_id', $termId);
        }
        if ($subjectId) {
            $q->where('e.subject_id', $subjectId);
        }

        return $q->orderByDesc('e.scheduled_at')->get();
    }
}
