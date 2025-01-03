<?php

declare(strict_types=1);

return [
    'dashboard_table' => [
        'experiments' => [
            'supervisor' => [
                'name' => 'Experiments',
                'description' => 'Total experiments under your supervision',
            ],
            'principal' => [
                'name' => 'My Experiments',
                'description' => 'Total of your experiments',
            ],
        ],
        'sessions' => [
            'supervisor' => [
                'name' => 'Sessions',
                'description' => 'Total number of sessions',
            ],
            'principal' => [
                'name' => 'Sessions',
                'description' => 'Total number of sessions',
            ],
        ],
        'users' => [
            'supervisor' => [
                'name' => 'Experimenters',
                'description' => 'Main experimenters',
            ],
            'principal' => [
                'name' => 'Experimenters',
                'description' => 'Secondary experimenters',
            ],
        ],
        'completions' => [
            'supervisor' => [
                'name' => 'Completion Rate',
                'description' => 'Sessions successfully completed',
            ],
            'principal' => [
                'name' => 'Completion Rate',
                'description' => 'Sessions successfully completed',
            ],
        ],
        'sessions_test' => [
            'supervisor' => [
                'name' => 'Test Sessions',
                'description' => 'Sessions currently in test',
            ],
            'principal' => [],
        ],
        'registrations' => [
            'supervisor' => [
                'name' => 'Registration Requests',
                'description' => 'Pending approval',
            ],
        ],
        'banned' => [
            'supervisor' => [
                'name' => 'Banned Users',
                'description' => 'Disabled accounts',
            ],
        ],
    ],
    'banned' => [
        'principal' => [
            'title' => 'Banned Account',
            'description' => 'Your account has been banned. If you think this is a mistake or wish to appeal the ban, 
            you can contact the administrator via the "Contact Administrator" page.',
        ],
        'secondary' => [
            'title' => 'Banned Account',
            'description' => 'The principal experimenter of your account has been banned. Your access is temporarily restricted. 
            Please contact the administrator via the "Contact Administrator" page for further information.',
        ],
    ],
    'access_requests' => [
        'heading' => 'Borrowed Experiments',
        'column' => [
            'name' => 'Experiment',
            'created_by' => 'Creator',
            'sessions_count' => 'Number of Participants',
            'created_at' => 'Requested on',
            'type' => [
                'label' => 'Access type',
                'options' => [
                    'results' => 'Results only',
                    'access' => 'Full collaboration',
                ]
            ],
            'status' => [
                'label' => 'Status',
                'options' => [
                    'pending' => 'Pending',
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                ]
            ],
        ],
    ],
    'experiment_table' => [
        'title' => 'My Experiments',
        'title_secondary_experimenter' => 'Assigned experiments',
        'title_default' => 'Experiments available',
        'column' => [
            'name' => 'Experiment Name',
            'type' => [
                'label' => 'Type',
                'options' => [
                    'image' => 'Image',
                    'sound' => 'Sound',
                    'image_sound' => 'Image and Sound',
                ]
            ],
            'status' => [
                'label' => 'Statut',
                'options' => [
                    'start' => 'Start',
                    'pause' => 'Pause',
                    'stop' => 'Stop',
                    'test' => 'Test',
                ],
            ],
            'sessions_count' => 'Number of Participants',
            'created_at' => 'Creation Date',
            'user_role' => 'Your Role',
        ],
        'roles' => [
            'supervisor' => 'Supervisor',
            'creator' => 'Creator',
            'manager' => 'Manager',
            'observer' => 'Observer',
        ],
        'actions' => [
            'statistics' => 'Statistics',
            'details' => 'Details',
            'edit' => 'Edit',
            'contact_creator' => 'Contact Creator',
            'results' => 'Results',

        ],
    ],
];
