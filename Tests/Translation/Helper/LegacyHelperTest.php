<?php

namespace PrestaShop\TranslationToolsBundle\Tests\Translation\Helper;

use PrestaShop\TranslationToolsBundle\Tests\PhpUnit\TestCase;
use PrestaShop\TranslationToolsBundle\Translation\Helper\LegacyHelper;

class LegacyHelperTest extends TestCase
{
    public function getOutputInfoProvider()
    {
        return [
            [
                null,
                '/root/universe/milkyway/mars/somewhere/something.else',
            ],
            [
                ['file' => 'translations/[locale]/admin.php', 'var' => '_LANGADM'],
                'controllers/admin/AdminAccessController.php',
            ],
            [
                ['file' => 'translations/[locale]/admin.php', 'var' => '_LANGADM'],
                'override/controllers/admin/AdminAccessController.php',
            ],
            [
                ['file' => 'translations/[locale]/admin.php', 'var' => '_LANGADM'],
                'classes/helper/Helper.php',
            ],
            [
                ['file' => 'themes/classic/lang/[locale].php', 'var' => '_LANG'],
                'themes/classic/modules/blockcart/modal.tpl',
            ],
            [
                ['file' => 'themes/classic/lang/[locale].php', 'var' => '_LANG'],
                'themes/classic/templates/catalog/category.tpl',
            ],
            [
                null,
                'override/classes/pdf/index.php',
            ],
            [
                ['file' => 'translations/[locale]/pdf.php', 'var' => '_LANGPDF'],
                'override/classes/pdf/PDF.php',
            ],
            [
                ['file' => 'translations/[locale]/pdf.php', 'var' => '_LANGPDF'],
                'classes/pdf/PDF.php',
            ],
            [
                ['file' => 'modules/bankwire/translations/[locale].php', 'var' => '_MODULE'],
                'modules/bankwire/views/templates/front/payment_infos.tpl',
            ],
            [
                ['file' => 'mails/[locale]/lang.php', 'var' => '_LANGMAIL'],
                'mails/en/account.html',
            ],
            [
                ['file' => 'translations/[locale]/fields.php', 'var' => '_FIELDS'],
                'Fixtures/fields_catalogue.php',
            ],
        ];
    }

    /**
     * @dataProvider getOutputInfoProvider
     */
    public function testGetOutputInfo($expected, $input)
    {
        $result = LegacyHelper::getOutputInfo($input);

        // Skip check for closures
        if (is_array($result) && isset($result['generateKey'])) {
            unset($result['generateKey']);
        }

        $this->assertEquals($expected, $result);
    }

    public function testGetKey()
    {
        $originalString = 'foo';
        $expectedOutput = 'acbd18db4cc2f85cedef654fccc4a4d8';

        $this->assertEquals($expectedOutput, LegacyHelper::getKey($originalString));
    }

    public function getKeyPrefixProvider()
    {
        return [
            [null, 'foobar2000'],
            ['AdminController', '/Controllers/AdminController.php'],
            ['PaymentModule', '/Modules/PaymentModule.php'],
            ['Helper', '/helpers/FoobarHelper.php'],
            ['Toto', '/universe/earth/TotoController.php'],
        ];
    }

    /**
     * @dataProvider getKeyPrefixProvider
     */
    public function testGetKeyPrefix($expected, $file)
    {
        $this->assertEquals($expected, LegacyHelper::getKeyPrefix($file));
    }
}
