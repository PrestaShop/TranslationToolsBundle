<?php

// @todo
class T
{
    protected $_conf;

    public function test()
    {

        /* @yolo */
        $this->l('puff the cat');

        $this->_conf = array(
            // @todo
            1 => $this->l('Successful deletion'),
            2 => $this->trans('Prestashop', [], 'Domain'),
            3 => $this->l('The selection has been successfully de'),
        );
    }
}
