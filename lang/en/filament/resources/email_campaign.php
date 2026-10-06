<?php

declare(strict_types=1);

return [
    // Lowercase to match the company.php convention (Filament title-cases
    // the labels for display contexts).
    'label' => 'email campaign',
    'plural_label' => 'email campaigns',
    'navigation_label' => 'Email Campaigns',

    'sections' => [
        'details' => [
            'title' => 'Details',
        ],
        'segment' => [
            'title' => 'Recipients',
            'description' => 'Target companies matching these filters. Leave everything empty to select all companies.',
        ],
        'message' => [
            'title' => 'Message',
        ],
        'sending' => [
            'title' => 'Sending',
        ],
    ],

    'fields' => [
        'name' => [
            'label' => 'Name',
        ],
        'status' => [
            'label' => 'Status',
        ],
        'search' => [
            'label' => 'Search by name',
        ],
        'sector' => [
            'label' => 'Sector',
        ],
        'employees_min' => [
            'label' => 'Min employees',
        ],
        'employees_max' => [
            'label' => 'Max employees',
        ],
        'revenue_min' => [
            'label' => 'Min revenue',
        ],
        'revenue_max' => [
            'label' => 'Max revenue',
        ],
        'created_after' => [
            'label' => 'Created after',
        ],
        'created_before' => [
            'label' => 'Created before',
        ],
        'subject' => [
            'label' => 'Subject',
        ],
        'body' => [
            'label' => 'Body',
        ],
        'attachment' => [
            'label' => 'Attachment (PDF)',
        ],
        'from_email' => [
            'label' => 'From email',
        ],
        'scheduled_at' => [
            'label' => 'Send at',
            'helper' => 'Leave empty to send immediately.',
        ],
        'recipients' => [
            'label' => 'Recipients',
        ],
        'sent' => [
            'label' => 'Sent',
        ],
        'errors' => [
            'label' => 'Errors',
        ],
        'recipient_name' => [
            'label' => 'Name',
        ],
        'recipient_email' => [
            'label' => 'Email',
        ],
        'recipient_status' => [
            'label' => 'Status',
        ],
        'sent_at' => [
            'label' => 'Sent at',
        ],
        'error' => [
            'label' => 'Error',
        ],
        'created_at' => [
            'label' => 'Created at',
        ],
    ],

    'actions' => [
        'send' => [
            'label' => 'Send',
            'confirm_heading' => 'Send this campaign?',
            'confirm_description' => 'This will send the email to :count companies with an email address.',
            'success' => 'Campaign queued for sending.',
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

    'relation_managers' => [
        'recipients' => [
            'model_label' => 'recipient',
        ],
    ],
];
