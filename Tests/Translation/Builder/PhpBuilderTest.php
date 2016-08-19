<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Builder;

use Exception;
use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Builder\PhpBuilder;

class PhpBuilderTest extends TestCase
{
    protected $instance;

    public function setUp()
    {
        $this->instance = new PhpBuilder();

        // Remove opening <?php tag to allow output to be eval'd easier
        $this->setInaccessibleProperty($this->instance, 'output', '');
    }

    public function tearDown()
    {
        $this->instance = null;
    }

    public function testAppendGlobalDeclaration()
    {
        $expectedPos = PhpBuilder::POS_NEWLINE;

        $this->instance->appendGlobalDeclaration('foo');
        eval($this->getInaccessibleProperty($this->instance, 'output'));

        $this->assertTrue(array_key_exists('foo', $GLOBALS));

        $this->assertEquals(
            $expectedPos,
            $this->getInaccessibleProperty($this->instance, 'pos')
        );
    }

    public function appendMethodsProvider()
    {
        return [
            ['open', [], '<?php'.PHP_EOL.PHP_EOL, PhpBuilder::POS_NEWLINE],
            ['appendVar', ['foo'], '$foo', PhpBuilder::POS_VAR],
            ['appendKey', ['bar'], "['bar']", PhpBuilder::POS_ARRAY_KEY],
            ['appendVarAssignation', [], ' = ', PhpBuilder::POS_ASSIGN],
            ['appendValue', ["'value'"], "'value'", PhpBuilder::POS_VALUE],
            ['appendEndOfLine', [], ';'.PHP_EOL, PhpBuilder::POS_NEWLINE],
            ['appendStringLine', ['foo', 'bar', 'value'], '$foo[\'bar\'] = \'value\';'.PHP_EOL, PhpBuilder::POS_NEWLINE],
        ];
    }

    /**
     * @dataProvider appendMethodsProvider
     */
    public function testAppendMethods($method, $parameters, $expectedResult, $expectedPos)
    {
        $this->assertSame(
            $this->instance,
            $this->invokeInaccessibleMethod($this->instance, $method, $parameters)
        );
        $this->assertEquals(
            $expectedResult,
            $this->getInaccessibleProperty($this->instance, 'output')
        );
        $this->assertEquals(
            $expectedPos,
            $this->getInaccessibleProperty($this->instance, 'pos')
        );
    }

    public function appendMethodsWithWrongPosProvider()
    {
        return [
            ['appendStringLine', ['dummy', 'dummy', 'dummy'], PhpBuilder::POS_VAR, 'Exception'],
        ];
    }

    /**
     * @dataProvider appendMethodsWithWrongPosProvider
     */
    public function testAppendMethodsWithWrongPos($method, $parameters, $position, $expectedException)
    {
        $this->setInaccessibleProperty($this->instance, 'pos', $position);
        $this->setExpectedException($expectedException);
        $this->invokeInaccessibleMethod($this->instance, $method, $parameters);
    }

    public function testBuild()
    {
        $this->assertEquals(
            $this->getInaccessibleProperty($this->instance, 'output'),
            $this->invokeInaccessibleMethod($this->instance, 'build')
        );
    }
}
