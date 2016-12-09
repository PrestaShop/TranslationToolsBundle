<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Command;

use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use PrestaShop\TranslationToolsBundle\Command\ExtractCommand;

class ExtractCommandTest extends KernelTestCase
{
    // regular expressions
    const EXPECTED_OUTPUT = 'Extracting Translations for locale en';
    const EXPECTED_DUMP_OUTPUT = 'TranslationToolsBundle\/Tests\/Kernel\/dumps\/translatables';
    const STATUS_CODE_SUCCESS = 0;

    private $consoleApp;

    public function setUp()
    {
        self::bootKernel();
        $this->consoleApp = new Application(self::$kernel);
    }

    public function testExecute()
    {
        $this->consoleApp->add(new ExtractCommand());

        $command = $this->consoleApp->find('pr:tr:ex');
        $commandTester = new CommandTester($command);
        $commandTester->execute(array(
            'command' => $command->getName(),
            'configfile' => __DIR__ . '/../config-test.yml',
            )
        );

        $commandOutput = $commandTester->getDisplay();

        $this->assertRegExp('/'.self::EXPECTED_OUTPUT.'/', $commandOutput);
        $this->assertRegExp('/'.self::EXPECTED_DUMP_OUTPUT.'/', $commandOutput);
        $this->assertEquals(self::STATUS_CODE_SUCCESS, $commandTester->getStatusCode());
    }
}
