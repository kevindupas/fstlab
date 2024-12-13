<?php

return [
    'resources' => [
        'experiment-access-request' => [
            'label' => 'Access Request',
            'plural' => 'Access Requests',
            'navigation_label' => 'Access Requests',
            'form' => [
                'status' => [
                    'label' => 'Status',
                    'options' => [
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]
                ],
                'response_message' => [
                    'label' => 'Response Message',
                    'helper_text' => 'Please explain the reason for rejection',
                ],
                'request_message' => [
                    'label' => 'Request Message',
                ],
                'experiment' => [
                    'label' => 'Experiment',
                ],
                'user' => [
                    'label' => 'Requester',
                ],
            ],
            'table' => [
                'columns' => [
                    'experiment' => 'Experiment',
                    'user' => 'Requester',
                    'type' => 'Type',
                    'type_options' => [
                        'access' => 'Experiment Access',
                        'results' => 'Results Access',
                    ],
                    'status' => 'Status',
                    'created_at' => 'Request Date',
                ],
            ],
        ],
    ],
];
