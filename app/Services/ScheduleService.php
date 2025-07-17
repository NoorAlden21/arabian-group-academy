<?php

namespace App\Services;

use App\Models\User;
use App\Models\Schedule;
use Illuminate\Database\Eloquent\Collection;

class ScheduleService
{
    /**
     * Get the schedule for the authenticated user based on their role.
     *
     * @param User $user
     * @return Collection
     */
    public function getUserSchedule(User $user): Collection
    {
        $schedules = new Collection();

        if ($user->hasRole('student')) {
            $user->load([
                'studentProfile.classroom.classSubjectTeachers.schedules.classSubjectTeacher.classroom',
                'studentProfile.classroom.classSubjectTeachers.schedules.classSubjectTeacher.subject',
                'studentProfile.classroom.classSubjectTeachers.schedules.classSubjectTeacher.teacher' // <--- هنا نطلب User Model فقط للمعلم
            ]);
            if ($studentProfile = $user->studentProfile) {
                if ($classroom = $studentProfile->classroom) {
                    foreach ($classroom->classSubjectTeachers as $classSubjectTeacher) {
                        $schedules = $schedules->merge($classSubjectTeacher->schedules);
                    }
                }
            }
        } elseif ($user->hasRole('teacher')) {
            $user->load([
                'classSubjectTeachers.schedules.classSubjectTeacher.classroom',
                'classSubjectTeachers.schedules.classSubjectTeacher.subject',
                'classSubjectTeachers.schedules.classSubjectTeacher.teacher' // <--- هنا نطلب User Model فقط للمعلم
            ]);
            foreach ($user->classSubjectTeachers as $classSubjectTeacher) {
                $schedules = $schedules->merge($classSubjectTeacher->schedules);
            }
        } elseif ($user->hasRole('parent')) {
            $user->load([
                'parentProfile.students.classroom.classSubjectTeachers.schedules.classSubjectTeacher.classroom',
                'parentProfile.students.classroom.classSubjectTeachers.schedules.classSubjectTeacher.subject',
                'parentProfile.students.classroom.classSubjectTeachers.schedules.classSubjectTeacher.teacher' // <--- هنا نطلب User Model فقط للمعلم
            ]);
            if ($parentProfile = $user->parentProfile) {
                foreach ($parentProfile->students as $student) {
                    if ($classroom = $student->classroom) {
                        foreach ($classroom->classSubjectTeachers as $classSubjectTeacher) {
                            $schedules = $schedules->merge($classSubjectTeacher->schedules);
                        }
                    }
                }
            }
        } else {
            return new Collection();
        }

        return $schedules->sortBy('day')->sortBy('start_time')->values();
    }
}
