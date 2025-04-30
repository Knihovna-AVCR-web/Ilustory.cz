<?php

namespace App;

use Carbon_Fields\Carbon_Fields;

add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('global-styles');
    wp_enqueue_script('app', \App\assets()['/app.js'], [], null, false);
    wp_enqueue_style('app', \App\assets()['/app.css'], false, null);
}, 100);

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook === 'toplevel_page_literarni-soutez') {
        wp_enqueue_script('contest', \App\assets()['/admin.js'], [], null, false);
        wp_enqueue_style('contest', \App\assets()['/admin.css'], false, null);
    }
});

add_action('wp_footer', function () {
    wp_dequeue_script('wp-embed');
});


add_action('after_setup_theme', function () {
    Carbon_Fields::boot();

    register_nav_menus([
        'primary_navigation' => 'Hlavní menu',
    ]);

    add_theme_support('title-tag');

    add_theme_support('html5', [
        'caption',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    remove_theme_support('block-templates');
}, 20);


add_action('init', function () {
    if (!session_id()) {
        error_log('Setting session');
        session_start();
    }
    error_log('SESSION START: ' . session_id());
}, 1);
