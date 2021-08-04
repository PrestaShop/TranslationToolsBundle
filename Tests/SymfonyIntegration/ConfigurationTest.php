<?php

namespace PrestaShop\TranslationToolsBundle\Tests\SymfonyIntegration;

use PHPUnit\Framework\Assert as Assert;
use PrestaShop\TranslationToolsBundle\Translation\Compiler\Smarty\TranslationTemplateCompiler;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConfigurationTest extends KernelTestCase
{
    /**
     * @return void
     */
    public function testIsDeprecated()
    {
        self::bootKernel();

        $smartyTemplateCompiler = self::$kernel->getContainer()->get('prestashop.compiler.smarty.template');
        Assert::assertInstanceOf(TranslationTemplateCompiler::class, $smartyTemplateCompiler);
    }
}
