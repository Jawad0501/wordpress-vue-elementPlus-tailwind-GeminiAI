<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3ec9aaa182e9c7febe2801c6e81775d2
{
    public static $files = array (
        '2c42af8442971336441f58a7deaf2fd7' => __DIR__ . '/../..' . '/boot/globals.php',
    );

    public static $prefixLengthsPsr4 = array (
        'W' => 
        array (
            'WPFluent\\' => 9,
        ),
        'F' => 
        array (
            'FluentGemini\\Includes\\' => 22,
            'FluentGemini\\Framework\\' => 23,
            'FluentGemini\\App\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'WPFluent\\' => 
        array (
            0 => __DIR__ . '/..' . '/wpfluent/framework/src/WPFluent',
        ),
        'FluentGemini\\Includes\\' => 
        array (
            0 => __DIR__ . '/../..' . '/includes',
        ),
        'FluentGemini\\Framework\\' => 
        array (
            0 => __DIR__ . '/..' . '/wpfluent/framework/src/WPFluent',
        ),
        'FluentGemini\\App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'FluentGeminiDBMigrator' => __DIR__ . '/../..' . '/database/FluentGeminiDBMigrator.php',
        'FluentGeminiMigrations\\Setting' => __DIR__ . '/../..' . '/database/migrations/Setting.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3ec9aaa182e9c7febe2801c6e81775d2::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3ec9aaa182e9c7febe2801c6e81775d2::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3ec9aaa182e9c7febe2801c6e81775d2::$classMap;

        }, null, ClassLoader::class);
    }
}
