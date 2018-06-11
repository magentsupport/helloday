<?php

class Helloday_Mail_Model_Pricerulesextended_Observer extends Dg_Pricerulesextended_Model_Observer
{
    public function newsletterSubscriberSave(Varien_Event_Observer $observer)
    {
        if ( Mage::helper('helloday_mail')->checkIfSubscribeOnRegistration()) {
            return $this;
        }
        $helper = Mage::helper('pricerulesextended');
        $subscriber = $observer->getEvent()->getSubscriber();
        $email = $subscriber->getEmail();

        $promo_value = Mage::getStoreConfig('pricerulesextended/promocode/dollarvalue');
        $promo_min = Mage::getStoreConfig('pricerulesextended/promocode/minpurchase');

        $helper->addPromoCode($email, $promo_value, $promo_min);

        return $this;
    }
}