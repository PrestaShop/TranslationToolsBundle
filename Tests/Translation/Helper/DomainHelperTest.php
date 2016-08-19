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
}
