<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Translation\Extractor\Util\Flattenizer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class FlattenizerTest extends \PHPUnit_Framework_TestCase
{
    private static $fixturesPath;
    private static $fs;
    private static $outputPath;

    public static function setUpBeforeClass()
    {
        self::$fixturesPath = self::path('flattenizer/en-US');
        self::$fs = new Filesystem();
        self::$outputPath = self::path('flattenized-translations');
    }

    public static function tearDownAfterClass()
    {
        self::$fs->remove(self::$outputPath);
    }

    public function testFlatten()
    {
        $done = Flattenizer::flatten(
            self::$fixturesPath,
            self::$outputPath,'en-US'
        );

        $this->assertTrue($done);
        $isFilesExists = self::$fs->exists(array(
            self::$outputPath.'/ShopFooBar.en-US.xlf',
            self::$outputPath.'/ShopThemeActions.en-US.xlf',
            self::$outputPath.'/ShopThemeProduct.en-US.xlf',
            self::$outputPath.'/ShopThemeCart.en-US.xlf',
        ));

        $this->assertTrue($isFilesExists);
    }

    public function testFlattenFiles()
    {
        $finder = new Finder();
        $output = self::path('flattenized-translations/en-US');
        $packageFileList = $finder->files()->in(self::path('flattenizer/download'));
        self::$fs->remove($output);
        self::$fs->mkdir($output);

        $done = Flattenizer::flattenFiles($packageFileList, $output, 'en-US', self::$fs, false);

        $this->assertTrue($done);

        $isFilesExists = self::$fs->exists(array(
            $output.'/Emails.en-US.xlf',
            $output.'/Install.en-US.xlf',
            $output.'/messages.en-US.xlf',
            $output.'/AdminCatalogFeature.en-US.xlf',
            $output.'/AdminActions.en-US.xlf',
        ));

        $this->assertTrue($isFilesExists);
    }

    private static function path($resourceName)
    {
        return __DIR__.'/../../../resources/'.$resourceName;
    }
}
