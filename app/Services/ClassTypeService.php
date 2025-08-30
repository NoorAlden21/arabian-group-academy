<?php

namespace App\Services;

use App\Models\ClassType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClassTypeService
{
    /**
     * Get all class types.
     */
    public function getAllClassTypes(): Collection
    {
        return ClassType::all();
    }

    /**
     * Create a new class type.
     */
    public function createClassType(array $data): ClassType
    {
        return ClassType::create($data);
    }

    /**
     * Get a specific class type by ID.
     */
    public function getClassTypeById(int $id): ClassType
    {
        return ClassType::findOrFail($id);
    }

    /**
     * Update a specific class type.
     */
    public function updateClassType(int $id, array $data): ClassType
    {
        $classType = ClassType::findOrFail($id);
        $classType->update($data);
        return $classType;
    }

    /**
     * Delete a class type.
     */
    public function deleteClassType(int $id): bool
    {
        $classType = ClassType::findOrFail($id);
        return $classType->delete();
    }

    public function getSubjectsForClassType(int $id): Collection
    {
        $classType = ClassType::with([
            'subjects' => fn($q) => $q->select('subjects.id', 'subjects.name')
        ])->findOrFail($id);

        return $classType->subjects;
    }
}
