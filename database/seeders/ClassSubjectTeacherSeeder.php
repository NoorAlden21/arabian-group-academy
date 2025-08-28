<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassSubjectTeacher;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherProfile;

class ClassSubjectTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // نجيب كل الصفوف والمواد والمدرسين
        $classrooms = Classroom::all();
        $subjects   = Subject::all();
        $teachers   = TeacherProfile::all();

        if ($classrooms->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('⚠️ الجداول (classrooms, subjects, teachers) فارغة. اضف بياناتها أولاً.');
            return;
        }

        // توزيع المواد على الصفوف
        foreach ($classrooms as $classroom) {
            // كل صف يأخذ 5-7 مواد
            $selectedSubjects = $subjects->random(rand(5, 7));

            foreach ($selectedSubjects as $subject) {
                // اختيار مدرس عشوائي للمادة
                $teacher = $teachers->random();

                // إنشاء الربط
                ClassSubjectTeacher::create([
                    'classroom_id' => $classroom->id,
                    'subject_id'   => $subject->id,
                    'teacher_profile_id'   => $teacher->id,
                ]);
            }
        }

    }
}
