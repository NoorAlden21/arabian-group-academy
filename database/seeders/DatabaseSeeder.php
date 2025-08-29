<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RoleSeeder::class,
            AdminSeeder::class,
            SubjectSeeder::class,
            ClassTypeSeeder::class,
            ClassTypeSubjectSeeder::class,
            TeacherSeeder::class,
            ClassroomSeeder::class,
            ClassSubjectTeacherSeeder::class,
            ScheduleSeeder::class,
            ParentSeeder::class,
            StudentSeeder::class,
            HomeworkSeeder::class
        ]);
    }
}
