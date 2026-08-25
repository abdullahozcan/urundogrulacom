<?php

return [
    'admin' => [
        'language_switcher' => 'Language',
    ],
    'fields' => [
        'name' => 'Product Name',
        'description' => 'Description',
        'category' => 'Category',
        'redirect_url' => 'Redirect URL',
    ],
    'product' => [
        'label' => 'Product',
        'plural_label' => 'Products',
        'navigation_group' => 'Product Management',

        'sections' => [
            'general' => 'General Information',
            'general_desc' => 'Product name and description',
            'kunya' => 'Product Details',
            'kunya_desc' => 'Category, date, image and product type',
            'documents' => 'Documents',
            'documents_desc' => 'PDF, PNG, JPEG, GIF files',
            'social' => 'Social Media',
            'social_desc' => 'Social media links (optional)',
            'qr' => 'QR Code Settings',
            'qr_desc' => 'QR code type and general QR code value',
            'digital' => 'Digital Content',
            'digital_desc' => 'File and link settings for digital products',
        ],

        'table' => [
            'name' => 'Product Name',
            'category' => 'Category',
            'qr_mode' => 'QR Mode',
            'qr_code' => 'General QR',
            'serial' => 'Serial No',
            'expiry' => 'Expiry Date',
            'company' => 'Company',
        ],

        'batches_table' => [
            'title' => 'Batches',
            'batch_no' => 'Batch No',
            'quantity' => 'Quantity',
            'created_items' => 'Items Created',
            'created_at' => 'Created At',
        ],

        'reviews_table' => [
            'title' => 'Reviews',
            'rating' => 'Rating',
            'comment' => 'Comment',
            'approval' => 'Approved',
            'date' => 'Date',
        ],
    ],

    'batch' => [
        'label' => 'Batch',
        'plural_label' => 'Batches',
        'navigation_group' => 'Product Management',
    ],

    'item' => [
        'label' => 'Item',
        'plural_label' => 'Items',
        'navigation_group' => 'Product Management',
    ],

    'actions' => [
        'add_batch' => 'Add Batch',
        'new_batch' => 'Create New Batch',
        'edit_batch' => 'Edit Batch',
        'view_items' => 'View Items',
        'approve' => 'Approve',
        'unapprove' => 'Unapprove',
        'approved' => 'Review approved.',
        'unapproved' => 'Review unapproved.',
    ],
];