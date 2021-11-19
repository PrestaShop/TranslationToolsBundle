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

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\PhpExtractor;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @todo: Mock MessageCatalogue
 */
class PhpExtractorTest extends TestCase
{
    /**
     * @var PhpExtractor
     */
    protected $phpExtractor;

    public function setUp(): void
    {
        $this->phpExtractor = new PhpExtractor();
    }

    public function testItExtractsTransMethodWithSymfonyStyleParameters(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('Fingers', 'Admin.Product.Help'));
    }

    public function testItExtractsTransMethodWithPrestashopStyleParameters(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('This is how PrestaShop does it', 'Admin.Product.Help'));
    }

    public function testItWorksWithMultiLineTrans(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('This is how symfony does it', 'Admin.Product.Help'));
    }

    public function testItExtractsTransWithoutDomain(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('Look, no domain', 'messages'));
        $this->assertTrue($messageCatalogue->defines('It works with no domain and with parameters', 'messages'));
    }

    public function testItInterpolatesDomainVariables(): void
    {
        $this->markTestIncomplete("The extractor doesn't know how to interpolate variables yet");

        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('Bar', 'admin.product.plop'));
    }

    public function testItExtractsArrays(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertTrue($messageCatalogue->defines('This text is lonely', 'Admin.Notifications.Error'));
        $this->assertTrue($messageCatalogue->defines('This text has a sibling', 'Admin.Superserious.Messages'));
        $this->assertTrue($messageCatalogue->defines("I ain't need no parameter", 'Like.A.Gangsta'));
        $this->assertTrue($messageCatalogue->defines('Parameters work in any order', 'Admin.Notifications.Error'));
        $this->assertTrue($messageCatalogue->defines('This text is coming back somewhere', 'Admin.Notifications.Error'));
    }

    public function testItDoesNotExtractArraysWhenItShouldNot(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestController.php');

        $this->assertFalse($messageCatalogue->defines('No domain, no gain'));
        $this->assertFalse($messageCatalogue->defines('No domain, no gain, even with parameters'));
        $this->assertNotContains('No.Key.WTF', $messageCatalogue->all());
        $this->assertNotContains('Parameters.Wont.Help.Here', $messageCatalogue->all());
        $this->assertFalse($messageCatalogue->defines("I'm with foo, which spoils any party", 'Admin.Notifications.Error'));
        $this->assertFalse($messageCatalogue->defines("I'm with foo, which spoils any party, even with parameters", 'Admin.Notifications.Error'));
    }

    public function testExtractWithoutNamespace(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestWithoutNamespaceController.php');

        $this->assertArrayHasKey('Shop', $messageCatalogue->all('messages'));
    }

    public function testExtractLegacyController(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/LegacyController.php');

        $this->assertTrue($messageCatalogue->has('Successful deletion'));
        $this->assertArrayHasKey('Prestashop', $messageCatalogue->all('Domain'));
    }

    public function testExtractEmails(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('fixtures/TestExtractEmails.php');

        $this->assertTrue($messageCatalogue->defines('Always keep your account details safe.', 'Emails.Body'));
    }

    /**
     * @dataProvider provideFormTranslationFixtures
     */
    public function testExtractFormTranslations(string $file, array $expected): void
    {
        $messageCatalogue = $this->buildMessageCatalogue($file);

        $this->verifyCatalogue($messageCatalogue, $expected);
    }

    public function provideFormTranslationFixtures(): array
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
                    ],
                ],
            ],
            'TestFormChoicesTranslatorAware.php' => [
                'file' => 'fixtures/TestFormChoicesTranslatorAware.php',
                'expected' => [
                    'Install' => [
                        'Some %foo% wording',
                        '-- Please choose your main activity --',
                        'Animals and Pets',
                        'Art and Culture',
                        'Babies',
                        'Beauty and Personal Care',
                        'Cars',
                        'Computer Hardware and Software',
                        'Download',
                        'Fashion and accessories',
                        'Flowers, Gifts and Crafts',
                        'Food and beverage',
                        'HiFi, Photo and Video',
                        'Home and Garden',
                        'Home Appliances',
                        'Jewelry',
                        'Lingerie and Adult',
                        'Mobile and Telecom',
                        'Services',
                        'Shoes and accessories',
                        'Sport and Entertainment',
                        'Travel',
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
                    ],
                ],
            ],
        ];
    }

    public function testExtractFromDirectoryWithoutExclusion(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('directory/');

        $catalogue = $messageCatalogue->all();
        $this->assertCount(2, array_keys($catalogue));
        $this->assertCount(3, $catalogue['messages']);
        $this->assertCount(2, $catalogue['Admin.Product.Help']);

        $this->verifyCatalogue($messageCatalogue, [
            'messages' => [
                'SecondSubdirShop' => 'SecondSubdirShop',
                'SubdirShop' => 'SubdirShop',
                'Shop' => 'Shop',
            ],
            'Admin.Product.Help' => [
                'SubdirFingers' => 'SubdirFingers',
                'Fingers' => 'Fingers',
            ],
        ]);
    }

    public function testExtractFromDirectoryExcludingSubDirectories(): void
    {
        // Exclude one directory
        $messageCatalogue = new MessageCatalogue('en');
        $this->phpExtractor
            ->setExcludedDirectories(['subdirectory'])
            ->extract($this->getResource('directory/'), $messageCatalogue);

        $catalogue = $messageCatalogue->all();
        $this->assertCount(2, array_keys($catalogue));
        $this->assertCount(2, $catalogue['messages']);
        $this->assertCount(1, $catalogue['Admin.Product.Help']);

        $this->verifyCatalogue($messageCatalogue, [
            'messages' => [
                'SecondSubdirShop' => 'SecondSubdirShop',
                'Shop' => 'Shop',
            ],
            'Admin.Product.Help' => [
                'Fingers' => 'Fingers',
            ],
        ]);

        // Exclude multiple directories
        $messageCatalogue = new MessageCatalogue('en');
        $this->phpExtractor
            ->setExcludedDirectories(['subdirectory', 'subdirectory2'])
            ->extract($this->getResource('directory/'), $messageCatalogue);

        $catalogue = $messageCatalogue->all();
        $this->assertCount(2, array_keys($catalogue));
        $this->assertCount(1, $catalogue['messages']);
        $this->assertCount(1, $catalogue['Admin.Product.Help']);

        $this->verifyCatalogue($messageCatalogue, [
            'messages' => [
                'Shop' => 'Shop',
            ],
            'Admin.Product.Help' => [
                'Fingers' => 'Fingers',
            ],
        ]);
    }

    private function buildMessageCatalogue(string $fixtureResource): MessageCatalogue
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->phpExtractor->extract($this->getResource($fixtureResource), $messageCatalogue);

        return $messageCatalogue;
    }
}
