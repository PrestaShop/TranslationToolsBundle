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
use PrestaShop\TranslationToolsBundle\Translation\Extractor\TwigExtractor;
use PrestaShop\TranslationToolsBundle\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\MessageCatalogue;
use Twig\Environment;
use Twig\Error\Error;
use Twig\Loader\FilesystemLoader;

class TwigExtractorTest extends TestCase
{
    /**
     * @throws Error
     */
    public function testExtractWithDomain(): void
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
     * @throws Error
     */
    private function buildMessageCatalogue(string $fixtureResource): MessageCatalogue
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->buildExtractor()->extract($this->getResource($fixtureResource), $messageCatalogue);

        return $messageCatalogue;
    }

    private function buildExtractor(): TwigExtractor
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
     */
    protected function getResource($resourceName): string
    {
        return parent::getResource('fixtures/twig/' . $resourceName);
    }
}
