<?php

namespace Database\Seeders;

use App\Models\ClassTypeSubject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TeacherSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teacher = User::create([
            'name' => 'Mohammed Math',
            'phone_number' => '0955111222',
            'password' => Hash::make('password'),
            'gender' => 'male',
            'birth_date' => '1985-03-10',
        ]);

        $teacher->assignRole('teacher');

        $profile = $teacher->teacherProfile()->create([
            'department' => 'math',
        ]);

        $mathSubjects = ClassTypeSubject::wherehas('subject',fn($q) => $q->where('name','math'))->get();
        
        foreach($mathSubjects as $mathSubject){
            $profile->teachableSubjects()->create([
                'class_type_subject_id' => $mathSubject->id
            ]);
        }
    }
}
