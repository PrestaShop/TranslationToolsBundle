<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Compiler\Smarty;

use Smarty;
use Smarty_Internal_Templateparser;
use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase as TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Compiler\Smarty\TranslationTemplateCompiler;

class TranslationTemplateCompilerTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new TranslationTemplateCompiler(
            'Smarty_Internal_Templatelexer',
            'Smarty_Internal_Templateparser',
            new Smarty()
        );
    }

    public function tearDown()
    {
        $this->instance = null;
    }

    public function testSetTemplateFile()
    {
        $validFile = $this->getResource('TranslationTemplateCompilerTest.tpl');
        $invalidFile = 'TranslationTemplateCompilerTest.tpl';

        $this->assertSame($this->instance, $this->instance->setTemplateFile($validFile));
        $this->assertEquals($validFile, $this->getInaccessibleProperty($this->instance, 'templateFile'));
        $this->setExpectedException('Symfony\Component\Filesystem\Exception\FileNotFoundException');

        $this->instance->setTemplateFile($invalidFile);
    }

    public function testInit()
    {
        $validFile = $this->getResource('TranslationTemplateCompilerTest.tpl');

        $this->setInaccessibleProperty($this->instance, 'templateFile', $validFile);
        $this->invokeInaccessibleMethod($this->instance, 'init');

        $this->assertInstanceOf('Smarty_Internal_Template', $this->instance->template);
        $this->assertInstanceOf('Smarty_Internal_Templatelexer', $this->instance->lex);
        $this->assertInstanceOf('Smarty_Internal_Templateparser', $this->instance->parser);

        if (((int) ini_get('mbstring.func_overload')) & 2) {
            $this->assertSame('ASCII', mb_internal_encoding());
        }
    }

    public function testExplodeLTag()
    {
        $validLTag = [
            $this->getYYStackEntryMock(0, 0, null),
            $this->getYYStackEntryMock(92, 20, 'l'),
            $this->getYYStackEntryMock(177, 92, [['s' => '"foo"'], ['l' => '"bar"']]),
        ];
        $invalidLTag = [
            $this->getYYStackEntryMock(0, 0, null),
            $this->getYYStackEntryMock(92, 20, 'x'),
            $this->getYYStackEntryMock(177, 92, [['foo' => 'bar']]),
        ];

        $validResult = $this->invokeInaccessibleMethod($this->instance, 'explodeLTag', [$validLTag]);
        $invalidResult = $this->invokeInaccessibleMethod($this->instance, 'explodeLTag', [$invalidLTag]);

        $this->assertEquals(['s' => 'foo', 'l' => 'bar'], $validResult);
        $this->assertNull($invalidResult);
    }

    public function testGetTranslationTags()
    {
        $validFile = $this->getResource('TranslationTemplateCompilerTest.tpl');
        $this->instance->setTemplateFile($validFile);

        $expectedResult = [
            [
                'tag' => [
                    's' => 'The page you are looking for was not found.',
                    'd' => 'errors',
                ],
                'line' => 2,
                'template' => $validFile,
            ],
            [
                'tag' => [
                    's' => 'Sorry for inconvenience.',
                    'd' => 'apologies',
                ],
                'line' => 7,
                'template' => $validFile,
            ],
            [
                'tag' => [
                    's' => 'Search again what you are looking for',
                    'd' => 'advices',
                ],
                'line' => 8,
                'template' => $validFile,
            ],
            [
                'tag' => [
                    'foo' => 'bar',
                    's' => 'Yep nope',
                    'l' => '',
                    'b' => 'a',
                ],
                'line' => 13,
                'template' => $validFile,
            ],
        ];
        $result = $this->instance->getTranslationTags();

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetTag()
    {
        $value = ['s' => 'translate'];
        $exception = $this->getMockBuilder('SmartyCompilerException')
            ->getMock()
        ;
        $previousComment = [];

        $expected = [
            'tag' => $value,
            'line' => $exception->line,
            'template' => $exception->template,
        ];

        $result = $this->invokeInaccessibleMethod(
            $this->instance,
            'getTag',
            [$value, $exception, $previousComment]
        );

        $this->assertEquals($expected, $result);

        $expected['line'] = $exception->line = 10;
        $previousComment = ['line' => 9, 'value' => 'someComment'];
        $expected['comment'] = 'someComment';

        $result = $this->invokeInaccessibleMethod(
            $this->instance,
            'getTag',
            [$value, $exception, $previousComment]
        );

        $this->assertEquals($expected, $result);
    }

    public function naturalizeProvider()
    {
        return [
            ['String', ["'String'"]],
            ['tag', ['{tag}']],
            ['tag', ['{tag}', Smarty_Internal_Templateparser::TP_TEXT]],
            ['tag', ['{*tag*}', Smarty_Internal_Templateparser::TP_TEXT]],
            ['tag', ['{* tag*}', Smarty_Internal_Templateparser::TP_TEXT]],
            ['tag', ['{* tag *}', Smarty_Internal_Templateparser::TP_TEXT]],
        ];
    }

    /**
     * @dataProvider naturalizeProvider
     */
    public function testNaturalize($expected, $args)
    {
        $this->assertEquals(
            $expected,
            $this->invokeInaccessibleMethod($this->instance, 'naturalize', $args)
        );
    }

    private function getYYStackEntryMock($stateno, $major, $minor)
    {
        $mock = $this->getMockBuilder('TP_yyStackEntry')
            ->getMock()
        ;

        $mock->stateno = $stateno;
        $mock->major = $major;
        $mock->minor = $minor;

        return $mock;
    }
}
