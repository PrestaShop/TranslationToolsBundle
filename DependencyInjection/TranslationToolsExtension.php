<?php
/**
 * This file is authored by PrestaShop SA and Contributors <contact@prestashop.com>
 *
 * It is distributed under MIT license.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PrestaShop\TranslationToolsBundle\DependencyInjection;

use PrestaShop\TranslationToolsBundle\Twig\Extension\TranslationExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class TranslationToolsExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $definition = $container->register(
            'translation_tools.translation.extension',
            TranslationExtension::class
        );

        $definition->addTag('twig.extension');

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $this->processConfiguration(new Configuration(), $configs);
    }
}
