<?php

class Helloday_Mail_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * System config path.
     */
    const XML_PATH_MAILCHIMP_KEY = 'mailchimp/general/apikey';

    /**
     * Get Mailchimp api key.
     *
     * @return string
     */
    public function getMailChimpApiKey()
    {
        return Mage::getStoreConfig(self::XML_PATH_MAILCHIMP_KEY);
    }

    /**
     * Get Subscriber List.
     */
    public function getGeneralList()
    {
        return Mage::helper('mailchimp')->getGeneralList(0);
    }

    /**
     * @return mixed
     */
    public function getSenderName()
    {
        $sender = Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_SUCCESS_EMAIL_IDENTITY);
        return Mage::getStoreConfig('trans_email/ident_' . $sender . '/name');
    }

    /**
     * @return mixed
     */
    public function getSenderEmail()
    {
        $sender = Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_SUCCESS_EMAIL_IDENTITY);
        return Mage::getStoreConfig('trans_email/ident_' . $sender . '/email');
    }

    /**
     * Check if subscription made on registration.
     *
     * @return bool
     */
    public function checkIfSubscribeOnRegistration()
    {
        if (Mage::app()->getRequest()->getActionName() == 'createpost'
            && Mage::app()->getRequest()->getModuleName() == 'customer') {
            return true;
        }
        if (Mage::app()->getRequest()->getActionName() == 'save'
            && Mage::app()->getRequest()->getRequestUri() == '/newsletter/manage/save/') {
            return true;
        }
        return false;
    }

    /**
     * @param $subscriber
     *
     * @throws Mage_Core_Exception
     */
    public function sendTemplate11($email, $name)
    {
        $emailT = Mage::getModel('core/email_template');
        $emailT->sendTransactional(
            Mage::getStoreConfig('newsletter/subscription/automated_response_email_template'),
            'general',
            $email,
            $name,
            array(
                'username' => $name,
            )
        );
    }
}
