<?php

namespace App;

add_action('admin_init', function () {
    $role = get_role('administrator');
    $role->add_cap('view_writing_contest_records');
    $role->add_cap('manage_writing_contest_records');
});

add_action('admin_menu', function () {
    add_menu_page(
        'Literární soutěž',
        'Literární soutěž',
        'view_writing_contest_records',
        'literarni-soutez',
        function () {
            echo view('admin', [
                'records' => getRecords(),
                'headers' => [
                    'ID', 'Jméno', 'E-mail', 'Telefon', 'Povídka', 'Věk', 'Poznámka', 'Datum', 'Akce',
                ],
                'ajaxUrl' => admin_url('admin-ajax.php'),
            ])->render();
        },
        'dashicons-media-text',
        10
    );
});

function getRecords()
{
    $records = collect($GLOBALS['wpdb']->get_results(
        "SELECT id, created_at, fullname, email, phone, work_name, work_url, note, age, token_verified, work_status
        FROM `{$GLOBALS['wpdb']->prefix}writing_contest`
        ORDER BY created_at DESC",
        ARRAY_A
    ));

    return [
        'invalid' => $records
            ->where('work_status', '=', 'invalid')
            ->toArray(),
        'unverified' => $records
            ->where('work_status', '=', 'valid')
            ->whereNull('token_verified')
            ->toArray(),
        'verified' => $records
            ->where('work_status', '=', 'valid')
            ->whereNotNull('token_verified')
            ->toArray(),
    ];
}
