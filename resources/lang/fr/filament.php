<?php

return [
    'widgets' => [
        'experiment_table' => [
            'columns' => [
                'creator' => 'Créé par',
                'name' => 'Nom de l\'expérimentation',
                'status' => 'État',
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
                'details' => 'Détails'
            ]
        ]
    ],
    'resources' => [
        'my_experiment' => [
            'navigation_label' => 'Mes Expérimentations',
            'navigation_group' => 'Experiments',
            'label' => 'Expérimentation',
            'plural' => 'Mes Expérimentations',

            'form' => [
                'status' => [
                    'label' => 'Démarrer l\'expérience ?',
                    'options' => [
                        'none' => 'Non',
                        'start' => 'Oui',
                    ]
                ],
                'link' => [
                    'label' => 'Lien',
                    'no_active_session' => 'Aucune session active'
                ],
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
                'media' => 'Médias'
            ],

            'table' => [
                'columns' => [
                    'name' => 'Nom',
                    'type' => 'Type',
                    'status' => 'État',
                    'created_at' => 'Date de création',
                ]
            ],

            'actions' => [
                'create' => 'Créer une expérimentation',
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
                    'start_desc' => 'Active la session et génère un lien unique s\'il n\'en existe pas. La session devient accessible aux participants.',
                    'pause_desc' => 'Suspend temporairement la session. Le lien reste actif, mais les participants ne peuvent pas continuer la session jusqu\'à sa reprise.',
                    'stop_desc' => 'Termine la session et désactive le lien. Pour réactiver la session, vous devez la redémarrer, ce qui génère un nouveau lien.',
                ],
                'export' => [
                    'label' => 'Exporter',
                    'json' => 'Exporter en JSON',
                    'xml' => 'Exporter en XML',
                    'desc' => 'Sélectionnez le format dans lequel vous souhaitez exporter les données de l\'expérience.',
                    'media_info' => 'L\'inclusion des médias ajoutera tous les fichiers médias associés à l\'export.',
                    'include_media' => 'Inclure les médias',
                    'success' => 'Export réalisé avec succès'
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
                'status' => [
                    'label' => 'Statut',
                    'options' => [
                        'pending' => 'En attente',
                        'approved' => 'Approuvée',
                        'rejected' => 'Rejetée',
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
                    'label' => 'Expérience',
                ],
                'user' => [
                    'label' => 'Demandeur',
                ],
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
            ],
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
