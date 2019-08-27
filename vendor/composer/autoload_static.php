<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit9a47d0f1abf7fba0b3144d81845b81f6
{
    public static $files = array (
        'da253f61703e9c22a5a34f228526f05a' => __DIR__ . '/..' . '/wixel/gump/gump.class.php',
    );

    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
            'PHPMailer\\PHPMailer\\' => 20,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
            'Moment\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Moment\\' => 
        array (
            0 => __DIR__ . '/..' . '/fightbulc/moment/src',
        ),
    );

    public static $classMap = array (
        'Config' => __DIR__ . '/../..' . '/config/Config.php',
        'Database' => __DIR__ . '/../..' . '/libs/Database.php',
        'DatabaseAccessObject' => __DIR__ . '/../..' . '/libs/DatabaseAccessObject.php',
        'Mail' => __DIR__ . '/../..' . '/libs/Mail.php',
        'MySQL' => __DIR__ . '/../..' . '/config/MySQL.php',
        'Request' => __DIR__ . '/../..' . '/libs/Request.php',
        'Router' => __DIR__ . '/../..' . '/libs/Router.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit9a47d0f1abf7fba0b3144d81845b81f6::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit9a47d0f1abf7fba0b3144d81845b81f6::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit9a47d0f1abf7fba0b3144d81845b81f6::$classMap;

        }, null, ClassLoader::class);
    }
}
