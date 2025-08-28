<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\ClassSubjectTeacher;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $days = ['saturday','sunday','monday','tuesday','wednesday','thursday','friday'];

        // وقت البداية لكل فترة (فترة واحدة = 45 دقيقة)
        $periodDurationMinutes = 45;

        // افتراض عدد الفترات اليومية
        $periodsPerDay = 5;

        // وقت بدء أول حصة (مثلاً 08:00 صباحاً)
        $startOfDay = \Carbon\Carbon::createFromTime(8, 0);

        // الحصول على كل روابط الصف-المادة-المعلم بشكل عشوائي أو مرتب
        $relations = ClassSubjectTeacher::all();
        if ($relations->isEmpty()) {
            $this->command->warn('لا توجد بيانات في جدول class_subject_teachers. يرجى تشغيل Seeder الخاص به أولاً.');
            return;
        }

        // لتوزيع الحصص بطريقة منظمة، نحتاج انشاء جدول زمني مع مراعاة عدم تكرار الفترات لكل صف
        // سنستخدم مصفوفة لتتبع الفترات المحجوزة لكل class_subject_teacher_id في يوم معين لتجنب التضارب

        $scheduled = [];

        // توزيع الحصص عشوائياً على الأسبوع مع التأكد من عدم التكرار:
        foreach ($days as $day) {
            // في كل يوم نحجز الفترات حسب $periodsPerDay
            for ($period = 1; $period <= $periodsPerDay; $period++) {
                // نختار بشكل متسلسل عينة من العلاقات بحيث نوزعها
                $relation = $relations->random();

                // تحقق إذا تم حجز الحصة من قبل لنفس class_subject_teacher_id في نفس اليوم وفي نفس الفترة
                $key = $relation->id . '_' . $day . '_' . $period;
                if (isset($scheduled[$key])) {
                    continue;
                }

                // حساب وقت البدء والإنهاء للحصة
                $startTime = (clone $startOfDay)->addMinutes(($period - 1) * $periodDurationMinutes)->format('H:i:s');
                $endTime = (clone $startOfDay)->addMinutes($period * $periodDurationMinutes)->format('H:i:s');

                Schedule::create([
                    'class_subject_teacher_id' => $relation->id,
                    'day' => $day,
                    'period' => $period,
                    'start_time' => $startTime,
                    'end_time' => $endTime,
                ]);

                $scheduled[$key] = true;
            }
        }

    }
}
