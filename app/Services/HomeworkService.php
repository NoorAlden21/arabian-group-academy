<?php

namespace App\Services;

use App\Models\Homework;
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


    public function getStudentHomeworks(User $studentUser): Collection
    {
        if (!$studentUser->hasRole('student') || !$studentUser->studentProfile || !$studentUser->studentProfile->classroom) {
            return collect();
        }

        $studentUser->load('studentProfile.classroom.classSubjectTeachers.homeworks');

        $homeworks = collect();
        foreach ($studentUser->studentProfile->classroom->classSubjectTeachers as $cst) {
            $homeworks = $homeworks->merge($cst->homeworks);
        }

        return $homeworks;
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
}
