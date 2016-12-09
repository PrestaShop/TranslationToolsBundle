<?php

namespace  PrestaShop\TranslationToolsBundle;

use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use PrestaShop\TranslationToolsBundle\DependencyInjection\CompilerPass\ExtractorCompilerPass;
use PrestaShop\TranslationToolsBundle\DependencyInjection\CompilerPass\TranslationCompilerPass;

class TranslationToolsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtractorCompilerPass());
        $container->addCompilerPass(new TranslationCompilerPass());
    }

        public function registerCommands(Application $application)
        {
            // disable registering of commands
            return;
        }
}
