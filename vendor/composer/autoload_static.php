<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf08e0220862e48d4953d23cc76ef6ec7
{
    public static $prefixLengthsPsr4 = array (
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'K' => 
        array (
            'Katzgrau\\KLogger\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Katzgrau\\KLogger\\' => 
        array (
            0 => __DIR__ . '/..' . '/katzgrau/klogger/src',
        ),
    );

    public static $classMap = array (
        'Katzgrau\\KLogger\\Logger' => __DIR__ . '/..' . '/katzgrau/klogger/src/Logger.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf08e0220862e48d4953d23cc76ef6ec7::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf08e0220862e48d4953d23cc76ef6ec7::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf08e0220862e48d4953d23cc76ef6ec7::$classMap;

        }, null, ClassLoader::class);
    }
}
