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
    'pharmacies' => [
        'heureka' => ['label' => 'Heuréka', 'url' => 'https://www.heureka.cz', 'active' => true, 'logo' => 'img/pharmacies/heureka.jpg'],
        'benu' => ['label' => 'Benu', 'url' => 'https://www.benu.cz', 'active' => true, 'logo' => 'img/pharmacies/benu.jpg'],
        'drmax' => ['label' => 'Dr. Max', 'url' => 'https://www.drmax.cz', 'active' => true, 'logo' => 'img/pharmacies/drmax.jpg'],
        'lekarna_cz' => ['label' => 'Lékárna.cz', 'url' => 'https://www.lekarna.cz', 'active' => true, 'logo' => 'img/pharmacies/lekarna-cz.jpg'],
        'magistra' => ['label' => 'Magistra', 'url' => 'https://www.magistra.cz', 'active' => true, 'logo' => 'img/pharmacies/magistra.jpg'],
        'mojelekarna' => ['label' => 'Moje lékárna', 'url' => 'https://www.mojelekarna.cz', 'active' => true, 'logo' => 'img/pharmacies/moje-lekarna.jpg'],
        'euclekarna' => ['label' => 'EUC lékárna', 'url' => 'https://www.euclekarna.cz', 'active' => true, 'logo' => 'img/pharmacies/euc-lekarna.jpg'],
        'alza' => ['label' => 'Alza', 'url' => 'https://www.alza.cz/lekarna', 'active' => true, 'logo' => 'img/pharmacies/alza.jpg'],
        'alphega' => ['label' => 'Alphega', 'url' => 'https://www.alphega.cz', 'active' => false, 'logo' => 'img/pharmacies/alphega.png'],
        'pilulka' => ['label' => 'Pilulka', 'url' => 'https://www.pilulka.cz', 'active' => false, 'logo' => 'img/pharmacies/pilulka.png'],
        'ave' => ['label' => 'Lékárna Ave', 'url' => 'https://www.lekarnaave.cz', 'active' => false, 'logo' => 'img/pharmacies/lekarna-ave.png'],
    ],
];
