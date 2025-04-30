<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;

class Submission extends Composer
{
    public function handle()
    {
        $_SESSION['ilustory-2025-form'] = $_POST;

        $redirect_url = esc_url($_POST['current_url']);

        $validation = $this->validate();

        if (!empty($validation)) {
            $_SESSION['ilustory-2025']['subscribed'] = $validation;
            wp_redirect($redirect_url . '?subscribed=' . $validation);
            exit;
        }

        $file = $this->saveFile();

        if ($file['success'] === false) {
            $_SESSION['ilustory-2025']['subscribed'] = '1';
            wp_redirect($redirect_url . '?subscribed=true');
            exit;
        }

        $token = 'il-' .  md5(mt_rand());
        $this->save($file['message'], $token);
        $this->sendVerificationMail(sanitize_email($_POST['email']), $token);
        $_SESSION['ilustory-2025']['subscribed'] = '1';
        $_SESSION['ilustory-2025-form'] = [];

        wp_redirect($redirect_url . '?subscribed=true');
        exit;
    }

    protected function validate()
    {
        if (isset($_POST['fax']) && $_POST['fax'] == '1') {
            return __('Formulář se nepodařilo ověřit', 'ilustory');
        }

        if ($this->isEmailUsed($_POST['email'])) {
            return __('Zadaná e-mailová adresa je už použitá.', 'ilustory');
        }

        $v = new \Valitron\Validator($_POST);
        $v->rule('required', ['fullname', 'email', 'age', 'story', 'consent'])->message('Pole {field} je povinné');
        $v->rule('email', 'email')->message('{field} nemá správný formát');
        $v->rule('accepted', 'consent')->message('{field} je povinný');
        $v->rule('integer', 'age', true)->message('{field} nemá správný formát');
        $v->rule('min', 'age', 3)->message('Pole {field} je neplatné');
        $v->rule('max', 'age', 120)->message('Pole {field} je neplatné');
        $v->rule('numeric', 'phone', true)->message('{field} nemá správný formát');

        $v->labels([
            'fullname' => 'Jméno a příjmení',
            'email' => 'E-mailová adresa',
            'phone' => 'Telefonní číslo',
            'age' => 'Věk',
            'story' => 'Název povídky',
            'note' => 'Komentář',
            'consent' => 'Souhlas s pravidly soutěže a se zpracováním osobních údajů'

        ]);

        return $v->validate() ? '' : $v->errors();
    }

    protected function save($fileUrl, $token)
    {
        $this->createTable();

        $query = $GLOBALS['wpdb']->query(
            $GLOBALS['wpdb']->prepare(
                "INSERT INTO {$GLOBALS['wpdb']->prefix}writing_contest (created_at, fullname, email, phone, work_name, work_url, note, age, token, work_status, consent)
                VALUES (%s, %s, %s, %s, %s, %s, %s, %d, %s, %s, %d)",
                date('Y-m-d H:i:s'),
                sanitize_text_field($_POST['fullname']),
                sanitize_email($_POST['email']),
                (int) $_POST['phone'],
                sanitize_text_field($_POST['story']),
                $fileUrl,
                sanitize_textarea_field($_POST['note']),
                (int) $_POST['age'],
                $token,
                'valid',
                1
            )
        );

        if (!$query) {
            wp_die($GLOBALS['wpdb']->last_error);
        }

        return $query;
    }

    protected function saveFile()
    {
        $file = $_FILES['story-file'];

        if (!file_exists($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return [
                'message' => __('Chybí soubor', 'ilustory'),
                'success' => false,
            ];
        }

        if ($file['size'] > 2097152) { // 2 mb
            return [
                'message' => __('Soubor je větší než 2 MB.', 'ilustory'),
                'success' => false,
            ];
        }

        if (!in_array($file['type'], ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/msword', 'application/pdf', 'text/plain', 'application/vnd.oasis.opendocument.text'])) {
            return [
                'message' => __('Nepodporovaný formát.', 'ilustory'),
                'success' => false,
            ];
        }

        $upload_dir = wp_upload_dir()['basedir'] . '/soutez';
        $name = $this->createFileName($file);
        $filename = $upload_dir . '/' . $name;

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $uploaded = move_uploaded_file($file['tmp_name'], $filename);

        if (!$uploaded) {
            return [
                'message' => error_get_last()['message'],
                'success' => false,
            ];
        }

        return [
            'message' => wp_get_upload_dir()['baseurl'] . '/soutez/' . $name,
            'success' => true,
        ];
    }

    protected function createFileName($file)
    {
        return time()
            . '-' . sanitize_file_name(strtolower(remove_accents($_POST['story'])))
            . '.'
            . pathinfo($file['name'], PATHINFO_EXTENSION);
    }

    protected function createTable()
    {
        $query = $GLOBALS['wpdb']->query(
            "CREATE TABLE IF NOT EXISTS `{$GLOBALS['wpdb']->prefix}writing_contest` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `created_at` datetime NOT NULL,
                `fullname` varchar(255) NOT NULL,
                `email` varchar(255) NOT NULL,
                `phone` varchar(255) DEFAULT NULL,
                `work_name` varchar(255) NOT NULL,
                `work_url` varchar(255) NOT NULL,
                `note` longtext NOT NULL,
                `age` int(11) NOT NULL,
                `consent` tinyint(1) NOT NULL,
                `token` varchar(255) NOT NULL,
                `token_verified` datetime DEFAULT NULL,
                `work_status` varchar(255) NOT NULL,
                PRIMARY KEY (`id`)
            )"
        );

        if (!$query) {
            wp_die($GLOBALS['wpdb']->last_error);
        }

        return $query;
    }

    protected function isEmailUsed($email)
    {
        $record = $GLOBALS['wpdb']->get_var(
            $GLOBALS['wpdb']->prepare(
                "SELECT COUNT(id) FROM `{$GLOBALS['wpdb']->prefix}writing_contest` WHERE email = %s",
                $email
            )
        );

        return (int) $record > 0 ? true : false;
    }

    protected function sendVerificationMail($email, $token)
    {
        $subject = __('Ověření účasti v soutěži IluStory 2025', 'ilustory');
        $link = home_url('verification?token=' . $token);
        $message = __('Milí účastníci,  <br><br>');
        $message .= __('děkujeme za Váš zájem o účast v soutěži IluStory 2025. <br>', 'ilustory');
        $message .= __('Pro ověření Vaší registrace prosím klikněte na následující odkaz: <br><br>');
        $message .= "<a href='{$link}'>{$link}</a> <br><br>";
        $message .= __('V případě dotazů se na nás neváhejte obrátit. <br><br>');
        $message .= __('S přáním hezkého dne, <br><br>');
        $message .= __('Tým IluStory');



        $headers = [
            'From: Knihovna Akademie věd ČR <neodpovidat@ilustory.cz>',
            'Reply-To: Knihovna Akademie věd ČR <pr@knav.cz>',
            'Content-Type: text/html; charset=UTF-8',
        ];

        wp_mail($email . ' <' . $email . '>', $subject, \App\getEmailTemplate($subject, "<p>$message</p>"), $headers);
    }
}
