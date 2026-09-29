<?php



function punksetter_gmail_get_messages($limit = 10)
{

    $token_file = __DIR__ . '/gmail-token.json';

    if (!file_exists($token_file)) {
        return new WP_Error(
            'gmail_token_missing',
            'gmail-token.json no encontrado'
        );
    }

    $tokens = json_decode(
        file_get_contents($token_file),
        true
    );

    if (!is_array($tokens)) {
        return new WP_Error(
            'gmail_token_invalid',
            'Token Gmail inválido'
        );
    }

    if (!isset($tokens['created'])) {
        $tokens['created'] = time();
    }



    if (
        isset($tokens['expires_in']) &&
        time() > ($tokens['created'] + $tokens['expires_in'])
    ) {



        $secret_file = __DIR__ . '/client_secret.json';

        if (!file_exists($secret_file)) {
            return new WP_Error(
                'gmail_secret_missing',
                'client_secret.json no encontrado'
            );
        }

        $google = json_decode(
            file_get_contents($secret_file),
            true
        );

        $refresh = wp_remote_post(
            'https://oauth2.googleapis.com/token',
            [
                'body' => [
                    'client_id' => $google['web']['client_id'],
                    'client_secret' => $google['web']['client_secret'],
                    'refresh_token' => $tokens['refresh_token'],
                    'grant_type' => 'refresh_token'
                ]
            ]
        );

        if (is_wp_error($refresh)) {
            return $refresh;
        }

        $new_token = json_decode(
            wp_remote_retrieve_body($refresh),
            true
        );



        if (!empty($new_token['access_token'])) {

            $tokens['access_token'] = $new_token['access_token'];
            $tokens['created'] = time();

            file_put_contents(
                $token_file,
                json_encode($tokens, JSON_PRETTY_PRINT)
            );
        }
    }

    $access_token = $tokens['access_token'];


    $list = wp_remote_get(
        'https://gmail.googleapis.com/gmail/v1/users/me/messages?maxResults=' . intval($limit),
        [
            'headers' => [
                'Authorization' => 'Bearer ' . $access_token
            ]
        ]
    );

    if (is_wp_error($list)) {
        return $list;
    }

    $list_body = json_decode(
        wp_remote_retrieve_body($list),
        true
    );


    $messages = [];

    if (!empty($list_body['messages'])) {

        foreach ($list_body['messages'] as $msg) {

            $msg_id = $msg['id'];

            $detail = wp_remote_get(
                'https://gmail.googleapis.com/gmail/v1/users/me/messages/' .
                    $msg_id .
                    '?format=metadata&metadataHeaders=Subject&metadataHeaders=From&metadataHeaders=Date',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $access_token
                    ]
                ]
            );

            if (is_wp_error($detail)) {
                continue;
            }

            $detail_body = json_decode(
                wp_remote_retrieve_body($detail),
                true
            );

            if (
                empty($detail_body['payload']['headers'])
            ) {
                continue;
            }

            $from = '';
            $subject = '';
            $date = '';

            foreach ($detail_body['payload']['headers'] as $header) {

                if (!isset($header['name'], $header['value'])) {
                    continue;
                }

                switch (strtolower($header['name'])) {

                    case 'from':
                        $from = $header['value'];
                        break;

                    case 'subject':
                        $subject = $header['value'];
                        break;

                    case 'date':
                        $date = $header['value'];
                        break;
                }
            }

            $messages[] = [
                'id' => $msg_id,
                'from' => $from,
                'subject' => $subject,
                'date' => $date,
            ];
        }
    }

    return [
        'status' => 'ok',
        'messages' => $messages,
    ];
}
