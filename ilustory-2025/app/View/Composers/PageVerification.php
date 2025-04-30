<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use App\View\Composers\Verification;

class PageVerification extends Composer
{
    public function __construct()
    {
        if (!isset($_GET['token'])) {
            die('<script>location.href = "' . home_url() . '"</script>');
        }
    }

    public function with()
    {
        return [
            'verification' => (new Verification($_GET['token']))->verify(),
        ];
    }
}
