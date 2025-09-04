<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Homework;
use App\Models\ClassSubjectTeacher;
use Carbon\Carbon;

class HomeworkSeeder extends Seeder
{
    public function run(): void
    {

        $classSubjectTeachers = ClassSubjectTeacher::with(['subject', 'teacher.user'])->get();

        if ($classSubjectTeachers->isEmpty()) {
            $this->command->warn('⚠️ جدول class_subject_teachers فارغ. شغّل seeder الخاص به أولاً.');
            return;
        }

        // قوالب للوظائف حسب قسم المدرس
        $homeworkTemplates = [
            'math'       => ['حل 10 مسائل من الدرس', 'إعداد تقرير قصير عن نظرية فيثاغورس'],
            'english'    => ['كتابة فقرة قصيرة عن نفسك', 'حفظ 15 كلمة جديدة واستخدامها في جمل'],
            'physics'    => ['حل مسائل عن قوانين نيوتن', 'تجربة بسيطة وكتابة تقرير عنها'],
            'arabic'     => ['إعراب الجملة التالية', 'كتابة موضوع تعبير عن العلم'],
            'chemistry'  => ['موازنة 5 معادلات كيميائية', 'حل مسائل عن الجدول الدوري'],
            'biology'    => ['رسم مخطط لجهاز الهضم', 'كتابة تقرير عن عملية التنفس'],
            'history'    => ['قراءة الفصل الرابع والإجابة عن الأسئلة', 'إعداد ملخص عن ثورة 1919'],
            'geography'  => ['تحديد 5 أنهار على الخريطة', 'كتابة تقرير عن المناخ في منطقتك'],
            'philosophy' => ['كتابة مقالة قصيرة عن الحرية', 'مناقشة فكرة سقراط عن المعرفة'],
        ];

        foreach ($classSubjectTeachers as $cst) {
            $department = strtolower($cst->teacher->department ?? 'general');

            // عدد الوظائف (0 أو 1 أو 2)
            $numberOfHomeworks = rand(0, 2);

            for ($i = 1; $i <= $numberOfHomeworks; $i++) {
                $subjectName = $cst->subject->name;
                $teacherName = $cst->teacher->user->name;

                // اختيار واجب مناسب للمادة
                $templates = $homeworkTemplates[$department] ?? ['حل تمارين من الكتاب المدرسي'];
                $task = $templates[array_rand($templates)];

                $title = "واجب مادة {$subjectName}";
                $description = "المعلم {$teacherName} يطلب من الطلاب: {$task}.";

                $dueDate = Carbon::now()->addDays(rand(5, 14))->setTime(23, 59);

                Homework::create([
                    'class_subject_teacher_id' => $cst->id,
                    'title'       => $title,
                    'description' => $description,
                    'due_time'    => $dueDate,
                ]);
            }
        }

    }
}
