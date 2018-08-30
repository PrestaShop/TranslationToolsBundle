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
