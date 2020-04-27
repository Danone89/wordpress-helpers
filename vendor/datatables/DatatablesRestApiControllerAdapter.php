<?php


abstract class DatatablesRestApiControllerAdapter
{

    var $namespace = '';

    function register_table_routes($prefix)
    {
        register_rest_route($this->namespace, '/def/wydr/', array(
            'methods' => \WP_REST_Server::READABLE,
            'callback' => array($this, 'get_folder'),
            'permission_callback' => array($this, 'get_items_permissions_check'),
            'args' =>
            [
                'symbol' => ['default' => '*.*'],
                'search' => [
                    'default' => 73,
                    'sanitize_callback' => function ($param, $request, $key) {
                        return intval($param);
                    }
                ],
                'limit' => [
                    'default' => 100,
                    'sanitize_callback' => function ($param, $request, $key) {
                        return intval($param);
                    }
                ],
            ],
        ));
    }
}
