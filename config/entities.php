<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Entity Pagination
    |--------------------------------------------------------------------------
    |
    | Default number of items per page for entity lists.
    | This can be overridden in individual blocks or programmatically.
    |
    */
    'pagination' => env('ENTITY_PAGINATION', 12),

    /*
    |--------------------------------------------------------------------------
    | Entity Defaults
    |--------------------------------------------------------------------------
    |
    | Default settings for entity display and behavior.
    |
    */
    'defaults' => [
        'show_image' => true,
        'show_excerpt' => true,
        'show_date' => true,
        'show_category' => true,
        'layout' => 'grid',
        'columns' => 3,
    ],

    /*
    |--------------------------------------------------------------------------
    | Entity Types Configuration
    |--------------------------------------------------------------------------
    |
    | Specific configurations for different entity types.
    | These can be used for future features like cart functionality.
    |
    */
    'types' => [
        'news' => [
            'pagination' => 12,
            'order_by' => 'published_at',
        ],
        'article' => [
            'pagination' => 9,
            'order_by' => 'published_at',
        ],
        'product' => [
            'pagination' => 16,
            'order_by' => 'created_at',
            // Future cart functionality settings
            'enable_cart' => false, // Will be enabled later
            'enable_wishlist' => false,
            'show_stock' => true,
            'show_price' => true,
        ],
    ],
];
