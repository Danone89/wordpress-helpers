<?php

namespace Wordpress_helpers\classes\pods;

use PodsView;
use WPH;

class Pods_Helper
{

    protected $fields = [
        'pick',
        'currency',
        'paragraph',
        'phone',
        'text',
        'date',
        'datetime',
    ];

    function register_form_shortcode($tag)
    {
        add_shortcode($tag, function ($args = [], $content = '') use ($tag) {
            $args = shortcode_atts(array(
                'fields' => '',
                'name' => '',
                'id_key' => 'pid',
                'label' => 'Zapisz',
                'redirect_url' => null,
            ), $args, $tag);

            if ($args['name'] == '')
                return 'Błąd #15 w Pods Form Shortcode';
            $id = null;
            if (!empty($_GET[$args['id_key']])) {
                $id = intval($_GET[$args['id_key']]);
            }
            $pod = new \Pods($args['name'], $id);
            if (!$pod) return 'Błąd #21 w Pods Form Shortcode';

            $fields = $args['fields'] == '' ? $pod->fields() : explode(',', $args['fields']);
            return $pod->form($fields, $args['label'], $args['redirect_url']);
        });
    }

    static function forms_add_fields_class($class)
    {
        static $classes = [];

        if ($classes == []) {
            $register_function = function ($classes, $field) {
                add_filter("pods_form_ui_field_{$field}_options", function ($options) use ($classes) {
                    $options['class'] = implode(' ', $classes);
                    return $options;
                });
            };
            //@TODO later hook neede
            add_action('wp', function () use ($classes, $register_function) {
                foreach (self::$fields as $field) {
                    $register_function($classes, $field);
                }
            });
        }
        if (!in_array($class, $classes))
            $classes[] = $class;
    }
    static function forms_add_btn_class($class)
    {
        add_filter("pods_form_ui_field_submit_options", function ($options) use ($class) {
            $options['class'] = $class;
            return $options;
        });
    }

    static  function forms_adopt_bootstrap()
    {
        self::forms_add_fields_class('form-control');
        self::forms_add_btn_class('btn btn-primary');
        self::register_form_view_loader();
    }

    static function register_form_view_loader()
    {
        static $registred = false;
        add_filter('pods_view_alt_view', function ($view, $org_view, $data, $expires, $cache_mode) {
            if ($org_view == PODS_DIR . 'ui/front/form.php') {


                $view = PodsView::view(
                    WPH::get_template('pods/form'),
                    $data,
                    $expires,
                    $cache_mode
                );
                return $view;
            }
        }, 10, 5);
        $registred = true;
    }
}
