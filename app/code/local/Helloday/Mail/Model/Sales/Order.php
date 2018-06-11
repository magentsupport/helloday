<?php

class Helloday_Mail_Model_Sales_Order extends Mage_Sales_Model_Order {
    /**
     * Queue email with new order data
     *
     * @param bool $forceMode if true then email will be sent regardless of the fact that it was already sent previously
     *
     * @return Mage_Sales_Model_Order
     * @throws Exception
     */
    public function queueNewOrderEmail($forceMode = false)
    {
        $storeId = $this->getStore()->getId();

        if (!Mage::helper('sales')->canSendNewOrderEmail($storeId)) {
            return $this;
        }

        // Get the destination email addresses to send copies to
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $storeId);

        // Start store emulation process
        /** @var $appEmulation Mage_Core_Model_App_Emulation */
        $appEmulation = Mage::getSingleton('core/app_emulation');
        $initialEnvironmentInfo = $appEmulation->startEnvironmentEmulation($storeId);

        try {
            // Retrieve specified view block from appropriate design package (depends on emulated store)
            $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
                ->setIsSecureMode(true);
            $paymentBlock->getMethod()->setStore($storeId);
            $paymentBlockHtml = $paymentBlock->toHtml();

            $orderItemsHtml = Mage::getBlockSingleton('sales/order_email_items')
                ->addItemRender('default', 'sales/order_email_items_order_default', 'email/helloday/order/items/order/default.phtml')
                ->addItemRender('grouped', 'sales/order_email_items_order_grouped', 'email/helloday/order/items/order/default.phtml')
                ->setTemplate('email/helloday/order/items.phtml')
                ->setData('order', $this)
                ->toHtml();

            $tax = Mage::getBlockSingleton('tax/sales_order_tax')
                ->setIsPlaneMode(1)
                ->setTemplate('tax/order/tax.phtml');
            $totals = Mage::getBlockSingleton('sales/order_totals')
                ->setChild('tax', $tax)
                ->setOrder($this)
                ->setTemplate('email/helloday/order/totals.phtml')
                ->toHtml();

        } catch (Exception $exception) {
            // Stop store emulation process
            $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);
            throw $exception;
        }

        // Stop store emulation process
        $appEmulation->stopEnvironmentEmulation($initialEnvironmentInfo);

        // Retrieve corresponding email template id and customer name
        if ($this->getCustomerIsGuest()) {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $storeId);
            $customerName = $this->getBillingAddress()->getName();
        } else {
            $templateId = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId);
            $customerName = $this->getCustomerName();
        }

        /** @var $mailer Mage_Core_Model_Email_Template_Mailer */
        $mailer = Mage::getModel('core/email_template_mailer');
        /** @var $emailInfo Mage_Core_Model_Email_Info */
        $emailInfo = Mage::getModel('core/email_info');
        $emailInfo->addTo($this->getCustomerEmail(), $customerName);
        if ($copyTo && $copyMethod == 'bcc') {
            // Add bcc to customer email
            foreach ($copyTo as $email) {
                $emailInfo->addBcc($email);
            }
        }
        $mailer->addEmailInfo($emailInfo);

        // Email copies are sent as separated emails if their copy method is 'copy'
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $emailInfo = Mage::getModel('core/email_info');
                $emailInfo->addTo($email);
                $mailer->addEmailInfo($emailInfo);
            }
        }

        // Set all required params and send emails
        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
        $mailer->setStoreId($storeId);
        $mailer->setTemplateId($templateId);
        $mailer->setTemplateParams(array(
            'order'        => $this,
            'billing'      => $this->getBillingAddress(),
            'payment_html' => $paymentBlockHtml,
            'totals' => $totals,
            'order_items_html' => $orderItemsHtml,
            'shipping_description_custom' => $this->getShippingDescriptionCustom()
        ));

        /** @var $emailQueue Mage_Core_Model_Email_Queue */
        $emailQueue = Mage::getModel('core/email_queue');
        $emailQueue->setEntityId($this->getId())
            ->setEntityType(self::ENTITY)
            ->setEventType(self::EMAIL_EVENT_NAME_NEW_ORDER)
            ->setIsForceCheck(!$forceMode);

        $mailer->setQueue($emailQueue)->send();

        $emailT = Mage::getModel('core/email_template');
        $emailT->sendTransactional(
            Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $storeId),
            'general',
            $this->getCustomerEmail(),
            $this->getCustomerName(),
            array(
                'order'        => $this,
                'billing'      => $this->getBillingAddress(),
                'payment_html' => $paymentBlockHtml,
                'totals' => $totals,
                'order_items_html' => $orderItemsHtml,
                'shipping_description_custom' => $this->getShippingDescriptionCustom()
            )
        );

        $this->setEmailSent(true);
        $this->_getResource()->saveAttribute($this, 'email_sent');

        return $this;
    }

    /**
     * Get Shipping Description Custom.
     *
     * @return mixed
     */
    public function getShippingDescriptionCustom()
    {
        return str_replace(
            array('Select Shipping Method', ':'),
            array('Selected Shipping Method', ''),
            $this->getShippingDescription()
        );
    }
}