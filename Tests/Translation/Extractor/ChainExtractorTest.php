<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Extractor;

use PrestaShop\TranslationToolsBundle\Translation\Extractor\ChainExtractor;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\Extractor\ExtractorInterface;
use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;

class ChainExtractorTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new ChainExtractor();
    }

    public function tearDown()
    {
        $this->instance = null;
    }
    
    public function testAddExtractor()
    {
        $extractor = $this->createMock(ExtractorInterface::class);
        $this->assertSame($this->instance, $this->instance->addExtractor('test', $extractor));
    }
    
    public function testExtract()
    {
        $directory = '/';
        $catalog = $this->createMock(MessageCatalogue::class, [], ['en']);
        $extractor = $this->getMockBuilder(ExtractorInterface::class)
            ->setMethods(['setFinder', 'extract', 'setPrefix'])
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->getMock();
        
        $extractor->expects($this->once())->method('setFinder');
        $extractor->expects($this->once())
            ->method('extract')
            ->with($directory, $catalog);
        
        $this->instance->addExtractor('test', $extractor);
        $this->instance->extract($directory, $catalog);
    }
}
