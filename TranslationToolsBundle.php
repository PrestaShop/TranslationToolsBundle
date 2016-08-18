<?php

namespace TranslationToolsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use TranslationToolsBundle\DependencyInjection\CompilerPass\ExtractorCompilerPass;
use TranslationToolsBundle\DependencyInjection\CompilerPass\TranslationCompilerPass;

class TranslationToolsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ExtractorCompilerPass());
        $container->addCompilerPass(new TranslationCompilerPass());
    }
}
