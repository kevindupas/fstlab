<?php

return [
    'registration_submitted' => [
        'subject' => 'Demande d\'inscription enregistrée',
        'greeting' => 'Bonjour',
        'line1' => 'Votre demande d\'inscription a été enregistrée avec succès.',
        'line2' => 'Un administrateur va examiner votre demande et vous recevrez une notification dès qu\'elle sera traitée.',
        'line3' => 'Merci de votre patience !',
    ],
    'new_registration_request' => [
        'subject' => 'Nouvelle demande d\'inscription',
        'greeting' => 'Bonjour',
        'line1' => 'Une nouvelle demande d\'inscription a été soumise.',
        'line2' => 'Détails du demandeur :',
        'line3' => 'Nom :',
        'line4' => 'Université :',
        'line5' => 'Email :',
        'line6' => 'Motif :',
        'action' => 'Gérer la demande',
    ],
    'registration_approved' => [
        'subject' => 'Votre inscription a été approuvée',
        'line1' => 'Votre demande d\'inscription a été approuvée.',
        'line2' => 'Vous pouvez maintenant vous connecter à l\'application.',
        'action' => 'Se connecter',
    ],
    'registration_rejected' => [
        'subject' => 'Votre inscription a été refusée',
        'line1' => 'Votre demande d\'inscription a été refusée.',
        'line2' => 'Motif :',
    ],
    'password_reset' => [
        'subject' => 'Définir votre mot de passe',
        'line1' => 'Votre compte a été créé avec succès.',
        'line2' => 'Cliquez sur le bouton ci-dessous pour définir votre mot de passe.',
        'action' => 'Définir mon mot de passe',
        'line3' => 'Si vous n\'avez pas demandé la réinitialisation de votre mot de passe, aucune action n\'est requise.',
    ],
    'user_banned' => [
        'subject' => 'Votre compte a été banni',
        'greeting' => 'Bonjour',
        'line1' => 'Votre compte a été banni de la plateforme.',
        'line2' => 'Motif du bannissement :',
        'line3' => 'Si vous pensez qu\'il s\'agit d\'une erreur, veuillez contacter l\'administrateur.',
    ],
    'user_unbanned' => [
        'subject' => 'Votre compte a été débanni',
        'greeting' => 'Bonjour',
        'line1' => 'Votre compte a été débanni de la plateforme.',
        'line2' => 'Motif du débannissement :',
        'line3' => 'Vous pouvez maintenant vous connecter à nouveau.',
    ],
    'user_deleted' => [
        'subject' => 'Votre compte a été supprimé',
        'greeting' => 'Bonjour',
        'line1' => 'Votre compte a été supprimé pour la raison suivante :',
        'line2' => 'Si vous pensez qu\'il s\'agit d\'une erreur, veuillez contacter l\'administrateur.',
    ],
    'new_access_request_received' => [
        'subject' => 'Nouvelle demande :type',
        'greeting' => 'Bonjour :name',
        'line1' => 'Vous avez reçu une nouvelle demande :type pour votre expérimentation.',
        'details' => 'Détails de la demande :',
        'experiment' => 'Expérimentation : :name',
        'requester' => 'Demandeur : :name',
        'message' => 'Message : :message',
        'access_details' => "Cette collaboration donnera accès :\n- Aux résultats et statistiques\n- À la possibilité de faire passer des sessions\n- Au partage des résultats avec les autres collaborateurs",
        'duplicate_details' => "En acceptant la duplication :\n- Une copie de votre expérimentation sera créée\n- Le demandeur en sera le nouveau créateur\n- Vous serez référencé comme créateur original\n- Les médias seront dupliqués avec l'expérimentation",
        'action' => 'Gérer la demande',
        'type' => [
            'results' => 'aux résultats',
            'access' => 'de collaboration complète',
            'duplicate' => 'de duplication',
            'default' => "d'accès",
        ],
    ],
    'access_request_processed' => [
        'subject' => 'Réponse à votre demande :type',
        'greeting' => 'Bonjour :name',
        'line1' => 'Votre demande :type pour l\'expérimentation ":experiment" a été traitée.',
        'approved' => 'Votre demande a été approuvée.',
        'rejected' => 'Votre demande a été refusée.',
        'reason' => 'Motif : :message',
        'access_details' => "Vous pouvez maintenant :\n- Accéder aux résultats et statistiques\n- Faire passer des sessions\n- Partager vos résultats avec les autres collaborateurs",
        'duplicate_details' => "La duplication a été effectuée avec succès. Vous trouverez la copie de l'expérimentation dans votre liste d'expérimentations.\nVous pouvez maintenant :\n- Modifier la copie selon vos besoins\n- Commencer à collecter vos propres données\n- Gérer vos propres collaborateurs",
        'results_details' => 'Vous pouvez maintenant accéder aux résultats de l\'expérimentation.',
        'action' => 'Accéder à l\'expérimentation',
        'type' => [
            'results' => 'aux résultats',
            'access' => 'de collaboration',
            'duplicate' => 'de duplication',
            'default' => "d'accès",
        ],
    ],
    'access_request_submitted' => [
        'subject' => 'Demande :type envoyée',
        'greeting' => 'Bonjour :name',
        'line1' => 'Votre demande :type a bien été enregistrée.',
        'details' => 'Détails de la demande :',
        'experiment' => 'Expérimentation : :name',
        'message' => 'Message : :message',
        'access_details' => "Cette collaboration vous donnera accès :\n- Aux résultats et statistiques\n- À la possibilité de faire passer des sessions\n- Au partage des résultats avec les autres collaborateurs",
        'duplicate_details' => "Si votre demande est acceptée :\n- Vous recevrez une copie complète de l'expérimentation\n- Vous en serez le créateur\n- Vous pourrez la modifier selon vos besoins\n- Le créateur original sera référencé sur votre copie",
        'pending' => 'Nous vous informerons dès que votre demande aura été traitée.',
        'type' => [
            'results' => 'aux résultats',
            'access' => 'de collaboration complète (résultats + sessions)',
            'duplicate' => 'de duplication d\'expérimentation',
            'default' => 'd\'accès',
        ],
    ],
    'access_revoked_notification' => [
        'subject' => 'Accès révoqué à l\'expérimentation',
        'line1' => 'Votre accès à l\'expérimentation ":experiment" a été révoqué.',
        'line2' => 'Raison :',
        'reason' => ':message',
        'contact' => 'Si vous avez des questions, veuillez contacter le propriétaire de l\'expérimentation.',
    ],
    'new_access_upgrade_request_received' => [
        'subject' => 'Demande de mise à niveau d\'accès - :experiment',
        'greeting' => 'Bonjour :name',
        'line1' => ':user demande une mise à niveau d\'accès pour votre expérience :experiment.',
        'line2' => 'Cette personne a déjà accès aux résultats et souhaite maintenant devenir collaborateur pour pouvoir faire passer des sessions.',
        'request_message' => 'Message de la demande : ":message"',
        'action' => 'Traiter la demande',
    ],
    'access_upgraded_notification' => [
        'subject' => 'Mise à jour de vos accès pour l\'expérience :experiment',
        'greeting' => 'Bonjour :name',
        'line1' => 'Votre demande d\'accès complet à l\'expérience :experiment a été acceptée.',
        'line2' => 'Vous êtes maintenant collaborateur sur cette expérience.',
        'line3' => 'Cela signifie que vous pouvez :',
        'line4' => '- Voir les résultats de l\'expérience',
        'action' => 'Voir l\'expérience',
        'thank_you' => 'Merci de votre collaboration !',
    ],
    'access_upgrade_request_submitted' => [
        'subject' => 'Demande de mise à niveau - :experiment',
        'greeting' => 'Bonjour :name',
        'line1' => 'Votre demande de mise à niveau d\'accès pour l\'expérience :experiment a été envoyée.',
        'line2' => 'En attendant l\'approbation, vous conservez votre accès aux résultats.',
        'line3' => 'Vous serez notifié dès que le créateur aura traité votre demande.',
        'action' => 'Voir l\'expérience',
    ],
    'supervisor_message' => [
        'subject' => 'Message du superviseur',
        'greeting' => 'Bonjour :name',
        'line1' => 'Le superviseur :supervisor vous a envoyé un message :',
        'line2' => 'Concernant l\'expérience :experiment',
        'line3' => 'Vous pouvez répondre via la page "Contacter l\'administrateur" de la plateforme.',
    ],
    'admin_contact_message' => [
        'subject' => ':subject',
        'line1' => 'Message de : :sender_name (:sender_email)',
        'line2' => 'Sujet : :subject',
        'line3' => 'Message :',
        'action' => 'Gérer l\'utilisateur',
        'subjects' => [
            'unban' => 'Demande de débannissement',
            'principal_banned' => 'Principal expérimentateur banni',
            'question' => 'Question Générale',
            'other' => 'Autre',
            'secondary_option' => 'Demande de compte principal'
        ],
    ],
    'user_message' => [
        'supervisor' => 'superviseur',
        'researcher' => 'expérimentateur',
        'subject' => 'Message du :senderType',
        'greeting' => 'Bonjour :name',
        'line1' => 'Le :senderType :senderName vous a envoyé un message :',
        'experiment' => 'Concernant l\'expérience :experimentName',
        'response_supervisor' => 'Vous pouvez répondre via la page "Contacter l\'administrateur" de la plateforme.',
        'response_researcher' => 'Vous pouvez répondre via la plateforme.',
    ],
];
