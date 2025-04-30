<?php

namespace App;

use Carbon_Fields\Field;
use Carbon_Fields\Block;

add_action('carbon_fields_register_fields', function () {
    Block::make('Youtube')
        ->add_fields([
            Field::make('text', 'url', 'URL')->set_required(true),
            Field::make('rich_text', 'description', 'Popisek'),
        ])
        ->set_render_callback(function ($fields) {
            preg_match("#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $fields['url'], $matches);

            echo view('partials.youtube', [
                'videoId' => $matches[0],
                'description' => str_replace('<p>', '', str_replace('</p>', '', $fields['description'])),
            ])->render();
        });
});

add_action('carbon_fields_register_fields', function () {
    Block::make('Porotce')
        ->add_fields([
            Field::make('text', 'name', 'Jméno')->set_required(true),
            Field::make('text', 'short_description', 'Krátký popisek')->set_required(true),
            Field::make('rich_text', 'description', 'Detailní popisek')->set_required(true),
            Field::make('image', 'photo', 'Fotka'),
        ])
        ->set_render_callback(function ($args) {
            echo view('partials.jury', $args)->render();
        });
});

add_action('carbon_fields_register_fields', function () {
    Block::make('Úvody stránek')
        ->add_fields([
            Field::make('association', 'advanced_excerpts', 'Vybrané stránky')
                ->set_types([
                    [
                        'type' => 'post',
                        'post_type' => 'page',
                    ],
                ])
        ])
        ->set_render_callback(function ($args) {
            echo view('partials.excerpts', $args)->render();
        });
});

add_action('admin_head', function () {
    echo '<style>
    [data-type="carbon-fields/youtube"] {
        border: 1px solid #00b7ff;
    }
    [data-type="carbon-fields/porotce"] {
        border: 1px solid #00b7ff;
        padding: 4px;
    }
    </style>';
});
