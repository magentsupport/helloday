<?php

class Helloday_Campaign_IndexController extends Mage_Core_Controller_Front_Action 
{
    const XML_PATH_EMAIL_IDENTITY   = 'sales_email/order/identity';

    public function IndexAction() 
    {
        $this->norouteAction();
    }

    public function FeelbettersatisfactionAction() 
    {
        $group_ids = array('5','6','7');
        if(Mage::getSingleton('customer/session')->isLoggedIn() && in_array(Mage::getSingleton('customer/session')->getCustomer()->getGroupId(), $group_ids)) {
                $order_id = $this->getRequest()->getParam('order_id');
                //$customer = Mage::getSingleton('customer/session')->getCustomer();
                $order = Mage::getModel('sales/order')->load($order_id);
            
                if($order->getData()) {

                    $campaignOrder = Mage::getModel('campaign/campaignlog')->getCollection();
                    $campaignOrder->addFieldToFilter('order_id', $order->getIncrementId());
                    $campaignOrder->addFieldToFilter('customer_id', $order->getCustomerId());
                    //$campaignOrder->addFieldToFilter('customer_group_id', array('eq' => 4));
                    //$campaignOrder->addFieldToFilter('order_return', 0);
                    $campaignOrderObj = $campaignOrder->getFirstItem();
                   
                    if($campaignOrderObj->getData('order_return') == 0) {

                    $vars = array(
                        'cust_firstname' => $order->getCustomerFirstname(),
                        'cust_lastname'  => $order->getCustomerLastname(),
                        'cust_email' => $order->getCustomerEmail(),
                        'orderno' => $order->getIncrementId(),
                        'orderdate' => $order->getCreatedAt()
                    );

                    try {
                       
                        $storeId = Mage::app()->getStore()->getId();
                        $templateId = 'order_return_email_template';

                        $mailer = Mage::getModel('core/email_template_mailer');
                        
                        $emailInfo = Mage::getModel('core/email_info');
                        
                        $emailInfo->addTo("nicolas.guiraud@hello-day.com", "Nicolas Guiraud");
                        $emailInfo->addTo("ness.dinger@hello-day.com", "Ness Dinger");

                        $emailInfo->addBcc("magento.support@multidots.com", "Magento Support");

                        $mailer->addEmailInfo($emailInfo);

                        // Set all required params and send emails
                        $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $storeId));
                        $mailer->setStoreId($storeId);
                        $mailer->setTemplateId($templateId);
                        $mailer->setTemplateParams($vars);

                        $mailer->send();

                        
                        //$campaignOrderObj->setOrderReturn(1);
                        $campaignOrderObj->setData('order_return', 1);
                        $campaignOrderObj->save();

                        
                    } catch (Exception $e) {
                        Mage::log($e->getMessage(), null, 'campaign.log', true);
                    }
                    }
                }
                $this->_redirect('feel-better-satisfaction-guarantee');
        } else {
            $this->norouteAction();
        }
        return;
    }
}