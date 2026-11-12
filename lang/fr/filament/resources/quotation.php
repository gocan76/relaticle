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
        'fornitura' => [
            'title' => 'Fourniture',
        ],
        'areas' => [
            'title' => 'Zones du devis',
        ],
    ],

    'areas' => [
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
        'subtotal' => [
            'label' => 'Sous-total',
        ],
        'iva' => [
            'label' => 'TVA (22 %)',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'created_at' => [
            'label' => 'Créé le',
        ],
        'fornitura' => [
            'description' => 'Description',
            'unit' => 'Unité',
            'quantity' => 'Quantité',
            'unit_price' => 'Prix unitaire',
        ],
    ],

    'actions' => [
        'add_fornitura_item' => 'Ajouter une ligne',
        'send' => [
            'label' => 'Envoyer',
            'confirm_heading' => 'Envoyer ce devis ?',
            'confirm_description' => 'Le devis sera envoyé à :company en pièce jointe PDF.',
            'success' => 'Devis envoyé.',
            'email_subject' => 'Devis :number',
            'mail_intro' => 'Veuillez trouver ci-joint le devis :number.',
            'no_email' => 'Cette entreprise n\'a pas d\'adresse email enregistrée.',
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
