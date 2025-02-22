<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit028d0a1d764f47e2b338ae0c53e0753e
{
    public static $prefixLengthsPsr4 = array (
        'd' => 
        array (
            'diogodg\\neoorm\\' => 15,
        ),
        'c' => 
        array (
            'core\\' => 5,
        ),
        'a' => 
        array (
            'app\\' => 4,
        ),
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
        'P' => 
        array (
            'PHPMailer\\PHPMailer\\' => 20,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'diogodg\\neoorm\\' => 
        array (
            0 => __DIR__ . '/..' . '/diogodg/neoorm/src',
        ),
        'core\\' => 
        array (
            0 => __DIR__ . '/../..' . '/core',
        ),
        'app\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
        'PHPMailer\\PHPMailer\\' => 
        array (
            0 => __DIR__ . '/..' . '/phpmailer/phpmailer/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit028d0a1d764f47e2b338ae0c53e0753e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit028d0a1d764f47e2b338ae0c53e0753e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit028d0a1d764f47e2b338ae0c53e0753e::$classMap;

        }, null, ClassLoader::class);
    }
}
