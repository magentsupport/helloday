<?php
/***
 * Dg_Pricerulesextended is licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License (http://creativecommons.org/licenses/by-sa/3.0/)
 * Original work at www.drewgillson.com
***/
class Dg_Pricerulesextended_Model_Observer {

	public function newsletterSubscriberSave(Varien_Event_Observer $observer) {
		if(Mage::getStoreConfig('pricerulesextended/email/enable')) {
			$helper = Mage::helper('pricerulesextended');
		    $subscriber = $observer->getEvent()->getSubscriber();
		    $email = $subscriber->getEmail();

	        $promo_value = Mage::getStoreConfig('pricerulesextended/promocode/dollarvalue');
	        $promo_min = Mage::getStoreConfig('pricerulesextended/promocode/minpurchase');
	        
	        $helper->addPromoCode($email, $promo_value, $promo_min);
    	}

	    return $this;
	}
}