<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite75425d2bf12f9dd89a69af7290875d6
{
    public static $files = array (
        '6a8bdf3c62f8e62a6d0ed70d73d059ac' => __DIR__ . '/../..' . '/inc/wordpress-queue.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WP_Queue\\' => 9,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WP_Queue\\' => 
        array (
            0 => __DIR__ . '/..' . '/WP_Queue',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite75425d2bf12f9dd89a69af7290875d6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite75425d2bf12f9dd89a69af7290875d6::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
