<?php
class  Helloday_Mail_Model_Observer
{
    /**
     * @throws Mage_Core_Exception
     */
    public function sendEmail($observer)
    {
        $customer = $observer->getCustomer();
        Mage::helper('helloday_mail')->sendTemplate11($customer->getEmail(), $customer->getFirstname());
    }
}