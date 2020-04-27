<?php

//app class

use Wordpress_helpers\classes\WP_Singleton;

class WPH
{

    use WP_Singleton;
    static $plugin_dir;

    function init()
    {
        self::$plugin_dir = dirname(__DIR__);
    }

    function get_template($name)
    {
        $path = locate_template($name);
        return   $path ? $path : self::$plugin_dir . DIRECTORY_SEPARATOR . $name;
    }
}
