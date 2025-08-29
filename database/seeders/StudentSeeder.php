<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::channel('student_tokens')->info("new logs:");

        $classrooms = Classroom::all();
        $parents = ParentProfile::all();

        if ($classrooms->isEmpty() || $parents->isEmpty()) {
            $this->command->warn('⚠️ Classrooms or ParentProfiles tables are empty. Please run their seeders first.');
            return;
        }

        // Define a list of students with data
        $studentsData = [
            // Students for 9A
            ['name' => 'Ahmed Student', 'phone_number' => '0955000101', 'gender' => 'male', 'birth_date' => '2009-05-12', 'classroom_name' => '9A', 'level' => '9th Grade', 'gpa' => 3.75, 'previous_status' => null],
            ['name' => 'Nour Student', 'phone_number' => '0955000102', 'gender' => 'female', 'birth_date' => '2010-03-15', 'classroom_name' => '9A', 'level' => '9th Grade', 'gpa' => 3.80, 'previous_status' => null],
            ['name' => 'Kareem Student', 'phone_number' => '0955000103', 'gender' => 'male', 'birth_date' => '2009-11-20', 'classroom_name' => '9A', 'level' => '9th Grade', 'gpa' => 3.50, 'previous_status' => null],

            // Students for 9B
            ['name' => 'Hala Student', 'phone_number' => '0955000104', 'gender' => 'female', 'birth_date' => '2011-07-08', 'classroom_name' => '9B', 'level' => '9th Grade', 'gpa' => 3.90, 'previous_status' => null],
            ['name' => 'Omar Student', 'phone_number' => '0955000105', 'gender' => 'male', 'birth_date' => '2009-12-25', 'classroom_name' => '9B', 'level' => '9th Grade', 'gpa' => 3.25, 'previous_status' => null],
            ['name' => 'Lina Student', 'phone_number' => '0955000106', 'gender' => 'female', 'birth_date' => '2010-08-14', 'classroom_name' => '9B', 'level' => '9th Grade', 'gpa' => 3.60, 'previous_status' => null],

            // Students for BacSci-A
            ['name' => 'Ali BacSci', 'phone_number' => '0955000107', 'gender' => 'male', 'birth_date' => '2007-04-30', 'classroom_name' => 'BacSci-A', 'level' => 'Baccalaureate', 'gpa' => 3.40, 'previous_status' => 'qualifying'],
            ['name' => 'Mona BacSci', 'phone_number' => '0955000108', 'gender' => 'female', 'birth_date' => '2008-01-18', 'classroom_name' => 'BacSci-A', 'level' => 'Baccalaureate', 'gpa' => 3.85, 'previous_status' => null],
            ['name' => 'Samer BacSci', 'phone_number' => '0955000109', 'gender' => 'male', 'birth_date' => '2007-09-05', 'classroom_name' => 'BacSci-A', 'level' => 'Baccalaureate', 'gpa' => 3.70, 'previous_status' => null],

            // Students for BacLit-A
            ['name' => 'Huda BacLit', 'phone_number' => '0955000110', 'gender' => 'female', 'birth_date' => '2008-06-22', 'classroom_name' => 'BacLit-A', 'level' => 'Baccalaureate', 'gpa' => 3.55, 'previous_status' => null],
            ['name' => 'Rami BacLit', 'phone_number' => '0955000111', 'gender' => 'male', 'birth_date' => '2007-02-11', 'classroom_name' => 'BacLit-A', 'level' => 'Baccalaureate', 'gpa' => 3.45, 'previous_status' => null],
            ['name' => 'Dina BacLit', 'phone_number' => '0955000112', 'gender' => 'female', 'birth_date' => '2008-10-09', 'classroom_name' => 'BacLit-A', 'level' => 'Baccalaureate', 'gpa' => 3.95, 'previous_status' => null],
        ];

        foreach ($studentsData as $index => $data) {
            $student = User::create([
                'name' => $data['name'],
                'phone_number' => $data['phone_number'],
                'password' => Hash::make('password'),
                'gender' => $data['gender'],
                'birth_date' => $data['birth_date']
            ]);
            $student->assignRole('student');

            // Assign a random parent
            $parent = $parents->random();

            // Get the classroom_id based on the classroom_name
            $classroom = $classrooms->firstWhere('name', $data['classroom_name']);

            // Create the student profile
            $student->studentProfile()->create([
                'parent_id' => $parent->id, // Parent ID from ParentSeeder
                'classroom_id' => $classroom->id,
                'level' => $data['level'],
                'previous_status' => $data['previous_status'],
                'gpa' => $data['gpa'],
                'enrollment_year' => date('Y'),
            ]);

            // Create and log token
            $token = $student->createToken('student')->plainTextToken;
            Log::channel('student_tokens')->info("Student ID: {$student->id}, Name: {$student->name}, Classroom: {$classroom->name}, Parent: " . $parent->name . ", Token: {$token}");
        }

        Log::channel('student_tokens')->info("Total students created: " . count($studentsData));
    }
}
