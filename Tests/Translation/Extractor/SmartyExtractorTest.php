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
use PrestaShop\TranslationToolsBundle\Translation\Helper\Smarty\SmartyResourceModule;
use PrestaShop\TranslationToolsBundle\Translation\Helper\Smarty\SmartyResourceParent;
use Smarty_Internal_Templatelexer;
use Smarty_Internal_Templateparser;
use Symfony\Component\Translation\MessageCatalogue;

class SmartyExtractorTest extends TestCase
{
    public function testExtractWithDomain()
    {
        $messageCatalogue = $this->buildMessageCatalogue('payment_return.tpl');

        $expected = [
            'Modules.Wirepayment.Shop' => [
                'Your order on %s is complete.',
                'Please send us a bank wire with:',
                'Please specify your order reference %s in the bankwire description.',
                'We\'ve also sent you this information by e-mail.',
                'Your order will be sent as soon as we receive payment.',
                'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].',
                'We noticed a problem with your order. If you think this is an error, feel free to contact our [1]expert customer support team[/1].',
            ]
        ];

        $this->verifyCatalogue($messageCatalogue, $expected);
    }

    public function testExtractWithoutExternalModules()
    {
        $messageCatalogue = $this->buildMessageCatalogue('oldsystem.tpl');

        $this->assertEmpty($messageCatalogue->getDomains());
    }

    public function testWithExternalModules()
    {
        $messageCatalogue = $this->buildMessageCatalogue('oldsystem.tpl', SmartyExtractor::INCLUDE_EXTERNAL_MODULES);

        $expected = [
            'Modules.Themecusto.Oldsystem' => [
                'Advanced Customization',
                'You can edit your theme sheet by using the Parent/Child theme feature',
                'Advanced use only.',
                'Support team might not be able to assist you on issues created by your own child theme.',
                'Download your current theme',
                'You picked a theme but still want to bring some specific adjustments? Get a child theme, it will allow you to keep the parts you want and customize the others!',
                'Edit your child theme',
                'Once the child theme created, next step is simple: apply the changes you want within the desired files, it will handle the customization part while keeping the parent theme’s look and functionality.',
                'Upload your child theme',
                'As you only bring modification to the child theme, you can upgrade the parent theme easily, without losing your customization.',
                'An error occurred',
                'Please check that you have the rights to write to the folders /app/cache/ and /themes/',
                'Download theme',
                'Downloading',
                'Information',
                'By using this method you can only override the CSS of your theme.',
                'By using this method you can override the CSS and html of your theme, and add analytics tags.',
                'Once uploaded, the child theme will be available in your Theme & Logo section',
            ]
        ];

        $this->verifyCatalogue($messageCatalogue, $expected);
    }

    /**
     * @param $fixtureResource
     *
     * @param bool $includeExternalModules
     *
     * @return MessageCatalogue
     */
    private function buildMessageCatalogue($fixtureResource, $includeExternalModules = false)
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->buildExtractor($includeExternalModules)->extract($this->getResource($fixtureResource), $messageCatalogue);

        return $messageCatalogue;
    }

    /**
     * @param bool $includeExternalModules
     *
     * @return SmartyExtractor
     */
    private function buildExtractor($includeExternalModules = false)
    {
        $smarty = new Smarty();
        $smarty
            ->setCompileDir(__DIR__ . '/../../cache/smarty')
            ->setForceCompile(true);

        $smarty->registerResource('module', new SmartyResourceModule());
        $smarty->registerResource('parent', new SmartyResourceParent());

        $compiler = new TranslationTemplateCompiler(
            Smarty_Internal_Templatelexer::class,
            Smarty_Internal_Templateparser::class,
            $smarty
        );

        return new SmartyExtractor($compiler, $includeExternalModules);
    }

    /**
     * @param string $resourceName
     *
     * @return string
     */
    protected function getResource($resourceName)
    {
        return parent::getResource('fixtures/smarty/'.$resourceName);
    }
}
