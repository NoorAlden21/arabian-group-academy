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
                'studentProfile.classroom.classSubjectTeachers.schedules.classSubjectTeacher.teacher.user'
            ]);

            $classSubjectTeachers = optional($user->studentProfile?->classroom)->classSubjectTeachers ?? collect();

            foreach ($classSubjectTeachers as $cst) {
                $schedules = $schedules->merge($cst->schedules);
            }
        } elseif ($user->hasRole('teacher')) {
            $user->load([
                'classSubjectTeachers.schedules.classSubjectTeacher.classroom',
                'classSubjectTeachers.schedules.classSubjectTeacher.subject',
                'classSubjectTeachers.schedules.classSubjectTeacher.teacher.user'
            ]);

            foreach ($user->classSubjectTeachers as $cst) {
                $schedules = $schedules->merge($cst->schedules);
            }
        } elseif ($user->hasRole('parent')) {
            $user->load([
                'parentProfile.students.classroom.classSubjectTeachers.schedules.classSubjectTeacher.classroom',
                'parentProfile.students.classroom.classSubjectTeachers.schedules.classSubjectTeacher.subject',
                'parentProfile.students.classroom.classSubjectTeachers.schedules.classSubjectTeacher.teacher.user'
            ]);

            foreach ($user->parentProfile?->students ?? [] as $student) {
                foreach ($student->classroom?->classSubjectTeachers ?? [] as $cst) {
                    $schedules = $schedules->merge($cst->schedules);
                }
            }
        }

        return $schedules->sortBy([
            ['day', 'asc'],
            ['start_time', 'asc']
        ])->values();
    }


    public function getAllSchedules(): Collection
    {
        return Schedule::with(['classSubjectTeacher.classroom', 'classSubjectTeacher.subject', 'classSubjectTeacher.teacher.user'])->get();
    }

    /**
     * Create a new schedule.
     */
    public function createSchedule(array $data): Schedule
    {
        return Schedule::create($data);
    }

    /**
     * Get a specific schedule by ID.
     */
    public function getScheduleById(int $id): Schedule
    {
        return Schedule::with(['classSubjectTeacher.classroom', 'classSubjectTeacher.subject', 'classSubjectTeacher.teacher.user'])->findOrFail($id);
    }

    /**
     * Update a specific schedule.
     */
    public function updateSchedule(int $id, array $data): Schedule
    {
        $schedule = Schedule::findOrFail($id);
        $schedule->update($data);
        $schedule->load(['classSubjectTeacher.classroom', 'classSubjectTeacher.subject', 'classSubjectTeacher.teacher.user']);
        return $schedule;
    }

    /**
     * Delete a schedule.
     */
    public function deleteSchedule(int $id): bool
    {
        $schedule = Schedule::findOrFail($id);
        return $schedule->delete();
    }
}
