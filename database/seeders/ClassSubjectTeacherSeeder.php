<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ClassSubjectTeacher;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassSubjectTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $classrooms = Classroom::all()->keyBy('name');
        $subjects = Subject::all()->keyBy('name');
        $teachers = User::role('teacher')->get()->keyBy('name');

        $assignments = [
            [
                'classroom' => '9A',
                'subject' => 'math',
                'teacher' => 'Mohammed Math'
            ],
            [
                'classroom' => 'BacSci-A',
                'subject' => 'math',
                'teacher' => 'Mohammed Math'
            ],
            [
                'classroom' => '9A',
                'subject' => 'english',
                'teacher' => 'Aisha English'
            ],
            [
                'classroom' => 'BacLit-A',
                'subject' => 'english',
                'teacher' => 'Aisha English'
            ],
            [
                'classroom' => 'BacSci-A',
                'subject' => 'physics',
                'teacher' => 'Sami Physics'
            ],
        ];

        foreach ($assignments as $assignment) {
            $classroom = $classrooms[$assignment['classroom']] ?? null;
            $subject = $subjects[$assignment['subject']] ?? null;
            $teacherUser = $teachers[$assignment['teacher']] ?? null;

            if ($classroom && $subject && $teacherUser) {
                $classTypeSubjectExists = $classroom->classType
                    ->classTypeSubjects()
                    ->where('subject_id', $subject->id)
                    ->exists();

                $teacherCanTeachSubjectInClassType = $teacherUser->teacherProfile
                    ->teachableSubjects()
                    ->whereHas('classTypeSubject', function ($query) use ($classroom, $subject) {
                        $query->where('class_type_id', $classroom->class_type_id)
                            ->where('subject_id', $subject->id);
                    })
                    ->exists();

                if ($classTypeSubjectExists && $teacherCanTeachSubjectInClassType) {
                    ClassSubjectTeacher::firstOrCreate([
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $teacherUser->id,
                    ]);
                } else {
                    $this->command->warn("Skipping assignment for Classroom: {$assignment['classroom']}, Subject: {$assignment['subject']}, Teacher: {$assignment['teacher']} - ClassTypeSubject or TeacherCanTeach mismatch.");
                }
            } else {
                $this->command->warn("Skipping assignment for Classroom: {$assignment['classroom']}, Subject: {$assignment['subject']}, Teacher: {$assignment['teacher']} - Missing required entity.");
            }
        }
    }
}
