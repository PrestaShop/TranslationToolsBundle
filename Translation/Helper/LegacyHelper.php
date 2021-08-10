<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Helper;

class LegacyHelper
{
    /**
     * @param string $inputFilename NB: Remove the working directory (eg: controllers/foo|themes/bar)
     *
     * @return array|null
     */
    public static function getOutputInfo($inputFilename)
    {
        switch (1) {
            case preg_match('#controllers/admin#', $inputFilename):
            case preg_match('#override/controllers/admin#', $inputFilename):
            case preg_match('#classes/helper#', $inputFilename):
                return [
                    'file' => 'translations/[locale]/admin.php',
                    'var' => '_LANGADM',
                    'generateKey' => function ($string) use ($inputFilename) {
                        return self::getKeyPrefix($inputFilename) . self::getKey($string);
                    },
                ];

            case preg_match('#^themes/([A-Za-z0-9_]+).+\.tpl$#', $inputFilename, $matches):
            case preg_match('#^themes/([A-Za-z0-9_]+)(?!modules/)[a-zA-Z0-9/-]+(?!_ss\d|\d)\.tpl$#', $inputFilename, $matches):
                return [
                    'file' => 'themes/' . $matches[1] . '/lang/[locale].php',
                    'var' => '_LANG',
                    'generateKey' => function ($string) use ($inputFilename) {
                        return pathinfo(basename($inputFilename), PATHINFO_FILENAME) . '_' . self::getKey($string);
                    },
                ];

            // when we get a simple theme
            case preg_match('#themes/([A-Za-z0-9_-]+).+\.tpl$#', $inputFilename, $matches):
                return [
                    'file' => '/lang/[locale].php',
                    'var' => '_LANG',
                    'generateKey' => function ($string) use ($inputFilename) {
                        return pathinfo(basename($inputFilename), PATHINFO_FILENAME) . '_' . self::getKey($string);
                    },
                ];

            case preg_match('#override/classes/pdf/(?!index)\w+\.php$#', $inputFilename):
            case preg_match('#classes/pdf/(?!index)\w+\.php$#', $inputFilename):
                return [
                    'file' => 'translations/[locale]/pdf.php',
                    'var' => '_LANGPDF',
                    'generateKey' => function ($string) {
                        return 'PDF' . self::getKey($string);
                    },
                ];

            case preg_match('#(?:/|^)modules/([A-Za-z0-9_]+)/#', $inputFilename, $matches):
                return [
                    'file' => 'modules/' . $matches[1] . '/translations/[locale].php',
                    'var' => '_MODULE',
                    'generateKey' => function ($string) use ($inputFilename, $matches) {
                        return '<{' . $matches[1] . '}prestashop>' . pathinfo(basename($inputFilename), PATHINFO_FILENAME) . '_' . self::getKey($string);
                    },
                ];

            case preg_match('#^mails/#', $inputFilename):
                return [
                    'file' => 'mails/[locale]/lang.php',
                    'var' => '_LANGMAIL',
                    'generateKey' => function ($string) {
                        return $string;
                    },
                ];

            case preg_match('/fields_catalogue.php$/', $inputFilename):
                return [
                    'file' => 'translations/[locale]/fields.php',
                    'var' => '_FIELDS',
                    'generateKey' => function ($string, $domain) {
                        return $domain . '_' . md5($string);
                    },
                ];

            case preg_match('#controllers/admin/([A-Za-z]+)Controller.php$#', $inputFilename):
                return [
                    'file' => 'translations/[locale]/tabs.php',
                    'var' => '_TABS',
                    'generateKey' => function () use ($inputFilename) {
                        return self::getKeyPrefix($inputFilename);
                    },
                ];

//            case !preg_match('#tools/|cache/|\.tpl\.php$|[a-z]{2}\.php$#', $inputFilename) && preg_match('/\.php$/', $inputFilename):
//                return [
//                    'file' => 'translations/[locale]/errors.php',
//                    'var' => '_ERRORS',
//                    'generateKey' => function ($string) {
//                        return self::getKey($string);
//                    },
//                ];
        }
    }

    /**
     * @param string $string
     *
     * @return string
     */
    public static function getKey($string)
    {
        return md5(preg_replace("/\\\*'/", "\'", $string));
    }

    /**
     * @param string $file
     *
     * @return string|null
     */
    public static function getKeyPrefix($file)
    {
        $fileName = basename($file);

        switch (${false} = true) {
            case $fileName === 'AdminController.php':
                return 'AdminController';

            case $fileName === 'PaymentModule.php':
                return 'PaymentModule';

            case strpos($file, 'Helper') !== false:
                return 'Helper';

            case strpos($file, 'Controller.php') !== false:
                return basename(substr($file, 0, -14));
        }
    }
}
