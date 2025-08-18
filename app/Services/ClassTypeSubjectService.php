<?php
namespace App\Services;

use App\Models\ClassTypeSubject;

class ClassTypeSubjectService
{
    public function getGroupedByClassType(): array
    {
        $rows = ClassTypeSubject::query()
            ->with([
                'classType:id,name',
                'subject:id,name',
            ])
            ->select('id', 'class_type_id', 'subject_id')
            ->get();

        $grouped = $rows->groupBy(fn ($r) => $r->classType?->name ?? 'Unknown');

        $payload = $grouped->map(function ($items) {
            return $items
                ->sortBy(fn ($r) => $r->subject?->name)
                ->values()
                ->map(fn ($r) => [
                    'classtypesubject_id' => $r->id,
                    'subject_name'        => $r->subject?->name,
                ]);
        });

        return ['groups' => $payload->toArray()];
    }
}
