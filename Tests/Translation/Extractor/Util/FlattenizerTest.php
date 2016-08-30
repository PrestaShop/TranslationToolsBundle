<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Translation\Extractor\Util\Flattenizer;

use Symfony\Component\Filesystem\Filesystem;

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
    
    private static function path($resourceName)
    {
        return __DIR__.'/../../../resources/'.$resourceName;
    }
}
