<?php

class Helloday_Mail_Model_Newsletter_Subscriber extends ES_Newssubscribers_Model_Subscriber
{
    /**
     * Config settings path.
     */
    const XML_PATH_CONFIG_ENABLE_CONFIRMATION_SUCCESS_EMAIL_MAGENTO_SEND = 'mailchimp/helloday/confirmation_success_email_magento_send';

    /**
     * Send confirmation success email from Magento depends on config settings.
     *
     * @return $this|Mage_Newsletter_Model_Subscriber
     */
    public function sendConfirmationSuccessEmail()
    {
        if (Mage::getStoreConfig(Ebizmarts_MailChimp_Model_Config::GENERAL_ACTIVE)
            && !Mage::getStoreConfigFlag(self::XML_PATH_CONFIG_ENABLE_CONFIRMATION_SUCCESS_EMAIL_MAGENTO_SEND)) {
            return $this;
        } else {
            if (!Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_TEMPLATE)
                || !Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY)
            ) {
                return $this;
            }

            $translate = Mage::getSingleton('core/translate');
            /* @var $translate Mage_Core_Model_Translate */
            $translate->setTranslateInline(false);

            $email = Mage::getModel('core/email_template');
            $username = $this->getCustomerFirstName($this);

            $template = $this->checkIfSubscribeOnRegistration()
                ? Mage::getStoreConfig('newsletter/subscription/success_email_template')
                : Mage::getStoreConfig('newsletter/subscription/success_email_template_popup');

            $email->sendTransactional(
                $template,
                Mage::getStoreConfig(self::XML_PATH_SUCCESS_EMAIL_IDENTITY),
                $this->getEmail(),
                $this->getName(),
                array(
                    'subscriber' => $this,
                    'username' => $username,
                    'subscription_preferences_url' => Mage::getUrl('newsletter/manage'),
                    'subscribe_on_registration' => !$this->checkIfSubscribeOnRegistration(),
                    'footerstyle' => $this->getFooterStyle()
                )
            );

            if (!$this->checkIfSubscribeOnRegistration()) {
                Mage::helper('helloday_mail')->sendTemplate11($this->getEmail(), $username);
            }

            $translate->setTranslateInline(true);

            return $this;
        }
    }

    /**
     * Get Customer First and Last Name.
     *
     * @param Helloday_Mail_Model_Newsletter_Subscriber $subscriber
     *
     * @return string
     */
    protected function getCustomerFirstName($subscriber)
    {
        $firstname = (string) Mage::app()->getRequest()->getPost('first_name', '');
        $customerId = $subscriber->getCustomerId();
        if ($customerId && $firstname == '') {
            $customer = Mage::getModel('customer/customer')->load($customerId);
            $firstname = $customer->getFirstname();
        }
        return $firstname;
    }

    /**
     * Check if subscription made on registration.
     *
     * @return bool
     */
    protected function checkIfSubscribeOnRegistration()
    {
        return Mage::helper('helloday_mail')->checkIfSubscribeOnRegistration();
    }

    protected function getFooterStyle()
    {
        return $this->checkIfSubscribeOnRegistration() ? 'height="385" style="height:385px; background-position:center bottom;"' : 'height="678" style="height:678px; background-position:center bottom;"';
    }
}
