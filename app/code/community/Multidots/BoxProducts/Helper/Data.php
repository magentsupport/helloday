<?php

class Multidots_BoxProducts_Helper_Data extends Mage_Core_Helper_Abstract {

    public function getCartSession() {
        return Mage::getSingleton('checkout/cart')->init();
    }

    public function insertLog($WSMethod, $logData) {
        Mage::log($logData, null, $WSMethod . '.log', true);
    }

    public function getPreOrderText() {
        $label = "Pre Order";
        $preorderLabel = Mage::getStoreConfig('amacart/preorder/preorder_general', Mage::app()->getStore());
        if (is_null($preorderLabel) && empty($preorderLabel)) {
            return $label;
        } else {
            return $preorderLabel;
        }
    }

    public function getCartQuantity($product) {
        $cart = Mage::getModel('checkout/cart')->getQuote();
        $cartItems = $cart->getAllVisibleItems();
        foreach ($cartItems as $item) {
            if ($product->getSku() == $item->getSku()) {
                return $qty = $item->getQty();
            } else {
                return 0;
            }
        }
    }

}
