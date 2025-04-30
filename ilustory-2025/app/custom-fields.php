<?php

namespace App;

use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action('carbon_fields_register_fields', function () {
    Container::make('theme_options', 'Nastavení šablony')
        ->add_fields([
            Field::make('checkbox', 'contest_active', 'Soutěž je spuštěná'),
            Field::make('text', 'rules_url', 'Adresa pravidel'),
        ]);
});

add_action('carbon_fields_register_fields', function () {
    Container::make('post_meta', 'Nastavení stránky')
        ->where('post_type', '=', 'page')
        ->add_fields([
            Field::make('rich_text', 'advanced_excerpt', 'Rozšířený úvod')
                ->set_help_text('K použití na úvodní stránce'),
        ]);
});
