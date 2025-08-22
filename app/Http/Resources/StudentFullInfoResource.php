<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentFullInfoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profile   = $this->studentProfile;
        $classroom = $profile?->classroom;
        $parentProfile = $profile?->parentProfile;

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'phone_number' => $this->phone_number,
            'gender'       => $this->gender,
            'birthDate'    => $this->birth_date,

            'profile' => [
                'level'          => $profile->level ?? null,
                'enrollmentYear' => $profile->enrollment_year ?? null,
                'isAssigned'     => filled($profile?->classroom_id),

                'classroom'      => $this->when(
                    $classroom,
                    fn () => [
                        'id'        => $classroom->id,
                        'name'      => $classroom->name,
                        'year'      => $classroom->year,
                        'classType' => $this->when(
                            $classroom->classType ?? null,
                            fn () => [
                                'id'   => $classroom->classType->id,
                                'name' => $classroom->classType->name,
                            ]
                        ),
                    ]
                ),
                'parent' => $this->when($parentProfile, fn () => [
                'id'           => $parentProfile->id,
                'name'         => data_get($parentProfile, 'user.name'),
                'phone_number' => data_get($parentProfile, 'user.phone_number'),
            ]),
            ],
            

            // 'parent' => [
            //     'id'           => $parentProfile->id ?? null,
            //     'name'         => data_get($parentProfile, 'user.name'),
            //     'phone_number' => data_get($parentProfile, 'user.phone_number'),
            // ],
        ];
    }
}
