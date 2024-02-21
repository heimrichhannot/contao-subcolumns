<?php

namespace HeimrichHannot\Subcolumns;

use Contao\Config;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class SubcolumnTypes
{
    const DEFAULT_TYPE = 'yaml3';
    protected static string $strSet;

    public static function compatSetType(string $default = self::DEFAULT_TYPE): string
    {
        if (class_exists(SubColumnsBootstrapBundle::class)) {
            return SubColumnsBootstrapBundle::getProfile();
        }

        if (isset(static::$strSet)) {
            return static::$strSet;
        }

        static::$strSet = Config::get('subcolumns') ?: $default;

        return static::$strSet;
    }

}
