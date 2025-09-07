<?php

namespace App\Services;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ParentService
{

    protected ScheduleService $scheduleService;
    protected HomeworkService $homeworkService;

    public function __construct(ScheduleService $scheduleService, HomeworkService $homeworkService)
    {
        $this->scheduleService = $scheduleService;
        $this->homeworkService = $homeworkService;
    }
    public function getAllParents()
    {
        return User::role('parent')->get();
    }

    public function getParentById($id)
    {
        return User::role('parent')
            ->with(['parentProfile.children.user'])
            ->whereHas('parentProfile')
            ->find($id);
    }

    public function updateParent($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $parent = User::role('parent')->findOrFail($id);

            $parent->update([
                'name' => $data['name'] ?? $parent->name,
                'phone_number' => $data['phone_number'] ?? $parent->phone_number,
                //'gender' => $data['gender'] ?? $parent->gender,
                //'birth_date' => $data['birth_date'] ?? $parent->birth_date,
            ]);

            if ($parent->parentProfile) {
                $parent->parentProfile->update([
                    'occupation' => $data['occupation'] ?? $parent->parentProfile->occupation
                ]);
            }

            return $parent;
        });
    }

    public function deleteParent($id)
    {
        return DB::transaction(function () use ($id) {
            $parent = User::role('parent')->with('parentProfile')->findOrFail($id);

            $parent->parentProfile->delete();
            $parent->delete();

            if ($parent && $parent->children()->count() === 0) {
                $parent->delete();
            }

            return true;
        });
    }

    public function restoreParent($id)
    {
        $parent = User::onlyTrashed()->role('parent')->where('id', $id)->firstOrFail();
        $parent->restore();

        if ($parent->parentProfile && method_exists($parent->parentProfile, 'restore')) {
            $parent->parentProfile->restore();
        }

        return $parent;
    }

    public function forceDeleteparent($id)
    {

        $parent = User::onlyTrashed()->role('parent')->where('id', $id)->firstOrFail();

        if ($parent && $parent->children()->count() === 0) {
            $parent->children()->forceDelete();
        }

        if ($parent->parentProfile) {
            $parent->parentProfile()->forceDelete();
        }


        $parent->forceDelete();


        return true;
    }

    public function searchParents($filters)
    {

        $query = User::role('parent')->with('parentProfile');

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['phone_number'])) {
            $query->where('phone_number', 'like', '%' . $filters['phone_number'] . '%');
        }

        return $query->paginate(10);
    }
    //-------------mobile
    public function getParentChildren(User $parentUser): Collection
    {
        if (!$parentUser->hasRole('parent') || !$parentUser->parentProfile) {
            return collect();
        }

        return $parentUser->parentProfile->children()->with('user', 'classroom')->get();
    }

    /**
     * Get the schedule for a specific child.
     */
    public function getChildSchedule(User $parentUser, int $childId): Collection
    {
        $child = $this->getChildIfAuthorized($parentUser, $childId);

        if (!$child) {
            throw new ModelNotFoundException('Child not found or not authorized.');
        }

        return $this->scheduleService->getSchedulesByClassroomId($child->classroom_id);
    }

    /**
     * Get the homework for a specific child.
     */
    public function getChildHomework(User $parentUser, int $childId): Collection
    {
        $child = $this->getChildIfAuthorized($parentUser, $childId);

        if (!$child) {
            throw new ModelNotFoundException('Child not found or not authorized.');
        }

        return $this->homeworkService->getHomeworksByClassroomId($child->classroom_id);
    }

    /**
     * Get the grades for a specific child.
     */
    public function getChildGrades(User $parentUser, int $childId): Collection
    {
        $child = $this->getChildIfAuthorized($parentUser, $childId);

        if (!$child) {
            return collect();
        }

        return $child->grades()->with('subject')->get();
    }

    /**
     * Helper method to check if the parent is authorized to view the child's data.
     */
    protected function getChildIfAuthorized(User $parentUser, int $childId): ?StudentProfile
    {
        if (!$parentUser->hasRole('parent') || !$parentUser->parentProfile) {
            return null;
        }

        $child = $parentUser->parentProfile->children()->where('id', $childId)->first();

        return $child;
    }
}
