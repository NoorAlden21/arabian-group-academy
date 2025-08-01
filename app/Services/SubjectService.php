<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SubjectService
{
    public function getAllSubjects(): Collection
    {
        return Subject::all();
    }

    public function createSubject(array $data): Subject
    {
        return Subject::create($data);
    }

    public function getSubjectById(int $id): Subject
    {
        return Subject::findOrFail($id);
    }

    public function updateSubject(int $id, array $data): Subject
    {
        $subject = Subject::findOrFail($id);
        $subject->update($data);
        return $subject;
    }

    public function deleteSubject(int $id): bool
    {
        $subject = Subject::findOrFail($id);
        return $subject->delete();
    }
}
