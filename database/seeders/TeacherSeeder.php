<?php

namespace Database\Seeders;

use App\Models\ClassTypeSubject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
      public function run(): void
    {

        Log::channel('teacher_tokens')->info("new logs:");
        $teacher1 = User::create([
            'name' => 'Mohammed Math',
            'phone_number' => '0955111222',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '1985-03-10',
        ]);
        $teacher1->assignRole('teacher');
        $profile1 = $teacher1->teacherProfile()->create([
            'department' => 'math',
        ]);
        $mathSubjects = ClassTypeSubject::whereHas('subject', fn($q) => $q->where('name', 'math'))->get();
        foreach ($mathSubjects as $mathSubject) {
            $profile1->teachableSubjects()->create([
                'class_type_subject_id' => $mathSubject->id
            ]);
        }
        $token1 = $teacher1->createToken('teacher')->plainTextToken;
        Log::channel('teacher_tokens')->info("Teacher ID: {$teacher1->id} Token: {$token1}");

        $teacher2 = User::create([
            'name' => 'Aisha English',
            'phone_number' => '0955333444',
            'password' => Hash::make('password'),
            'gender' => 'female',
            'birth_date' => '1990-07-15',
        ]);
        $teacher2->assignRole('teacher');
        $profile2 = $teacher2->teacherProfile()->create([
            'department' => 'english',
        ]);
        $englishSubjects = ClassTypeSubject::whereHas('subject', fn($q) => $q->where('name', 'english'))->get();
        foreach ($englishSubjects as $englishSubject) {
            $profile2->teachableSubjects()->create([
                'class_type_subject_id' => $englishSubject->id
            ]);
        }
        $token2 = $teacher2->createToken('teacher')->plainTextToken;
        Log::channel('teacher_tokens')->info("Teacher ID: {$teacher2->id} Token: {$token2}");

        $teacher3 = User::create([
            'name' => 'Sami Physics',
            'phone_number' => '0955666777',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '1982-11-20',
        ]);
        $teacher3->assignRole('teacher');
        $profile3 = $teacher3->teacherProfile()->create([
            'department' => 'physics',
        ]);
        $physicsSubjects = ClassTypeSubject::whereHas('subject', fn($q) => $q->where('name', 'physics'))->get();
        foreach ($physicsSubjects as $physicsSubject) {
            $profile3->teachableSubjects()->create([
                'class_type_subject_id' => $physicsSubject->id
            ]);
        }
        $token3 = $teacher3->createToken('teacher')->plainTextToken;
        Log::channel('teacher_tokens')->info("Teacher ID: {$teacher3->id} Token: {$token3}");
    }
}
