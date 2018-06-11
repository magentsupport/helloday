<?php
class Multidots_CompSignup_Model_Mysql4_Compsignup extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("compsignup/compsignup", "id");
    }
}