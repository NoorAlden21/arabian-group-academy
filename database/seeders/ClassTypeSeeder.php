<?php

namespace Database\Seeders;

use App\Models\ClassType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ClassTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
        '9th Grade', 
        '10th Scientific', '10th Literature',
        '11th Scientific', '11th Literature',
        'Baccalaureate Scientific', 'Baccalaureate Literature'];

        foreach ($types as $type) {
            ClassType::create(['name' => $type]);
        }
    }
}
