<?php

namespace App\Services;

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

        $user->load(['roles']);

        if ($user->hasRole('student')) {
            $user->load([
                'studentProfile.classroom.classType',
                'studentProfile.parent.user',
            ]);
        } elseif ($user->hasRole('teacher')) {
            $user->load([
                'teacherProfile.user', //,
                'teacherProfile.teachableSubjects.classTypeSubject.classType', //,,
                'teacherProfile.teachableSubjects.classTypeSubject.subject', //,,
            ]);
        } elseif ($user->hasRole('parent')) {
            $user->load([
                'parentProfile.children.classroom.classType',
            ]);
        }

        return $user;
    }


}
