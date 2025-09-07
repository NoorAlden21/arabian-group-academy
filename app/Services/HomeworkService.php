<?php

namespace App\Services;

use App\Models\Homework;
use App\Models\HomeworkStudentStatus;
use App\Models\User;
use Illuminate\Support\Collection;


class HomeworkService
{
    /**
     * Get a list of homeworks created by the authenticated teacher.
     */
    public function getTeacherHomeworks(User $teacherUser): Collection
    {
        if (!$teacherUser->hasRole('teacher') || !$teacherUser->teacherProfile) {
            return collect();
        }

        $teacherUser->load('teacherProfile.classSubjectTeachers.homeworks');

        $homeworks = collect();
        foreach ($teacherUser->teacherProfile->classSubjectTeachers as $cst) {
            $homeworks = $homeworks->merge($cst->homeworks);
        }

        return $homeworks;
    }


    public function getStudentHomeworks(User $studentUser, array $filters = []): Collection
    {
        if (!$studentUser->hasRole('student') || !$studentUser->studentProfile || !$studentUser->studentProfile->classroom) {
            return collect();
        }

        $query = Homework::query();
        $query->whereHas('classSubjectTeacher.classroom', function ($q) use ($studentUser) {
            $q->where('id', $studentUser->studentProfile->classroom->id);
        });

        // Filtering by subject name
        if (isset($filters['subject'])) {
            $query->whereHas('classSubjectTeacher.subject', function ($q) use ($filters) {
                $q->where('name', $filters['subject']);
            });
        }

        $homeworks = $query->with('classSubjectTeacher.subject')
            ->get();

        $studentProfileId = $studentUser->studentProfile->id;

        return $homeworks->map(function ($homework) use ($studentProfileId) {
            $status = HomeworkStudentStatus::where('homework_id', $homework->id)
                ->where('student_profile_id', $studentProfileId)
                ->first();
            $homework->is_completed = (bool) $status;
            return $homework;
        })->values();
    }

    public function toggleHomeworkStatus(User $studentUser, int $homeworkId): Homework
    {
        if (!$studentUser->hasRole('student')) {
            throw new \Exception("Unauthorized to update homework status.");
        }

        $homework = Homework::with('classSubjectTeacher.classroom')->findOrFail($homeworkId);

        if ($homework->classSubjectTeacher->classroom->id !== $studentUser->studentProfile->classroom_id) {
            throw new \Exception("Unauthorized to update this homework status.");
        }

        $submission = HomeworkStudentStatus::where([
            'homework_id' => $homeworkId,
            'student_profile_id' => $studentUser->studentProfile->id,
        ])->first();

        if ($submission) {
            $submission->delete();
        } else {
            HomeworkStudentStatus::create([
                'homework_id' => $homeworkId,
                'student_profile_id' => $studentUser->studentProfile->id,
            ]);
        }

        return $homework;
    }

    /**
     * Create a new homework.
     */
    public function createHomework(User $teacherUser, array $data): Homework
    {
        $cst = $teacherUser->teacherProfile->classSubjectTeachers()->where('id', $data['class_subject_teacher_id'])->firstOrFail();

        return Homework::create($data);
    }

    /**
     * Get a specific homework by ID.
     */
    public function getHomeworkById(int $id): Homework
    {
        return Homework::findOrFail($id);
    }

    /**
     * Update a specific homework.
     */
    public function updateHomework(User $teacherUser, int $id, array $data): Homework
    {
        $homework = Homework::findOrFail($id);

        if ($homework->classSubjectTeacher->teacher->user->id !== $teacherUser->id) {
            throw new \Exception("Unauthorized to update this homework.");
        }

        $homework->update($data);
        return $homework;
    }

    /**
     * Delete a homework.
     */
    public function deleteHomework(User $teacherUser, int $id): bool
    {
        $homework = Homework::findOrFail($id);

        if ($homework->classSubjectTeacher->teacher->user->id !== $teacherUser->id) {
            throw new \Exception("Unauthorized to delete this homework.");
        }

        return $homework->delete();
    }
    public function getHomeworksByClassroomId(int $classroomId): Collection
    {
        $homeworks = Homework::query()
            ->whereHas('classSubjectTeacher.classroom', function ($q) use ($classroomId) {
                $q->where('id', $classroomId);
            })
            ->with('classSubjectTeacher.subject')
            ->get();

        return $homeworks;
    }
}
