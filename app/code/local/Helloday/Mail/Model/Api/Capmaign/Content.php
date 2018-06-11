<?php

class Helloday_Mail_Model_Api_Capmaign_Content
{
    /**
     * Update Campaign content.
     *
     * @param string $campaignId
     * @param string $html
     *
     * @return array|false
     */
    public function updateContent($html, $campaignId)
    {
        $apikey = Mage::helper('helloday_mail')->getMailChimpApiKey();
        if (!$apikey) return;
        $mailChimp = new Helloday_Mail_Model_Api_Mailchimp_MailChimp($apikey);
        $result = $mailChimp->put('campaigns/' . $campaignId . '/content', [
            'html' => $html
        ]);
        return $result;
    }
}