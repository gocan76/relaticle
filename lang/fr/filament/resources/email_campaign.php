<?php

declare(strict_types=1);

return [
    // Lowercase to match the en/ convention (Filament title-cases for display contexts).
    'label' => 'campagne email',
    'plural_label' => 'campagnes email',
    'navigation_label' => 'Campagnes email',

    'sections' => [
        'details' => [
            'title' => 'Détails',
        ],
        'segment' => [
            'title' => 'Destinataires',
            'description' => 'Entreprises ciblées par ces filtres. Laissez tout vide pour sélectionner toutes les entreprises.',
        ],
        'message' => [
            'title' => 'Message',
        ],
        'sending' => [
            'title' => 'Envoi',
        ],
    ],

    'fields' => [
        'name' => [
            'label' => 'Nom',
        ],
        'status' => [
            'label' => 'Statut',
        ],
        'search' => [
            'label' => 'Recherche par nom',
        ],
        'sector' => [
            'label' => 'Secteur',
        ],
        'employees_min' => [
            'label' => 'Employés min.',
        ],
        'employees_max' => [
            'label' => 'Employés max.',
        ],
        'revenue_min' => [
            'label' => 'Revenu min.',
        ],
        'revenue_max' => [
            'label' => 'Revenu max.',
        ],
        'created_after' => [
            'label' => 'Créée après le',
        ],
        'created_before' => [
            'label' => 'Créée avant le',
        ],
        'subject' => [
            'label' => 'Objet',
        ],
        'body' => [
            'label' => 'Corps du message',
        ],
        'attachment' => [
            'label' => 'Pièce jointe (PDF)',
        ],
        'from_email' => [
            'label' => 'Adresse d\'envoi',
        ],
        'scheduled_at' => [
            'label' => 'Envoyer le',
            'helper' => 'Laissez vide pour envoyer immédiatement.',
        ],
        'recipients' => [
            'label' => 'Destinataires',
        ],
        'sent' => [
            'label' => 'Envoyés',
        ],
        'errors' => [
            'label' => 'Erreurs',
        ],
        'recipient_name' => [
            'label' => 'Nom',
        ],
        'recipient_email' => [
            'label' => 'Email',
        ],
        'recipient_status' => [
            'label' => 'Statut',
        ],
        'sent_at' => [
            'label' => 'Envoyé le',
        ],
        'error' => [
            'label' => 'Erreur',
        ],
        'created_at' => [
            'label' => 'Créée le',
        ],
    ],

    'actions' => [
        'send' => [
            'label' => 'Envoyer',
            'confirm_heading' => 'Envoyer cette campagne ?',
            'confirm_description' => 'L\'email sera envoyé à :count entreprises disposant d\'une adresse email.',
            'success' => 'Campagne mise en file d\'envoi.',
        ],
    ],

    'pages' => [
        'view' => [
            'actions' => [
                'edit' => [
                    'label' => 'Modifier',
                ],
            ],
        ],
    ],

    'relation_managers' => [
        'recipients' => [
            'model_label' => 'destinataire',
        ],
    ],
];
