<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Dumper;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Dumper\XliffFileDumper;
use Symfony\Component\Translation\MessageCatalogue;

class XliffFileDumperTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new XliffFileDumper();
    }

    public function tearDown()
    {
        $this->instance = null;
    }

    private function getEmptyMessageCatalogue()
    {
        return new MessageCatalogue('en');
    }

    private function getFilledMessageCatalogue()
    {
        // key, translation, domain, metadata
        // metadata must match LegacyHelper::getOutputInfo
        $messages = [
            ['first', 'first', 'messages', ['line' => 6, 'file' => 'controllers/admin/AdminAccessController.php']],
            ['second', 'second', 'messages', ['line' => 6, 'file' => 'override/controllers/admin/AdminAccessController.php']],
            ['third', 'third', 'messages', ['line' => 6, 'file' => 'classes/helper/Helper.php']],
            ['fourth', 'fourth', 'messages', ['line' => 0, 'file' => 'themes/classic/modules/blockcart/modal.tpl']],
            ['fifth', 'fifth', 'messages', ['line' => 0, 'file' => 'themes/classic/templates/catalog/category.tpl']],
            ['sixth', 'sixth', 'messages', ['line' => 0, 'file' => 'override/classes/pdf/PDF.php']],
            ['seventh', 'seventh', 'messages', ['line' => 0, 'file' => 'none (skip)']],
        ];

        $catalogue = $this->getEmptyMessageCatalogue();

        foreach ($messages as $message) {
            $catalogue->set($message[0], $message[1], $message[2]);
            $catalogue->setMetadata($message[0], $message[3], $message[2]);
        }

        return $catalogue;
    }

    public function getNoteProvider()
    {
        return [
            ['', [[]]],
            ['Line: 0', [['file' => 'file', 'line' => '0']]],
        ];
    }

    /**
     * @dataProvider getNoteProvider
     */
    public function testGetNote($expected, $args)
    {
        $this->assertSame($expected, $this->invokeInaccessibleMethod($this->instance, 'getNote', $args));
    }

    public function testFormatCatalogue()
    {
        $result = $this->instance->formatCatalogue($this->getFilledMessageCatalogue(), 'messages', ['default_locale' => 'en']);

        $this->assertXmlStringEqualsXmlString(file_get_contents($this->getResource('sampleXliff.xlf')), $result);
    }

    public function testDumpWithoutPath()
    {
        $this->setExpectedException('InvalidArgumentException', 'The file dumper needs a path option.');
        $this->instance->dump($this->getFilledMessageCatalogue());
    }

    public function testDumpWithUnwritablePath()
    {
        $directory = $this->getResource('').'/unwritable';

        if (is_dir($directory)) {
            rmdir($directory);
        }

        mkdir($directory, 0500);
        $this->setExpectedException('RuntimeException');
        $this->instance->dump($this->getFilledMessageCatalogue(), ['path' => $directory]);
    }

    public function testDumpWithValidConfig()
    {
        $directory = $this->getResource('').'/dump';

        $this->instance->dump(
            $this->getFilledMessageCatalogue(),
            ['path' => $directory, 'default_locale' => 'en']
        );

        $this->assertFileExists($this->getResource('dump/en/messages.xlf'));
    }
}
