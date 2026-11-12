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
        'fornitura' => [
            'title' => 'Fornitura',
        ],
        'areas' => [
            'title' => 'Quotation Areas',
        ],
    ],

    'areas' => [
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
        'subtotal' => [
            'label' => 'Subtotal',
        ],
        'iva' => [
            'label' => 'VAT (22%)',
        ],
        'total' => [
            'label' => 'Total',
        ],
        'created_at' => [
            'label' => 'Created at',
        ],
        'fornitura' => [
            'description' => 'Description',
            'unit' => 'Unit',
            'quantity' => 'Quantity',
            'quantity_per_meter' => 'Quantity (m)',
            'quantity_per_square_meter' => 'Quantity (m²)',
            'unit_price' => 'Unit price',
            'price_per_meter' => 'Price per meter',
            'price_per_square_meter' => 'Price per m²',
        ],
    ],

    'actions' => [
        'add_fornitura_item' => 'Add item',
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
