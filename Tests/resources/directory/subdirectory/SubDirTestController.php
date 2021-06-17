<?php

namespace Test\Subdirectory;

class SubDirTestController
{
    public function init()
    {
        $this->l('SubdirShop');
    }

    public function productAction()
    {
        $this->trans('SubdirFingers', [], 'admin.product.help');
    }
}
