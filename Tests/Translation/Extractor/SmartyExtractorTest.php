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
    public function testExtractWithDomain(): void
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
            ],
        ];

        $this->verifyCatalogue($messageCatalogue, $expected);
    }

    public function testExtractWithoutExternalModules(): void
    {
        $messageCatalogue = $this->buildMessageCatalogue('oldsystem.tpl');

        $this->assertEmpty($messageCatalogue->getDomains());
    }

    public function testWithExternalModules(): void
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
                'How to use parents/child themes',
                'Upload child theme',
                'Information',
                'By using this method you can only override the CSS of your theme.',
                'By using this method you can override the CSS and html of your theme, and add analytics tags.',
                'Once uploaded, the child theme will be available in your Theme & Logo section',
            ],
        ];

        $this->verifyCatalogue($messageCatalogue, $expected);
    }

    private function buildMessageCatalogue(string $fixtureResource, bool $includeExternalModules = false): MessageCatalogue
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->buildExtractor($includeExternalModules)->extract($this->getResource($fixtureResource), $messageCatalogue);

        return $messageCatalogue;
    }

    private function buildExtractor(bool $includeExternalModules = false): SmartyExtractor
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

    public function testExtractFromDirectoryWithoutExclusion(): void
    {
        $messageCatalogue = new MessageCatalogue('en');
        $this->buildExtractor(SmartyExtractor::INCLUDE_EXTERNAL_MODULES)->extract(
            parent::getResource('directory/'), $messageCatalogue
        );

        $catalogue = $messageCatalogue->all();
        $this->assertCount(1, array_keys($catalogue));
        $this->assertCount(5, $catalogue['Modules.Wirepayment.Shop']);

        $this->verifyCatalogue($messageCatalogue, [
            'Modules.Wirepayment.Shop' => [
                'Your order on %s is complete.' => 'Your order on %s is complete.',
                'Please send us a bank wire with:' => 'Please send us a bank wire with:',
                'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' => 'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].',
                'Please specify your order reference %s in the bankwire description.' => 'Please specify your order reference %s in the bankwire description.',
                'Your order will be sent as soon as we receive payment.' => 'Your order will be sent as soon as we receive payment.',
            ],
        ]);
    }

    public function testExtractFromDirectoryExcludingDirectories(): void
    {
        // Exclude one directory
        $messageCatalogue = new MessageCatalogue('en');
        $this->buildExtractor(SmartyExtractor::INCLUDE_EXTERNAL_MODULES)
            ->setExcludedDirectories(['subdirectory'])
            ->extract(parent::getResource('directory/'), $messageCatalogue);

        $catalogue = $messageCatalogue->all();
        $this->assertCount(1, array_keys($catalogue));
        $this->assertCount(4, $catalogue['Modules.Wirepayment.Shop']);

        $this->verifyCatalogue($messageCatalogue, [
            'Modules.Wirepayment.Shop' => [
                'Your order on %s is complete.' => 'Your order on %s is complete.',
                'Please send us a bank wire with:' => 'Please send us a bank wire with:',
                'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' => 'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].',
                'Your order will be sent as soon as we receive payment.' => 'Your order will be sent as soon as we receive payment.',
            ],
        ]);

        // Exclude multiple directories
        $messageCatalogue = new MessageCatalogue('en');
        $this->buildExtractor(SmartyExtractor::INCLUDE_EXTERNAL_MODULES)
            ->setExcludedDirectories(['subdirectory', 'subdirectory2'])
            ->extract(parent::getResource('directory/'), $messageCatalogue);

        $catalogue = $messageCatalogue->all();
        $this->assertCount(1, array_keys($catalogue));
        $this->assertCount(3, $catalogue['Modules.Wirepayment.Shop']);

        $this->verifyCatalogue($messageCatalogue, [
            'Modules.Wirepayment.Shop' => [
                'Your order on %s is complete.' => 'Your order on %s is complete.',
                'Please send us a bank wire with:' => 'Please send us a bank wire with:',
                'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' => 'If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].',
            ],
        ]);
    }

    /**
     * @param string $resourceName
     */
    protected function getResource($resourceName): string
    {
        return parent::getResource('fixtures/smarty/' . $resourceName);
    }
}
