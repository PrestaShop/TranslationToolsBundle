<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TranslationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->register(
            'translation_tools.translation.node_visitor',
            'PrestaShop\TranslationToolsBundle\Twig\NodeVisitor\TranslationNodeVisitor'
        );
    }
}
