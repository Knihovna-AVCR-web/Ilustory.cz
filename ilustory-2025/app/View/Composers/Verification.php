<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Verification extends Composer
{
    protected $token;

    public function __construct($token = '')
    {
        $this->token = sanitize_text_field($token);
    }

    public function verify()
    {
        if (!$this->tokenExists()) {
            return [
                'message' => __('Ověření selhalo', 'ilustory'),
                'success' => false,
            ];
        }

        if ($this->isVerified()) {
            return [
                'message' => __('E-mail je už ověřený', 'ilustory'),
                'success' => false,
            ];
        }

        $save = $this->saveVerification();

        if (empty($save)) {
            return [
                'message' => __('Ověření selhalo', 'ilustory'),
                'success' => false,
            ];
        }

        $this->sendVerification($save);
        $this->sendNotification($save);

        return [
            'message' => __('Vaše e-mailová adresa byla úspěšně ověřena', 'ilustory'),
            'success' => true,
        ];
    }

    protected function tokenExists()
    {
        return (bool) $GLOBALS['wpdb']->get_var(
            $GLOBALS['wpdb']->prepare(
                "SELECT COUNT(id) FROM `{$GLOBALS['wpdb']->prefix}writing_contest` WHERE token = %s",
                $this->token
            )
        );
    }

    protected function isVerified()
    {
        $record = $GLOBALS['wpdb']->get_var(
            $GLOBALS['wpdb']->prepare(
                "SELECT token_verified FROM `{$GLOBALS['wpdb']->prefix}writing_contest` WHERE token = %s",
                $this->token
            )
        );

        return !is_null($record);
    }

    protected function saveVerification()
    {
        $query = $GLOBALS['wpdb']->update(
            "{$GLOBALS['wpdb']->prefix}writing_contest",
            ['token_verified' => date('Y-m-d H:i:s')],
            ['token' => $this->token],
            ['%s'],
            ['%s']
        );

        if (!$query) {
            return [];
        }

        return $GLOBALS['wpdb']->get_row(
            $GLOBALS['wpdb']->prepare(
                "SELECT fullname, age, email, phone, work_name, work_url FROM `{$GLOBALS['wpdb']->prefix}writing_contest` WHERE token = %s",
                $this->token
            ),
            ARRAY_A
        );
    }

    public function sendVerification($data)
    {
        $subject = __('Potvrzení přihlášky do soutěže IluStory 2025', 'Ilustory');
        ob_start(); ?>
        <p>
                Milý soutěžící/milá soutěžící,
        </p>
        <p>
                právě jste se úspěšně zapojili do literární soutěže IluStory 2025.
        </p>
        <p>
                Zde jsou údaje, které jste uvedli:
        </p>
        <ul>
            <li>
                Jméno, příjmení: <?= $data['fullname'] ?>
            </li>
            <li>
                Věková kategorie: <?= $data['age'] >= 15 ? 'od 15 let výše' : 'do 15 let' ?>
            </li>
            <li>
                Název povídky: <?= $data['work_name']; ?>
            </li>
            <li>
                E-mail: <?= $data['email'] ?>
            </li>
            <li>
                Telefonní číslo: <?= $data['phone'] ?>
            </li>
        </ul>
        <p>
                Pokud se Vaše povídka dostane do užšího výběru, ozveme se Vám na uvedený kontakt a pozveme Vás na slavnostní předání cen, které proběhne v sobotu 8. 11. 2025.
                <br>
                Výsledky zveřejníme také na webu <a href="https://ilustory.cz/">www.ilustory.cz</a>.
        </p>
        <p>
                Těší nás Váš zájem o soutěž
                <br>
                <p>Tým IluStory 2025</p>
        </p>
        <?php
        $message = ob_get_clean();

        wp_mail(
            $data['email'] . ' <' . $data['email'] . '>',
            $subject,
            \App\getEmailTemplate($subject, $message),
            [
                'From: Knihovna Akademie věd ČR <neodpovidat@ilustory.cz>',
                'Reply-To: Knihovna Akademie věd ČR <pr@knav.cz>',
                'Content-Type: text/html; charset=UTF-8',
            ],
            [
                ABSPATH . '..' . wp_make_link_relative($data['work_url']),
            ]
        );
    }

    protected function sendNotification($data)
    {
        $subject = __('Ilustory: nová přihláška', 'ilustory');
        $message = __('Nově přihlášená a ověřená povídka: ', 'ilustory');
        $message .= "<a href='{$data['work_url']}' target='_blank'>{$data['work_name']}</a>";

        wp_mail(
            'pr@knav.cz <pr@knav.cz>',
            $subject,
            \App\getEmailTemplate($subject, $message),
            [
                'From: Ilustory <neodpovidat@ilustory.cz>',
                'Content-Type: text/html; charset=UTF-8',
            ]
        );
    }
}
