<?php
/***
 * Dg_Pricerulesextended is licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License (http://creativecommons.org/licenses/by-sa/3.0/)
 * Original work at www.drewgillson.com
***/
class Dg_Pricerulesextended_GenerateController extends Mage_Core_Controller_Front_Action 
{
    public function promocodeAction() {
        $helper = Mage::helper('pricerulesextended');

        $params = $this->getRequest()->getParams();
        $email = $params['email'];
        $email = explode(',',$email);
        $email = $email[0];


        $promo_value = Mage::getStoreConfig('pricerulesextended/promocode/dollarvalue');
        $promo_min = Mage::getStoreConfig('pricerulesextended/promocode/minpurchase');
        
        $helper->addPromoCode($email, $promo_value, $promo_min);

        $this->_redirectUrl('/');

    }
}