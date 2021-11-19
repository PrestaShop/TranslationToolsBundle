<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor\Util;

use PHPUnit\Framework\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\Util\Flattenizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FlattenizerTest extends TestCase
{
    private static $fixturesPath;
    private static $fs;
    private static $outputPath;

    public static function setUpBeforeClass(): void
    {
        self::$fixturesPath = self::path('flattenizer/en-US');
        self::$fs = new Filesystem();
        self::$outputPath = self::path('flattenized-translations');
    }

    public static function tearDownAfterClass(): void
    {
        self::$fs->remove(self::$outputPath);
    }

    public function testFlatten(): void
    {
        $done = Flattenizer::flatten(
            self::$fixturesPath,
            self::$outputPath,
            'en-US'
        );

        $this->assertTrue($done);
        $isFilesExists = self::$fs->exists([
            self::$outputPath . '/ShopFooBar.en-US.xlf',
            self::$outputPath . '/ShopThemeActions.en-US.xlf',
            self::$outputPath . '/ShopThemeProduct.en-US.xlf',
            self::$outputPath . '/ShopThemeCart.en-US.xlf',
        ]);

        $this->assertTrue($isFilesExists);
    }

    public function testFlattenFiles(): void
    {
        $finder = new Finder();
        $output = self::path('flattenized-translations/en-US');
        $packageFileList = $finder->files()->in(self::path('flattenizer/download'));
        self::$fs->remove($output);
        self::$fs->mkdir($output);

        $done = Flattenizer::flattenFiles($packageFileList, $output, 'en-US', self::$fs, false);

        $this->assertTrue($done);

        $isFilesExists = self::$fs->exists([
            $output . '/Emails.en-US.xlf',
            $output . '/Install.en-US.xlf',
            $output . '/messages.en-US.xlf',
            $output . '/AdminCatalogFeature.en-US.xlf',
            $output . '/AdminActions.en-US.xlf',
        ]);

        $this->assertTrue($isFilesExists);
    }

    private static function path(string $resourceName): string
    {
        return __DIR__ . '/../../../resources/' . $resourceName;
    }
}
