<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\StudentProfile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
            $q->whereIn('status', ['published', 'done']);
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
        $profile     = $this->getMyProfile();
        $classTypeId = $profile->classroom->class_type_id;

        $types = [\App\Models\Exam::class, 'exam'];

        // حضّر select مرن حسب وجود الأعمدة (تجنّب Unknown column)
        $selResultsPublished = Schema::hasColumn('exams', 'results_published_at')
            ? 'e.results_published_at'
            : 'NULL as results_published_at';

        $selTermName = Schema::hasColumn('exam_terms', 'name')
            ? 't.name as term_name'
            : 'NULL as term_name';

        $selTermYear = Schema::hasColumn('exam_terms', 'academic_year')
            ? 't.academic_year'
            : 'NULL as academic_year';

        $selTermKind = Schema::hasColumn('exam_terms', 'term')
            ? 't.term as term_kind'
            : 'NULL as term_kind';

        // أهم تعديل: هالأعمدة غير موجودة في migration => خليه Fallback
        $selGradedAt = Schema::hasColumn('grades', 'graded_at')
            ? 'g.graded_at'
            : 'NULL as graded_at';

        $selVerifiedAt = Schema::hasColumn('grades', 'verified_at')
            ? 'g.verified_at'
            : 'NULL as verified_at';

        $q = DB::table('grades as g')
            ->join('exams as e', 'e.id', '=', 'g.gradable_id')
            ->join('subjects as s', 's.id', '=', 'e.subject_id')
            ->leftJoin('exam_terms as t', 't.id', '=', 'e.exam_term_id')
            ->where('g.student_profile_id', $profile->id)
            ->whereIn('g.gradable_type', $types)
            ->where('e.class_type_id', $classTypeId)
            ->selectRaw("
            g.id as grade_id,
            g.score,
            g.max_score,
            g.status as grade_status,
            g.remark,
            {$selGradedAt},
            {$selVerifiedAt},
            e.id as exam_id,
            e.scheduled_at,
            e.duration_minutes,
            e.max_score as exam_max_score,
            {$selResultsPublished},
            s.id as subject_id,
            s.name as subject_name,
            t.id as term_id,
            {$selTermName},
            {$selTermYear},
            {$selTermKind}
        ");

        // لا تُظهر الدرجات إلا بعد نشر النتائج
        if (Schema::hasColumn('exams', 'results_published_at')) {
            $q->whereNotNull('e.results_published_at');
        } else {
            // بديل منطقي لو ما عندك العمود: الحالة
            $q->whereIn('e.status', ['done', 'published']);
        }

        if ($termId) {
            $q->where('e.exam_term_id', $termId);
        }
        if ($subjectId) {
            $q->where('e.subject_id', $subjectId);
        }

        return $q->orderByDesc('e.scheduled_at')->get();
    }
}
