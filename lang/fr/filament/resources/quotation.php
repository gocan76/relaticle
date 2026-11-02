<?php

declare(strict_types=1);

return [
    // Lowercase to match the en/ convention (Filament title-cases for display contexts).
    'label' => 'devis',
    'plural_label' => 'devis',
    'navigation_label' => 'Devis',

    'sections' => [
        'header' => [
            'title' => 'En-tête',
        ],
        'areas' => [
            'title' => 'Zones du devis',
        ],
    ],

    'areas' => [
        'fornitura' => 'Fourniture',
        'produzione' => 'Production',
        'installazione' => 'Installation',
        'trasferta' => 'Déplacement',
        'trasporto' => 'Transport',
    ],

    'fields' => [
        'number' => [
            'label' => 'Numéro',
        ],
        'company' => [
            'label' => 'Entreprise',
        ],
        'status' => [
            'label' => 'Statut',
        ],
        'issued_at' => [
            'label' => 'Émis le',
        ],
        'valid_until' => [
            'label' => 'Valable jusqu\'au',
        ],
        'notes' => [
            'label' => 'Notes',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'created_at' => [
            'label' => 'Créé le',
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
];
