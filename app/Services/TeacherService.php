<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class TeacherService
{
    public function createTeacher(array $data)
    {

        return DB::transaction(function () use ($data) {
            $teacher = User::create([
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                'password' => Hash::make($data['password']),
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date'],
            ]);

            $teacher->assignRole('teacher');

            $teacher->teacherProfile()->create([
                'department' => $data['department'],
            ]);

            if ($data['class_type_subjects']) {
                foreach ($data['class_type_subjects'] as $ctsid) {
                    $teacher->teacherProfile->teachableSubjects()->create([
                        'class_type_subject_id' => $ctsid
                    ]);
                }
            }

            return $teacher;
        });
    }

    public function getAllTeachers()
    {

        return User::role('teacher')->with('teacherProfile')->get();
    }

    public function getTeacherById($id)
    {
        return User::role('teacher')
            ->with([
                'teacherProfile',
                'teacherProfile.teachableSubjects.classTypeSubject.subject',
                'teacherProfile.teachableSubjects.classTypeSubject.classType'
            ])
            ->find($id);
    }

    public function updateTeacher($id, array $data)
    {

        return DB::transaction(function () use ($id, $data) {
            $teacher = User::role('teacher')->with('teacherProfile')->findOrFail($id);

            $teacher->update([
                'name' => $data['name'] ?? $teacher->name,
                'phone_number' => $data['phone_number'] ?? $teacher->phone_number,
                'gender' => $data['gender'] ?? $teacher->gender,
                'birth_date' => $data['birth_date'] ?? $teacher->birth_date,
            ]);

            $teacher->teacherProfile->update([
                'department' => $data['department'] ?? $teacher->teacherProfile->department,
            ]);

            return $teacher;
        });
    }

    public function deleteTeacher($id)
    {

        return DB::transaction(function () use ($id) {
            $teacher = User::role('teacher')->with('teacherProfile')->findOrFail($id);
            $teacher->teacherProfile->delete();
            $teacher->delete();
            return true;
        });
    }

    public function restoreTeacher($id)
    {

        $teacher = User::onlyTrashed()->role('teacher')->where('id', $id)->firstOrFail();
        $teacher->restore();

        if ($teacher->teacherProfile && method_exists($teacher->teacherProfile, 'restore')) {
            $teacher->teacherProfile->restore();
        }

        return $teacher;
    }

    public function forceDeleteTeacher($id)
    {

        $teacher = User::onlyTrashed()->role('teacher')->where('id', $id)->withTrashed()->firstOrFail();

        if ($teacher->teacherProfile) {
            $teacher->teacherProfile()->forceDelete();
        }

        $teacher->forceDelete();

        return true;
    }

    public function searchTeachers($filters)
    {

        $query = User::role('teacher')->with('teacherProfile');

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['phone_number'])) {
            $query->where('phone_number', 'like', '%' . $filters['phone_number'] . '%');
        }

        if (!empty($filters['department'])) {
            $query->whereHas('teacherProfile', fn($q) => $q->where('department', $filters['department']));
        }

        return $query->paginate(10);
    }

    public function getAssignedClassesAndSubjects(User $teacherUser): Collection
    {
        if (!$teacherUser->hasRole('teacher') || !$teacherUser->teacherProfile) {
            return collect();
        }

        $teacherUser->load([
            'teacherProfile.classSubjectTeachers.classroom',
            'teacherProfile.classSubjectTeachers.subject',
        ]);

        return $teacherUser->teacherProfile->classSubjectTeachers ?? collect();
    }
}
