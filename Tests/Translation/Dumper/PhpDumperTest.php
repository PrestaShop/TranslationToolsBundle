<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Dumper;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Dumper\PhpDumper;
use Symfony\Component\Translation\MessageCatalogue;

class PhpDumperTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new PhpDumper();
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
        $expectedFiles = [
            'dump/themes/classic/lang/en.php',
            'dump/translations/en/admin.php',
            'dump/translations/en/pdf.php',
        ];

        $this->instance->dump(
            $this->getFilledMessageCatalogue(),
            ['path' => $directory, 'default_locale' => 'en']
        );

        foreach ($expectedFiles as $expectedFile) {
            $this->assertFileExists($this->getResource($expectedFile));
        }
    }

    public function testFormatCatalogue()
    {
        $this->instance->formatCatalogue($this->getFilledMessageCatalogue(), 'messages');
        $builders = $this->getInaccessibleProperty($this->instance, 'builders');

        $this->assertCount(3, $builders);

        foreach ($builders as $builder) {
            // Check if all builders have at least one translation
            $this->assertRegExp("/\\\$_[A-Z]+\['.+'\] = \'.+\';/", $builder->build());
        }
    }

    public function testGetExtension()
    {
        $this->assertEquals('php', $this->instance->getExtension());
    }
}
