<?php

declare(strict_types=1);

return [
    'pages' => [
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
                'type' => [
                    'label' => 'Type',
                    'options' => [
                        'image' => 'Image',
                        'sound' => 'Son',
                        'image_sound' => 'Image & Son',
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
                'sessions_count' => 'Nombre de sessions',
                'created_at' => 'Créé le',
            ],
            'tabs' => [
                'all' => 'Toutes les expérimentations',
                'sound' => 'Sons',
                'image' => 'Images',
                'image_sound' => 'Image et Son'
            ],
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
                    'sessions_count' => 'Nombre de participants',
                    'howitworks' => 'Disponible sur "Comment ça marche ?"',
                    'created_at' => 'Date de création',
                ]
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
                    'type' => [
                        'label' => 'Type d\'accès',
                        'options' => [
                            'results' => 'Résultats uniquement',
                            'access' => 'Collaboration complète',
                        ]
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
                    'sessions_count' => 'Nombre de participants',
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
                'results' => 'Résultats seulement',
                'access' => 'Collaboration Complète',
            ],
            'notifications' => [
                'no_completed_sessions' => 'Aucune session complétée à exporter',
                'no_selection_completed' => 'Aucune session complétée sélectionnée'
            ],
        ],
        'secondary_user' => [
            'title' => 'Expérimentations attribuées à :username',
            'table' => [
                'column' => [
                    'name' => 'Nom de l\'expérimentation',
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
                    'created_at' => 'Date de création',
                    'can_configure' => 'Configuration',
                    'can_pass' => 'Sessions',
                ]
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
        ]
    ],
];
