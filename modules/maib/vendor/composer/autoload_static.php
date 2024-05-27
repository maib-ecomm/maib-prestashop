<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit1fbecd2a8a1c79ea600181245e48f4e8
{
    public static $prefixLengthsPsr4 = array (
        'M' => 
        array (
            'MaibEcomm\\MaibSdk\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'MaibEcomm\\MaibSdk\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit1fbecd2a8a1c79ea600181245e48f4e8::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit1fbecd2a8a1c79ea600181245e48f4e8::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit1fbecd2a8a1c79ea600181245e48f4e8::$classMap;

        }, null, ClassLoader::class);
    }
}