<?php

define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('mainjs', get_template_directory_uri() . '/assets/js/app.js', ['jquery'], filemtime(get_template_directory() . '/assets/js/app.js'), true);
    wp_register_script('plyr', 'https://cdn.plyr.io/3.6.7/plyr.js');
    wp_register_style('plyr', 'https://cdn.plyr.io/3.6.7/plyr.css');
});


if (function_exists('register_sidebar')) {
    register_sidebar(array(
        'name' => 'Hlavní menu',
        'id'   => 'main_menu',
        'description'   => 'Hlavní vertikální menu v sidebaru.',
        'before_widget' => '<div class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h4>',
        'after_title'   => '</h4>',
    ));
}

if (function_exists('register_nav_menu')) {
    register_nav_menu('horni_menu', 'Horní menu pro rozdělení uživatelů');
}


add_shortcode('toggle', function ($atts, $content = null) {
    extract(shortcode_atts([
        'title' => 'Kliknutím otevřít',
    ], $atts));

    $randomId = 'collapsible-' . mt_rand(0, 9999);
    ob_start(); ?>
    <div class="wrap-collabsible">
        <input id="<?= $randomId; ?>" class="toggle-checkbox" type="checkbox">
        <label for="<?= $randomId; ?>" class="lbl-toggle" tabindex="0" role="tab" aria-expanded="false">
            <?= $title; ?>
        </label>
        <div class="collapsible-content" aria-hidden="true" role="tabpanel">
            <div class="content-inner">
                <?= do_shortcode($content); ?>
            </div>
        </div>
    </div>
    <?php return ob_get_clean();
});


function get_permalink_current_language($post_id)
{
    global $sitepress;

    $lang_post_id = icl_object_id($post_id, 'page', true, ICL_LANGUAGE_CODE);

    if ($lang_post_id != 0) {
        return get_permalink($lang_post_id);
    }

    return $sitepress->language_url(ICL_LANGUAGE_CODE);
}

add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');

    add_image_size('featured-thumb', 145, 185, true);
    add_image_size('new-in-fond', 84, 110, true);
    add_image_size('gallery', 167, 145, true);

    load_theme_textdomain('twentyten', TEMPLATEPATH . '/languages');

    $locale = get_locale();
    $locale_file = TEMPLATEPATH . "/languages/$locale.php";
    if (is_readable($locale_file)) {
        require_once($locale_file);
    }

    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'twentyten'),
    ));

    add_theme_support('title-tag');
});


add_filter('wp_page_menu_args', function ($args) {
    $args['show_home'] = true;
    return $args;
});


add_filter('excerpt_length', function () {
    return 40;
});


function twentyten_continue_reading_link()
{
    return ' <a style="display:block;margin-top: 8px;" href="' . get_permalink() . '">' . __('Číst dále &raquo;', 'twentyten') . '</a>';
}

add_filter('excerpt_more', function () {
    return ' &hellip;' . twentyten_continue_reading_link();
});


add_filter('get_the_excerpt', function ($output) {
    if (has_excerpt() && !is_attachment()) {
        $output .= twentyten_continue_reading_link();
    }

    return $output;
});


add_filter('use_default_gallery_style', '__return_false');


/*-----------------------------------------------------------------------------------*/
/*  New menu walker for the nav_menu menu
/*-----------------------------------------------------------------------------------*/

class menu_walker extends Walker_Nav_Menu
{
    public function start_el(&$output, $item, $depth = 0, $args = [], $id = 0)
    {
        $indent = ($depth) ? str_repeat("", $depth) : '';

        $class_names = $value = '';

        $classes = empty($item->classes) ? array() : (array) $item->classes;

        $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item));
        $class_names = ' class="' . esc_attr($class_names) . '"';

        $output .= $indent . '<li id="menu-item-' . $item->ID . '"' . $value . $class_names . '>';

        $attributes  = !empty($item->attr_title) ? ' title="' . esc_attr($item->attr_title) . '"' : '';
        $attributes .= !empty($item->target) ? ' target="' . esc_attr($item->target) . '"' : '';
        $attributes .= !empty($item->xfn) ? ' rel="' . esc_attr($item->xfn) . '"' : '';
        $attributes .= !empty($item->url) ? ' href="' . esc_attr($item->url) . '"' : '';

        $prepend = '';
        $append = '';

        $item_output = $args->before;
        $item_output .= '<a' . $attributes . '>';
        $item_output .= $args->link_before . $prepend . apply_filters('the_title', $item->title, $item->ID) . $append;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters('walker_nav_menu_start_el', $item_output, $item, $depth, $args);
    }
}


add_shortcode('mapa', function () {
    ob_start(); ?>

    <iframe src="https://api.mapy.cz/frame?params=%7B%22x%22%3A14.414522879236483%2C%22y%22%3A50.081556055521744%2C%22base%22%3A%221%22%2C%22layers%22%3A%5B%5D%2C%22zoom%22%3A16%2C%22url%22%3A%22https%3A%2F%2Fmapy.cz%2Fs%2F2JFIx%22%2C%22mark%22%3A%7B%22x%22%3A%2214.414522879236483%22%2C%22y%22%3A%2250.081556055521744%22%2C%22title%22%3A%22Knihovna%20AV%20%C4%8CR%2C%20v.v.i.%22%7D%2C%22overview%22%3Afalse%7D&amp;width=511&amp;height=333&amp;lang=cs" width="511" height="333" style="border:none" frameBorder="0" title="Knihovna AV ČR, v.v.i., mapa"></iframe>
    <?php return ob_get_clean();
});


function language_selector_flags()
{
    $languages = icl_get_languages('skip_missing=0&orderby=code');

    if (empty($languages)) {
        return;
    }

    echo '<ul id="language-chooser">';

    foreach ($languages as $l) {
        $class = '';

        if ($l['active'] == 1) {
            $class = 'class="active"';
        }

        echo '<li ' . $class . '><a href="' . $l['url'] . '">';
        echo $l['native_name'];
        echo '</a></li>';
    }

    echo '</ul>';
}


//remove junk
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('admin_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'index_rel_link');
remove_action('wp_head', 'wlwmanifest_link');
remove_action('wp_head', 'start_post_rel_link', 10, 0);
remove_action('wp_head', 'parent_post_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
remove_action('wp_head', 'rel_canonical', 10, 0);
remove_action('wp_head', 'rest_output_link_wp_head');
remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('template_redirect', 'rest_output_link_header', 11, 0);
global $sitepress;
remove_action('wp_head', array($sitepress, 'meta_generator_tag'));


// add meta to api
add_action('rest_api_init', function () {
    register_rest_field(
        'post',
        'meta',
        [
            'get_callback' => 'get_post_meta_for_api',
            'schema' => null,
        ]
    );
});


function get_post_meta_for_api($object)
{
    return get_post_meta($object['id']);
}


add_filter('rest_endpoints', function ($endpoints) {
    if (isset($endpoints['/wp/v2/users'])) {
        unset($endpoints['/wp/v2/users']);
    }

    if (isset($endpoints['/wp/v2/users/(?P<id>[\d]+)'])) {
        unset($endpoints['/wp/v2/users/(?P<id>[\d]+)']);
    }

    return $endpoints;
});


add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('language-selector');
    wp_deregister_style('language-selector');
    wp_dequeue_style('wpml-legacy-dropdown-0');
    wp_dequeue_style('wcml-dropdown-0-css');
    wp_dequeue_style('wcml-dropdown-0');
    wp_dequeue_style('wp-block-library');
}, 99);


add_filter('allowed_http_origins', function ($origins) {
    $origins[] = 'https://intranet.lib.cas.cz';
    $origins[] = 'https://digit.lib.cas.cz/';
    $origins[] = 'https://digit.lib.cas.cz';
    $origins[] = 'https://intranet.test';

    return $origins;
});


function get_json_news($category_name)
{
    $args = [
        'category_name' => $category_name,
        'post_status' => 'publish',
        'posts_per_page' => 2,
    ];

    $the_query = new WP_Query($args);

    $result = [];

    $index = 0;

    while ($the_query->have_posts()) {
        $the_query->the_post();

        $result[$index] = [
            'link' => get_post_meta(get_the_ID(), 'Odkaz', 'single'),
            'thumb' => get_the_post_thumbnail_url(get_the_ID()),
            'title' => get_the_title(),
        ];

        $index++;
    };

    wp_reset_postdata();

    return json_encode($result, JSON_UNESCAPED_UNICODE);
}


function get_diginews()
{
    header('Content-type:application/json');

    $news = get_json_news('nove-v-digitalni-knihovne');

    wp_die($news);
}
add_action('wp_ajax_get_diginews', 'get_diginews');
add_action('wp_ajax_nopriv_get_diginews', 'get_diginews');


function get_fondnews()
{
    header('Content-type:application/json');

    $news = get_json_news('nove-ve-fondu');

    wp_die($news);
}
add_action('wp_ajax_get_fondnews', 'get_fondnews');
add_action('wp_ajax_nopriv_get_fondnews', 'get_fondnews');


function get_vufind_suggestions()
{
    if (!array_key_exists('search', $_GET)) {
        wp_die(404);
    }

    $search_term = filter_var($_GET['search'], FILTER_SANITIZE_STRING);

    $url = "https://vufind.lib.cas.cz/KNAV/Search/Suggest?lookfor={$search_term}&format=JSON";

    $suggestions = file_get_contents($url);

    if (!$suggestions) {
        wp_die(404);
    }

    $suggestions = json_decode($suggestions)[1];

    header('Content-type:application/json');

    echo json_encode($suggestions, JSON_UNESCAPED_UNICODE);

    wp_die();
}
add_action('wp_ajax_get_vufind_suggestions', 'get_vufind_suggestions');
add_action('wp_ajax_nopriv_get_vufind_suggestions', 'get_vufind_suggestions');


function get_book_meta()
{
    if (!array_key_exists('sysno', $_GET)) {
        wp_die(404);
    }

    $sysno = filter_var($_GET['sysno'], FILTER_SANITIZE_STRING);

    $url = "https://vufind.lib.cas.cz/Record/{$sysno}/RDF";

    $xml = new DOMDocument();
    $xml->load($url);

    $meta['title'] = $xml->getElementsByTagName('title')[0]->nodeValue;
    $meta['sysno'] = $sysno;
    $meta['author'] = $xml->getElementsByTagName('namePart')[0]->nodeValue;

    header('Content-type:application/json');

    echo json_encode($meta, JSON_UNESCAPED_UNICODE);

    wp_die();
}
add_action('wp_ajax_get_book_meta', 'get_book_meta');
add_action('wp_ajax_nopriv_get_book_meta', 'get_book_meta');


function get_contact_list()
{
    global $wpdb;

    $query = "
    SELECT $wpdb->posts.ID as ID,
    $wpdb->posts.post_title as post_title,
    GROUP_CONCAT(DISTINCT CONCAT(meta.meta_key,' | ',meta.meta_value)
    ORDER BY $wpdb->posts.ID SEPARATOR ' ;|; ') as meta
    FROM $wpdb->posts
    LEFT JOIN $wpdb->postmeta AS meta ON ($wpdb->posts.ID = meta.post_id)
    WHERE
    $wpdb->posts.post_type = 'zamestnanec' AND $wpdb->posts.post_status = 'publish'
    GROUP BY $wpdb->posts.ID
    ORDER BY $wpdb->posts.post_title
    ";

    $output = [];

    $posts = $wpdb->get_results($query);

    foreach ($posts as $post) {
        $meta = [];
        $metaArr = explode(" ;|; ", $post->meta);
        foreach ($metaArr as $m) {
            $m = explode(' | ', $m);

            $ma = [
                $m[0] => $m[1]
            ];
            array_push($meta, $ma);
        }
        $post->meta = $meta;
        array_push($output, $post);

        $post->meta = array_reduce($post->meta, 'array_merge', []);
    }

    return $output;
}


function get_contact_list_json()
{
    //https://www.lib.cas.cz/wp-admin/admin-ajax.php?action=get_contact_list

    $contact_data = get_contact_list();

    $output_data["draw"] = 1;
    $output_data["recordsTotal"] = count($contact_data);
    $output_data["recordsFiltered"] = count($contact_data);
    $output_data["data"] = $contact_data;

    echo json_encode($output_data, JSON_UNESCAPED_UNICODE);

    wp_die();
}
add_action('wp_ajax_get_contact_list', 'get_contact_list_json');
add_action('wp_ajax_nopriv_get_contact_list', 'get_contact_list_json');



function get_vydani($rubrika)
{
    $term = get_term_by('slug', get_query_var('term'), get_query_var('taxonomy'));

    $args = [
        'post_type' => 'casopis_informace',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'tax_query' => [
            'relation' => 'AND',
            [
                'taxonomy' => 'vydani',
                'field'    => 'name',
                'terms'    => $term->name,
            ],
            [
                'taxonomy' => 'rubriky',
                'field'    => 'name',
                'terms'    => $rubrika,
            ],
        ],
        'order' => 'ASC',
        'orderby'   => 'meta_value_num',
        'meta_key'  => 'poradi_clanku',
    ];

    $my_query = new WP_Query($args); ?>
    <?php if ($my_query->have_posts()) : ?>
        <h3><?= $rubrika; ?></h3>
        <ul class="vydani">
            <?php while ($my_query->have_posts()) : ?>
                <?php $my_query->the_post(); ?>
                <li>
                    <a href="<?php the_permalink() ?>" title="<?php the_title_attribute(); ?>">
                        <?php the_title(); ?>
                    </a>
                    <br>
                    Autor článku: <?= get_the_term_list(get_the_ID(), 'autori', '', ', ', ''); ?>
                </li>

            <?php endwhile; ?>
        </ul>
    <?php endif;
    wp_reset_query();
}


add_filter('tiny_mce_before_init', function ($in) {
    // everything without wptextpattern
    $in['plugins'] = 'charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,fullscreen,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wpview';
    return $in;
});

add_shortcode('vufind_form', function () {
    ob_start(); ?>

    <form action="https://katalog.lib.cas.cz/KNAV/Search/Results" method="GET" role="search">
        <fieldset>
            <h3 style="margin: 16px 0;"><?php _e('Chci dokument z fondu KNAV', 'knav') ?></h3>
            <h4><?php _e('(vyhledávání tištěných dokumentů a elektronických knih)', 'knav') ?></h4>
            <div class="row">
                <div class="col col-6">
                    <label for="vufind-search" class="screen-reader-text"><?php _e('Hledaný termín', 'knav'); ?></label>
                    <input class="search" name="lookfor" type="text" id="vufind-search">
                </div>
                <input name="type" type="hidden" value="AllFields">
                <input name="limit" type="hidden" value="10">
                <!--<input name="institution" type="hidden" value="KNAV">
                <input name="filter[]" type="hidden" value="institution:KNAV">-->
                <div class="col col-6">
                    <input type="submit" value="<?php _e('Hledat', 'knav'); ?>" class="button primary outline">
                </div>
            </div>
        </fieldset>
    </form>

    <?php return ob_get_clean();
});


add_shortcode('novinky_ve_fondu', function () {
    $novinky_query = new WP_Query([
        'post_type' => 'post',
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_key'  => 'novinky_ve_fondu',
        'meta_value' => 1,
        'post_status' => 'publish',
        'posts_per_page' => 1
    ]);

    ob_start();

    while ($novinky_query->have_posts()) :
        $novinky_query->the_post();
        if (get_page_template_slug(get_the_ID()) == 'single-knizni-tipy.php') : ?>
            <div style="margin-bottom: 48px;">
                <?php the_content(); ?>
            </div>
            <?php
            get_template_part('loop', 'knizni-tipy');
        else : ?>
            <div>
                <?php the_content(); ?>
            </div>
            <?php
        endif;
    endwhile;

    wp_reset_postdata();

    return ob_get_clean();
});


add_shortcode('knizni_tipy', function () {
    $tipy_query = new WP_Query([
        'post_type' => 'post',
        'orderby' => 'date',
        'order' => 'DESC',
        'meta_key'  => 'knizni_tipy',
        'meta_value' => 1,
        'post_status' => 'publish',
        'posts_per_page' => 1
    ]);

    ob_start();

    while ($tipy_query->have_posts()) :
        $tipy_query->the_post();
        if (get_page_template_slug(get_the_ID()) == 'single-knizni-tipy.php') : ?>
            <div style="margin-bottom: 48px;">
                <?php the_content(); ?>
            </div>
            <?php
            get_template_part('loop', 'knizni-tipy');
        else : ?>
            <div>
                <?php the_content(); ?>
            </div>
            <?php
        endif;
    endwhile;

    wp_reset_postdata();

    return ob_get_clean();
});


add_action('init', function () {
    $args = [
        'labels' => [
            'name' => 'Zaměstnanci',
            'singular_name' => 'Zaměstnanec',
            'add_new' => 'Přidat nového zaměstnance',
            'menu_name' => 'Zaměstnanci'
        ],
        'public' => true,
        'menu_icon' => 'dashicons-admin-users',
        'menu_position' => 15,
        'has_archive' => true,
        'supports' => ['title'],
        'capability_type' => [
            'employee',
            'employees',
        ],
        'map_meta_cap' => true,
    ];
    register_post_type('zamestnanec', $args);
});


function zamestnanec_metadata_get_meta($value)
{
    global $post;

    $field = get_post_meta($post->ID, $value, true);

    if (!empty($field)) {
        return is_array($field) ? stripslashes_deep($field) : stripslashes(wp_kses_decode_entities($field));
    }

    return false;
}


function remove_end_chars($string)
{
    $string = trim($string);
    $string = substr($string, 0, strlen($string) - 1);
    $string = trim($string);
    $string = stripslashes($string);
    $string = htmlspecialchars($string);
    return $string;
}


function file_get_contents_curl($url)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $data = curl_exec($ch);

    curl_close($ch);

    return $data;
}


add_action('init', function () {
    $args = [
        'labels' => [
            'name' => 'Knižní tipy',
            'singular_name' => 'Knižní tip',
        ],
        'public' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => false,
        'show_ui' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-thumbs-up',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'editor'],
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true,
        'capability_type' => [
            'book-tip',
            'book-tips'
        ],
        'map_meta_cap' => true
    ];
    register_post_type('knizni_tip', $args);
});


add_action('init', function () {
    $args = [
        'labels' => [
            'name' => 'Projekty',
            'singular_name' => 'Projekt',
        ],
        'public' => true,
        'exclude_from_search' => false,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_nav_menus' => true,
        'show_in_menu' => true,
        'show_in_admin_bar' => true,
        'menu_position' => 20,
        'menu_icon' => 'dashicons-admin-site',
        'capability_type' => 'post',
        'hierarchical' => false,
        'supports' => ['title', 'editor'],
        'has_archive' => false,
        'query_var' => true,
        'can_export' => true
    ];
    register_post_type('projekt', $args);
});


add_action('init', function () {
    register_taxonomy(
        'resitel',
        'projekt',
        [
            'labels' => [
                'name' => 'Řešitelé',
                'singular_name' => 'Řešitel'
            ],
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'resitel'],
        ]
    );
});


add_action('init', function () {
    register_taxonomy(
        'zdroj_financi',
        'projekt',
        [
            'labels' => [
                'name' => 'Zdroje financí',
                'singular_name' => 'Zdroj financí'
            ],
            'show_admin_column' => true,
            'rewrite' => ['slug' => 'zdroj-financi'],
        ]
    );
});


function getAllPostsWithID($post_type)
{
    $results = [];

    $the_query = new WP_Query([
        'post_type' => $post_type,
        'post_status' => 'publish',
        'posts_per_page' => -1,
    ]);

    while ($the_query->have_posts()) {
        $the_query->the_post();
        if (!empty(get_the_title())) {
            $results[get_the_ID()] = get_the_title();
        }
    }

    wp_reset_postdata();

    return $results;
}


function formResitel($id)
{
    $resitele_obj = wp_get_post_terms($id, 'resitel');
    $resitele = [];
    $i = 0;

    foreach ($resitele_obj as $res) {
        $term_meta = get_term_meta($res->term_id);
        $resitel_name = '';

        if (isset($term_meta['titul_pred']) && $term_meta['titul_pred'] !== null) {
            $resitel_name .= $term_meta['titul_pred'][0] . ' ';
        }

        $resitel_name .= $term_meta['rodne_jmeno'][0] . ' ';

        $resitel_name .= $term_meta['prijmeni'][0];

        if (isset($term_meta['titul_za']) && $term_meta['titul_za'] !== null) {
            $resitel_name .= ', ' . $term_meta['titul_za'][0];
        }

        $resitele[$i]['link'] = home_url('resitel/' . $res->slug);
        $resitele[$i]['slug'] = $res->slug;
        $resitele[$i]['name'] = $resitel_name;

        $i++;
    };

    return $resitele;
}


function formSingleResitelName($id)
{
    $term_meta = get_term_meta($id);

    $resitel_name = '';

    if (isset($term_meta['titul_pred']) && $term_meta['titul_pred'] !== null) {
        $resitel_name .= $term_meta['titul_pred'][0] . ' ';
    }

    $resitel_name .= $term_meta['rodne_jmeno'][0] . ' ';

    $resitel_name .= $term_meta['prijmeni'][0];

    if (isset($term_meta['titul_za']) && $term_meta['titul_za'] !== null) {
        $resitel_name .= ', ' . $term_meta['titul_za'][0];
    }

    return $resitel_name;
}

function formShortSingleProjekt($id, $showResitel, $showZdroj)
{
    ob_start();

    $meta = get_post_meta($id);
    $resitel = formResitel($id)[0];
    $zdroj = wp_get_post_terms($id, 'zdroj_financi')[0];

    $start = get_post_meta($id, 'obdobi_zacatek', true);
    $end = get_post_meta($id, 'obdobi_konec', true);

    ?>
    <div class="pr-single-short" data-resitel="<?= $resitel['slug'] ?>" data-zdroj="<?= $zdroj->slug ?>" data-years="<?= stringifyRange($meta['obdobi_zacatek'][0], $meta['obdobi_konec'][0]) ?>">
        <h3><?= get_the_title($id) ?></h3>
        <div class="pr-row">
            <?php if ($showResitel) : ?>
                <div class="pr-col">
                    <div>
                        <span class="term">
                            <?php _e('Řešitel projektu', 'knav') ?>:
                        </span>
                        <br>
                        <span class="desc">
                            <a href="<?= $resitel['link'] ?>">
                                <?= $resitel['name'] ?>
                            </a>
                        </span>
                    </div>
                </div>
            <?php endif; ?>
            <div class="pr-col">
                <?php if ($showZdroj) : ?>
                    <span class="term">
                        <?php _e('Zdroj financování', 'knav') ?>:
                    </span>
                    <span class="desc">
                        <a href="<?= home_url('zdroj-financi/' . $zdroj->slug); ?>">
                            <?= $zdroj->name ?>
                        </a>
                    </span>
                    <br>
                <?php endif; ?>
                <span class="term">
                    <?php _e('Období realizace', 'knav') ?>:
                </span>
                <span class="desc">
                    <?= ($start !== $end) ? "{$start}–$end" : $end; ?>
                </span>
                <br>
                <a href="<?= get_permalink($id) ?>">
                    <?php _e('Více o projektu', 'knav') ?>
                </a>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function format_price($str)
{
    $str = str_replace(' ', '', $str);

    $str = str_replace(',', '.', $str);

    $price = (float) filter_var($str, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    $price = number_format($price, 2, ',', ' ');

    $currency = preg_replace('/[0-9. ]/', '', $str);

    if ($currency !== '') {
        return $price . ' ' . $currency;
    }

    return $price . ' Kč';
}

function stringifyRange($start, $end)
{
    return implode(',', range($start, $end));
}

function getAllVolumes()
{
    global $wpdb;

    $sql_volume = "
    SELECT distinct(name) FROM $wpdb->posts
    LEFT JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)
    LEFT JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
    LEFT JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
    LEFT JOIN $wpdb->terms ON($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
    WHERE $wpdb->term_taxonomy.taxonomy = 'rocnik'
    AND $wpdb->posts.post_status = 'publish'
    AND $wpdb->posts.post_type = 'casopis_informace'
    ORDER BY $wpdb->terms.name DESC
    ";

    return $wpdb->get_results($sql_volume, OBJECT);
}

function getIssues()
{
    global $wpdb;

    $sql_issue = "
    SELECT DISTINCT(name) as name, post_id, slug FROM $wpdb->posts
    LEFT OUTER JOIN $wpdb->postmeta ON($wpdb->posts.ID = $wpdb->postmeta.post_id)
    LEFT OUTER JOIN $wpdb->term_relationships ON($wpdb->posts.ID = $wpdb->term_relationships.object_id)
    LEFT OUTER JOIN $wpdb->term_taxonomy ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
    LEFT OUTER JOIN $wpdb->terms ON($wpdb->terms.term_id = $wpdb->term_taxonomy.term_id)
    WHERE ($wpdb->term_taxonomy.taxonomy = 'vydani')
    AND $wpdb->posts.post_status = 'publish'
    AND $wpdb->posts.post_type = 'casopis_informace'
    /**/

    GROUP BY $wpdb->terms.slug
    ORDER BY $wpdb->terms.name ASC
    ";

    return $wpdb->get_results($sql_issue, OBJECT);
}

add_action('upload_mimes', function ($file_types) {
    return array_merge(
        $file_types,
        [
            'svg' => 'image/svg+xml',
        ]
    );
});


add_action('init', function () {
    register_post_type(
        'zpravicky',
        [
            'capability_type' => [
                'zpravicka',
                'zpravicky'
            ],
            'labels' => [
                'name' => 'Informace – tipy',
                'singular_name' => 'Informace – tip'
            ],
            'has_archive' => true,
            'map_meta_cap' => true,
            'public' => true,
            'supports' => ['title', 'editor',],
        ]
    );
});


add_action('init', function () {
    register_post_type(
        'casopis_informace',
        [
            'labels' => [
                'name' => 'Články',
                'singular_name' => 'Článek'
            ],
            'public' => true,
            'has_archive' => true,
            'supports' => ['title', 'editor', 'custom-fields'],
            'rewrite' => [
                [
                    'slug' => 'casopis-informace'
                ]
            ],
            'capability_type' => [
                'clanek',
                'clanky'
            ],
            'map_meta_cap' => true
        ]
    );
});


function encode_string_to_ASCII($string)
{
    $output = '';
    $length = strlen($string);
    for ($i = 0; $i < $length; $i++) {
        $output .= '&#' . ord($string[$i]) . ';';
    }
    return $output;
}


add_shortcode('email', function () {
    extract(shortcode_atts([
        'adresa' => '',
    ], $atts));


    $email = encode_string_to_ASCII($atts['adresa']);
    $mailto = encode_string_to_ASCII('mailto:');
    ob_start(); ?>
    <a href="<?= $mailto . $email; ?>">
        <?= $email; ?>
    </a>

    <?php return ob_get_clean();
});


add_action('admin_menu', function () {
    $user = wp_get_current_user();

    if (in_array('administrator', (array) $user->roles)) {
        remove_menu_page('edit-comments.php');
        return;
    }

    if (!in_array('pomocnik', (array) $user->roles)) {
        return;
    }

    remove_menu_page('tools.php');
    remove_menu_page('edit-comments.php');
    remove_menu_page('page.php');
    remove_menu_page('knav_database.php');
    remove_menu_page('edit.php?post_type=acf-field-group');
    remove_menu_page('edit.php?post_type=page');
    remove_menu_page('wpcf7');
    remove_menu_page('edit.php?post_type=zpravicky');
    remove_menu_page('edit-tags.php?taxonomy=rubriky&amp;post_type=casopis_informace');
});


function create_informace_citation($authors, $title, $rocnik, $vydani, $id)
{
    $link =  get_permalink($id);

    $citation = join('; ', $authors) . '. ';
    $citation .= $title;
    $citation .= '. <em>Informace</em> [online]. ';
    $citation .= "{$rocnik}, {$vydani} ";
    $citation .= '[cit. ' . date('Y-m-d') . ']. ISSN 1805-2800. Dostupné z: ';
    $citation .=  "<a href='{$link}'>{$link}</a>";

    return $citation;
}


add_action('template_redirect', function () {
    if (is_author()) {
        wp_redirect(home_url('/archiv-aktualit'));
        exit;
    }
});


function output_breadcrumbs()
{
    if (!function_exists('bcn_display')) {
        return;
    }

    if (is_home() || is_front_page()) {
        return;
    }

    bcn_display();
}


function get_knav_db_meta($id, $title)
{
    global $wpdb;

    $query = $wpdb->prepare("SELECT * FROM " . $wpdb->prefix . "databases WHERE post_id = %d", $id);

    $result = $wpdb->get_row($query);

    $img = '';

    $attachment = get_post_meta($result->post_id, 'meta-image', true);
    $image_attributes = wp_get_attachment_image_src($attachment, 'full');

    if ($image_attributes) {
        $img = "<img class='logo-db' src='{$image_attributes[0]}' width='{$image_attributes[1]}' height='{$image_attributes[2]}' alt='{$title}'>";
    }

    return [
        'knav_link_enable' => get_post_meta($result->post_id, 'knav_link_enable', true),
        'ez_proxy_enable' => get_post_meta($result->post_id, 'ez_proxy_enable', true),
        'shibolleth_enable' => get_post_meta($result->post_id, 'shibolleth_enable', true),
        'volny_pristup_enable' => get_post_meta($result->post_id, 'volny_pristup_enable', true),
        'knav_link' => get_post_meta($result->post_id, 'knav_link', true),
        'ez_proxy' => get_post_meta($result->post_id, 'ez_proxy', true),
        'shibolleth' => get_post_meta($result->post_id, 'shibolleth', true),
        'volny_pristup' => get_post_meta($result->post_id, 'volny_pristup', true),
        'img' => $img,
    ];
}


function ouput_alert_notice()
{
    return;
    global $post;

    if (!is_object($post)) {
        return;
    }

    if (in_array(149, get_post_ancestors($post->ID)) || in_array($post->ID, ['6948', '29354 '])) {
        return;
    }

    $notice = ' <a style="color:#ed0000;" href="https://www.lib.cas.cz/sluzby/aktualni-informace-o-sluzbach/">Aktuální informace o provozu knihovny »</a>';

    if (ICL_LANGUAGE_CODE == 'en') {
        $notice = '<a style="color:#ed0000;" href="https://www.lib.cas.cz/en/services/current-info-about-library-services/">Current information on the operation of the library »</a>';
    }

    echo '<div style="font-style: italic;color: #ed0000;margin-bottom:8px">' . $notice . '</div>';
}


function ouput_service_alert_notice()
{
    return;
    $notice = 'Uzavření knihovny (2.–31. srpna 2021) omezí poskytování některých služeb. <a style="color:#ed0000;" href="https://www.lib.cas.cz/30053/letni-uzavreni-knihovny-2021/">Více informací »</a>';

    if (ICL_LANGUAGE_CODE == 'en') {
        $notice = 'The library closure (August 2–31, 2021) might affect some services. <a style="color:#ed0000;" href="https://www.lib.cas.cz/en/30055/summer-library-closure-2021/">More info »</a>';
    }

    $notice = '<div style="font-style: italic;color: #ed0000;">' . $notice . '</div>';

    global $post;

    if (is_page(20695) || is_page(20698) || is_page(203) || is_page(6848) || is_page(12582) || is_page(14120)) { // 20695, 20698 = služby, 203, 6848 - katalogy, 12582, 14120 - eiz
        echo $notice;
        return;
    }

    if (in_array(20695, get_post_ancestors($post->ID)) || in_array(20698, get_post_ancestors($post->ID)) || in_array(203, get_post_ancestors($post->ID)) || in_array(6848, get_post_ancestors($post->ID)) || in_array(12582, get_post_ancestors($post->ID)) || in_array(14120, get_post_ancestors($post->ID))) {
        echo $notice;
        return;
    }
}

require 'custom-fields.php';


add_filter('caldera_forms_phone_js_options', function ($options) {
    $options['initialCountry'] = 'CZ';
    $options['onlyCountries'] = ['CZ', 'SK'];
    return $options;
});


add_filter('admin_footer', function () { ?>
    <style type="text/css">
        .jquery-migrate-deprecation-notice,
        .jquery-migrate-dashboard-notice {
            display: none !important
        }
    </style>
    <?php
}, 99);


add_shortcode('video-prehravac', function ($atts) {
    if (!isset($atts['soubor'])) {
        return '';
    }
    wp_enqueue_script('plyr');
    wp_enqueue_style('plyr');
    $id = 'player-' . mt_rand(0, 9999);
    ob_start(); ?>
    <video id="<?= $id ?>" data-handle="<?= $atts['soubor'] ?>"></video>
    <script>
        document.addEventListener('DOMContentLoaded', (event) => {
            var player = new Plyr('#<?= $id ?>');
            player.source = {
                type: 'video',
                sources: [{
                    src: 'https://lib.cas.cz/videa/' + document.getElementById('<?= $id ?>').dataset.handle,
                    type: 'video/mp4',
                }, ],
            };
        })
    </script>
    <?php return ob_get_clean();
});


function nonbreaking_spaces($content)
{
    $content = str_replace(
        [
            ' k ', ' K ',
            ' o ', ' O ',
            ' s ', ' S ',
            ' u ', ' U ',
            ' v ', ' V ',
            ' z ', ' Z ',
        ],
        [
            ' k&nbsp;', ' K&nbsp;',
            ' o&nbsp;', ' O&nbsp;',
            ' s&nbsp;', ' S&nbsp;',
            ' u&nbsp;', ' U&nbsp;',
            ' v&nbsp;', ' V&nbsp;',
            ' z&nbsp;', ' Z&nbsp;',
        ],
        $content
    );

    return $content;
}
add_filter('the_title', 'nonbreaking_spaces');
add_filter('the_content', 'nonbreaking_spaces');
add_filter('the_excerpt', 'nonbreaking_spaces');


add_action('cmb2_admin_init', function () {
    $cmb_options = new_cmb2_box([
        'id' => 'kn_option_metabox',
        'title' => 'Nastavení šablony',
        'object_types' => [
            'options-page'
        ],
        'option_key' => 'kn_options',
        'icon_url' => 'dashicons-carrot',
    ]);

    $cmb_options->add_field([
        'id' => 'opening_cs',
        'name' => 'Otevírací doba česky',
        'type' => 'text',
    ]);

    $cmb_options->add_field([
        'id' => 'opening_en',
        'name' => 'Otevírací doba anglicky',
        'type' => 'text',
    ]);
});

function kn_get_option($key = '', $default = false)
{
    if (function_exists('cmb2_get_option')) {
        return cmb2_get_option('kn_options', $key, $default);
    }

    $opts = get_option('kn_options', $default);

    $val = $default;

    if ('all' == $key) {
        $val = $opts;
    } elseif (is_array($opts) && array_key_exists($key, $opts) && false !== $opts[$key]) {
        $val = $opts[$key];
    }

    return $val;
}

function wpf_dev_smart_phone_field_initial_country() {
    ?>
    <script type="text/javascript">
        jQuery( document ).on( 'wpformsReady', function() {
            jQuery( '.wpforms-smart-phone-field' ).each(function(e){
                var $el = jQuery( this ),
                    iti = $el.data( 'plugin_intlTelInput' ),
                    options;
                // Options are located in different keys of minified and unminified versions of jquery.intl-tel-input.js.
                if ( iti.d ) {
                    options = Object.assign( {}, iti.d );
                } else if ( iti.options ) {
                    options = Object.assign( {}, iti.options );
                }
                if ( ! options ) {
                    return;
                }
                $el.intlTelInput( 'destroy' );
                 
                // Put a country code here according to this list: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2
                options.initialCountry = 'CZ';
                 
                $el.intlTelInput( options );
                 
                // Restore hidden input name after intlTelInput is reinitialized.
                $el.siblings( 'input[type="hidden"]' ).attr( 'name', 'wpforms[fields][' + options.hiddenInput + ']' );
            });
        } );
    </script>
    <?php
}
add_action( 'wpforms_wp_footer_end', 'wpf_dev_smart_phone_field_initial_country', 30 );

/**
 * Load the date picker locale strings.
 *
 * @link https://wpforms.com/developers/localize-the-date-picker-strings/
 */
 
function wpf_dev_datepicker_locale() {
     
    wp_enqueue_script( 
        'wpforms-datepicker-locale', 
            'https://npmcdn.com/flatpickr@4.6.13/dist/l10n/cs.js',
        array( 'wpforms-flatpickr' ), 
        null, 
        true
    );
 
}
 
add_action( 'wp_enqueue_scripts', 'wpf_dev_datepicker_locale', 10 );
/**
 * Apply the date picker locale strings.
 *
 * @link https://wpforms.com/developers/localize-the-date-picker-strings/
 */
 
function wpf_dev_datepicker_apply_locale() {
    ?>
 
    <script type="text/javascript">
    jQuery( document ).ready( function() {
 
        jQuery( '.wpforms-datepicker-wrap' ).each( function() {
            var calendar = this._flatpickr;
 
            if ( 'object' === typeof calendar ) {
                calendar.set( 'locale', 'cs' );
            }
 
        } );
 
    } );
 
    </script>
 
    <?php
}
 
/**
 * Limit the times available in the Date Time field time picker.
 *
 * @link https://wpforms.com/developers/customize-the-date-time-field-time-picker/
 */
 
function wpf_dev_limit_time_picker() {
    ?>
 
    <script type="text/javascript">
 
    jQuery( document ).ready( function() {

        setTimeout(function(){ 
            $('.flatpickr-day.today').addClass('flatpickr-disabled');
            $('.flatpickr-day.today').next("span").addClass('flatpickr-disabled');
        }, 2000);

        jQuery( '#wpforms-33512-field_3' ).click( function() {
            $('.flatpickr-day.today').addClass('flatpickr-disabled');
            $('.flatpickr-day.today').next("span").addClass('flatpickr-disabled');
        } );
    } );
 
    </script>
 
    <?php
}
add_action( 'wpforms_wp_footer', 'wpf_dev_limit_time_picker', 30 );


function load_posts_ajax() {
    $args = array(
        'category_name' => 'aktualne',
        'posts_per_page' => 10,
        'post__not_in' => get_option('sticky_posts'),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) :
        while ($query->have_posts()) : $query->the_post();
            get_template_part('loop', 'new-item');
        endwhile;
        wp_reset_postdata();
    else :
        echo 'No posts found';
    endif;

    die(); // Always include this to exit
}
add_action('wp_ajax_load_posts', 'load_posts_ajax');
add_action('wp_ajax_nopriv_load_posts', 'load_posts_ajax');

// Localize the ajaxurl variable
function localize_ajaxurl() {
    wp_localize_script('jquery', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'localize_ajaxurl');


/**
 * Opening hours and free places
 */

// Function to define constant variables and messages
function define_constants() {
    return array(
        'current_time' => current_time('Y-m-d H:i:s'),
        'current_date' => current_time('Y-m-d'),
        'czech_days' => array(
            'Monday' => 'V pondělí',
            'Tuesday' => 'V úterý',
            'Wednesday' => 'Ve středu',
            'Thursday' => 'Ve čtvrtek',
            'Friday' => 'V pátek',
            'Saturday' => 'V sobotu',
            'Sunday' => 'V neděli',
        ),
        'opening_time' => '09:00',
        'closing_time' => '19:00',
        'db_host' => 'aleph23.lib.cas.cz',
        'db_port' => '1521',
        'db_service' => 'aleph23',
        'db_user' => 'turniket',
        'db_password' => 'turniket05',
        'correlation_coefficient' => 0.17,
    );
}

// Function to check if a given date is a working day in the Czech Republic
function is_working_day($date) {
    $state_holidays = array(
        '01-01' => 'Nový rok',
        'easter-2' => 'Velký pátek',
        'easter-1' => 'Velikonoční pondělí',
        '05-01' => 'Svátek práce',
        '07-05' => 'Den vítězství',
        '07-06' => 'Den upálení mistra Jana Husa',
        '09-28' => 'Den české státnosti',
        '10-28' => 'Den vzniku samostatného československého státu',
        '11-17' => 'Den boje za svobodu a demokracii',
        '12-24' => 'Štědrý den',
        '12-25' => '1. svátek vánoční',
        '12-26' => '2. svátek vánoční',
    );

    $date_key = date('m-d', strtotime($date));

    if (isset($state_holidays[$date_key])) {
        return false;
    }

    // Check if the date is Saturday or Sunday
    if (date('N', strtotime($date)) >= 6) {
        return false;
    }

    return true;
}

// Function to get the next working day
function get_next_working_day($date) {
    $next_day = strtotime($date . ' +1 day');

    while (!is_working_day(date('Y-m-d', $next_day))) {
        $next_day = strtotime(date('Y-m-d', $next_day) . ' +1 day');
    }

    return date('Y-m-d', $next_day);
}

// Function to calculate Good Friday for a given year
function calculate_good_friday($year) {
    $easterDate = date('Y-m-d', easter_date($year));
    $velkyPatekTimestamp = strtotime('-2 days', strtotime($easterDate));
    return date('Y-m-d', $velkyPatekTimestamp);
}

// Shortcode to display opening hours
function opening_hours_shortcode() {
    $constants = define_constants();
    extract($constants);

    date_default_timezone_set('Europe/Prague');
    $output = '';
    $circle_color = 'closed';
    $additional_info = '';

    $current_timestamp = strtotime($current_time);

if (is_working_day($current_date)) {
    $opening_timestamp = strtotime($opening_time);
    $closing_timestamp = strtotime($closing_time);

    if ($current_timestamp >= $closing_timestamp) {
        $next_working_day = get_next_working_day($current_date);
        $next_working_day_name = $czech_days[date('l', strtotime($next_working_day))];
        $additional_info = sprintf(
            __("Zavřeno. Otevírá se %s v %s", "knav"),
            $next_working_day_name,
            $opening_time
        );
    } elseif ($current_timestamp >= $opening_timestamp && $current_timestamp < $closing_timestamp) {
        $circle_color = 'open';
        $current_day_name = $czech_days[date('l', strtotime($current_date))];
        $additional_info = sprintf(
            __("Otevřeno (zavírá se v %s)", "knav"),
            $closing_time
        );
    } else { // KNAV is closed but will open later the same day
        $circle_color = 'closed';
        $current_day_name = $czech_days[date('l', strtotime($current_date))];
        $additional_info = $current_date === date('Y-m-d') ?
            sprintf(__("Zavřeno (otevírá se dnes v %s)", "knav"), $opening_time) :
            sprintf(
                __("Zavřeno (otevírá se %s v %s)", "knav"),
                $current_day_name,
                $opening_time
            );
    }
} else {
    $next_working_day = get_next_working_day($current_date);
    $next_working_day_name = $czech_days[date('l', strtotime($next_working_day))];
    $additional_info = sprintf(
        __("Zavřeno. Otevírá se %s v %s", "knav"),
        $next_working_day_name,
        $opening_time
    );
}

$current_year = date('Y');
$good_friday = calculate_good_friday($current_year);

if ($current_date == $good_friday) {
    $circle_color = 'closed';
    $next_working_day_name = $czech_days[date('l', strtotime(get_next_working_day($good_friday)))];
    $additional_info = sprintf(
        __("Zavřeno. Otevírá se ve %s %s", "knav"),
        $next_working_day_name,
        $opening_time
    );
}

    $current_year = date('Y');
    $good_friday = calculate_good_friday($current_year);

    if ($current_date == $good_friday) {
        $circle_color = 'closed';
        $next_working_day_name = $czech_days[date('l', strtotime(get_next_working_day($good_friday)))];
        $additional_info = "Zavřeno. Otevírá se ve $next_working_day_name $opening_time";
    }

    $output .= "<span class='opening-hours $circle_color'></span><span class='opening-hours-info'>$additional_info</span>";

    return $output;
}
add_shortcode('opening_hours', 'opening_hours_shortcode');

// Shortcode to display free places
function get_free_places_data() {
    $constants = define_constants();
    extract($constants);

    $output = '';

    $conn = oci_connect($db_user, $db_password, "(DESCRIPTION=(ADDRESS=(PROTOCOL=TCP)(HOST=$db_host)(PORT=$db_port))(CONNECT_DATA=(SERVICE_NAME=$db_service)))");

    if (!$conn) {
        return "Žádná data";
    } else {
        if (date("N") >= 1 && date("N") <= 5 && date("H") >= 9 && date("H") < 19 && is_working_day(date("Y-m-d"))) {
            $desired_time = "09:00:00";
            $desired_datetime = $current_date . ' ' . $desired_time;

            $sql_station1 = "SELECT COUNT(*) FROM kna50.turniket WHERE Udalost=1 AND StaniceID=1 AND (DatumCas BETWEEN To_Date('$desired_datetime', 'YYYY-MM-DD HH24:MI:SS') AND To_Date('$current_time', 'YYYY-MM-DD HH24:MI:SS'))";
            $stmt_station1 = oci_parse($conn, $sql_station1);
            oci_execute($stmt_station1);
            oci_fetch($stmt_station1);
            $count_station1 = oci_result($stmt_station1, 1);

            $sql_station2 = "SELECT COUNT(*) FROM kna50.turniket WHERE Udalost=1 AND StaniceID=2 AND (DatumCas BETWEEN To_Date('$desired_datetime', 'YYYY-MM-DD HH24:MI:SS') AND To_Date('$current_time', 'YYYY-MM-DD HH24:MI:SS'))";
            $stmt_station2 = oci_parse($conn, $sql_station2);
            oci_execute($stmt_station2);
            oci_fetch($stmt_station2);
            $count_station2 = oci_result($stmt_station2, 1);

            oci_free_statement($stmt_station1);
            oci_free_statement($stmt_station2);

            $difference = $count_station1 - $count_station2;
            $difference += round($difference * $correlation_coefficient);
            $difference = 200 - $difference;

            if ($difference >= 0) {
                $output .= "<span class='free-places free-places-yes'><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M396.8 352h22.4c6.4 0 12.8-6.4 12.8-12.8V108.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v230.4c0 6.4 6.4 12.8 12.8 12.8zm-192 0h22.4c6.4 0 12.8-6.4 12.8-12.8V140.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v198.4c0 6.4 6.4 12.8 12.8 12.8zm96 0h22.4c6.4 0 12.8-6.4 12.8-12.8V204.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v134.4c0 6.4 6.4 12.8 12.8 12.8zM496 400H48V80c0-8.8-7.2-16-16-16H16C7.2 64 0 71.2 0 80v336c0 17.7 14.3 32 32 32h464c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16zm-387.2-48h22.4c6.4 0 12.8-6.4 12.8-12.8v-70.4c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v70.4c0 6.4 6.4 12.8 12.8 12.8z'/></svg>" . __("Volných míst: ", 'knav') . "$difference/200</span>";
            } else {
                $output .= "<span class='free-places free-places-no'><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M396.8 352h22.4c6.4 0 12.8-6.4 12.8-12.8V108.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v230.4c0 6.4 6.4 12.8 12.8 12.8zm-192 0h22.4c6.4 0 12.8-6.4 12.8-12.8V140.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v198.4c0 6.4 6.4 12.8 12.8 12.8zm96 0h22.4c6.4 0 12.8-6.4 12.8-12.8V204.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v134.4c0 6.4 6.4 12.8 12.8 12.8zM496 400H48V80c0-8.8-7.2-16-16-16H16C7.2 64 0 71.2 0 80v336c0 17.7 14.3 32 32 32h464c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16zm-387.2-48h22.4c6.4 0 12.8-6.4 12.8-12.8v-70.4c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v70.4c0 6.4 6.4 12.8 12.8 12.8z'/></svg>" . __("Volných míst: ", 'knav') . "Volná místa nejsou</span>";
            }
        } else {
            $output .= "<span class='free-places free-places-no'><svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 512 512'><path d='M396.8 352h22.4c6.4 0 12.8-6.4 12.8-12.8V108.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v230.4c0 6.4 6.4 12.8 12.8 12.8zm-192 0h22.4c6.4 0 12.8-6.4 12.8-12.8V140.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v198.4c0 6.4 6.4 12.8 12.8 12.8zm96 0h22.4c6.4 0 12.8-6.4 12.8-12.8V204.8c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v134.4c0 6.4 6.4 12.8 12.8 12.8zM496 400H48V80c0-8.8-7.2-16-16-16H16C7.2 64 0 71.2 0 80v336c0 17.7 14.3 32 32 32h464c8.8 0 16-7.2 16-16v-16c0-8.8-7.2-16-16-16zm-387.2-48h22.4c6.4 0 12.8-6.4 12.8-12.8v-70.4c0-6.4-6.4-12.8-12.8-12.8h-22.4c-6.4 0-12.8 6.4-12.8 12.8v70.4c0 6.4 6.4 12.8 12.8 12.8z'/></svg>" . __("Volná místa nejsou: ", 'knav') . "</span>";
        }

        oci_close($conn);

        return $output;
    }
}

function get_free_places_callback() {
    $output = get_free_places_data();
    echo $output;
    wp_die();
}

add_action('wp_ajax_nopriv_get_free_places', 'get_free_places_callback');
add_action('wp_ajax_get_free_places', 'get_free_places_callback');

function display_free_places_shortcode() {
    $output = get_free_places_data();

    // Output the initial data
    $output = "<span class='free-places'>" . $output . "</span>";

    // Output the JavaScript code
    $output .= "<script>
        function getFreePlaces() {
            jQuery.ajax({
                url: '" . admin_url('admin-ajax.php') . "',
                data: {
                    action: 'get_free_places', // matches the WordPress AJAX action
                },
                success: function(response) {
                    //console.log(response); // for debugging purposes
                    // Update the HTML of your element with the response
                    jQuery('.free-places').html(response);
                },
                error: function(error) {
                    console.warn(error); // for debugging purposes
                },
                complete: function() {
                    // Schedule the next request when the current one's complete
                    setTimeout(getFreePlaces, 60000); // 60000 milliseconds = 1 minute
                },
            });
        }
        // Start the first request after the initial data is loaded
        jQuery(document).ready(function() {
            setTimeout(getFreePlaces, 5000); // 5000 milliseconds = 5 seconds
        });
    </script>";

    return $output;
}

add_shortcode('display_tables', 'display_free_places_shortcode');

add_action('init', function () {
    if (!function_exists('new_cmb2_box')) {
        var_dump('CMB2 is NOT loaded');
    } else {
        var_dump('CMB2 IS loaded');
    }
});


