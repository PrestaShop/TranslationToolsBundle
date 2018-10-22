<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Translation\Extractor\PhpExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;

/**
 * @todo: Mock MessageCatalogue
 */
class PhpExtractorTest extends TestCase
{
    /**
     * @var PhpExtractor
     */
    protected $phpExtractor;

    public function setUp()
    {
        $this->phpExtractor = new PhpExtractor();
    }

    public function testItExtractsTransMethodWithSymfonyStyleParameters()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('Fingers', 'admin.product.help'));
    }

    public function testItExtractsTransMethodWithPrestashopStyleParameters()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('This is how PrestaShop does it', 'admin.product.help'));
    }

    public function testItWorksWithMultiLineTrans()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('This is how symfony does it', 'admin.product.help'));
    }

    public function testItInterpolatesDomainVariables()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->markTestIncomplete("The extractor doesn't know how to interpolate variables yet");

        $this->assertTrue($messageCatalogue->defines('Bar', 'admin.product.plop'));
    }

    public function testItExtractsArrays()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('This text is lonely', 'Admin.Notifications.Error'));
        $this->assertTrue($messageCatalogue->defines('This text has a sibling', 'Admin.Superserious.Messages'));
        $this->assertTrue($messageCatalogue->defines("I ain't need no parameter", 'Like.A.Gangsta'));
        $this->assertTrue($messageCatalogue->defines('Parameters work in any order', 'Admin.Notifications.Error'));
        $this->assertTrue($messageCatalogue->defines('This text is coming back somewhere', 'Admin.Notifications.Error'));
    }

    public function testItDoesNotExtractArraysWhenItShouldNot()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertFalse($messageCatalogue->defines('No domain, no gain'));
        $this->assertFalse($messageCatalogue->defines('No domain, no gain, even with parameters'));
        $this->assertNotContains('No.Key.WTF', $messageCatalogue->all());
        $this->assertNotContains('Parameters.Wont.Help.Here', $messageCatalogue->all());
        $this->assertFalse($messageCatalogue->defines("I'm with foo, which spoils any party", 'Admin.Notifications.Error'));
        $this->assertFalse($messageCatalogue->defines("I'm with foo, which spoils any party, even with parameters", 'Admin.Notifications.Error'));
    }

    public function testExtractWithoutNamespace()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestWithoutNamespaceController.php');

        $this->assertArrayHasKey('Shop', $messageCatalogue->all('messages'));
    }

    public function testExtractLegacyController()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/LegacyController.php');

        $this->assertTrue($messageCatalogue->has('Successful deletion'));
        $this->assertArrayHasKey('Prestashop', $messageCatalogue->all('Domain'));
    }

    public function testExtractEmails()
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestExtractEmails.php');

        $this->assertTrue($messageCatalogue->defines('Always keep your account details safe.', 'Emails.Body'));
    }

    /**
     * @param $file
     * @param $expected
     *
     * @dataProvider provideFormTranslationFixtures
     */
    public function testExtractFormTranslations($file, $expected)
    {
        $messageCatalogue = $this->buildMessageCatalogue($file);

        $this->verifyCatalogue($messageCatalogue, $expected);
    }

    public function provideFormTranslationFixtures()
    {
        return [
            'TestFormChoices.php' => [
                'file' => 'fixtures/TestFormChoices.php',
                'expected' => [
                    'Admin.Global' => [
                        'Ascending',
                        'Descending',
                    ],
                    'Admin.Shopparameters.Feature' => [
                        'Product name',
                        'Product price',
                        'Product add date',
                        'Product modified date',
                        'Position inside category',
                        'Brand',
                        'Product quantity',
                        'Product reference',
                    ]
                ]
            ],
            'TestFormChoicesTranslatorAware.php' => [
                'file' => 'fixtures/TestFormChoicesTranslatorAware.php',
                'expected' => [
                    'Install' => [
                        "Animals and Pets",
                        "Art and Culture",
                        "Babies",
                        "Beauty and Personal Care",
                        "Cars",
                        "Computer Hardware and Software",
                        "Download",
                        "Fashion and accessories",
                        "Flowers, Gifts and Crafts",
                        "Food and beverage",
                        "HiFi, Photo and Video",
                        "Home and Garden",
                        "Home Appliances",
                        "Jewelry",
                        "Lingerie and Adult",
                        "Mobile and Telecom",
                        "Services",
                        "Shoes and accessories",
                        "Sport and Entertainment",
                        "Travel",
                    ],
                    'Admin.Shopparameters.Feature' => [
                        'Round up away from zero, when it is half way there (recommended)',
                        'Round down towards zero, when it is half way there',
                        'Round towards the next even value',
                        'Round towards the next odd value',
                        'Round up to the nearest value',
                        'Round down to the nearest value',
                        'Round on each item',
                        'Round on each line',
                        'Round on the total',
                    ]
                ]
            ],
        ];
    }

    /**
     * @param $fixtureResource
     *
     * @return MessageCatalogue
     */
    private function buildMessageCatalogue($fixtureResource)
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->phpExtractor->extract($this->getResource($fixtureResource), $messageCatalogue);

        return $messageCatalogue;
    }

}
