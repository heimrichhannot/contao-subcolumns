<?php

namespace HeimrichHannot\Subcolumns;

use Contao\Config;
use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class SubcolumnTypes
{
    protected static string $strSet;

    public static function compatSetType(string $default = 'yaml3'): string
    {
        if (class_exists(SubColumnsBootstrapBundle::class)) {
            return SubColumnsBootstrapBundle::getSubType();
        }

        if (isset(static::$strSet)) {
            return static::$strSet;
        }

        static::$strSet = Config::get('subcolumns') ?: $default;

        return static::$strSet;
    }

}
