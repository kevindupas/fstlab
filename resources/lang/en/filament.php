<?php

declare(strict_types=1);

return [
    'pages' => [
        'experiment_details' => [
            'title' => "Experiment Details",
            'information_section' => [
                'title' => "Experiment Information",
                'description' => "Details and configuration of the experiment",
                'name' => "Name",
                'created_by' => "Created By",
                'created_at' => "Created On",
                'doi' => "Unique identifier",
                'link' => "Link",
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
            ],
            'description_section' => [
                'title' => "Description",
                'description' => "Detailed description of the experiment",
                'label' => "Description",
            ],
            'instruction_section' => [
                'title' => "Instructions",
                'description' => "Instructions for participants",
                'label' => "Instructions",
            ],
            'settings_section' => [
                'title' => "Visual Settings",
                'description' => "Configuration of visual elements",
                'button_size' => "Button Size",
                'button_color' => "Button Color",
            ],
            'medias_section' => [
                'title' => "Media",
                'description' => "Media files used in the experiment",
                'medias' => "Media",
                'images' => "Images",
                'sounds' => "Sounds",
            ],
            'documents_section' => [
                'title' => "Documents",
                'description' => "Supplementary documents",
                'documents' => "Documents",
            ],
            'ban_action' => [
                'label' => 'Ban Experimenter',
                'reason' => 'Reason for Ban',
                'helper' => 'Explain why you are banning this experimenter',
                'modalHeading' => 'Ban Experimenter',
                'modalDescription' => 'This action is irreversible. The experimenter and all their secondary experimenters will no longer have access to the platform.',
            ],
            'notification' => [
                'banned' => 'Experimenter successfully banned',
            ],
            'actions' => [
                'contact' => 'Contact Experimenter',
                'edit' => 'Edit',
            ],
        ],
        'experiments_sessions' => [
            'title' => 'Participants for the experiment',
            'columns' => [
                'participant_number' => 'Participant ID',
                'status' => 'Status',
                'created_at' => 'Creation Date',
                'completed_at' => 'Completion Date',
            ],
            'actions' => [
                'export' => 'Export data',
                'details' => 'Details',
                'export_all' => 'Export all',
                'export_selection' => 'Export selection',
            ],
            'notifications' => [
                'no_completed_sessions' => 'No completed sessions to export',
                'no_selection_completed' => 'No completed sessions selected',
            ],
            'access_denied' => 'You do not have access to this experiment',
            'csv_headers' => [
                'participant' => 'Participant',
                'created_at' => 'Creation Date',
                'completed_at' => 'Completion Date',
                'duration' => 'Duration (s)',
                'browser' => 'Browser',
                'system' => 'System',
                'device' => 'Device',
                'screen_dimensions' => 'Screen Dimensions',
                'feedback' => 'Feedback',
                'group' => [
                    'name' => 'Group :number - Name',
                    'comment' => 'Group :number - Comment',
                    'media' => 'Group :number - Media',
                    'media_interactions' => 'Group :number - :media - Interactions',
                    'media_position' => 'Group :number - :media - Position',
                ],
            ],
        ],
        'experiments_sessions_export' => [
            'title' => 'Export session data - :participant',
            'access_denied' => 'Only the creator can export the data.',
            'tabs' => [
                'title' => 'Export Options',
                'basic_info' => 'Basic Information',
                'group_data' => 'Group Data',
                'action_log' => 'Action Log',
            ],
            'fields' => [
                'basic_fields' => [
                    'label' => 'Select fields to export',
                    'options' => [
                        'participant_number' => 'Participant ID',
                        'created_at' => 'Creation Date',
                        'completed_at' => 'Completion Date',
                        'duration' => 'Duration (seconds)',
                        'browser' => 'Browser',
                        'operating_system' => 'Operating System',
                        'device_type' => 'Device Type',
                        'screen_dimensions' => 'Screen Dimensions',
                        'feedback' => 'Feedback',
                    ],
                ],
                'group_fields' => [
                    'label' => 'Select group information to export',
                    'options' => [
                        'group_names' => 'Group Names',
                        'group_comments' => 'Group Comments',
                        'media_positions' => 'Final Media Positions',
                        'media_interactions' => 'Number of interactions per media',
                        'group_compositions' => 'Group Compositions',
                    ],
                ],
                'action_fields' => [
                    'label' => 'Select actions to export',
                    'options' => [
                        'moves' => 'Movements',
                        'sounds' => 'Sound Playback',
                        'images' => 'Image Views',
                    ],
                ],
                'time_format' => [
                    'label' => 'Time Format',
                    'options' => [
                        'timestamp' => 'Timestamp',
                        'readable' => 'Readable Format (HH:mm:ss)',
                        'elapsed' => 'Elapsed Time (seconds)',
                    ],
                ],
            ],
            'csv' => [
                'participant' => 'Participant',
                'created_at' => 'Creation Date',
                'completed_at' => 'Completion Date',
                'duration' => 'Duration (s)',
                'browser' => 'Browser',
                'system' => 'System',
                'device' => 'Device',
                'screen' => 'Screen Dimensions',
                'feedback' => 'Feedback',
                'group_prefix' => 'Group :number',
                'name' => 'Name',
                'comment' => 'Comment',
                'media' => 'Media',
                'interactions' => 'Interactions',
                'position' => 'Position',
                'position_format' => 'X::x, Y::y',
                'time' => 'Time',
                'type' => 'Type',
                'position_x' => 'Position X',
                'position_y' => 'Position Y',
                'action_types' => [
                    'move' => 'Movement',
                    'sound' => 'Sound Playback',
                    'image' => 'Image View',
                ],
            ],
        ],
        'experiments_sessions_details' => [
            'access_denied' => 'You do not have access to this session\'s details.',
            'title' => 'Session Details - :participant',
            'sections' => [
                'participant' => 'Participant Information',
                'technical' => 'Technical Information',
                'feedback' => 'Feedback and Notes',
            ],
            'fields' => [
                'participant_number' => 'Name',
                'created_at' => 'Participation Date',
                'duration' => 'Duration',
                'browser' => 'Browser',
                'operating_system' => 'Operating System',
                'device_type' => 'Device Type',
                'screen_width' => 'Screen Width',
                'screen_height' => 'Screen Height',
                'feedback' => 'Participant Feedback',
                'errors' => 'Reported Errors',
                'examiner_notes' => 'Examiner Notes',
            ],
            'time' => [
                'seconds' => 'seconds',
            ],
            'na' => 'N/A',
            'error_format' => 'Error :type at :time',
            'actions' => [
                'add_note' => 'Add/Edit Note',
            ],
            'notifications' => [
                'note_saved' => 'Note successfully saved',
            ],
            'breadcrumbs' => [
                'participants' => 'Participants for the experiment: :name',
                'details' => 'Session Details - :participant',
            ],
        ],
        'experiments_statistics' => [
            'title' => 'Statistics for the experiment: :name',
            'widgets' => [
                'actions_timeline' => [
                    'heading' => 'Actions Timeline',
                    'session' => 'Session',
                    'action' => 'Action',
                    'time' => 'Time',
                ],
                'completion' => [
                    'heading' => 'Session Progression',
                    'sessions' => 'Sessions',
                ],
                'device_type' => [
                    'heading' => 'Device Types',
                    'total' => 'Total',
                ],
                'duration' => [
                    'heading' => 'Duration Distribution',
                    'duration' => 'Duration',
                    'min' => 'Min',
                    'q1' => 'Q1',
                    'median' => 'Median',
                    'q3' => 'Q3',
                    'max' => 'Max',
                ],
                'stats' => [
                    'total' => [
                        'label' => 'Total Sessions',
                        'description' => 'Total number of sessions',
                    ],
                    'completed' => [
                        'label' => 'Completed Sessions',
                        'description' => ':percentage% completion',
                    ],
                    'duration' => [
                        'label' => 'Average Duration',
                        'description' => 'Average time per session',
                    ],
                    'error' => [
                        'label' => 'Error',
                        'value' => 'Loading error',
                    ],
                ],
            ],
        ],
    ],
    'resources' => [
        'experiment_list' => [
            'title' => 'Experiment List',
            'titleFilter' => 'list of :username experiments',
            'column' => [
                'created_by' => 'Created by',
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
                    'label' => 'Status',
                    'options' => [
                        'start' => 'Start',
                        'pause' => 'Pause',
                        'stop' => 'Stop',
                        'test' => 'Test',
                    ],
                ],
                'sessions_count' => 'Number of Sessions',
                'created_at' => 'Created on',
            ],
            'tabs' => [
                'all' => 'All Experiments',
                'sound' => 'Sound',
                'image' => 'Image',
                'image_sound' => 'Image and Sound',
            ]
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
                'heading' => 'Regulatory documents',
                'description' => 'Add regulatory documents related to your experiment (Ethics Committee approval and other official documents)',
            ],
            'section_responsible' => [
                'heading' => 'Responsible institution',
                'description' => 'Identification of the institution responsible for the experiment',
            ],
            'form' => [
                'doi' => 'Unique identifier',
                'doi_placeholder' => 'The identifier will be generated automatically',
                'doi_helper' => 'A unique identifier is automatically assigned to your experiment during creation.',
                'howitworks' => 'How it works',
                'howitworks_helper' => 'If enabled, the experiment in "test" mode will be visible on the How it Works? page. Automatically disabled if the status changes.',
                'is_public' => 'Make it public',
                'is_public_helper' => 'Make your experiment public so that it can be seen by all users',
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
                'is_random' => 'Random order of stimuli',
                'is_random_helper' => 'If enabled, stimuli will be presented in random order for each participant',
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
                'responsible_institution' => 'Institution responsible for the experiment and data collection',
                'responsible_institution_placeholder' => 'Enter the institution name',
                'responsible_institution_helper' => 'Specify the institution that will be responsible for this experiment',
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
                    'sessions_count' => 'Number of Participants',
                    'howitworks' => 'Available on "How it Works?"',
                    'created_at' => 'Creation Date',
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
            ],
            'actions' => [
                'delete' => [
                    'heading' => 'Delete permanently',
                    'desc_issues_delete' => 'This experiment cannot be deleted because it is shared or has pending requests.',
                    'confirm_delete' => 'To delete this experiment, please enter the code below.',
                    'code_confirm' => 'Confirmation code',
                    'code' => 'Code',
                    'code_fail' => 'The code does not match.'
                ],
            ],
        ],
        'experiment-access-request' => [
            'label' => 'Access Request',
            'plural' => 'Access Requests',
            'navigation_label' => 'Access Requests',
            'form' => [
                'section' => [
                    'status_title' => 'Request status',
                    'status_description' => 'Approve or reject request',
                    'information_title' => 'Request information',
                    'information_description' => 'Request details',
                ],
                'status' => [
                    'label' => 'Status',
                    'options' => [
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'revoked' => 'Revoked',
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
                    'label' => 'Name of experiment',
                ],
                'user' => [
                    'label' => 'Access request issued by',
                ],
                'duplicate' => [
                    'copy' => '(Copy)',
                    'success' => 'Experiment duplicated successfully',
                    'error' => 'An error occurred while duplicating the experiment',
                ],
                'informations' => [
                    'information_access' => 'Information on access types',
                    'result_access' => 'Access to results',
                    'result_description' => 'Allow access to the results of the experiment',
                    'experiment_access' => 'Access to the experiment',
                    'experiment_description' => 'Gives access to results and allows sessions to be run',
                    'duplicate_access' => 'Duplicate the experiment',
                    'duplicate_description' => 'Creates a copy of the experiment. Approval is final and cannot be revoked',
                ]
            ],
            'table' => [
                'columns' => [
                    'experiment' => 'Experiment',
                    'user' => 'Requester',
                    'type' => [
                        'label' => 'Access type',
                        'options' => [
                            'results' => 'Results only',
                            'access' => 'Full collaboration',
                        ]
                    ],
                    'status' => 'Status',
                    'created_at' => 'Request Date',
                ],
                'actions' => [
                    'information' => 'Information',
                    'revoke' => 'Revoke access',
                    'revoke_label' => 'Revocation message',
                    'revoke_message' => 'Please explain why you are revoking access...',
                    'revoke_description' => 'Are you sure you want to revoke access? The user will be informed.',
                    'view' => 'View details',
                ],
                'message' => [
                    'banned' => 'Your account has been banned.',
                    'banned_secondary' => 'L\'expérimentateur principal de votre compte est banni.',
                    'no_access' => 'You do not have access to this experiment.',
                    'no_access_section' => 'You do not have access to this section',
                ]
            ],
            'tabs' => [
                'all' => 'All Requests',
                'pending' => 'Pending Requests',
                'approved' => 'Approved Requests',
                'revoked' => 'Rejected/Revoked Requests',
            ],
        ],
        'borrowed_experiment' => [
            'label' => 'Borrowed Experiments',
            'plural' => 'Borrowed Experiments',
            'navigation_label' => 'Borrowed Experiments',
            'table' => [
                'columns' => [
                    'experiment' => 'Experiment',
                    'created_by' => 'Creator',
                    'type_experiments' => [
                        'label' => 'Experiment Type',
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
                            'test' => 'Testing',
                        ]
                    ],
                    'type_access' => [
                        'label' => 'Access Type',
                        'results' => 'Results Only',
                        'access' => 'Full Collaboration'
                    ],
                    'sessions_count' => 'Number of Participants',
                    'access_granted_at' => 'Access Granted At',
                ],
                'actions' => [
                    'sessions' => 'Manage Sessions',
                    'view' => 'View Details',
                    'statistics' => 'Statistics',
                    'results' => 'Results',
                ],
                'message' => [
                    'banned' => 'Your account is banned.',
                    'banned_secondary' => 'The main experimenter on your account is banned.',
                    'no_access' => 'You do not have access to this experiment.',
                    'no_access_section' => 'You do not have access to this section.',
                ]
            ],
            'tabs' => [
                'all' => 'All Experiments',
                'results' => 'Results Only',
                'access' => 'Full Collaboration',
            ],
            'notifications' => [
                'no_completed_sessions' => 'No completed sessions to export',
                'no_selection_completed' => 'No completed sessions selected'
            ],
        ],
        'secondary_user' => [
            'title' => 'Experiments Assigned to :username',
            'table' => [
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
                        'label' => 'Status',
                        'options' => [
                            'start' => 'Started',
                            'pause' => 'Paused',
                            'stop' => 'Stopped',
                            'test' => 'Testing',
                            'none' => 'None',
                        ]
                    ],
                    'created_at' => 'Creation Date',
                    'can_configure' => 'Configuration',
                    'can_pass' => 'Sessions',
                ]
            ],
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
        'pending_registration' => [
            'title' => "Registration Requests",
            'form' => [
                'name' => "Name",
                'email' => "Email",
                'university' => "University",
                'registration_reason' => "Reason for Registration",
                'banned_reason' => "Reason for Banning",
                'status' => [
                    'approved' => "Approved",
                    'rejected' => "Rejected",
                ],
                'rejected_reason' => [
                    'label' => "Reason for Rejection",
                    'placeholder' => "Reason for rejection",
                    'helper' => "Explain the reason for rejecting the registration request",
                ],
            ],
            'table' => [
                'name' => 'Name',
                'email' => 'Email',
                'university' => 'University',
                'created_at' => 'Request Date',
                'status' => [
                    'label' => 'Status',
                    'pending' => 'Pending',
                ],
            ],
            'notification' => [
                'rejected_reason' => 'Main account unbanned:',
                'rejected' => 'User successfully unbanned',
            ],
            'action' => [
                'create' => "Add a User",
                'contact' => 'Contact',
                'show_experiment' => 'View Experiments',
                'details' => "Details",
                'delete' => "Delete User",
            ],
        ],
        'rejected_user' => [
            'title' => "Rejected Users",
            'form' => [
                'name' => "Name",
                'email' => "Email",
                'university' => "University",
                'registration_reason' => "Reason for Registration",
                'banned_reason' => "Reason for Banning",
                'status' => [
                    'approved' => "Approved",
                    'rejected' => "Rejected",
                ],
                'rejected_reason' => "Reason for Rejection",
            ],
            'table' => [
                'name' => 'Name',
                'email' => 'Email',
                'university' => 'University',
                'created_at' => 'Request Date',
                'status' => [
                    'label' => 'Status',
                    'rejected' => 'Rejected',
                ],
            ],
            'notification' => [
                'rejected_reason' => 'Main account unbanned:',
                'rejected' => 'User successfully unbanned',
            ],
            'action' => [
                'create' => "Add a User",
                'contact' => 'Contact',
                'show_experiment' => 'View Experiments',
                'details' => "Details",
                'delete' => "Delete User",
            ],
        ],
    ],
];
