<?php

declare(strict_types=1);

return [
    // Lowercase to match the company.php convention (Filament title-cases
    // the labels for display contexts).
    'label' => 'quotation',
    'plural_label' => 'quotations',
    'navigation_label' => 'Quotations',

    'sections' => [
        'header' => [
            'title' => 'Header',
        ],
        'areas' => [
            'title' => 'Quotation Areas',
        ],
    ],

    'areas' => [
        'fornitura' => 'Fornitura',
        'produzione' => 'Produzione',
        'installazione' => 'Installazione',
        'trasferta' => 'Trasferta',
        'trasporto' => 'Trasporto',
    ],

    'fields' => [
        'number' => [
            'label' => 'Number',
        ],
        'company' => [
            'label' => 'Company',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'issued_at' => [
            'label' => 'Issued at',
        ],
        'valid_until' => [
            'label' => 'Valid until',
        ],
        'notes' => [
            'label' => 'Notes',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'created_at' => [
            'label' => 'Created at',
        ],
    ],

    'actions' => [
        'send' => [
            'label' => 'Send',
            'confirm_heading' => 'Send this quotation?',
            'confirm_description' => 'The quotation will be sent to :company as a PDF attachment.',
            'success' => 'Quotation sent.',
            'email_subject' => 'Quotation :number',
            'mail_intro' => 'Please find attached quotation :number.',
            'no_email' => 'This company has no email address on file.',
        ],
    ],

    'pages' => [
        'view' => [
            'actions' => [
                'edit' => [
                    'label' => 'Edit',
                ],
            ],
        ],
    ],
];
