<?php

class Multidots_CompSignup_IndexController extends Mage_Core_Controller_Front_Action {

    public function IndexAction() {

        $this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Signup"));
        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
        $breadcrumbs->addCrumb("home", array(
            "label" => $this->__("Home Page"),
            "title" => $this->__("Home Page"),
            "link" => Mage::getBaseUrl()
        ));
        $breadcrumbs->addCrumb("signup", array(
            "label" => $this->__("Signup"),
            "title" => $this->__("Signup")
        ));
        $this->renderLayout();
    }

    public function SaveAction() {
        $post_data = $this->getRequest()->getPost();
        if ($post_data) {
            try {
                if (Mage::getModel('customer/session')->getId()) {
                    $customerId = Mage::getModel('customer/session')->getId();
                } else {
                    $customerId = 0;
                }

                $model = Mage::getModel("compsignup/compsignup")
                        ->addData($post_data)
                        ->save();
            } catch (Exception $e) {
                Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
                $this->_redirect("*/*/");
                return;
            }
            $this->_redirect("compsignup/index/success");
        }
        
    }

    public function successAction() {
    	$this->loadLayout();
        $this->getLayout()->getBlock("head")->setTitle($this->__("Request submit Successfully"));
        $this->renderLayout();
    }

}