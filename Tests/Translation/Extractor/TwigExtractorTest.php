<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\TwigExtractor;
use PrestaShop\TranslationToolsBundle\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TwigExtractorTest extends TestCase
{
    public function testExtractWithDomain()
    {
        $messageCatalogue = $this->buildMessageCatalogue('payment_return.html.twig');

        $expected = [
            'Modules.Wirepayment.Shop' => [
                'Your order on %s is complete.',
                'Please send us a bank wire with:',
                'Please specify your order reference %s in the bankwire description.',
                'We\'ve also sent you this information by e-mail.',
                'Your order will be sent as soon as we receive payment.',
                'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].',
                'We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].',
            ],
        ];

        $this->verifyCatalogue($messageCatalogue, $expected);
    }

    /**
     * @param $fixtureResource
     *
     * @return MessageCatalogue
     */
    private function buildMessageCatalogue($fixtureResource)
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->buildExtractor()->extract($this->getResource($fixtureResource), $messageCatalogue);

        return $messageCatalogue;
    }

    /**
     * @return TwigExtractor
     */
    private function buildExtractor()
    {
        $loader = new FilesystemLoader(parent::getResource('fixtures/twig'));
        $twig = new Environment($loader, [
            'cache' => __DIR__ . '/../../cache/twig',
        ]);
        $twig->addExtension(new TranslationExtension());

        return new TwigExtractor($twig);
    }

    /**
     * @param string $resourceName
     *
     * @return string
     */
    protected function getResource($resourceName)
    {
        return parent::getResource('fixtures/twig/' . $resourceName);
    }
}
