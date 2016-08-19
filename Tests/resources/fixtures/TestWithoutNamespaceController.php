<?php

class TestController
{
    public function init()
    {
        $this->l('Shop');
    }

    public function productAction()
    {
        $this->trans('Shop', [], 'admin.product.help');
    }
}
