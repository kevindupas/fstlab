<?php

declare(strict_types=1);

return [
    'auth' => [
        'register' => [
            'name' => 'Nom',
            'email' => [
                'label' => 'Email',
                'unique' => 'L\'email est déjà utilisé',
            ],
            'university' => 'Université',
            'registration_reason' => [
                'label' => 'Pourquoi souhaitez-vous vous inscrire ?',
                'helpMessage' => 'Votre description doit faire minimum 50 caractères'
            ],
            'orcid' => 'Numéro ORCID',
            'password' => [
                'label' => 'Mot de passe',
                'helpMessage' => 'Votre mot de passe doit faire minimum 8 caractères'
            ],
            'confirm_password' => [
                'label' => 'Confirmer mot de passe',
                'helpMessage' => 'Le mot de passe ne correspond pas'
            ],
            'terms_accept' => "J'accepte les",
            'terms_read' => 'CGU',
            'terms_end' => 'de TCL-Labx Web',
        ],
        'profile' => [
            'language' => 'Votre langue',
            'helper_text' => 'Cette choix va permettre de vous envoyer des notifications dans la langue choisie.',
            'notifications' => [
                'saved' => 'Profil enregistré avec succès.',
            ],
        ]
    ],
    'admin_contact' => [
        'title' => 'Contacter l\'administrateur',
        'form' => [
            'subject' => 'Sujet',
            'options' => [
                'unban' => 'Demande de débannissement',
                'principal_banned' => 'Principal expérimentateur banni',
                'question' => 'Question Générale',
                'other' => 'Autre',
            ],
            'message' => [
                'label' => 'Description de la demande',
                'placeholder' => 'Saisissez votre message',
            ],
            'submit' => 'Envoyer le message',
            'success' => 'Message envoyé avec succès',
            'error' => 'Erreur',
            'no_user' => 'Utilisateur non spécifié'
        ]
    ],
    'user_contact' => [
        'title_secondary_experimenter' => 'Contacter l\'expérimentateur principal',
        'title' => 'Contacter un utilisateur',
        'form' => [
            'user' => 'Utilisateur',
            'experiment' => 'Expérimentation concernée (optionnel)',
            'message' => [
                'label' => 'Message',
                'placeholder' => 'Saisissez votre message',
            ],
            'submit' => 'Envoyer le message',
            'success' => 'Message envoyé avec succès'
        ]
    ],
    'principal_contact' => [
        'title_secondary_experimenter' => 'Contacter l\'expérimentateur principal',
        'title' => 'Contacter un utilisateur',
        'form' => [
            'user' => 'Utilisateur',
            'experiment' => 'Expérimentation concernée (optionnel)',
            'message' => [
                'label' => 'Message',
                'placeholder' => 'Saisissez votre message',
            ],
            'submit' => 'Envoyer le message',
            'success' => 'Message envoyé avec succès'
        ]
    ],
    'experiment_details' => [
        'title' => "Détails de l'expérimentation",
        'information_section' => [
            'title' => "Informations de l'expérimentation",
            'description' => "Détails et configuration de l'expérimentation",
            'name' => "Nom",
            'created_by' => "Créateur",
            'created_at' => "Créé le",
            'doi' => "DOI",
            'link' => "Lien",
            'type' => [
                'label' => 'Type',
                'options' => [
                    'image' => 'Image',
                    'sound' => 'Son',
                    'image_sound' => 'Image et Son',
                ]
            ],
            'status' => [
                'label' => 'Statut',
                'options' => [
                    'start' => 'Démarré',
                    'pause' => 'En pause',
                    'stop' => 'Arrêté',
                    'test' => 'En test',
                    'none' => 'Aucun',
                ]
            ],
        ],
        'description_section' => [
            'title' => "Description",
            'description' => "Description détaillée de l'expérimentation",
            'label' => "Description",
        ],
        'instruction_section' => [
            'title' => "Instructions",
            'description' => "Instructions pour les participants",
            'label' => "Instructions",
        ],
        'settings_section' => [
            'title' => "Paramètres visuels",
            'description' => "Configuration des éléments visuels",
            'button_size' => "Taille du bouton",
            "button_color" => "Couleur du bouton"
        ],
        'medias_section' => [
            'title' => "Médias",
            'description' => "Fichiers médias utilisés dans l'expérimentation",
            'medias' => "Médias",
            "images" => "Images",
            "sounds" => "Sons"
        ],
        'documents_section' => [
            'title' => "Documents",
            'description' => "Documents complémentaires",
            'documents' => "Documents"
        ],
        'ban_action' => [
            'label' => 'Bannir l\'expérimentateur',
            'reason' => 'Raison du bannissement',
            'helper' => 'Expliquez pourquoi vous bannissez cet expérimentateur',
            'modalHeading' => 'Bannir l\'expérimentateur',
            'modalDescription' => 'Cette action est irréversible. L\'expérimentateur et tous ses expérimentateurs secondaires n\'auront plus accès à la plateforme.'
        ],
        'notification' => [
            'banned' => 'Expérimentateur banni avec succès'
        ],
        'actions' => [
            'contact' => 'Contacter l\'expérimentateur',
            'edit' => 'Modifier',
        ]
    ],
    'experiments_sessions' => [
        'title' => 'Participants pour l\'expérimentation',
        'columns' => [
            'participant_number' => 'Identifiant du participant',
            'status' => 'Statut',
            'created_at' => 'Date de création',
            'completed_at' => 'Date de complétion',
            'experimenter' => 'Expérimentateur',
            'experimenter_types' => [
                'me' => '(Moi)',
                'creator' => '(Créateur)',
                'secondary' => '(Compte secondaire)',
                'collaborator' => '(Collaborateur)',
            ],
        ],
        'actions' => [
            'export' => 'Exporter les données',
            'details' => 'Détails',
            'export_all' => 'Exporter tous',
            'export_selection' => 'Exporter la sélection',
            'search' => 'Rechercher un mot ou une phrase',
            'reset' => 'Réinitialiser',
            'cancel' => 'Annuler',
            'reset_filter' => 'Réinitialiser le filtre',
        ],
        'notifications' => [
            'no_completed_sessions' => 'Aucune session complétée à exporter',
            'no_selection_completed' => 'Aucune session complétée sélectionnée',
        ],
        'access_denied' => 'Vous n\'avez pas accès à cette expérience',
        'search' => [
            'modal' => [
                'title' => 'Rechercher dans les sessions',
                'search_label' => 'Rechercher un mot ou une phrase',
                'search_placeholder' => 'Rechercher un mot (par exemple, jaune, animal, etc.)',
                'submit' => 'Chercher',
            ],
            'results' => [
                'occurrences_found_singular' => 'Occurrence de ":term" trouvée',
                'occurrences_found_plural' => 'Occurrences de ":term" trouvées',
                'no_results' => 'Aucune occurrence de ":term" trouvée',
                'locations' => [
                    'comments' => ':count commentaire|:count commentaires',
                    'feedback' => 'dans le feedback',
                ],
            ],
        ],
        'tabs' => [
            'all' => [
                'label' => 'Tous les résultats',
                'badge' => 'Tous',
            ],
            'creator' => [
                'label' => 'Mes résultats (Créateur)',
                'badge' => 'Créateur',
                'label_for_others' => 'Résultats du créateur',
            ],
            'mine' => [
                'label' => 'Mes résultats',
                'badge' => 'Mes résultats',
            ],
            'collaborators' => [
                'label' => 'Autres collaborateurs',
                'badge' => 'Collaborateurs',
            ],
        ],
        'buttons' => [
            'export_list' => 'Exporter la liste',
        ],
        'status' => [
            'completed' => 'Complétée',
            'created' => 'Créée',
        ],
    ],
    'experiments_sessions_export' => [
        'title' => 'Exporter les données de la session - :participant',
        'access_denied' => 'Seul le créateur peut exporter les données.',
        'tabs' => [
            'title' => 'Export Options',
            'basic_info' => 'Informations basiques',
            'group_data' => 'Données des groupes',
            'action_log' => 'Journal des actions'
        ],
        'fields' => [
            'basic_fields' => [
                'label' => 'Sélectionnez les champs à exporter',
                'options' => [
                    'participant_number' => 'Identifiant du participant',
                    'created_at' => 'Date de création',
                    'completed_at' => 'Date de complétion',
                    'duration' => 'Durée (secondes)',
                    'browser' => 'Navigateur',
                    'operating_system' => 'Système d\'exploitation',
                    'device_type' => 'Type d\'appareil',
                    'screen_dimensions' => 'Dimensions de l\'écran',
                    'feedback' => 'Feedback'
                ]
            ],
            'group_fields' => [
                'label' => 'Sélectionnez les informations de groupe à exporter',
                'options' => [
                    'group_names' => 'Noms des groupes',
                    'group_comments' => 'Commentaires des groupes',
                    'media_positions' => 'Positions finales des médias',
                    'media_interactions' => 'Nombre d\'interactions par média',
                    'group_compositions' => 'Composition des groupes'
                ]
            ],
            'action_fields' => [
                'label' => 'Sélectionnez les actions à exporter',
                'options' => [
                    'moves' => 'Déplacements',
                    'sounds' => 'Écoutes de sons',
                    'images' => 'Visualisations d\'images'
                ]
            ],
            'time_format' => [
                'label' => 'Format du temps',
                'options' => [
                    'timestamp' => 'Timestamp',
                    'readable' => 'Format lisible (HH:mm:ss)',
                    'elapsed' => 'Temps écoulé (secondes)'
                ]
            ]
        ],
        'csv' => [
            'participant' => 'Participant',
            'created_at' => 'Date création',
            'completed_at' => 'Date complétion',
            'duration' => 'Durée (s)',
            'browser' => 'Navigateur',
            'system' => 'Système',
            'device' => 'Appareil',
            'screen' => 'Dimensions écran',
            'feedback' => 'Feedback',
            'group_prefix' => 'Groupe :number',
            'name' => 'Nom',
            'comment' => 'Commentaire',
            'media' => 'Médias',
            'interactions' => 'Interactions',
            'position' => 'Position',
            'position_format' => 'X::x, Y::y',
            'time' => 'Temps',
            'type' => 'Type',
            'position_x' => 'Position X',
            'position_y' => 'Position Y',
            'action_types' => [
                'move' => 'Déplacement',
                'sound' => 'Lecture son',
                'image' => 'Vue image',
            ],
        ]
    ],
    'experiments_sessions_details' => [
        'access_denied' => 'Vous n\'avez pas accès aux détails de cette session.',
        'title' => 'Détails de la session - :participant',
        'sections' => [
            'participant' => 'Informations du participant',
            'technical' => 'Informations techniques',
            'feedback' => 'Feedback et notes',
            'canvas_size' => 'Dimensions du canvas',
        ],
        'fields' => [
            'participant_number' => 'Nom',
            'created_at' => 'Date de participation',
            'duration' => 'Durée',
            'browser' => 'Navigateur',
            'operating_system' => 'Système d\'exploitation',
            'device_type' => 'Type d\'appareil',
            'screen_width' => 'Largeur écran',
            'screen_height' => 'Hauteur écran',
            'feedback' => 'Feedback du participant',
            'errors' => 'Erreurs signalées',
            'examiner_notes' => 'Notes de l\'examinateur',
            'canvas_dimensions' => 'Dimensions du canvas',
            'screen_dpi' => 'Résolution écran',
        ],
        'time' => [
            'seconds' => 'secondes',
        ],
        'na' => 'N/A',
        'error_format' => 'Erreur :type à :time',
        'actions' => [
            'add_note' => 'Ajouter/Modifier une note',
            'search' => 'Rechercher un mot ou une phrase',
            'submit' => 'Chercher',
            'cancel' => 'Annuler',
        ],
        'notifications' => [
            'note_saved' => 'Note enregistrée avec succès',
        ],
        'breadcrumbs' => [
            'participants' => 'Participants pour l\'expérience : :name',
            'details' => 'Détails de la session - :participant',
        ],
        'search' => [
            'modal' => [
                'title' => 'Rechercher dans les sessions',
                'search_label' => 'Rechercher un mot ou une phrase',
                'search_placeholder' => 'Rechercher un mot (par exemple, jaune, animal, etc.)',
                'submit' => 'Chercher',
            ],
        ],
        'search_results' => [
            'occurrences_found' => 'Occurrence|Occurrences de ":term" trouvée|trouvées',
            'no_occurrences' => 'Aucune occurrence de ":term" trouvée',
            'locations' => [
                'comments' => ':count commentaire|commentaires',
                'feedback' => 'dans le feedback',
            ],
        ],
        'examiner_notes' => [
            'title' => 'Notes de l\'examinateur',
        ],
        'groups' => [
            'title' => 'Groupes d\'éléments',
            'comment_label' => 'Commentaire :',
            'media' => [
                'name' => 'Nom :',
                'position' => 'Position : X=:x, Y=:y',
                'interactions' => ':count interaction|interactions',
                'audio_unsupported' => 'Votre navigateur ne supporte pas l\'élément audio.',
                'play_count' => ':count lecture|:count lectures',
                'move_count' => ':count déplacement|:count déplacements',
                'group_changes' => ':count changement de groupe|:count changements de groupe',
            ],
        ],
        'actions_log' => [
            'title' => 'Journal des actions',
            'headers' => [
                'time' => 'Temps',
                'action' => 'Action',
                'details' => 'Détails',
            ],
            'actions' => [
                'move' => 'Déplacement',
                'sound' => 'Lecture son',
                'image' => 'Vue image',
                'simple_group_created' => 'Création d\'un groupe',
                'simple_group_change' => 'Changement de groupe',
            ],
            'details' => [
                'name' => 'Nom :',
                'position' => 'Position : X=:x, Y=:y',
                'group_created_details' => 'Création du groupe :name avec la couleur :color',
                'item_moved_details' => ':name déplacé du groupe :from vers le groupe :to',
            ],
        ],
    ],
    'experiments_statistics' => [
        'title' => 'Statistiques pour l\'expérimentation : :name',
        'widgets' => [
            'actions_timeline' => [
                'heading' => 'Timeline des actions',
                'session' => 'Session',
                'action' => 'Action',
                'time' => 'Temps'
            ],
            'completion' => [
                'heading' => 'Progression des sessions',
                'sessions' => 'Sessions'
            ],
            'device_type' => [
                'heading' => 'Types d\'appareils',
                'total' => 'Total'
            ],
            'duration' => [
                'heading' => 'Distribution des durées',
                'duration' => 'Durée',
                'min' => 'Min',
                'q1' => 'Q1',
                'median' => 'Médiane',
                'q3' => 'Q3',
                'max' => 'Max'
            ],
            'stats' => [
                'total' => [
                    'label' => 'Total des sessions',
                    'description' => 'Nombre total de sessions'
                ],
                'completed' => [
                    'label' => 'Sessions complétées',
                    'description' => ':percentage% de complétion'
                ],
                'duration' => [
                    'label' => 'Durée moyenne',
                    'description' => 'Temps moyen par session'
                ],
                'error' => [
                    'label' => 'Erreur',
                    'value' => 'Erreur de chargement'
                ]
            ]
        ]
    ],
    'bulk_experiment_session_export' => [
        'title' => 'Export de :count session:plural',
        'actions' => [
            'export_selected_sessions' => 'Exporter :count session|Exporter :count sessions',
            'back_to_list' => 'Retour à la liste',
        ],
        'export_options' => [
            'title' => 'Options d\'export',
            'basic' => 'Informations basiques',
            'group' => 'Données des groupes',
        ],
        'labels' => [
            'select_fields' => 'Sélectionner les champs',
            'select_group_data' => 'Sélectionner les données des groupes',
        ],
        'basic_fields' => [
            'participant_number' => 'Numéro du participant',
            'experimenter_info' => 'Informations sur l\'expérimentateur (nom et type)',
            'dates' => 'Dates (création et complétion)',
            'duration' => 'Durée',
            'system_info' => 'Informations système (navigateur, OS, appareil, résolution)',
            'feedback' => 'Feedback',
        ],
        'group_fields' => [
            'group_names' => 'Noms des groupes',
            'group_comments' => 'Commentaires des groupes',
            'media_info' => 'Informations sur les médias (noms, positions, interactions)',
        ],
        'helper_text' => [
            'basic' => 'Ces informations seront exportées pour chaque session',
            'group' => 'Ces informations seront exportées pour chaque groupe',
        ],
        'error' => [
            'no_selection' => 'Aucune session sélectionnée',
            'not_found' => 'Session introuvable',
        ],
        'csv_headers' => [
            'session_id' => 'ID de session',
            'participant_number' => 'Numéro du participant',
            'experimenter_name' => 'Nom de l\'expérimentateur',
            'experimenter_type' => 'Type d\'expérimentateur',
            'created_at' => 'Date de création',
            'completed_at' => 'Date de complétion',
            'duration_seconds' => 'Durée (secondes)',
            'browser' => 'Navigateur',
            'system' => 'Système d\'exploitation',
            'device' => 'Type d\'appareil',
            'screen_width' => 'Largeur écran',
            'screen_height' => 'Hauteur écran',
            'feedback' => 'Feedback',
            'group_name' => 'Nom du groupe :number',
            'group_comment' => 'Commentaire du groupe :number',
            'media_name' => 'Nom du média :number (groupe :group)',
            'media_interactions' => 'Interactions média :number (groupe :group)',
            'media_x' => 'Position X média :number (groupe :group)',
            'media_y' => 'Position Y média :number (groupe :group)',
        ],
        'experimenter_types' => [
            'creator' => 'Créateur',
            'secondary' => 'Secondaire',
            'collaborator' => 'Collaborateur',
            'na' => 'N/A',
        ],
        'values' => [
            'na' => 'N/A',
        ],
        'download_filename' => 'sessions-export-:date.csv',
    ],
    'experiment_session_export' => [
        'title' => 'Export de la session : :participant',
        'actions' => [
            'back_to_list' => 'Retour à la liste',
            'export_csv' => 'Exporter en CSV',
        ],
        'export_options' => [
            'title' => 'Options d\'export',
            'basic' => 'Informations basiques',
            'group' => 'Données des groupes',
        ],
        'labels' => [
            'select_fields' => 'Sélectionner les champs',
            'select_group_data' => 'Sélectionner les données des groupes',
        ],
        'basic_fields' => [
            'participant_number' => 'Numéro du participant',
            'experimenter_info' => 'Informations sur l\'expérimentateur (nom et type)',
            'dates' => 'Dates (création et complétion)',
            'duration' => 'Durée',
            'system_info' => 'Informations système (navigateur, OS, appareil, résolution)',
            'feedback' => 'Feedback',
        ],
        'group_fields' => [
            'group_names' => 'Noms des groupes',
            'group_comments' => 'Commentaires des groupes',
            'media_info' => 'Informations sur les médias (noms, positions, interactions)',
        ],
        'helper_text' => [
            'basic' => 'Ces informations seront exportées pour chaque session',
            'group' => 'Ces informations seront exportées pour chaque groupe',
        ],
        'error' => [
            'not_found' => 'Session introuvable',
        ],
        'csv_headers' => [
            'session_id' => 'ID de session',
            'participant_number' => 'Numéro du participant',
            'experimenter_name' => 'Nom de l\'expérimentateur',
            'experimenter_type' => 'Type d\'expérimentateur',
            'created_at' => 'Date de création',
            'completed_at' => 'Date de complétion',
            'duration_seconds' => 'Durée (secondes)',
            'browser' => 'Navigateur',
            'system' => 'Système d\'exploitation',
            'device' => 'Type d\'appareil',
            'screen_width' => 'Largeur écran',
            'screen_height' => 'Hauteur écran',
            'feedback' => 'Feedback',
            'group_name' => 'Nom du groupe :number',
            'group_comment' => 'Commentaire du groupe :number',
            'media_name' => 'Nom du média :number (groupe :group)',
            'media_interactions' => 'Interactions média :number (groupe :group)',
            'media_x' => 'Position X média :number (groupe :group)',
            'media_y' => 'Position Y média :number (groupe :group)',
        ],
        'experimenter_types' => [
            'creator' => 'Créateur',
            'secondary' => 'Secondaire',
            'collaborator' => 'Collaborateur',
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
            'back_to_list' => 'Retour à la liste',
            'export_csv' => 'Exporter en CSV',
        ],
        'tabs' => [
            'creator' => 'du créateur',
            'mine' => 'mes résultats',
            'collaborators' => 'des collaborateurs',
            'all' => 'tous les résultats',
        ],
        'export_options' => [
            'title' => 'Options d\'export',
            'basic' => 'Informations basiques',
            'group' => 'Données des groupes',
        ],
        'labels' => [
            'select_fields' => 'Sélectionner les champs',
            'select_group_data' => 'Sélectionner les données des groupes',
        ],
        'basic_fields' => [
            'participant_number' => 'Numéro du participant',
            'experimenter_info' => 'Informations sur l\'expérimentateur (nom et type)',
            'dates' => 'Dates (création et complétion)',
            'duration' => 'Durée',
            'system_info' => 'Informations système (navigateur, OS, appareil, résolution)',
            'feedback' => 'Feedback',
        ],
        'group_fields' => [
            'group_names' => 'Noms des groupes',
            'group_comments' => 'Commentaires des groupes',
            'media_info' => 'Informations sur les médias (noms, positions, interactions)',
        ],
        'helper_text' => [
            'basic' => 'Ces informations seront exportées pour chaque session',
            'group' => 'Ces informations seront exportées pour chaque groupe',
        ],
        'error' => [
            'experiment_not_found' => 'Expérimentation non trouvée',
        ],
        'csv_headers' => [
            'session_id' => 'ID de session',
            'participant_number' => 'Numéro du participant',
            'experimenter_name' => 'Nom de l\'expérimentateur',
            'experimenter_type' => 'Type d\'expérimentateur',
            'created_at' => 'Date de création',
            'completed_at' => 'Date de complétion',
            'duration_seconds' => 'Durée (secondes)',
            'browser' => 'Navigateur',
            'system' => 'Système d\'exploitation',
            'device' => 'Type d\'appareil',
            'screen_width' => 'Largeur écran',
            'screen_height' => 'Hauteur écran',
            'feedback' => 'Feedback',
            'group_name' => 'Nom du groupe :number',
            'group_comment' => 'Commentaire du groupe :number',
            'media_name' => 'Nom du média :number (groupe :group)',
            'media_interactions' => 'Interactions média :number (groupe :group)',
            'media_x' => 'Position X média :number (groupe :group)',
            'media_y' => 'Position Y média :number (groupe :group)',
        ],
        'experimenter_types' => [
            'creator' => 'Créateur',
            'secondary' => 'Secondaire',
            'collaborator' => 'Collaborateur',
            'na' => 'N/A',
        ],
        'values' => [
            'na' => 'N/A',
        ],
        'download_filename' => 'all-sessions-export-:date.csv',
    ],
];
