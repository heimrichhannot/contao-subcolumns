<?php

namespace FelixPfeiffer\Subcolumns;

use Contao\System;

class Polyfill
{
    /**
     * @return array
     * @deprecated This is a polyfill of Contao 4's {@see \Contao\ModuleLoader::getActive()} method for Contao 5.
     */
    public static function legacyPolyfill_getActiveModules(): array
    {
        $bundles = array_keys(System::getContainer()->getParameter('kernel.bundles'));

        $legacy = [
            'ContaoCoreBundle'       => 'core',
            'ContaoCalendarBundle'   => 'calendar',
            'ContaoCommentsBundle'   => 'comments',
            'ContaoFaqBundle'        => 'faq',
            'ContaoListingBundle'    => 'listing',
            'ContaoNewsBundle'       => 'news',
            'ContaoNewsletterBundle' => 'newsletter'
        ];

        foreach ($legacy as $bundleName => $module)
        {
            if (in_array($bundleName, $bundles))
            {
                $bundles[] = $module;
            }
        }

        return $bundles;
    }
}
