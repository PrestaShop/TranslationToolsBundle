<?php

namespace Test;

class TestController
{
    public function init()
    {
        $this->l('Shop');
    }

    public function productAction()
    {
        $this->trans('Fingers', [], 'Admin.Product.Help');
    }

    public function nothingAction()
    {
        // the parser should not capture this
        $version = phpversion();
    }

    public function fooAction()
    {
        $this->trans(
            'This is how symfony does it',
            [],
            'Admin.Product.Help'
        );

        $this->trans(
            'This is how PrestaShop does it',
            'Admin.Product.Help',
            []
        );

        $this->trans(
            'Look, no parameters',
            'Admin.Product.Help'
        );

        $this->trans(
            'Look, no domain'
        );

        $this->trans(
            'It works with no domain and with parameters',
            []
        );
    }

    public function barAction()
    {
        $domain = 'admin.product.plop';

        $this->trans(
            'Bar',
            [],
            $domain
        );

        $text = [
            'key' => 'This text is lonely',
            'parameters' => [],
            'domain' => 'Admin.Notifications.Error',
        ];

        $several = [
            [
                'key' => 'This text has a sibling',
                'parameters' => [],
                'domain' => 'Admin.Superserious.Messages',
            ],
            [
                'key' => "I ain't need no parameter",
                'domain' => 'Like.A.Gangsta',
            ],
            [
                'key' => 'Parameters work in any order',
                'domain' => 'Admin.Notifications.Error',
                'parameters' => [],
            ],
            [
                'key' => 'No domain, no gain',
            ],
            [
                'key' => 'No domain, no gain, even with parameters',
                'parameters' => [],
            ],
            [
                'domain' => 'No.Key.WTF',
            ],
            [
                'domain' => 'Parameters.Wont.Help.Here',
                'parameters' => [],
            ],
            [
                'key' => "I'm with foo, which spoils any party",
                'domain' => 'Admin.Notifications.Error',
                'foo' => [],
            ],
            [
                'key' => "I'm with foo, which spoils any party, even with parameters",
                'domain' => 'Admin.Notifications.Error',
                'parameters' => [],
                'foo' => [],
            ],
        ];

        return [
            'key' => 'This text is coming back somewhere',
            'parameters' => [],
            'domain' => 'Admin.Notifications.Error',
        ];
    }
}
