<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\Translation\Extractor\Util;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

/**
 * This class have only one thing to do, transform:
 *
 * en-US
 *   ├── Admin
 *   │   └── Catalog
 *   │        ├── Feature.xlf
 *   │        ├── Help.xlf
 *   │        └── Notification.xlf
 *   └── Shop
 *        └── PDF.xlf
 *
 *
 * Into:
 *
 * ├── AdminCatalogFeature.en-US.xlf
 * ├── AdminCatalogHelp.en-US.xlf
 * ├── AdminCatalogNotification.en-US.xlf
 * └── ShopPDF.en-US.xlf
 */
class Flattenizer
{
    public static $finder = null;

    public static $filesystem = null;

    /**
     * @input string $inputPath Path of directory to flattenize
     * @input string $outputPath Location of flattenized files newly created
     * @input string $locale Selected locale for theses files.
     * @input boolean $cleanPath Clean input path after flatten.
     */
    public static function flatten($inputPath, $outputPath, $locale, $cleanPath = true)
    {
        $finder = self::$finder;
        $filesystem = self::$filesystem;

        if (is_null(self::$finder)) {
            $finder = new Finder();
        }

        if (is_null(self::$filesystem)) {
            $filesystem = new Filesystem();
        }

        if ($cleanPath) {
            $filesystem->remove($outputPath);
            $filesystem->mkdir($outputPath);
        }

        return self::flattenFiles($finder->in($inputPath)->files(), $outputPath, $locale, $filesystem);
    }

    /**
     * @param SplFileInfo $files List of files to flattenize
     * @param string $outputPath Location of flattenized files newly created
     * @param string $locale Selected locale for theses files
     * @param Filesystem $filesystem Instance of Filesystem
     * @param bool $addLocale Should add the locale to filename
     *
     * @return bool
     */
    public static function flattenFiles($files, $outputPath, $locale, $filesystem, $addLocale = true)
    {
        foreach ($files as $file) {
            $flatName = preg_replace('#[\/\\\]#', '', $file->getRelativePath()) . $file->getFilename();

            if ($addLocale) {
                $flatName = preg_replace('#\.xlf#', '.' . $locale . '.xlf', $flatName);
            }

            $filesystem->copy($file->getRealpath(), $outputPath . '/' . $flatName);
        }

        return true;
    }
}
