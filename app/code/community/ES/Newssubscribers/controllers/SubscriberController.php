<?php

include_once('Mage/Newsletter/controllers/SubscriberController.php');

class ES_Newssubscribers_SubscriberController extends Mage_Newsletter_SubscriberController {

    public function newAjaxAction() {
        $session = Mage::getSingleton('core/session');
        if ($this->getRequest()->isPost() && $this->getRequest()->getPost('email')) {
            $customerSession = Mage::getSingleton('customer/session');
            $email = (string) $this->getRequest()->getPost('email');
            $name  = (string) $this->getRequest()->getPost('first_name');

            try {

                if ( !Zend_Validate::is($name, 'NotEmpty')|| !Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Name and email is required for subscription.'));
                }
                if (!Zend_Validate::is($email, 'EmailAddress')) {
                    Mage::throwException($this->__('Please enter a valid email address.'));
                }

                if (Mage::getStoreConfig(Mage_Newsletter_Model_Subscriber::XML_PATH_ALLOW_GUEST_SUBSCRIBE_FLAG) != 1 &&
                        !$customerSession->isLoggedIn()) {
                    Mage::throwException($this->__('Sorry, but administrator denied subscription for guests. Please <a href="%s">register</a>.', Mage::helper('customer')->getRegisterUrl()));
                }

                $ownerId = Mage::getModel('customer/customer')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($ownerId !== null && $ownerId != $customerSession->getId()) {
                    Mage::throwException($this->__('This email address already exists'));
                }

                $subscriberId = Mage::getModel('newsletter/subscriber')
                        ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                        ->loadByEmail($email)
                        ->getId();
                if ($subscriberId !== null)
                    Mage::throwException($this->__('This email address already exists'));

                $status = Mage::getModel('newsletter/subscriber')->subscribe($email);
                if ($status == Mage_Newsletter_Model_Subscriber::STATUS_NOT_ACTIVE) {
                    $session->addSuccess($this->__('Confirmation request has been sent.'));
                } else {
                    $session->addSuccess($this->__('for subscribing'));
                }
            } catch (Mage_Core_Exception $e) {
                $session->addException($e, $this->__('%s', $e->getMessage()));
            } catch (Exception $e) {
                $session->addException($e, $this->__('There was a problem with the subscription.'));
            }
        }

        $messages = $session->getMessages(true);
        $errors = $messages->getErrors();
        $response = array(
            'errorMsg' => '',
            'successMsg' => ''
        );

        if ($errors) {
            $response['errorMsg'] = $errors[0]->getText();
        } else {
            $success = $messages->getItemsByType('success');
            $response['successMsg'] = $success[0]->getText();
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
    }

}
