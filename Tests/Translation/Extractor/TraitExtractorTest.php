<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Dumper;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use Symfony\Component\Finder\Finder;

class TraitExtractorTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = $this->getMockForTrait('PrestaShop\TranslationToolsBundle\Translation\Extractor\TraitExtractor');
    }

    public function teaDown()
    {
        $this->instance = null;
    }

    public function resolveDomainProvider()
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
    public function testResolveDomain($expected, $input)
    {
        $this->assertEquals($expected, $this->invokeInaccessibleMethod($this->instance, 'resolveDomain', [$input]));
    }

    public function testGetEntryComment()
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

    public function testGetSetFinder()
    {
        $finder = new Finder();

        $this->assertSame($this->instance, $this->instance->setFinder($finder));
        $this->assertSame($finder, $this->getInaccessibleProperty($this->instance, 'finder'));
        $this->assertSame($finder, $this->instance->getFinder());

        $this->setInaccessibleProperty($this->instance, 'finder', null);
        $this->assertInstanceOf('Symfony\Component\Finder\Finder', $this->instance->getFinder());
    }
}
