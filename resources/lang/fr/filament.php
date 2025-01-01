<?php

return [
    'widgets' => [
        'dashboard_table' => [
            'experiments' => [
                'supervisor' => [
                    'name' => 'Expérimentations',
                    'description' => 'Total des expérimentations sous votre supervision',
                ],
                'principal' => [
                    'name' => 'Mes expérimentations',
                    'description' => 'Total de vos expérimentations',
                ],
            ],
            'sessions' => [
                'supervisor' => [
                    'name' => 'Sessions',
                    'description' => 'Nombre total de sessions',
                ],
                'principal' => [
                    'name' => 'Sessions',
                    'description' => 'Nombre total de sessions',
                ],
            ],
            'users' => [
                'supervisor' => [
                    'name' => 'Expérimentateurs',
                    'description' => 'Expérimentateurs principales',
                ],
                'principal' => [
                    'name' => 'Expérimentateurs',
                    'description' => 'Expérimentateurs secondaires',
                ],
            ],
            'completions' => [
                'supervisor' => [
                    'name' => 'Taux de complétion',
                    'description' => 'Sessions terminées avec succès'
                ],
                'principal' => [
                    'name' => 'Taux de complétion',
                    'description' => 'Sessions terminées avec succès'
                ],
            ],
            'sessions_test' => [
                'supervisor' => [
                    'name' => 'Sessions Test',
                    'description' => 'Sessions en cours de test'
                ],
                'principal' => [],
            ],
            'registrations' => [
                'supervisor' => [
                    'name' => 'Demandes d\'inscription',
                    'description' => 'En attente d\'approbation',
                ],
            ],
            'banned' => [
                'supervisor' => [
                    'name' => 'Utilisateurs bannis',
                    'description' => 'Comptes désactivés',
                ]
            ]
        ],
        'banned' => [
            'principal' => [
                'title' => 'Compte banni',
                'description' => 'Votre compte a été banni. Si vous pensez qu\'il s\'agit d\'une erreur ou souhaitez faire une demande de débannissement,
                    vous pouvez contacter l\'administrateur via la page "Contacter l\'administrateur".',
            ],
            'secondary' => [
                'title' => 'Compte banni',
                'description' => 'L\'expérimentateur principal de votre compte a été banni. L\'accès à vos fonctionnalités est temporairement restreint.
                    Veuillez contacter l\'administrateur via la page "Contacter l\'administrateur" pour plus d\'informations.',
            ],
        ],
        'access_requests' => [
            'heading' => 'Expérimentations empruntés',
            'column' => [
                'name' => 'Expérimentation',
                'created_by' => 'Créateur',
                'type' => 'Type',
                'results' => 'Résultats',
                'pass' => 'Passage',
                'status' => 'Statut',
                'pending' => 'En attente',
                'approved' => 'Approuvé',
                'created_at' => 'Demandé le',
                'statistics' => 'Statistiques',
                'sessions' => 'Sessions',
                'actions' => 'Actions',
            ],
        ],
        'experiment_table' => [
            'title' => 'Mes expérimentations',
            'title_secondary_experimenter' => 'Expérimentations attribuées',
            'title_default' => 'Expérimentations disponibles',
            'column' => [
                'creator' => 'Créé par',
                'name' => 'Nom de l\'expérimentation',
                'status' => 'État',
                'start' => "Démarré",
                'pause' => 'En pause',
                'stop' => 'Arrêté',
                'test' => 'En test',
                'type' => [
                    'label' => 'Type',
                    'options' => [
                        'image' => 'Image',
                        'sound' => 'Son',
                        'image_sound' => 'Image et Son',
                    ]
                ],
                'sessions_count' => 'Nombre de participants',
                'created_at' => 'Date de création',
                'user_role' => 'Votre rôle'
            ],
            'roles' => [
                'supervisor' => 'Superviseur',
                'creator' => 'Créateur',
                'manager' => 'Responsable',
                'observer' => 'Observateur'
            ],
            'actions' => [
                'statistics' => 'Statistiques',
                'details' => 'Détails',
                'edit' => 'Editer',
                'contact_creator' => 'Contacter le créateur',
                'results' => 'Résultats',
            ]
        ],
    ],
    'pages' => [
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
                ]
            ],
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
                'success' => 'Message envoyé avec succès'
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
            'title' => 'Participants pour l\'expérimentation : :name',
            'columns' => [
                'participant_number' => 'Identifiant du participant',
                'status' => 'Statut',
                'created_at' => 'Date de création',
                'completed_at' => 'Date de complétion',
            ],
            'actions' => [
                'export' => 'Exporter les données',
                'details' => 'Détails',
                'export_all' => 'Exporter tous',
                'export_selection' => 'Exporter la sélection'
            ],
            'notifications' => [
                'no_completed_sessions' => 'Aucune session complétée à exporter',
                'no_selection_completed' => 'Aucune session complétée sélectionnée'
            ],
            'access_denied' => 'Vous n\'avez pas accès à cette expérience',
            'csv_headers' => [
                'participant' => 'Participant',
                'created_at' => 'Date création',
                'completed_at' => 'Date complétion',
                'duration' => 'Durée (s)',
                'browser' => 'Navigateur',
                'system' => 'Système',
                'device' => 'Appareil',
                'screen_dimensions' => 'Dimensions écran',
                'feedback' => 'Feedback',
                'group' => [
                    'name' => 'Groupe :number - Nom',
                    'comment' => 'Groupe :number - Commentaire',
                    'media' => 'Groupe :number - Médias',
                    'media_interactions' => 'Groupe :number - :media - Interactions',
                    'media_position' => 'Groupe :number - :media - Position'
                ]
            ]
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
                'feedback' => 'Feedback et Notes'
            ],
            'fields' => [
                'participant_number' => 'Nom',
                'created_at' => 'Date de participation',
                'duration' => 'Durée',
                'browser' => 'Navigateur',
                'operating_system' => 'Système d\'exploitation',
                'device_type' => 'Type d\'appareil',
                'screen_width' => 'Largeur d\'écran',
                'screen_height' => 'Hauteur d\'écran',
                'feedback' => 'Feedback du participant',
                'errors' => 'Erreurs rapportées',
                'examiner_notes' => 'Notes de l\'examinateur'
            ],
            'time' => [
                'seconds' => 'secondes'
            ],
            'na' => 'N/A',
            'error_format' => 'Erreur :type à :time',
            'actions' => [
                'add_note' => 'Ajouter/Modifier la note'
            ],
            'notifications' => [
                'note_saved' => 'Note enregistrée avec succès'
            ],
            'breadcrumbs' => [
                'participants' => 'Participants pour l\'expérimentation : :name',
                'details' => 'Détails de la session - :participant'
            ]
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
    ],
    'resources' => [
        'experiment_list' => [
            'title' => 'Liste des Expérimentations',
            'titleFilter' => 'Liste des expériences de :username',
            'column' => [
                'created_by' => 'Crée par',
                'name' => 'Nom de l\'expérimentation',
                'type' => 'Type',
                'status' => 'Status',
                'start' => "Démarré",
                'pause' => 'En pause',
                'stop' => 'Arrêté',
                'test' => 'En test',
                'none' => 'Aucun',
                'sound' => 'Son',
                'image' => 'Image',
                'image_sound' => 'Image et son',
                'sessions_count' => 'Nombre de sessions',
                'created_at' => 'Créé le',
                'action' => 'Voir l\'expérimentation'
            ],
            'tabs' => [
                'all' => 'Toutes les expérimentations',
                'sound' => 'Sons',
                'image' => 'Images',
                'image_sound' => 'Image et Son'
            ],
            'actions' => [
                'clearFilter' => 'Effacer le filtre'
            ]
        ],
        'my_experiment' => [
            'navigation_label' => 'Mes Expérimentations',
            'navigation_group' => 'Experiments',
            'label' => 'Expérimentation',
            'plural' => 'Mes Expérimentations',
            'section_base' => [
                'heading' => 'Configuration de base',
                'description' => 'Paramètres principaux de votre expérimentation',
            ],
            'general_section' => [
                'heading' => 'Informations générales',
                'description' => 'Définissez les caractéristiques principales de votre expérience',
            ],
            'apparence_section' => [
                'heading' => 'Apparence',
                'description' => 'Personnalisez l\'apparence des boutons dans votre expérience',
            ],
            'section_description' => [
                'heading' => 'Contenu',
                'description' => 'Décrivez votre expérience et fournissez les instructions nécessaires',
            ],
            'section_media' => [
                'heading' => 'Médias',
                'description' => 'Ajoutez vos fichiers médias (limite de 20Mo par fichier)',
            ],
            'section_documents' => [
                'heading' => 'Documents complémentaires',
                'description' => 'Ajoutez des documents supplémentaires liés à votre expérience',
            ],
            'form' => [
                'doi' => 'DOI',
                'doi_placeholder' => 'Saisissez le DOI de votre expérimentation',
                'doi_helper' => 'Le digital object identifier (DOI) est un mécanisme d\'identification de ressources stable, unique pour votre expérimentation.',
                'howitworks' => 'Comment ça marche',
                'howitworks_helper' => 'Si activé, l\'expérimentation en mode "test" sera visible sur la page Comment ça marche ?. Désactivé automatiquement si le status change.',
                'status' => [
                    'label' => 'Démarrer l\'expérience ?',
                    'helper_text' => 'Mode "test" pour essayer sans sauvegarder de résultats. Mode "start" pour démarrer réellement l\'expérience.',
                    'options' => [
                        'stop' => 'Ne pas rendre accessible',
                        'start' => 'Rendre accessible',
                        'test' => 'Rendre accessible en mode test',
                    ]
                ],
                'link' => 'Lien de l\'expérience',
                'link_helper' => 'Lien unique pour accéder à votre expérience. Cliquez pour copier.',
                'link_copied' => "Copié dans le presse-papier",
                'name' => 'Nom',
                'name_helper' => 'Donnez un nom unique et descriptif à votre expérimentation',
                'type' => [
                    'label' => 'Type de médias',
                    'helper_text' => 'Choisissez le type de médias pour votre expérience. Cela déterminera les types de fichiers que vous pourrez uploader.',
                    'options' => [
                        'image' => 'Images uniquement',
                        'sound' => 'Sons uniquement',
                        'image_sound' => 'Images et Sons',
                    ]
                ],
                'button_size' => [
                    'label' => 'Taille du bouton',
                    'helper_text' => 'La taille minimale recommandée est de 60px pour une bonne ergonomie'
                ],
                'button_color' => [
                    'label' => 'Couleur des boutons',
                    'helper_text' => 'Choisissez une couleur visible pour les boutons de sons'
                ],
                'description' => 'Description',
                'description_helper' => 'Décrivez les enjeux de votre expérimentation. Cette description sera visible publiquement.',
                'instructions' => 'Instructions',
                'instructions_helper' => 'Fournissez des instructions claires pour les participants.',
                'media' => 'Médias',
                'media_sound_helper' => 'Formats audio acceptés : MP3, WAV, AAC, OGG (max 20Mo)',
                'media_image_helper' => 'Formats image acceptés : JPG, JPEG, PNG, GIF (max 20Mo)',
                'media_image_sound_helper' => 'Formats acceptés : JPG, JPEG, PNG, GIF, WebP, MP3, WAV, AAC, OGG (max 20Mo)',
                'documents' => 'Documents',
                'documents_helper' => 'Formats acceptés : PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, CSV (max 20Mo)',
            ],
            'table' => [
                'columns' => [
                    'name' => 'Nom',
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
                    'howitworks' => 'Disponible sur "Comment ça marche ?"',
                    'created_at' => 'Date de création',
                ]
            ],
            'actions' => [
                'create' => 'Créer une expérimentation',
                'contact' => 'Contacter l\'expérimentateur principal',
                'results' => 'Voir les résultats',
                'details' => 'Détails',
                'statistics' => 'Statistiques',
                'edit' => 'Modifier',
                'delete' => 'Supprimer',
                'more_actions' => 'Actions',
                'session' => 'Session',
                'session_link' => 'Lien de l\'expérience',
                'exports' => 'Sauvegarder l’expérimentation',
                'manage_experiment' => [
                    'label' => 'Session',
                    'success' => 'Session mise à jour avec succès'
                ],
                'status' => [
                    'start' => 'Démarrer',
                    'pause' => 'Pause',
                    'stop' => 'Arrêter',
                    'test' => 'Test',
                    'start_desc' => 'Active la session et génère un lien unique s\'il n\'en existe pas. La session devient accessible aux participants.',
                    'pause_desc' => 'Suspend temporairement la session. Le lien reste actif, mais les participants ne peuvent pas continuer la session jusqu\'à sa reprise.',
                    'stop_desc' => 'Termine la session et désactive le lien. Pour réactiver la session, vous devez la redémarrer, ce qui génère un nouveau lien.',
                    'test_desc' => 'Active la session en mode test. La session est accessible aux participants, mais aucun résultat n\'est sauvegardé.'
                ],
                'export' => [
                    'label' => 'Exporter l\'expérimentation',
                    'json' => 'Exporter en JSON',
                    'xml' => 'Exporter en XML',
                    'desc' => 'Sélectionnez le format dans lequel vous souhaitez exporter les données de l\'expérience.',
                    'media_info' => 'L\'inclusion des médias ajoutera tous les fichiers médias associés à l\'export.',
                    'include_media' => 'Inclure les médias',
                    'success' => 'Export réalisé avec succès'
                ],
                'delete' => [
                    'heading' => 'Suppresion définitivement',
                    'desc_issues_delete' => 'Cette expérimentation ne peut pas être supprimée car elle est partagée ou a des demandes en attente.',
                    'confirm_delete' => 'Pour supprimer cette expérimentation, veuillez saisir le code ci-dessous.',
                    'code_confirm' => 'Code de confirmation',
                    'code' => 'Code',
                    'code_fail' => 'Le code de confirmation est incorrect',
                ],
            ],
            'notifications' => [
                'created' => 'Expérimentation créée avec succès',
                'updated' => 'Expérimentation mise à jour avec succès',
                'deleted' => 'Expérimentation supprimée avec succès',
                'session_updated' => 'État de la session mis à jour avec succès',
                'export_success' => 'Export réalisé avec succès'
            ],
            'messages' => [
                'no_active_session' => 'Aucune session active'
            ]
        ],
        'experiment-access-request' => [
            'label' => 'Demande d\'accès',
            'plural' => 'Demandes d\'accès',
            'navigation_label' => 'Demandes d\'accès',
            'form' => [
                'section' => [
                    'status_title' => 'Status de la requête',
                    'status_description' => 'Approuver ou rejeter la demande',
                    'information_title' => 'Information sur la demande',
                    'information_description' => 'Détails de la demande',
                ],
                'status' => [
                    'label' => 'Statut',
                    'options' => [
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
                        'revoked' => 'Révoquée',
                    ]
                ],
                'response_message' => [
                    'label' => 'Message de réponse',
                    'helper_text' => 'Veuillez expliquer la raison du refus',
                ],
                'request_message' => [
                    'label' => 'Message de demande',
                ],
                'experiment' => [
                    'label' => 'Nom de l\'expérimentation',
                ],
                'user' => [
                    'label' => 'Demande d\'accès émise par',
                ],
                'duplicate' => [
                    'copy' => '(Copie)',
                    'success' => 'Expérience dupliquée avec succès',
                    'error' => 'Erreur lors de la duplication',
                ],
                'informations' => [
                    'information_access' => 'Information sur les types d\'accès',
                    'result_access' => 'Accès aux résultats',
                    'result_description' => 'Permettre l\'accès aux résultats de l\'expérience',
                    'experiment_access' => 'Accès à l\'expérience',
                    'experiment_description' => 'Donne accès aux résultats et permet de faire passer des sessions.',
                    'duplicate_access' => 'Dupliquer l\'expérience',
                    'duplicate_description' => 'Crée une copie de l\'expérience. L\'approbation est définitive et ne peut être révoquée.',
                ]
            ],
            'table' => [
                'columns' => [
                    'experiment' => 'Expérience',
                    'user' => 'Demandeur',
                    'type' => 'Type',
                    'type_options' => [
                        'access' => 'Accès à l\'expérience',
                        'results' => 'Accès aux résultats',
                    ],
                    'status' => 'Statut',
                    'created_at' => 'Date de demande',
                ],
                'actions' => [
                    'informations' => 'Informations',
                    'revoke' => 'Révoquer l\'accès',
                    'revoke_label' => 'Message de révocation',
                    'revoke_message' => "Veuillez expliquer pourquoi vous révoquez l'accès...",
                    'revoke_description' => 'Êtes-vous sûr de vouloir révoquer l\'accès ? L\'utilisateur en sera informé.',
                    'view' => 'Voir les détails',
                ],
                'message' => [
                    'banned' => 'Votre compte est banni.',
                    'banned_secondary' => 'L\'expérimentateur principal de votre compte est banni.',
                    'no_access' => 'Vous n\'avez pas accès à cette expérience.',
                    'no_access_section' => 'Vous n\'avez pas accès à cette section.',
                ]
            ],
            'tabs' => [
                'all' => 'Toutes les demandes',
                'pending' => 'Demandes en attente',
                'approved' => 'Demandes approuvées',
                'revoked' => 'Demandes rejetées/révoquées',
            ],
        ],
        'borrowed_experiment' => [
            'label' => 'Expérimentations empruntées',
            'plural' => 'Expérimentations empruntées',
            'navigation_label' => 'Expérimentations empruntées',
            'table' => [
                'columns' => [
                    'experiment' => 'Expérimentation',
                    'created_by' => 'Créateur',
                    'type_experiments' => [
                        'label' => 'Type d\'expérimentation',
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
                        ]
                    ],
                    'type_access' => [
                        'label' => 'Type d\'accès',
                        'results' => 'Résultats seulement',
                        'access' => 'Collaboration Complète'
                    ],
                    'access_granted_at' => 'Accès accordé le',
                ],
                'actions' => [
                    'sessions' => 'Gérer les sessions',
                    'view' => 'Voir les détails',
                    'statistics' => 'Statistiques',
                    'results' => 'Résultats',
                ],
                'message' => [
                    'banned' => 'Votre compte est banni.',
                    'banned_secondary' => 'L\'expérimentateur principal de votre compte est banni.',
                    'no_access' => 'Vous n\'avez pas accès à cette expérience.',
                    'no_access_section' => 'Vous n\'avez pas accès à cette section.',
                ]
            ],
            'tabs' => [
                'all' => 'Toutes les expérimentations',
                'pending' => 'Résultats seulement',
                'approved' => 'Collaboration Complète',
            ],
            'notifications' => [
                'no_completed_sessions' => 'Aucune session complétée à exporter',
                'no_selection_completed' => 'Aucune session complétée sélectionnée'
            ],
        ],
        'users' => [
            'title' => "Utilisateur",
            'table' => [
                'name' => "Nom",
                'email' => "Email",
                'university' => "Université",
                'status' => [
                    'label' => "Status",
                    'approved' => "Approuvé"
                ],
                'role' => [
                    'label' => 'Rôle',
                    'options' => [
                        'supervisor' => 'Superviseur',
                        'principal_experimenter' => 'Expérimentateur principal',
                        'secondary_experimenter' => 'Expérimentateur secondaire',
                    ]
                ]
            ],
            'actions' => [
                'create' => "Ajouter un utilisateur",
                'contact' => 'Contact',
                'show_experiment' => 'Voir les expérimentations',
                'details' => "Détails",
                'delete' => "Supprimer l'utilisateur"
            ],
            'form' => [
                'name' => "Nom",
                'email' => "Email",
                'university' => "Université",
                'role' => [
                    'label' => 'Rôle',
                    'options' => [
                        'supervisor' => 'Superviseur',
                        'principal_experimenter' => 'Expérimentateur principal',
                        'secondary_experimenter' => 'Expérimentateur secondaire',
                    ]
                ],
                'registration_reason' => "Motif d'inscription",
                'banned_reason' => "Motif de bannissement",
                'status' => [
                    'label' => 'Status',
                    'options' => [
                        'approved' => 'Approuvé',
                        'banned' => 'Bannir',
                    ]
                ],
                'section' => [
                    'history_section' => "Historique des actions",
                    'history_section_description' => "Historique des différentes actions effectuées sur ce compte",
                    'registration_reason' => "Motif d'inscription",
                    'rejection_reason' => "Motif du rejet",
                    'banned_reason' => "Motif de bannissement",
                    'unbanned_reason' => "Motif de débannissement",
                ],
            ],
            'notification' => [
                'banned_reason' => 'Compte principal banni :',
                'banned' => 'Utilisateur banni avec succès',
            ]

        ],
        'banned' => [
            'title' => "Utilisateurs bannis",
            'form' => [
                'name' => "Nom",
                'email' => "Email",
                'university' => "Université",
                'registration_reason' => "Motif d'inscription",
                'banned_reason' => "Motif de bannissement",
                'status' => [
                    'unban' => "Débannir"
                ],
                'unbanned_reason' => [
                    'label' => "Motif du débannissement",
                    "placeholder" => "Raison du débannissement",
                    'helper' => "Une fois le débannissement enregistré, le compte principal reçoit un email. Les comptes secondaires qu'il a créé sont aussi débanni et reçoivent aussi un email"
                ],
            ],
            'table' => [
                'name' => 'Nom',
                'email' => 'Email',
                'university' => 'Université',
                'created_at' => 'Date de demande',
                'status' => [
                    'label' => 'Statut',
                    'banned' => 'Banni',
                ],
            ],
            'notification' => [
                'unbanned_reason' => 'Compte principal débanni :',
                'unbanned' => 'Utilisateur débanni avec succès'
            ],
            'action' => [
                'create' => "Ajouter un utilisateur",
                'contact' => 'Contact',
                'show_experiment' => 'Voir les expérimentations',
                'details' => "Détails",
                'delete' => "Supprimer l'utilisateur"
            ]
        ],
        'pending_registration' => [
            'title' => "Demandes D'inscription",
            'form' => [
                'name' => "Nom",
                'email' => "Email",
                'university' => "Université",
                'registration_reason' => "Motif d'inscription",
                'banned_reason' => "Motif de bannissement",
                'status' => [
                    'approved' => "Approuvé",
                    'rejected' => "Rejeté",
                ],
                'rejected_reason' => [
                    'label' => "Motif du rejet",
                    "placeholder" => "Raison du rejet",
                    'helper' => "Expliquez la raison du rejet de la demande d'inscription"
                ],
            ],
            'table' => [
                'name' => 'Nom',
                'email' => 'Email',
                'university' => 'Université',
                'created_at' => 'Date de demande',
                'status' => [
                    'label' => 'Statut',
                    'pending' => 'En Attente',
                ],
            ],
            'notification' => [
                'rejected_reason' => 'Compte principal débanni :',
                'rejected' => 'Utilisateur débanni avec succès'
            ],
            'action' => [
                'create' => "Ajouter un utilisateur",
                'contact' => 'Contact',
                'show_experiment' => 'Voir les expérimentations',
                'details' => "Détails",
                'delete' => "Supprimer l'utilisateur"
            ]
        ],
        'rejected_user' => [
            'title' => "Utilisateur rejetés",
            'form' => [
                'name' => "Nom",
                'email' => "Email",
                'university' => "Université",
                'registration_reason' => "Motif d'inscription",
                'banned_reason' => "Motif de bannissement",
                'status' => [
                    'approved' => "Approuvé",
                    'rejected' => "Rejeté",
                ],
                'rejected_reason' => "Motif du rejet",
            ],
            'table' => [
                'name' => 'Nom',
                'email' => 'Email',
                'university' => 'Université',
                'created_at' => 'Date de demande',
                'status' => [
                    'label' => 'Statut',
                    'rejected' => 'Rejeté',
                ],
            ],
            'notification' => [
                'rejected_reason' => 'Compte principal débanni :',
                'rejected' => 'Utilisateur débanni avec succès'
            ],
            'action' => [
                'create' => "Ajouter un utilisateur",
                'contact' => 'Contact',
                'show_experiment' => 'Voir les expérimentations',
                'details' => "Détails",
                'delete' => "Supprimer l'utilisateur"
            ]
        ],
        'experiment' => [
            'actions' => [
                'create' => 'Créer une expérimentation',
            ],
            'label' => 'Expérience',
            'plural' => 'Liste des Expériences',
            'form' => [
                'status' => [
                    'label' => 'Démarrer l\'expérience ?',
                    'options' => [
                        'none' => 'Non',
                        'start' => 'Oui',
                    ],
                ],
                'link' => 'Lien',
                'name' => 'Nom',
                'type' => [
                    'label' => 'Type',
                    'options' => [
                        'image' => 'Image',
                        'sound' => 'Son',
                        'image_sound' => 'Image et Son',
                    ]
                ],
                'button_size' => [
                    'label' => 'Taille du bouton',
                    'placeholder' => 'Taille du bouton en px'
                ],
                'button_color' => [
                    'label' => 'Couleur du bouton',
                    'placeholder' => 'Couleur du bouton'
                ],
                'description' => 'Description',
                'media' => 'Médias',
            ],
            'table' => [
                'columns' => [
                    'name' => 'Nom',
                    'type' => 'Type',
                    'status' => 'Statut',
                    'created_at' => 'Créé le',
                    'updated_at' => 'Modifié le',
                ],
                'actions' => [
                    'manageExperiment' => [
                        'label' => 'Session',
                        'fields' => [
                            'link' => 'Lien de l\'expérience',
                            'experimentStatus' => [
                                'options' => [
                                    'start' => 'Démarrer',
                                    'pause' => 'Pause',
                                    'stop' => 'Arrêter'
                                ]
                            ],
                            'info' => [
                                'start' => 'Active la session et génère un lien unique s\'il n\'en existe pas. La session devient accessible aux participants.',
                                'pause' => 'Suspend temporairement la session. Le lien reste actif, mais les participants ne peuvent pas continuer la session jusqu\'à sa reprise.',
                                'stop' => 'Termine la session et désactive le lien. Pour réactiver la session, vous devez la redémarrer, ce qui génère un nouveau lien.'
                            ]
                        ],
                        'notification' => 'État de la session d\'expérience mis à jour.'
                    ],
                    'export' => [
                        'label' => 'Exporter',
                        'fields' => [
                            'placeholder' => 'Sélectionnez le format dans lequel vous souhaitez exporter les données de l\'expérience. Vous pouvez choisir JSON, XML ou les deux.',
                            'json' => 'Exporter en JSON',
                            'xml' => 'Exporter en XML',
                            'media_info' => 'L\'inclusion des médias ajoutera tous les fichiers médias associés à l\'export. Cette option créera un dossier zip contenant votre configuration et les fichiers médias.',
                            'include_media' => 'Inclure les médias'
                        ],
                        'success' => 'Exporté avec succès'
                    ]
                ]
            ]
        ]
    ],
];
