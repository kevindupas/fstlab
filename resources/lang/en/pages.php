<?php

declare(strict_types=1);

return [
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
                'remaining' => 'Your description must be at least 50 characters long',
                'complete' => 'Description complete'
            ],
            'orcid' => 'ORCID Number',
            'password' => [
                'label' => 'Password',
                'helpMessage' => 'Your password must be at least 8 characters long',
                'requirements' => 'Password must contain at least one uppercase letter, one lowercase letter, one number and be at least 8 characters long'
            ],
            'confirm_password' => [
                'label' => 'Confirm Password',
                'helpMessage' => 'Passwords do not match',
            ],
            'terms_accept' => 'I accept the',
            'terms_read' => 'CGU',
            'terms_end' => 'from TCL-Labx Web',
        ],
        'profile' => [
            'language' => 'Language',
            'helper_text' => 'This will send you notifications in your chosen language.',
            'notifications' => [
                'saved' => 'Profile successfully registered.',
            ],
        ]
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
                'secondary_option' => 'Apply to become a master account and create experiments'
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
        'title_secondary_experimenter' => 'Contact my experimenter',
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
            'error' => 'Error',
            'no_user' => 'User not specified'
        ],
    ],
    'principal_contact' => [
        'title_secondary_experimenter' => 'Contact my experimenter',
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
            'error' => 'Error',
            'no_user' => 'User not specified'
        ],
    ],
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
        'title' => 'Participants for the Experiment',
        'columns' => [
            'participant_number' => 'Participant Identifier',
            'status' => 'Status',
            'created_at' => 'Creation Date',
            'completed_at' => 'Completion Date',
            'experimenter' => 'Experimenter',
            'experimenter_types' => [
                'me' => '(Me)',
                'creator' => '(Creator)',
                'secondary' => '(Secondary Account)',
                'collaborator' => '(Collaborator)',
            ],
        ],
        'actions' => [
            'export' => 'Export Data',
            'details' => 'Details',
            'export_all' => 'Export All',
            'export_selection' => 'Export Selection',
            'search' => 'Search for a Word or Phrase',
            'reset' => 'Reset',
            'cancel' => 'Cancel',
            'reset_filter' => 'Reset Filters',
        ],
        'notifications' => [
            'no_completed_sessions' => 'No completed sessions to export',
            'no_selection_completed' => 'No completed session selected',
        ],
        'access_denied' => 'You do not have access to this experiment',
        'search' => [
            'modal' => [
                'search_label' => 'Search in the Session',
                'search_placeholder' => 'Search for a word (e.g., yellow, animal, etc.)',
                'submit' => 'Search',
            ],
            'results' => [
                'occurrences_found_singular' => 'Occurrence of ":term" found',
                'occurrences_found_plural' => 'Occurrences of ":term" found',
                'no_results' => 'No occurrence of ":term" found',
                'locations' => [
                    'comments' => ':count comment|:count comments',
                    'feedback' => 'in feedback',
                ],
            ],
        ],
        'tabs' => [
            'all' => [
                'label' => 'All Results',
                'badge' => 'All',
            ],
            'creator' => [
                'label' => 'My Results (Creator)',
                'badge' => 'Creator',
                'label_for_others' => 'Creator\'s Results',
            ],
            'mine' => [
                'label' => 'My Results',
                'badge' => 'My Results',
            ],
            'collaborators' => [
                'label' => 'Other Collaborators',
                'badge' => 'Collaborators',
            ],
        ],
        'buttons' => [
            'export_list' => 'Export List',
        ],
        'status' => [
            'completed' => 'Completed',
            'created' => 'Created',
        ],
    ],
    'experiments_sessions_export' => [
        'title' => 'Export session data - :participant',
        'actions' => [
            'export' => 'Export Data in CSV',
            'cancel' => 'Cancel',
        ],
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
        'access_denied' => 'You do not have access to the details of this session.',
        'title' => 'Session Details - :participant',
        'sections' => [
            'participant' => 'Participant Information',
            'technical' => 'Technical Information',
            'feedback' => 'Feedback and Notes',
            'canvas_size' => 'Canvas dimensions',
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
            'canvas_dimensions' => 'Canvas dimensions',
            'screen_dpi' => 'Screen resolution',
        ],
        'time' => [
            'seconds' => 'seconds',
        ],
        'na' => 'N/A',
        'error_format' => 'Error :type at :time',
        'actions' => [
            'add_note' => 'Add/Edit a Note',
            'search' => 'Search for a Word or Phrase',
            'submit' => 'Search',
            'cancel' => 'Cancel',
        ],
        'notifications' => [
            'note_saved' => 'Note successfully saved',
        ],
        'breadcrumbs' => [
            'participants' => 'Participants for the Experiment: :name',
            'details' => 'Session Details - :participant',
        ],
        'search' => [
            'modal' => [
                'title' => 'Search in the Session',
                'search_label' => 'Search for a word or phrase',
                'search_placeholder' => 'Search for a word (e.g. yellow, animal, etc.)',
                'submit' => 'Search',
            ],
        ],
        'search_results' => [
            'occurrences_found' => 'Occurrence|Occurrences of ":term" found',
            'no_occurrences' => 'No occurrences of ":term" found',
            'locations' => [
                'comments' => ':count comment|comments',
                'feedback' => 'in feedback',
            ],
        ],
        'examiner_notes' => [
            'title' => 'Examiner Notes',
        ],
        'groups' => [
            'title' => 'Element Groups',
            'comment_label' => 'Comment:',
            'media' => [
                'name' => 'Name:',
                'position' => 'Position: X=:x, Y=:y',
                'interactions' => ':count interaction|interactions',
                'audio_unsupported' => 'Your browser does not support the audio element.',
                'play_count' => ':count lecture|:count lectures',
                'move_count' => ':count déplacement|:count déplacements',
                'group_changes' => ':count changement de groupe|:count changements de groupe',
            ],
        ],
        'actions_log' => [
            'title' => 'Actions Log',
            'headers' => [
                'time' => 'Time',
                'action' => 'Action',
                'details' => 'Details',
            ],
            'actions' => [
                'move' => 'Move',
                'sound' => 'Play Sound',
                'image' => 'View Image',
                'simple_group_created' => 'Create a group',
                'simple_group_change' => 'Change group',
            ],
            'details' => [
                'name' => 'Name:',
                'position' => 'Position: X=:x, Y=:y',
                'group_created_details' => 'Create group :name with color :color',
                'item_moved_details' => ':name moved from group :from to group :to',
            ],
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
    'bulk_experiment_session_export' => [
        'title' => 'Export of :count session:plural',
        'actions' => [
            'export_selected_sessions' => 'Export :count session|Export :count sessions',
            'back_to_list' => 'Back to list',
        ],

        'export_options' => [
            'title' => 'Export Options',
            'basic' => 'Basic Information',
            'group' => 'Group Data',
        ],
        'labels' => [
            'select_fields' => 'Select Fields',
            'select_group_data' => 'Select Group Data',
        ],
        'basic_fields' => [
            'participant_number' => 'Participant Number',
            'experimenter_info' => 'Experimenter Information (name and type)',
            'dates' => 'Dates (creation and completion)',
            'duration' => 'Duration',
            'system_info' => 'System Information (browser, OS, device, resolution)',
            'feedback' => 'Feedback',
        ],
        'group_fields' => [
            'group_names' => 'Group Names',
            'group_comments' => 'Group Comments',
            'media_info' => 'Media Information (names, positions, interactions)',
        ],
        'helper_text' => [
            'basic' => 'This information will be exported for each session',
            'group' => 'This information will be exported for each group',
        ],
        'error' => [
            'no_selection' => 'No session selected',
            'not_found' => 'Session not found',
        ],
        'csv_headers' => [
            'session_id' => 'Session ID',
            'participant_number' => 'Participant Number',
            'experimenter_name' => 'Experimenter Name',
            'experimenter_type' => 'Experimenter Type',
            'created_at' => 'Creation Date',
            'completed_at' => 'Completion Date',
            'duration_seconds' => 'Duration (seconds)',
            'browser' => 'Browser',
            'system' => 'Operating System',
            'device' => 'Device Type',
            'screen_width' => 'Screen Width',
            'screen_height' => 'Screen Height',
            'feedback' => 'Feedback',
            'group_name' => 'Group Name :number',
            'group_comment' => 'Group Comment :number',
            'media_name' => 'Media Name :number (group :group)',
            'media_interactions' => 'Media Interactions :number (group :group)',
            'media_x' => 'Media X Position :number (group :group)',
            'media_y' => 'Media Y Position :number (group :group)',
        ],
        'experimenter_types' => [
            'creator' => 'Creator',
            'secondary' => 'Secondary',
            'collaborator' => 'Collaborator',
            'na' => 'N/A',
        ],
        'values' => [
            'na' => 'N/A',
        ],
        'download_filename' => 'sessions-export-:date.csv',
    ],
    'experiment_session_export' => [
        'title' => 'Session Export: :participant',
        'actions' => [
            'back_to_list' => 'Back to list',
            'export_csv' => 'Export to CSV',
        ],
        'export_options' => [
            'title' => 'Export Options',
            'basic' => 'Basic Information',
            'group' => 'Group Data',
        ],
        'labels' => [
            'select_fields' => 'Select Fields',
            'select_group_data' => 'Select Group Data',
        ],
        'basic_fields' => [
            'participant_number' => 'Participant Number',
            'experimenter_info' => 'Experimenter Information (name and type)',
            'dates' => 'Dates (creation and completion)',
            'duration' => 'Duration',
            'system_info' => 'System Information (browser, OS, device, resolution)',
            'feedback' => 'Feedback',
        ],
        'group_fields' => [
            'group_names' => 'Group Names',
            'group_comments' => 'Group Comments',
            'media_info' => 'Media Information (names, positions, interactions)',
        ],
        'helper_text' => [
            'basic' => 'This information will be exported for each session',
            'group' => 'This information will be exported for each group',
        ],
        'error' => [
            'not_found' => 'Session not found',
        ],
        'csv_headers' => [
            'session_id' => 'Session ID',
            'participant_number' => 'Participant Number',
            'experimenter_name' => 'Experimenter Name',
            'experimenter_type' => 'Experimenter Type',
            'created_at' => 'Creation Date',
            'completed_at' => 'Completion Date',
            'duration_seconds' => 'Duration (seconds)',
            'browser' => 'Browser',
            'system' => 'Operating System',
            'device' => 'Device Type',
            'screen_width' => 'Screen Width',
            'screen_height' => 'Screen Height',
            'feedback' => 'Feedback',
            'group_name' => 'Group Name :number',
            'group_comment' => 'Group Comment :number',
            'media_name' => 'Media Name :number (group :group)',
            'media_interactions' => 'Media Interactions :number (group :group)',
            'media_x' => 'Media X Position :number (group :group)',
            'media_y' => 'Media Y Position :number (group :group)',
        ],
        'experimenter_types' => [
            'creator' => 'Creator',
            'secondary' => 'Secondary',
            'collaborator' => 'Collaborator',
            'na' => 'N/A',
        ],
        'values' => [
            'na' => 'N/A',
        ],
        'download_filename' => 'sessions-export-:date.csv',
    ],
    'experiment_sessions_export_all' => [
        'title' => 'Export :tab - :name',
        'actions' => [
            'back_to_list' => 'Back to list',
            'export_csv' => 'Export to CSV',
        ],
        'tabs' => [
            'creator' => 'from creator',
            'mine' => 'my results',
            'collaborators' => 'from collaborators',
            'all' => 'all results',
        ],
        'export_options' => [
            'title' => 'Export Options',
            'basic' => 'Basic Information',
            'group' => 'Group Data',
        ],
        'labels' => [
            'select_fields' => 'Select Fields',
            'select_group_data' => 'Select Group Data',
        ],
        'basic_fields' => [
            'participant_number' => 'Participant Number',
            'experimenter_info' => 'Experimenter Information (name and type)',
            'dates' => 'Dates (creation and completion)',
            'duration' => 'Duration',
            'system_info' => 'System Information (browser, OS, device, resolution)',
            'feedback' => 'Feedback',
        ],
        'group_fields' => [
            'group_names' => 'Group Names',
            'group_comments' => 'Group Comments',
            'media_info' => 'Media Information (names, positions, interactions)',
        ],
        'helper_text' => [
            'basic' => 'This information will be exported for each session',
            'group' => 'This information will be exported for each group',
        ],
        'error' => [
            'experiment_not_found' => 'Experiment not found',
        ],
        'csv_headers' => [
            'session_id' => 'Session ID',
            'participant_number' => 'Participant Number',
            'experimenter_name' => 'Experimenter Name',
            'experimenter_type' => 'Experimenter Type',
            'created_at' => 'Creation Date',
            'completed_at' => 'Completion Date',
            'duration_seconds' => 'Duration (seconds)',
            'browser' => 'Browser',
            'system' => 'Operating System',
            'device' => 'Device Type',
            'screen_width' => 'Screen Width',
            'screen_height' => 'Screen Height',
            'feedback' => 'Feedback',
            'group_name' => 'Group Name :number',
            'group_comment' => 'Group Comment :number',
            'media_name' => 'Media Name :number (group :group)',
            'media_interactions' => 'Media Interactions :number (group :group)',
            'media_x' => 'Media X Position :number (group :group)',
            'media_y' => 'Media Y Position :number (group :group)',
        ],
        'experimenter_types' => [
            'creator' => 'Creator',
            'secondary' => 'Secondary',
            'collaborator' => 'Collaborator',
            'na' => 'N/A',
        ],
        'values' => [
            'na' => 'N/A',
        ],
        'download_filename' => 'all-sessions-export-:date.csv',
    ],

];
