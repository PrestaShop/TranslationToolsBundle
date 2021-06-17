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
