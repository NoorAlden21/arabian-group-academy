<?php

namespace Database\Seeders;

use App\Models\ClassType;
use Illuminate\Database\Seeder;

class ClassTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['en' => '9th Grade',                'ar' => 'الصف التاسع'],
            ['en' => '10th Scientific',          'ar' => 'العاشر علمي'],
            ['en' => '10th Literature',          'ar' => 'العاشر أدبي'],
            ['en' => '11th Scientific',          'ar' => 'الحادي عشر علمي'],
            ['en' => '11th Literature',          'ar' => 'الحادي عشر أدبي'],
            ['en' => 'Baccalaureate Scientific', 'ar' => 'البكالوريا علمي'],
            ['en' => 'Baccalaureate Literature', 'ar' => 'البكالوريا أدبي'],
        ];

        foreach ($types as $t) {
            ClassType::create([
                'name'    => $t['en'],
                'name_ar' => $t['ar'],
            ]);
        }
    }
}
