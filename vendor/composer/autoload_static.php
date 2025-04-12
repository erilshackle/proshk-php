<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf250ae673a3e55c0292bf0742228c260
{
    public static $prefixLengthsPsr4 = array (
        'A' => 
        array (
            'App\\' => 4,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'App\\' => 
        array (
            0 => __DIR__ . '/../..' . '/app',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf250ae673a3e55c0292bf0742228c260::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf250ae673a3e55c0292bf0742228c260::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf250ae673a3e55c0292bf0742228c260::$classMap;

        }, null, ClassLoader::class);
    }
}
