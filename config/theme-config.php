<?php

// Per-install configuration. Copy/adjust this file for each new site built on this base theme.
return [
    'modules' => [
        'acf' => true,
        'cpt' => true,
        'elementor' => true,
    ],
    'layout' => [
        'container_width' => '1310px',
        // Each entry = one selectable variant, rendered from its own file in template-parts/{header,footer}/.
        // Add a new variant by dropping a file there and adding one entry here - no code changes needed.
        'header_variants' => [
            'default' => [
                'label' => 'Standard header (logo left, menu right)',
                'template' => 'template-parts/header/default',
            ],
            'centered' => [
                'label' => 'Centered logo + split menu',
                'template' => 'template-parts/header/centered',
            ],
        ],
        'footer_variants' => [
            'default' => [
                'label' => 'Standard footer',
                'template' => 'template-parts/footer/default',
            ],
        ],
        'header' => [
            'sticky' => true,
            'shrink' => true,
            'height' => 88,
            'shrink_height' => 68,
        ],
    ],
    // Single source of truth for brand colors - keep in sync with the Elementor Kit
    // (elementor-site-settings/site-settings.json) global colors, which must be imported per install
    // via Elementor's Import/Export Kit feature (no automatic sync exists).
    'colors' => [
        'primary' => '#333333',
        'secondary' => '#555555',
        'text' => '#222222',
        'accent' => '#006666',
        'white' => '#ffffff',
        'bg' => '#ffffff',
    ],
    'typography' => [
        'font_family' => 'Arial, Helvetica, sans-serif',
    ],
    'pharmacies' => [],
];
