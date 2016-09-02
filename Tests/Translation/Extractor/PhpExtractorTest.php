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
    protected $phpExtractor;

    public function setUp()
    {
        $this->phpExtractor = new PhpExtractor();
    }

    public function testExtractDomain()
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->phpExtractor->extract($this->getResource('fixtures/TestController.php'), $messageCatalogue);

        $this->assertEquals('Fingers', $messageCatalogue->get('Fingers', 'admin.product.help'));
    }

    public function testExtractWithoutNamespace()
    {
        $messageCatalogue = new MessageCatalogue('en');

        $this->phpExtractor->extract($this->getResource('fixtures/TestWithoutNamespaceController.php'), $messageCatalogue);

        $this->assertArrayHasKey('Shop', $messageCatalogue->all('messages'));
    }

    public function testExtractLegacyController()
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->phpExtractor->extract($this->getResource('fixtures/LegacyController.php'), $messageCatalogue);

        $this->assertTrue($messageCatalogue->has('Successful deletion'));
        $this->assertArrayHasKey('Prestashop', $messageCatalogue->all('Domain'));
    }

    public function testExtractEmails()
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->phpExtractor->extract($this->getResource('fixtures/TestExtractEmails.php'), $messageCatalogue);

        $this->assertTrue($messageCatalogue->has('Always keep your account details safe.', 'Emails'));
    }
}
