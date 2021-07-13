<?php
/**
 * 2007-2018 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Smarty;
use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Compiler\Smarty\TranslationTemplateCompiler;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\SmartyExtractor;
use PrestaShop\TranslationToolsBundle\Translation\Extractor\TwigExtractor;
use PrestaShop\TranslationToolsBundle\Translation\Helper\Smarty\SmartyResourceModule;
use PrestaShop\TranslationToolsBundle\Translation\Helper\Smarty\SmartyResourceParent;
use PrestaShop\TranslationToolsBundle\Twig\NodeVisitor\TranslationNodeVisitor;
use Smarty_Internal_Templatelexer;
use Smarty_Internal_Templateparser;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
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
            'cache' => __DIR__ . '/../../cache/twig'
        ]);
        $twig->addExtension(new TranslationExtension(null, new TranslationNodeVisitor()));

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
