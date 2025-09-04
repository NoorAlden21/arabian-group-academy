<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\StudentProfile;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ClassroomStudentService
{

    public function studentsOfClassroom(Classroom $classroom)
    {
        $query = StudentProfile::query()
            ->join('users', 'users.id', '=', 'student_profiles.user_id')
            ->whereNull('student_profiles.deleted_at')
            ->where('student_profiles.classroom_id', $classroom->id)
            ->orderBy('users.name', 'asc');

        if (Schema::hasColumn('users', 'deleted_at')) {
            $query->whereNull('users.deleted_at');
        }

        return $query->get([
            'student_profiles.id as student_profile_id',
            'student_profiles.user_id',
            'users.name',
            'users.phone_number',
            'users.gender',
        ]);
    }

    public function candidateStudentsForClassroom(Classroom $classroom, ?string $q = null, int $perPage = 20): LengthAwarePaginator
    {
        return StudentProfile::query()
            ->whereNull('deleted_at')
            ->whereNull('classroom_id')
            ->where('level', $classroom->level)
            ->when($q, function (Builder $b) use ($q) {
                $b->whereHas('user', fn (Builder $u) => $u->where('name', 'like', "%{$q}%"));
            })
            ->with([
                'user:id,name,phone_number,gender,birth_date',
                'classroom:id',
            ])
            ->orderBy('id')
            ->paginate($perPage);
    }

    public function candidateClassroomsForStudent(StudentProfile $student, ?string $q = null, int $perPage = 20): LengthAwarePaginator
    {
        return Classroom::query()
            ->whereNull('deleted_at')
            ->where('level', $student->level)
            ->when($q, fn (Builder $b) => $b->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate($perPage);
    }

    /** تعيين مجموعة طلاب إلى الصف (يقبل فقط من هم بنفس المستوى، وغير مخصصين حالياً) */
    public function assignStudentsToClassroom(Classroom $classroom, array $studentProfileIds): array
    {
        return DB::transaction(function () use ($classroom, $studentProfileIds) {
            $students = StudentProfile::query()
                ->whereIn('id', $studentProfileIds)
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->get();

            $mismatch = $students->firstWhere('level', '!=', $classroom->level);
            if ($mismatch) {
                throw ValidationException::withMessages([
                    'student_profile_ids' => ['All students must have the same level as the classroom.'],
                ]);
            }

            $eligible = $students->whereNull('classroom_id');

            foreach ($eligible as $s) {
                $s->classroom_id = $classroom->id;
                $s->save();
            }

            $classroom->students_count = StudentProfile::where('classroom_id', $classroom->id)->count();
            $classroom->save();

            return [
                'classroom_id'      => $classroom->id,
                'requested_count'   => count($studentProfileIds),
                'assigned_count'    => $eligible->count(),
                'already_assigned'  => $students->count() - $eligible->count(),
                'students_assigned' => $eligible->values()->map(fn ($s) => [
                    'id'           => $s->id,
                    'user_id'      => $s->user_id,
                    'classroom_id' => $s->classroom_id,
                ]),
            ];
        });
    }

    public function assignClassroomToStudent(StudentProfile $student, int $classroomId): array
    {
        return DB::transaction(function () use ($student, $classroomId) {
            $classroom = Classroom::query()->whereKey($classroomId)->whereNull('deleted_at')->lockForUpdate()->firstOrFail();

            if ($student->level !== $classroom->level) {
                throw ValidationException::withMessages([
                    'classroom_id' => ['Classroom level must match student level.'],
                ]);
            }

            $student->classroom_id = $classroom->id;
            $student->save();

            $classroom->students_count = StudentProfile::where('classroom_id', $classroom->id)->count();
            $classroom->save();

            return [
                'student' => [
                    'id'           => $student->id,
                    'user_id'      => $student->user_id,
                    'classroom_id' => $student->classroom_id,
                ],
                'classroom' => [
                    'id'    => $classroom->id,
                    'name'  => $classroom->name,
                    'level' => $classroom->level,
                ],
            ];
        });
    }
}
