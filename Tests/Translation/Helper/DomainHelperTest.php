<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Helper;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Helper\DomainHelper;

class DomainHelperTest extends TestCase
{
    public function getCrowdinPathProvider()
    {
        return [
            ['admin/patate/douce.ext', 'admin/patate/douce.ext'],
            ['admin/patate/douce.frite.ext', 'admin/patate/douce/frite.ext'],
            ['admin/patate/douce', 'admin/patate/douce'],
        ];
    }

    /**
     * @dataProvider getCrowdinPathProvider
     */
    public function testGetCrowdinPath($expectedCrowdinPath, $relativePath)
    {
        $this->assertEquals($expectedCrowdinPath, DomainHelper::getCrowdinPath($relativePath));
    }

    public function testGetExportpath()
    {
        $this->assertEquals('admin/patate/douce', DomainHelper::getExportPath('admin.patate.douce'));
    }

    public function testGetDomain()
    {
        $this->assertEquals('admin.patate.douce', DomainHelper::getDomain('admin/patate/douce'));
    }

    /**
     * @param string $moduleName
     * @param string $source
     * @param string $expected
     *
     * @dataProvider provideModuleFromLegasySourceCases
     */
    public function testBuildModuleDomainFromLegacySource($moduleName, $source, $expected)
    {
        $this->assertSame($expected, DomainHelper::buildModuleDomainFromLegacySource($moduleName, $source));
    }

    /**
     * @param string $moduleName
     * @param bool $withDots
     * @param string $expected
     *
     * @dataProvider provideModuleBaseDomainCases
     */
    public function testBuildModuleBaseDomain($moduleName, $withDots, $expected)
    {
        $this->assertSame($expected, DomainHelper::buildModuleBaseDomain($moduleName, $withDots));
    }

    public function provideModuleFromLegasySourceCases()
    {
        return [
            ['ps_test', '', 'Modules.Test.Pstest'],
            ['ps_test_Something', '', 'Modules.Testsomething.Pstestsomething'],
            ['ps_test_ps_Something', '', 'Modules.Testpssomething.Pstestpssomething'],
            ['ps_test_Ps_something', 'test_me', 'Modules.Testpssomething.Test_me'],
            ['ps_test_ps_something', 'test_Me', 'Modules.Testpssomething.Test_me'],
            ['ps_test_ps_something', 'testMe', 'Modules.Testpssomething.Testme'],
            ['ps_test_ps_something', 'test-Me', 'Modules.Testpssomething.Test-me'],
            ['ps_test_ps_something', 'test.Me', 'Modules.Testpssomething.Test.me'],
            ['some_module', 'ps_some_test.tpl', 'Modules.Somemodule.Ps_some_test'],
            ['some_module', 'some_test.tpl', 'Modules.Somemodule.Some_test'],
            ['some_module', 'some_test.html', 'Modules.Somemodule.Some_test.html'],
            ['some_module', 'some_test.html.twig', 'Modules.Somemodule.Some_test.html.twig'],
            ['some_module', 'SomeController', 'Modules.Somemodule.Some'],
            ['some_module', 'SomeController.php', 'Modules.Somemodule.Somecontroller.php'],
        ];
    }

    public function provideModuleBaseDomainCases()
    {
        return [
            ['ps_test', true, 'Modules.Test'],
            ['ps_test_Something', true, 'Modules.Testsomething'],
            ['ps_test_ps_Something', true, 'Modules.Testpssomething'],
            ['ps_test_Ps_something', true, 'Modules.Testpssomething'],
            ['ps_test', false, 'ModulesTest'],
            ['ps_test_Something', false, 'ModulesTestsomething'],
            ['ps_test_ps_Something', false, 'ModulesTestpssomething'],
            ['ps_test_Ps_something', false, 'ModulesTestpssomething'],
        ];
    }
}
