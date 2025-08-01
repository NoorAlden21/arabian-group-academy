<?php

namespace Database\Seeders;

use App\Models\ClassSubjectTeacher;
use App\Models\Schedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $daysOfWeek = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
        $periods = [
            1 => ['start_time' => '08:00:00', 'end_time' => '09:00:00'],
            2 => ['start_time' => '09:00:00', 'end_time' => '10:00:00'],
            3 => ['start_time' => '10:00:00', 'end_time' => '11:00:00'],
            4 => ['start_time' => '11:00:00', 'end_time' => '12:00:00'],
            5 => ['start_time' => '12:00:00', 'end_time' => '13:00:00'],
        ];

        $classSubjectTeachers = ClassSubjectTeacher::all();

        foreach ($classSubjectTeachers as $cst) {
            $numberOfClasses = rand(2, 5);
            $randomPeriods = array_rand($periods, $numberOfClasses);

            $day = $daysOfWeek[array_rand($daysOfWeek)];

            foreach ($randomPeriods as $periodNumber) {
                Schedule::create([
                    'class_subject_teacher_id' => $cst->id,
                    'day' => $day,
                    'period' => $periodNumber,
                    'start_time' => $periods[$periodNumber]['start_time'],
                    'end_time' => $periods[$periodNumber]['end_time'],
                ]);
            }
        }
    }
}
