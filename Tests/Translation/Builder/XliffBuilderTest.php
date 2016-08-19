<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Builder;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Builder\XliffBuilder;

class XliffBuilderTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new XliffBuilder();
    }

    public function tearDown()
    {
        $this->instance = null;
    }

    public function testSetVersion()
    {
        $this->assertSame($this->instance, $this->instance->setVersion('1.0'));
        $this->assertEquals('1.0', $this->getInaccessibleProperty($this->instance, 'version'));
    }

    public function testAddTransUnit()
    {
        $this->assertSame($this->instance, $this->instance->addTransUnit('filename', 'source', 'target', 'note'));
    }

    public function testAddFile()
    {
        $this->assertSame($this->instance, $this->instance->addFile('filename', 'sourceLanguage', 'targetLanguage'));
    }

    public function testBuild()
    {
        $this->instance->addFile('filename', 'sourceLanguage', 'targetLanguage');
        $this->instance->addTransUnit('filename', 'source', 'target', 'note');

        $this->assertInstanceOf('DOMDocument', $this->instance->build());
    }
}
