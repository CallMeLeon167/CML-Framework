<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit2ffbef45478db9fa13b3e24b60fa3162
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Routing\\' => 8,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Routing\\' => 
        array (
            0 => __DIR__ . '/../..' . '/classes',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit2ffbef45478db9fa13b3e24b60fa3162::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit2ffbef45478db9fa13b3e24b60fa3162::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit2ffbef45478db9fa13b3e24b60fa3162::$classMap;

        }, null, ClassLoader::class);
    }
}
