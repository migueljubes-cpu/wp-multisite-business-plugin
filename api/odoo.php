<?php
if (! defined('ABSPATH')) exit;

/**
 * api/odoo.php
 * Cliente JSON-RPC para Odoo 19 Online
 */

class Punksetter_Odoo_Client
{

    private $url;
    private $db;
    private $user;
    private $api_key;
    private $uid;

    public function __construct()
    {

        // Ajusta aquí tus settings Odoo
        $this->url     = 'https://XXXXXX.odoo.com';
        $this->db      = 'XXXXXXXXXX';
        $this->user    = 'XXXXXXXXXXXXX';

        // *** API KEY NUEVA ***
        $this->api_key = 'XXXXXXXXXXXXXXX';

        if (empty($this->url) || empty($this->db) || empty($this->user) || empty($this->api_key)) {
            $this->uid = null;
        }
    }

    /**
     * Autenticar y obtener UID
     */
    public function authenticate()
    {

        if (empty($this->url) || empty($this->db) || empty($this->user) || empty($this->api_key)) {
            return new WP_Error('odoo_settings_incomplete', 'Odoo settings incompletos (url/db/user/api_key).');
        }

        if ($this->uid) {
            return $this->uid;
        }

        $payload = [
            'jsonrpc' => '2.0',
            'method'  => 'call',
            'params'  => [
                'service' => 'common',
                'method'  => 'authenticate',
                'args'    => [
                    $this->db,
                    $this->user,
                    $this->api_key,
                    []
                ],
            ],
            'id' => time(),
        ];

        $response = wp_remote_post($this->url . '/jsonrpc', [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($payload),
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('odoo_http_error', 'Error HTTP al autenticar: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('odoo_auth_error', 'Error Odoo authenticate: ' . wp_json_encode($data['error']));
        }

        if (! isset($data['result']) || ! is_int($data['result'])) {
            return new WP_Error('odoo_auth_invalid', 'Respuesta de authenticate inválida.');
        }

        $this->uid = $data['result'];
        return $this->uid;
    }

    /**
     * Llamada genérica a object.execute_kw
     */
    private function call_execute_kw($model, $method, $args = [], $kwargs = [])
    {

        $uid = $this->authenticate();
        if (is_wp_error($uid)) {
            return $uid;
        }

        $payload = [
            'jsonrpc' => '2.0',
            'method'  => 'call',
            'params'  => [
                'service' => 'object',
                'method'  => 'execute_kw',
                'args'    => [
                    $this->db,
                    $uid,
                    $this->api_key,
                    $model,
                    $method,
                    $args,
                    $kwargs,
                ],
            ],
            'id' => time(),
        ];

        $response = wp_remote_post($this->url . '/jsonrpc', [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => wp_json_encode($payload),
            'timeout' => 30,
        ]);

        if (is_wp_error($response)) {
            return new WP_Error('odoo_http_error', 'Error HTTP en execute_kw: ' . $response->get_error_message());
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (isset($data['error'])) {
            return new WP_Error('odoo_execute_error', 'Error Odoo execute_kw: ' . wp_json_encode($data['error']));
        }

        if (! isset($data['result'])) {
            return new WP_Error('odoo_execute_invalid', 'Respuesta execute_kw inválida.');
        }

        return $data['result'];
    }

    /**
     * search_read simple
     */
    public function search_read($model, $domain = [], $fields = [], $limit = 50)
    {

        $kwargs = [
            'fields' => $fields,
            'limit'  => $limit,
        ];

        return $this->call_execute_kw($model, 'search_read', [$domain], $kwargs);
    }

    /**
     * Método genérico para search
     */
    public function search($model, $domain = [], $limit = 100)
    {
        return $this->call_execute_kw($model, 'search', [$domain, 0, $limit]);
    }

    /**
     * Método genérico para read
     */
    public function read($model, $ids = [], $fields = [])
    {
        return $this->call_execute_kw($model, 'read', [$ids], ['fields' => $fields]);
    }
}

/**
 * Instancia global
 */
global $punksetter_odoo;
if (! isset($punksetter_odoo)) {
    $punksetter_odoo = new Punksetter_Odoo_Client();
}
