<?php

include_once("Mage/Adminhtml/controllers/System/Email/TemplateController.php");

class Helloday_Mail_Adminhtml_System_Email_TemplateController extends Mage_Adminhtml_System_Email_TemplateController
{
    /**
     * Create Campaign with content as email template.
     */
    public function campaignAction()
    {
        $template = $this->_initTemplate('id');
        $campaignId = Mage::getModel('helloday_mail/api_campaign')->create($template->getTemplateSubject());
        $preview = Mage::getBlockSingleton('adminhtml/system_email_template_preview');
        $result = Mage::getModel('helloday_mail/api_capmaign_content')->updateContent($campaignId, $preview->toHtml());
        if(isset($result['html'])) {
            $this->getResponse()->setBody($result['html']);
        }
    }

    /**
     * Send template to Mailchimp.
     */
    public function mailchimpAction()
    {
        $template = $this->_initTemplate('id');
        $preview = Mage::getBlockSingleton('adminhtml/system_email_template_preview');
        if ($template->getMailchimpTemplateId()) {
            $mailchimpTemplateId = Mage::getModel('helloday_mail/api_template')->edit($template->getMailchimpTemplateId(), $preview->toHtml());
        } else {
            $mailchimpTemplateId = Mage::getModel('helloday_mail/api_template')->create($preview->toHtml(), $template->getTemplateSubject());
        }
        if ($mailchimpTemplateId) {
            $template
                ->setData('mailchimp_template_id', $mailchimpTemplateId)
                ->save();
            Mage::getModel('core/session')->addSuccess('Template has been sent successfully.');
        } else {
            Mage::getModel('core/session')->addError('Error happened during template sending.');
        }
        $this->_redirect('adminhtml/system_email_template/edit/id/' . $this->getRequest()->getParam('id'));
    }
}
