<?php

namespace Database\Seeders;

use App\Models\ClassSubjectTeacher;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon; // تأكد من استيراد Carbon إذا كنت ستستخدمه لتوليد الأوقات

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // جلب كل التعيينات (الفصول-المواد-المعلمين) التي أنشأناها في ClassSubjectTeacherSeeder
        $classSubjectTeachers = ClassSubjectTeacher::with(['classroom', 'subject', 'teacher.teacherProfile'])->get();

        // تعريف أوقات الفترات الدراسية الافتراضية
        $periodTimes = [
            1 => ['08:00:00', '09:00:00'],
            2 => ['09:00:00', '10:00:00'],
            3 => ['10:00:00', '11:00:00'],
            4 => ['11:00:00', '12:00:00'],
            5 => ['13:00:00', '14:00:00'],
            6 => ['14:00:00', '15:00:00'],
        ];

        // قائمة الأيام التي يتم فيها التدريس
        $weekDays = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday'];

        foreach ($classSubjectTeachers as $cst) {
            // يمكننا تخصيص الجداول هنا بناءً على الـ ClassSubjectTeacher
            // مثال:
            // - فصل 9A ومادة الرياضيات مع محمد Math: حصتان (الأحد، الثلاثاء)
            // - فصل BacSci-A ومادة الرياضيات مع محمد Math: حصة واحدة (الاثنين)
            // - فصل 9A ومادة الإنجليزية مع عائشة English: حصتان (الاثنين، الأربعاء)
            // - فصل BacLit-A ومادة الإنجليزية مع عائشة English: حصة واحدة (الأحد)
            // - فصل BacSci-A ومادة الفيزياء مع سامي Physics: حصتان (الثلاثاء، الخميس)

            $classroomName = $cst->classroom->name;
            $subjectName = $cst->subject->name;
            $teacherName = $cst->teacher->name;

            switch (true) {
                case ($classroomName == '9A' && $subjectName == 'math' && $teacherName == 'Mohammed Math'):
                    Schedule::firstOrCreate([
                        'class_subject_teacher_id' => $cst->id,
                        'day' => 'sunday',
                        'period' => 1,
                        'start_time' => $periodTimes[1][0],
                        'end_time' => $periodTimes[1][1],
                    ]);
                    Schedule::firstOrCreate([
                        'class_subject_teacher_id' => $cst->id,
                        'day' => 'tuesday',
                        'period' => 3,
                        'start_time' => $periodTimes[3][0],
                        'end_time' => $periodTimes[3][1],
                    ]);
                    break;

                case ($classroomName == 'BacSci-A' && $subjectName == 'math' && $teacherName == 'Mohammed Math'):
                    Schedule::firstOrCreate([
                        'class_subject_teacher_id' => $cst->id,
                        'day' => 'monday',
                        'period' => 2,
                        'start_time' => $periodTimes[2][0],
                        'end_time' => $periodTimes[2][1],
                    ]);
                    break;

                case ($classroomName == '9A' && $subjectName == 'english' && $teacherName == 'Aisha English'):
                    Schedule::firstOrCreate([
                        'class_subject_teacher_id' => $cst->id,
                        'day' => 'monday',
                        'period' => 1,
                        'start_time' => $periodTimes[1][0],
                        'end_time' => $periodTimes[1][1],
                    ]);
                    Schedule::firstOrCreate([
                        'class_subject_teacher_id' => $cst->id,
                        'day' => 'wednesday',
                        'period' => 4,
                        'start_time' => $periodTimes[4][0],
                        'end_time' => $periodTimes[4][1],
                    ]);
                    break;

                case ($classroomName == 'BacLit-A' && $subjectName == 'english' && $teacherName == 'Aisha English'):
                    Schedule::firstOrCreate([
                        'class_subject_teacher_id' => $cst->id,
                        'day' => 'sunday',
                        'period' => 5,
                        'start_time' => $periodTimes[5][0],
                        'end_time' => $periodTimes[5][1],
                    ]);
                    break;

                case ($classroomName == 'BacSci-A' && $subjectName == 'physics' && $teacherName == 'Sami Physics'):
                    Schedule::firstOrCreate([
                        'class_subject_teacher_id' => $cst->id,
                        'day' => 'tuesday',
                        'period' => 4,
                        'start_time' => $periodTimes[4][0],
                        'end_time' => $periodTimes[4][1],
                    ]);
                    Schedule::firstOrCreate([
                        'class_subject_teacher_id' => $cst->id,
                        'day' => 'thursday',
                        'period' => 1,
                        'start_time' => $periodTimes[1][0],
                        'end_time' => $periodTimes[1][1],
                    ]);
                    break;


            }
        }
    }
}
