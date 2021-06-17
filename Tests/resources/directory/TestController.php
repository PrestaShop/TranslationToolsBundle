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
        $this->trans('Fingers', [], 'admin.product.help');
    }
}
