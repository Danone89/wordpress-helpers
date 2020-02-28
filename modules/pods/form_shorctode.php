<?php

namespace wordpress_helpers\modules\pods;



class Pods_Form_shortcode
{

    static $TAG = 'pix_form';
    /**
     * Register form shortcode with $tag name.
     *
     * @param string $tag
     * @return void
     */
    static function register($tag = 'pix_form')
    {
        self::$TAG = $tag;
        add_shortcode($tag, [__NAMESPACE__ . '\\Pods_Form_shortcode', 'pix_form_handler']);
    }

    static function pix_form_handler($args = [], $content = '')
    {

        $args = shortcode_atts(array(
            'fields' => '',
            'name' => '',
            'id_key' => 'pid',
            'label' => 'Zapisz',
            'redirect_url' => null,
        ), $args, self::$TAG);

        if ($args['name'] == '')
            return __('Błąd #15 w Pods Form Shortcode');
        $id = null;
        if (!empty($_GET[$args['id_key']])) {
            $id = intval($_GET[$args['id_key']]);
        }
        $pod = new \Pods($args['name'], $id);
        if (!$pod) return __('Błąd #21 w Pods Form Shortcode');

        $fields = $args['fields'] == '' ? $pod->fields() : explode(',', $args['fields']);
        return $pod->form($fields, $args['label'], $args['redirect_url']);
    }
}
