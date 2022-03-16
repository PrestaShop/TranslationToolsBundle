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

namespace PrestaShop\TranslationToolsBundle\Tests\DependencyInjection;

use PrestaShop\TranslationToolsBundle\DependencyInjection\Configuration;
use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;

class ConfigurationTest extends TestCase
{
    public function testNewConfiguration(): void
    {
        $configuration = new Configuration();
        $this->assertSame('translation_tools', $configuration->getConfigTreeBuilder()->buildTree()->getName());
    }
}
