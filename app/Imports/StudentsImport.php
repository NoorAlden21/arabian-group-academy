<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\User;
use App\Services\StudentService;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class StudentsImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    protected StudentService $students;
    protected bool $dryRun;

    public int $processed = 0;
    public int $created   = 0;
    public int $skipped   = 0;

    public function __construct(StudentService $students, bool $dryRun = false)
    {
        $this->students = $students;
        $this->dryRun = $dryRun;
    }

    public function onRow(Row $row)
    {
        $this->processed++;
        $r = $row->toArray();

        $classroomId = $this->resolveClassroomId(
            Arr::get($r, 'classroom_id'),
            Arr::get($r, 'classroom')
        );

        $birthDate = $this->parseDate(Arr::get($r, 'birth_date'));

        $parentPassword = $this->ensurePassword(Arr::get($r, 'parent_password'), Arr::get($r, 'parent_phone_number'));
        $studentPassword = $this->ensurePassword(Arr::get($r, 'password'), Arr::get($r, 'phone_number'));

        $studentPhone = Arr::get($r, 'phone_number');
        if ($this->studentPhoneExists($studentPhone)) {
            $this->skipped++;
            return;
        }

        $payload = [
            'parent_name'          => Arr::get($r, 'parent_name'),
            'parent_phone_number'  => Arr::get($r, 'parent_phone_number'),
            'parent_password'      => $parentPassword,
            'parent_occupation'    => Arr::get($r, 'parent_occupation'),

            // Student
            'name'         => Arr::get($r, 'name'),
            'phone_number' => $studentPhone,
            'password'     => $studentPassword,
            'gender'       => Arr::get($r, 'gender'),
            'birth_date'   => $birthDate,

            'level'           => Arr::get($r, 'level'),
            'enrollment_year' => Arr::get($r, 'enrollment_year'),
            'classroom_id'    => $classroomId,
        ];

        if ($this->dryRun) {
            $this->created++;
            return;
        }

        $createdUser = $this->students->createStudent($payload);

        if ($createdUser) {
            $this->created++;
        } else {
            $this->skipped++;
        }
    }
    public function rules(): array
    {
        return [
            // Parent
            'parent_name'         => ['required', 'string', 'max:191'],
            'parent_phone_number' => ['required', 'string', 'size:10'],
            'parent_password'     => ['nullable', 'string', 'min:4'],
            'parent_occupation'   => ['nullable', 'string', 'max:191'],

            // Student
            'name'         => ['required', 'string', 'max:191'],
            'phone_number' => ['required', 'string', 'size:10'],
            'password'     => ['nullable', 'string', 'min:4'],
            'gender'       => ['nullable', 'in:male,female'],
            'birth_date'   => ['nullable'],

            'level'           => ['required', 'string', 'max:191'],
            'enrollment_year' => ['nullable', 'string', 'max:191'],

            'classroom_id'    => ['required_without:classroom', 'nullable', 'integer', 'exists:classrooms,id'],
            'classroom'       => ['required_without:classroom_id', 'nullable', 'string', 'max:191'],
        ];
    }

    protected function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        try {
            return Carbon::parse((string)$value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function ensurePassword($value, $fallbackPhone): string
    {
        if (is_string($value) && strlen($value) >= 4) {
            return $value;
        }
        if (is_string($fallbackPhone) && strlen($fallbackPhone) >= 4) {
            return $fallbackPhone;
        }
        return 'Std' . substr((string)now()->getTimestamp(), -6);
    }

    protected function resolveClassroomId($id, $name)
    {
        if ($id) {
            return (int)$id;
        }
        if ($name) {
            return Classroom::where('name', $name)->value('id');
        }
        return null;
    }

    protected function studentPhoneExists(?string $phone): bool
    {
        if (!$phone) return false;
        return User::role('student')->where('phone_number', $phone)->exists();
    }
}
