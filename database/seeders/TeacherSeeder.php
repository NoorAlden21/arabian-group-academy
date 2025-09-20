<?php

namespace Database\Seeders;

use App\Models\ClassTypeSubject;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        Log::channel('teacher_tokens')->info("new logs:");

        $teachersData = [
            ['name' => 'Mohammed Math', 'phone_number' => '0955111222', 'gender' => 'male',   'birth_date' => '1985-03-10', 'department' => 'math'],
            ['name' => 'Aisha English', 'phone_number' => '0955333444', 'gender' => 'female', 'birth_date' => '1990-07-15', 'department' => 'english'],
            ['name' => 'Sami Physics',  'phone_number' => '0955666777', 'gender' => 'male',   'birth_date' => '1982-11-20', 'department' => 'physics'],
            ['name' => 'Layla Arabic',  'phone_number' => '0955888999', 'gender' => 'female', 'birth_date' => '1988-05-25', 'department' => 'arabic'],
            ['name' => 'Omar Chemistry', 'phone_number' => '0955222111', 'gender' => 'male',   'birth_date' => '1991-09-01', 'department' => 'chemistry'],
            ['name' => 'Sara Biology',  'phone_number' => '0955444333', 'gender' => 'female', 'birth_date' => '1987-04-30', 'department' => 'biology'],
            ['name' => 'Khaled History', 'phone_number' => '0955777666', 'gender' => 'male',   'birth_date' => '1984-08-12', 'department' => 'history'],
            ['name' => 'Huda Geography', 'phone_number' => '0955999888', 'gender' => 'female', 'birth_date' => '1989-12-05', 'department' => 'geography'],
            ['name' => 'Ali Philosophy', 'phone_number' => '0955000111', 'gender' => 'male',   'birth_date' => '1981-02-18', 'department' => 'philosophy'],
        ];

        $subjectSlugToId = Subject::select('id', 'name')
            ->get()
            ->mapWithKeys(fn ($s) => [Str::slug($s->name) => $s->id]);

        $aliases = ['math' => 'mathematics'];

        foreach ($teachersData as $data) {
            $user = User::create([
                'name'         => $data['name'],
                'phone_number' => $data['phone_number'],
                'password'     => Hash::make('password'),
                'gender'       => $data['gender'],
                'birth_date'   => $data['birth_date'],
            ]);

            $user->assignRole('teacher');

            $profile = $user->teacherProfile()->create([
                'department' => $data['department'],
            ]);

            $depSlug   = $aliases[Str::slug($data['department'])] ?? Str::slug($data['department']);
            $subjectId = $subjectSlugToId[$depSlug] ?? null;
            if (!$subjectId) {
                $this->command?->warn("Unknown subject for department: {$data['department']} (slug: {$depSlug})");
                continue;
            }

            $ctsRows = ClassTypeSubject::where('subject_id', $subjectId)->get(['id']);
            foreach ($ctsRows as $cts) {
                $profile->teachableSubjects()->create([
                    'class_type_subject_id' => $cts->id
                ]);
            }

            $token = $user->createToken('teacher')->plainTextToken;
            Log::channel('teacher_tokens')->info("Teacher ID: {$user->id} Token: {$token}");
        }
    }
}
