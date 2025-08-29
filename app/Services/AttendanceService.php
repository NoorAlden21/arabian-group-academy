<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\StudentAbsence;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function getMeta(): array
    {
        $classrooms = Classroom::query()
            ->select('id','name','year','class_type_id')
            ->with(['classType:id,name'])
            ->orderBy('year','desc')
            ->orderBy('name')
            ->get();

        return [
            'classrooms' => $classrooms,
            'today'      => now()->toDateString(),
        ];
    }

    /**
     * @param array{classroom_id:int,date:string,period:int,entries:array<array{student_profile_id:int,status:string}>} $payload
     */
    public function storeStudentAbsencesBulk(array $payload): array
    {
        $date    = Carbon::createFromFormat('Y-m-d', $payload['date'])->startOfDay();
        $period  = (int) $payload['period'];
        $entries = collect($payload['entries'])->unique('student_profile_id')->values();

        if ($entries->isEmpty()) {
            return [
                'date'          => $date->toDateString(),
                'period'        => $period,
                'inserted_count'=> 0,
                'absent_count'  => 0,
                'late_count'    => 0,
            ];
        }

        $now = now();
        $rows = $entries->map(function ($e) use ($date, $period, $now) {
            return [
                'student_profile_id' => (int) $e['student_profile_id'],
                'period'             => $period,
                'absent_at'          => $date->copy()->addMinutes(1),
                'status'             => $e['status'],
                'created_at'         => $now,
                'updated_at'         => $now,
            ];
        })->all();
        DB::transaction(function () use ($rows) {
            StudentAbsence::upsert(
                $rows,
                ['student_profile_id','absent_date','period'],
                ['status','absent_at','updated_at',]
            );
        });

        $absentCount = $entries->where('status','absent')->count();
        $lateCount   = $entries->where('status','late')->count();

        return [
            'date'           => $date->toDateString(),
            'period'         => $period,
            'inserted_count' => count($rows),
            'absent_count'   => $absentCount,
            'late_count'     => $lateCount,
        ];
    }
}
