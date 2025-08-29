<?php

namespace App\Services;

use App\Http\Resources\ParentFullInfoResource;
use App\Http\Resources\StudentFullInfoResource;
use App\Http\Resources\TeacherFullResource;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserProfileService
{
    /**
     * Get the authenticated user's profile with all relevant details.
     *
     * @param User $user
     * @return User|null
     */
    public function getAuthenticatedUserProfile(User $user)
    {
        $user->load('roles');

        if ($user->hasRole('student')) {
            $user->load([
                'studentProfile.classroom.classType',
                'studentProfile.parentProfile.user',
            ]);

            return new StudentFullInfoResource($user);
        }

        if ($user->hasRole('teacher')) {
            $user->load([
                'teacherProfile.user',
                'teacherProfile.teachableSubjects.classTypeSubject.classType',
                'teacherProfile.teachableSubjects.classTypeSubject.subject',
            ]);

            return new TeacherFullResource($user);
        }

        if ($user->hasRole('parent')) {
            $user->load([
                'parentProfile.user',
                'parentProfile.children.user',
                'parentProfile.children.classroom.classType',
            ]);


            return new ParentFullInfoResource($user->parentProfile);
        }

        throw new \Exception('No valid role assigned to this user.');
    }
}
