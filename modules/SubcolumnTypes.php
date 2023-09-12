<?php

namespace HeimrichHannot\Subcolumns;

use HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle;

class SubcolumnTypes
{
    private static string $strSet;

    public static function compatSetType(string $default = 'yaml3'): string
    {
        if (isset(self::$strSet)) {
            return self::$strSet;
        }

        self::$strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ?? $default;

        if (class_exists(SubColumnsBootstrapBundle::class)) {
            self::$strSet = SubColumnsBootstrapBundle::validateTypeString(self::$strSet) ?? self::$strSet;
        }

        return self::$strSet;
    }

}
