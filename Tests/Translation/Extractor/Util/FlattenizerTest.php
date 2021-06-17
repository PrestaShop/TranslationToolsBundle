<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
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
