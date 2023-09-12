<?php

namespace HeimrichHannot\Subcolumns;

class SubcolumnTypes
{
    private static $strSet = null;

    public static function compatSetType($default = 'yaml3') {
        if (is_string(self::$strSet)) return self::$strSet;

        $strSet = $GLOBALS['TL_CONFIG']['subcolumns'] ?? $default;

        if (class_exists('\HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle')) {
            $strSet = \HeimrichHannot\SubColumnsBootstrapBundle\SubColumnsBootstrapBundle::validateTypeString($strSet) ?? $strSet;
        }

        return $strSet;
    }

}