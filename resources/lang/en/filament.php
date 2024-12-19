<?php

return [
    'widgets' => [
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
                'type' => 'Type',
                'results' => 'Results',
                'pass' => 'Pass',
                'status' => 'Status',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'created_at' => 'Requested on',
                'statistics' => 'Statistics',
                'sessions' => 'Sessions',
                'actions' => 'Actions',
            ],
        ],
        'experiment_table' => [
            'column' => [
                'creator' => 'Created by',
                'name' => 'Experiment Name',
                'status' => 'Status',
                'start' => 'Started',
                'pause' => 'Paused',
                'stop' => 'Stopped',
                'test' => 'In Test',
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
            ],
        ],
    ],
    'pages' => [
        'auth' => [
            'register' => [
                'name' => 'Name',
                'email' => [
                    'label' => 'Email',
                    'unique' => 'This email is already in use',
                ],
                'university' => 'University',
                'registration_reason' => [
                    'label' => 'Why do you want to register?',
                    'helpMessage' => 'Your description must be at least 50 characters long',
                ],
                'orcid' => 'ORCID Number',
                'password' => [
                    'label' => 'Password',
                    'helpMessage' => 'Your password must be at least 8 characters long',
                ],
                'confirm_password' => [
                    'label' => 'Confirm Password',
                    'helpMessage' => 'Passwords do not match',
                ],
            ],
        ],
        'admin_contact' => [
            'title' => 'Contact Administrator',
            'form' => [
                'subject' => 'Subject',
                'options' => [
                    'unban' => 'Unban Request',
                    'principal_banned' => 'Principal Experimenter Banned',
                    'question' => 'General Question',
                    'other' => 'Other',
                ],
                'message' => [
                    'label' => 'Request Description',
                    'placeholder' => 'Enter your message',
                ],
                'submit' => 'Send Message',
                'success' => 'Message sent successfully',
            ],
        ],
        'user_contact' => [
            'title' => 'Contact a User',
            'form' => [
                'user' => 'User',
                'experiment' => 'Related Experiment (optional)',
                'message' => [
                    'label' => 'Message',
                    'placeholder' => 'Enter your message',
                ],
                'submit' => 'Send Message',
                'success' => 'Message sent successfully',
            ],
        ],
        'experiment_list' => [
            'title' => 'Experiment List',
            'column' => [
                'created_by' => 'Created by',
                'name' => 'Experiment Name',
                'type' => 'Type',
                'status' => 'Status',
                'start' => 'Started',
                'pause' => 'Paused',
                'stop' => 'Stopped',
                'test' => 'In Test',
                'none' => 'None',
                'sound' => 'Sound',
                'image' => 'Image',
                'image_sound' => 'Image and Sound',
                'sessions_count' => 'Number of Sessions',
                'created_at' => 'Created on',
                'action' => 'View Experiment',
            ],
        ],
        'experiment_details' => [
            'title' => 'Experiment Details',
            'section_experiment' => [
                'heading' => 'Experiment Information',
                'description' => 'Details and configuration of the experiment',
                'column' => [
                    'name' => 'Name',
                    'created_by' => 'Created by',
                    'created_at' => 'Created on',
                    'type' => 'Type',
                    'status' => 'Status',
                    'start' => 'Started',
                    'pause' => 'Paused',
                    'stop' => 'Stopped',
                    'test' => 'In Test',
                    'none' => 'None',
                    'sound' => 'Sound',
                    'image' => 'Image',
                    'link' => 'Link',
                    'doi' => 'DOI',
                ],
            ],
            'section_description' => [
                'heading' => 'Description',
                'description' => 'Detailed description of the experiment',
            ],
        ],
        'experiments_sessions' => [],
        'experiments_sessions_details' => [],
        'experiments_sessions_export' => [],
        'experiments_statistics' => [],
    ],
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
        'my_experiment' => [
            'navigation_label' => 'My Experiments',
            'navigation_group' => 'Experiments',
            'label' => 'Experiment',
            'plural' => 'My Experiments',
            'section_base' => [
                'heading' => 'Basic Configuration',
                'description' => 'Main settings for your experiment',
            ],
            'general_section' => [
                'heading' => 'General Information',
                'description' => 'Define the main characteristics of your experiment',
            ],
            'apparence_section' => [
                'heading' => 'Appearance',
                'description' => 'Customize the appearance of the buttons in your experiment',
            ],
            'section_description' => [
                'heading' => 'Content',
                'description' => 'Describe your experiment and provide necessary instructions',
            ],
            'section_media' => [
                'heading' => 'Media',
                'description' => 'Add your media files (maximum size of 20MB per file)',
            ],
            'section_documents' => [
                'heading' => 'Additional Documents',
                'description' => 'Add supplementary documents related to your experiment',
            ],
            'form' => [
                'doi' => 'DOI',
                'doi_placeholder' => 'Enter the DOI of your experiment',
                'doi_helper' => 'The Digital Object Identifier (DOI) is a stable, unique identification mechanism for your experiment.',
                'howitworks' => 'How it works',
                'howitworks_helper' => 'If enabled, the experiment in "test" mode will be visible on the How it Works? page. Automatically disabled if the status changes.',
                'status' => [
                    'label' => 'Start the experiment?',
                    'helper_text' => 'Use "test" mode to try without saving results. Use "start" mode to officially launch the experiment.',
                    'options' => [
                        'stop' => 'Do not make accessible',
                        'start' => 'Make accessible',
                        'test' => 'Make accessible in test mode',
                    ]
                ],
                'link' => 'Experiment link',
                'link_helper' => 'Unique link to access your experiment. Click to copy.',
                'link_copied' => 'Copied to clipboard',
                'name' => 'Name',
                'name_helper' => 'Provide a unique and descriptive name for your experiment',
                'type' => [
                    'label' => 'Media type',
                    'helper_text' => 'Choose the type of media for your experiment. This will determine the types of files you can upload.',
                    'options' => [
                        'image' => 'Images only',
                        'sound' => 'Sounds only',
                        'image_sound' => 'Images and Sounds',
                    ]
                ],
                'button_size' => [
                    'label' => 'Button size',
                    'helper_text' => 'The minimum recommended size is 60px for good usability',
                ],
                'button_color' => [
                    'label' => 'Button color',
                    'helper_text' => 'Choose a visible color for the sound buttons',
                ],
                'description' => 'Description',
                'description_helper' => 'Describe the goals of your experiment. This description will be publicly visible.',
                'instructions' => 'Instructions',
                'instructions_helper' => 'Provide clear instructions for participants.',
                'media' => 'Media',
                'media_sound_helper' => 'Accepted audio formats: MP3, WAV, AAC, OGG (max 20MB)',
                'media_image_helper' => 'Accepted image formats: JPG, JPEG, PNG, GIF (max 20MB)',
                'media_image_sound_helper' => 'Accepted formats: JPG, JPEG, PNG, GIF, WebP, MP3, WAV, AAC, OGG (max 20MB)',
                'documents' => 'Documents',
                'documents_helper' => 'Accepted formats: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, CSV (max 20MB)',
            ],
            'table' => [
                'columns' => [
                    'name' => 'Name',
                    'type' => [
                        'label' => 'Type',
                        'options' => [
                            'image' => 'Image',
                            'sound' => 'Sound',
                            'image_sound' => 'Image and Sound',
                        ]
                    ],
                    'status' => [
                        'label' => 'Status',
                        'options' => [
                            'start' => 'Started',
                            'pause' => 'Paused',
                            'stop' => 'Stopped',
                            'test' => 'In Test',
                            'none' => 'None',
                        ]
                    ],
                    'howitworks' => 'Available on "How it Works?"',
                    'created_at' => 'Creation Date',
                ]
            ],
            'actions' => [
                'create' => 'Create an Experiment',
                'edit' => 'Edit',
                'delete' => 'Delete',
                'more_actions' => 'Actions',
                'session' => 'Session',
                'session_link' => 'Experiment link',
                'exports' => 'Save the Experiment',
                'manage_experiment' => [
                    'label' => 'Session',
                    'success' => 'Session successfully updated'
                ],
                'status' => [
                    'start' => 'Start',
                    'pause' => 'Pause',
                    'stop' => 'Stop',
                    'test' => 'Test',
                    'start_desc' => 'Activates the session and generates a unique link if one does not already exist. The session becomes accessible to participants.',
                    'pause_desc' => 'Temporarily suspends the session. The link remains active, but participants cannot continue the session until it is resumed.',
                    'stop_desc' => 'Ends the session and deactivates the link. To reactivate the session, you must restart it, which generates a new link.',
                    'test_desc' => 'Activates the session in test mode. The session is accessible to participants, but no results are saved.',
                ],
                'export' => [
                    'label' => 'Export',
                    'json' => 'Export to JSON',
                    'xml' => 'Export to XML',
                    'desc' => 'Select the format in which you want to export the experiment data.',
                    'media_info' => 'Including media will add all associated media files to the export.',
                    'include_media' => 'Include media',
                    'success' => 'Export completed successfully',
                ]
            ],
            'notifications' => [
                'created' => 'Experiment successfully created',
                'updated' => 'Experiment successfully updated',
                'deleted' => 'Experiment successfully deleted',
                'session_updated' => 'Session status successfully updated',
                'export_success' => 'Export completed successfully',
            ],
            'messages' => [
                'no_active_session' => 'No active session',
            ]
        ],
        'users' => [
            'title' => "User",
            'table' => [
                'name' => "Name",
                'email' => "Email",
                'university' => "University",
                'status' => [
                    'label' => "Status",
                    'approved' => "Approved"
                ],
                'role' => [
                    'label' => 'Role',
                    'options' => [
                        'supervisor' => 'Supervisor',
                        'principal_experimenter' => 'Principal Experimenter',
                        'secondary_experimenter' => 'Secondary Experimenter',
                    ]
                ]
            ],
            'actions' => [
                'create' => "Add a user",
                'contact' => 'Contact',
                'show_experiment' => 'View experiments',
                'details' => "Details",
                'delete' => "Delete user"
            ],
            'form' => [
                'name' => "Name",
                'email' => "Email",
                'university' => "University",
                'role' => [
                    'label' => 'Role',
                    'options' => [
                        'supervisor' => 'Supervisor',
                        'principal_experimenter' => 'Principal Experimenter',
                        'secondary_experimenter' => 'Secondary Experimenter',
                    ]
                ],
                'registration_reason' => "Reason for registration",
                'banned_reason' => "Reason for banning",
                'status' => [
                    'label' => 'Status',
                    'options' => [
                        'approved' => 'Approved',
                        'banned' => 'Ban',
                    ]
                ],
                'section' => [
                    'history_section' => "Action history",
                    'history_section_description' => "History of different actions performed on this account",
                    'registration_reason' => "Reason for registration",
                    'rejection_reason' => "Reason for rejection",
                    'banned_reason' => "Reason for banning",
                    'unbanned_reason' => "Reason for unbanning",
                ],
            ],
            'notification' => [
                'banned_reason' => 'Main account banned:',
                'banned' => 'User successfully banned',
            ]
        ],
        'banned' => [
            'title' => "Banned users",
            'form' => [
                'name' => "Name",
                'email' => "Email",
                'university' => "University",
                'registration_reason' => "Reason for registration",
                'banned_reason' => "Reason for banning",
                'status' => [
                    'unban' => "Unban"
                ],
                'unbanned_reason' => [
                    'label' => "Reason for unbanning",
                    "placeholder" => "Reason for unbanning",
                    'helper' => "Once the unbanning is recorded, the main account will receive an email. Secondary accounts created by the main account will also be unbanned and receive an email."
                ],
            ],
            'table' => [
                'name' => 'Name',
                'email' => 'Email',
                'university' => 'University',
                'created_at' => 'Request date',
                'status' => [
                    'label' => 'Status',
                    'banned' => 'Banned',
                ],
            ],
            'notification' => [
                'unbanned_reason' => 'Main account unbanned:',
                'unbanned' => 'User successfully unbanned'
            ],
            'action' => [
                'create' => "Add a user",
                'contact' => 'Contact',
                'show_experiment' => 'View experiments',
                'details' => "Details",
                'delete' => "Delete user"
            ]
        ],
    ],
];
