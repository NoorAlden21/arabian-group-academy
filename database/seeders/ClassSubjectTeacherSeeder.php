<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassSubjectTeacher;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\TeacherProfile;

class ClassSubjectTeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // تنظيف الجدول
        ClassSubjectTeacher::query()->delete();

        $classrooms = Classroom::all();
        $subjects   = Subject::all()->keyBy('name');
        $teachers   = TeacherProfile::all()->keyBy('department'); // المدرسين معرفين بالـ department

        if ($classrooms->isEmpty() || $subjects->isEmpty() || $teachers->isEmpty()) {
            $this->command->warn('⚠️ الجداول (classrooms, subjects, teachers) فارغة. اضف بياناتها أولاً.');
            return;
        }

        // خريطة: أي مادة يدرّسها أي قسم
        $subjectTeacherMap = [
            'math'       => 'math',
            'english'    => 'english',
            'physics'    => 'physics',
            'arabic'     => 'arabic',
            'chemistry'  => 'chemistry',
            'biology'    => 'biology',
            'history'    => 'history',
            'geography'  => 'geography',
            'philosophy' => 'philosophy',
            // المواد الأخرى مثل religion أو french أو general sciences أو social studies
            // ممكن تضيف لهم مدرسين حسب توفرهم
        ];

        foreach ($classrooms as $classroom) {
            foreach ($subjectTeacherMap as $subjectName => $teacherDept) {
                if (!isset($subjects[$subjectName]) || !isset($teachers[$teacherDept])) {
                    continue; // إذا المادة أو المدرس غير موجودين
                }

                ClassSubjectTeacher::create([
                    'classroom_id'       => $classroom->id,
                    'subject_id'         => $subjects[$subjectName]->id,
                    'teacher_profile_id' => $teachers[$teacherDept]->id,
                ]);
            }
        }
    }
}
