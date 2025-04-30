<?php

namespace App\View\Composers;

use Log1x\Navi\Navi;
use Roots\Acorn\View\Composer;

class App extends Composer
{
    protected static $views = [
        '*',
    ];

    public function with()
    {
        return [
            'assets' => \App\assets(),
            'navigation' => (new Navi())->build('primary_navigation'),
            'siteName' => get_bloginfo('name', 'display'),
            'title' => $this->title(),
        ];
    }

    protected function title()
    {
        if (is_home()) {
            if ($home = get_option('page_for_posts', true)) {
                return get_the_title($home);
            }

            return __('Novinky', 'ilustory');
        }

        if (is_archive()) {
            return single_term_title();
        }

        if (is_search()) {
            return sprintf(__('Výsledky hledání pro %s', 'ilustory'), get_search_query());
        }

        if (is_404()) {
            return __('Nenalezeno', 'ilustory');
        }

        return get_the_title();
    }
}
