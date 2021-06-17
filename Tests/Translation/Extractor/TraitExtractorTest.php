<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
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
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use Symfony\Component\Finder\Finder;

class TraitExtractorTest extends TestCase
{
    protected $instance;

    public function setUp(): void
    {
        $this->instance = $this->getMockForTrait('PrestaShop\TranslationToolsBundle\Translation\Extractor\TraitExtractor');
    }

    public function tearDown(): void
    {
        $this->instance = null;
    }

    public function resolveDomainProvider(): array
    {
        return [
            ['foo', 'foo'],
            ['foo.bar', 'foo.bar'],
            ['messages', ''],
            ['messages', null],
        ];
    }

    /**
     * @dataProvider resolveDomainProvider
     */
    public function testResolveDomain($expected, $input): void
    {
        $this->assertEquals($expected, $this->invokeInaccessibleMethod($this->instance, 'resolveDomain', [$input]));
    }

    public function testGetEntryComment(): void
    {
        $comments = [
            [
                'file' => 'product.html.twig',
                'comment' => 'puff the cat',
                'line' => 1,
            ],
            [
                'file' => 'product.html.twig',
                'comment' => 'superPuff',
                'line' => 10,
            ],
        ];

        $this->assertEquals(
            'puff the cat',
            $this->invokeInaccessibleMethod($this->instance, 'getEntryComment', [$comments, 'product.html.twig', 1])
        );
    }

    public function testGetSetFinder(): void
    {
        $finder = new Finder();

        $this->assertSame($this->instance, $this->instance->setFinder($finder));
        $this->assertSame($finder, $this->getInaccessibleProperty($this->instance, 'finder'));
        $this->assertSame($finder, $this->instance->getFinder());

        $this->setInaccessibleProperty($this->instance, 'finder', null);
        $this->assertInstanceOf('Symfony\Component\Finder\Finder', $this->instance->getFinder());
    }
}
