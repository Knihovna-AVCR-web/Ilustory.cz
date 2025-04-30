<?php

namespace App\View\Composers;

use Roots\Acorn\View\Composer;
use App\View\Composers\Verification;

class ContestAdmin extends Composer
{
    public function changeStatus()
    {
        if (!current_user_can('manage_writing_contest_records')) {
            wp_send_json_error(403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $query = $GLOBALS['wpdb']->update(
            "{$GLOBALS['wpdb']->prefix}writing_contest",
            ['work_status' => sanitize_text_field($data['status'])],
            ['id' => (int) $data['id']],
            ['%s'],
            ['%d'],
        );

        if (!$query) {
            wp_send_json_error(500);
        }

        wp_send_json_success(200);
    }

    public function verify()
    {
        if (!current_user_can('manage_writing_contest_records')) {
            wp_send_json_error(403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $query = $GLOBALS['wpdb']->update(
            "{$GLOBALS['wpdb']->prefix}writing_contest",
            ['token_verified' => date('Y-m-d H:i:s')],
            ['id' => (int) $data['id']],
            ['%s',],
            ['%s',]
        );

        if (!$query) {
            wp_send_json_error(500);
        }

        $data = $GLOBALS['wpdb']->get_row(
            $GLOBALS['wpdb']->prepare(
                "SELECT fullname, age, email, phone, work_name, work_url FROM `{$GLOBALS['wpdb']->prefix}writing_contest` WHERE id = %s",
                (int) $data['id']
            ),
            ARRAY_A
        );

        $verification = new Verification('');

        $verification->sendVerification($data);

        wp_send_json_success(200);
    }

    public function deleteStory()
    {
        if (!current_user_can('manage_writing_contest_records')) {
            wp_send_json_error(403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        $query = $GLOBALS['wpdb']->delete(
            "{$GLOBALS['wpdb']->prefix}writing_contest",
            ['id' => (int) $data['id']],
            ['%d']
        );

        if (!$query) {
            wp_send_json_error(500);
        }

        wp_send_json_success(200);
    }

    public function download()
    {
        $records = $GLOBALS['wpdb']->get_results(
            "SELECT `id`, `created_at`, `fullname`, `email`, `phone`, `work_name`, `work_url`, `note`, `age`
            FROM `{$GLOBALS['wpdb']->prefix}writing_contest`
            WHERE token_verified IS NOT NULL
            AND work_status = 'valid'
            ORDER BY created_at DESC",
            ARRAY_A
        );

        $records = collect($records)
            ->map(function ($record) {
                return [
                    'ID' => $record['id'],
                    'Vytvořeno' => $record['created_at'],
                    'Jméno' => $record['fullname'],
                    'Email' => $record['email'],
                    'Telefon' => $record['phone'],
                    'Povídka' => $record['work_name'],
                    'URL' => $record['work_url'],
                    'Poznámka' => trim(preg_replace('/\s+/', ' ', $record['note'])),
                    'Věk' => $record['age'],
                ];
            })
            ->toArray();

        ob_clean();
        header('Pragma: public');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Cache-Control: private', false);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=ilustory-' . date('Y-m-d') . '.csv');
        if (isset($records['0'])) {
            $fp = fopen('php://output', 'w');
            fputcsv($fp, array_keys($records['0']), ';');
            foreach ($records as $values) {
                fputcsv($fp, $values, ';');
            }
            fclose($fp);
        }
        ob_flush();
    }
}
