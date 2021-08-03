<?php

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/AppKernel.php';

use PHPUnit\Framework\Assert as Assert;
use PrestaShop\TranslationToolsBundle\Translation\Compiler\Smarty\TranslationTemplateCompiler;

$kernel = new AppKernel('test', false);
$kernel->boot();

$smartyTemplateCompiler = $kernel->getContainer()->get('prestashop.compiler.smarty.template');
Assert::assertInstanceOf(TranslationTemplateCompiler::class, $smartyTemplateCompiler);
