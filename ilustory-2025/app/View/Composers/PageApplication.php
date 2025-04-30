<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class PageApplication extends Composer
{
    public function with()
    {
        return [
            'active' => carbon_get_theme_option('contest_active'),
            'currentUrl' => home_url(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)),
            'rulesUrl' => carbon_get_theme_option('rules_url'),
        ];
    }
}
