<?php

declare(strict_types=1);

return [
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
        'heading' => 'Expérimentations empruntées',
        'column' => [
            'name' => 'Expérimentation',
            'created_by' => 'Créateur',
            'sessions_count' => 'Nombre de participants',
            'created_at' => 'Demandé le',
            'type' => [
                'label' => 'Type d\'accès',
                'options' => [
                    'results' => 'Résultats uniquement',
                    'access' => 'Collaboration complète',
                ]
            ],
            'status' => [
                'label' => 'Statut',
                'options' => [
                    'pending' => 'En attente',
                    'approved' => 'Approuvé',
                    'rejected' => 'Refusé',
                ]
            ],
        ]
    ],
    'experiment_table' => [
        'title' => 'Mes expérimentations',
        'title_secondary_experimenter' => 'Expérimentations attribuées',
        'title_default' => 'Expérimentations disponibles',
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
                    'start' => 'Start',
                    'pause' => 'Pause',
                    'stop' => 'Stop',
                    'test' => 'Test',
                ],
            ],
            'sessions_count' => 'Nombre de résultats',
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
];
