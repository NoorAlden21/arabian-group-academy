<?php

return [
    // ... keep your existing keys

    'classrooms' => [
        'index' => [
            'failed' => 'Failed to fetch classrooms.',
        ],
        'create' => [
            'success' => 'Classroom created successfully.',
            'failed'  => 'Failed to create classroom.',
        ],
        'show' => [
            'not_found' => 'Classroom not found.',
            'failed'    => 'Failed to fetch classroom details.',
        ],
        'update' => [
            'success' => 'Classroom updated successfully.',
            'failed'  => 'Failed to update classroom.',
        ],
        'delete' => [
            'success'   => 'Classroom deleted successfully.',
            'not_found' => 'Classroom not found.',
            'failed'    => 'Failed to delete classroom.',
        ],
        'restore' => [
            'success'   => 'Classroom restored successfully.',
            'not_found' => 'Deleted classroom not found.',
            'failed'    => 'Failed to restore classroom.',
        ],
        'force_delete' => [
            'success'   => 'Classroom permanently deleted.',
            'not_found' => 'Classroom not found for permanent deletion.',
            'failed'    => 'Failed to permanently delete classroom.',
        ],
        'search' => [
            'failed' => 'Failed to search classrooms.',
        ],
        'trashed' => [
            'failed' => 'Failed to fetch trashed classrooms.',
        ],
        'fetch_teachers' => [
            'success' => 'Eligible teachers fetched successfully.',
            'failed'  => 'Failed to fetch teachers.',
        ],
        'assign_teachers' => [
            'success' => 'Teachers assigned successfully.',
            'failed'  => 'Failed to assign teachers.',
        ],
    ],

    'classroom_students' => [
        'index' => [
            'failed' => 'Failed to fetch classroom students.',
        ],
        'candidates' => [
            'failed' => 'Failed to load candidates.',
        ],
        'assign' => [
            'failed' => 'Failed to assign students.',
        ],
    ],
];
