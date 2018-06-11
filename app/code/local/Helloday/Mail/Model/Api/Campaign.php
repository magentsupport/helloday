<?php

class Helloday_Mail_Model_Api_Campaign extends Helloday_Mail_Model_Api_Abstract
{
    /**
     * Create Campaign.
     */
    public function create($subject)
    {
        $mailChimp = $this->getClient();
        $listId = Mage::helper('helloday_mail')->getGeneralList();
        $replyTo = Mage::helper('helloday_mail')->getSenderEmail();
        $fromName = Mage::helper('helloday_mail')->getSenderName();
        $mailChimp->post("campaigns", [
            'type' => 'regular',
            'recipients' => ['list_id' => $listId],
            'settings' => ['subject_line' => $subject,
                'reply_to' => $replyTo,
                'from_name' => $fromName
            ]
        ]);

        $response = $mailChimp->getLastResponse();
        $responseObj = json_decode($response['body']);
        return $responseObj->id;
    }
}