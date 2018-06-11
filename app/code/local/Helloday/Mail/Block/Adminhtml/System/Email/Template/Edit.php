<?php

class Helloday_Mail_Block_Adminhtml_System_Email_Template_Edit extends Mage_Adminhtml_Block_System_Email_Template_Edit {

    /**
     * Prepare layout.
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $this->setChild('mailchimp_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(
                    array(
                        'label'   => Mage::helper('adminhtml')->__('Export Template to Mailchimp'),
                        'onclick' => 'templateControl.mailchimp();',
                        'id'      => 'mailchimp_button'
                    )
                )
        );

        return parent::_prepareLayout();
    }

    /**
     * Get Mailchimp action url.
     *
     * @return string
     */
    public function getMailchimpUrl()
    {
        return $this->getUrl('*/*/mailchimp/', array('id' => Mage::app()->getRequest()->getParam('id')));
    }

    /**
     * Get Mailchimp button.
     *
     * @return string
     */
    public function getMailchimpButtonHtml()
    {
        return $this->getChildHtml('mailchimp_button');
    }
}