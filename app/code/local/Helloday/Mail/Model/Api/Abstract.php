<?php
abstract class Helloday_Mail_Model_Api_Abstract extends Mage_Core_Model_Abstract
{
    /**
     * @var Helloday_Mail_Model_Api_Mailchimp_MailChimp
     */
    protected $_client;

    /**
     * Helloday_Mail_Model_Api_Abstract constructor.
     */
    public function __construct()
    {
        $apikey = Mage::helper('helloday_mail')->getMailChimpApiKey();
        $mailChimp = new Helloday_Mail_Model_Api_Mailchimp_MailChimp($apikey);
        $this->_client = $mailChimp;
    }

    /**
     * Get Mailchimp Client.
     *
     * @return Helloday_Mail_Model_Api_Mailchimp_MailChimp
     */
    public function getClient()
    {
        return $this->_client;
    }
}