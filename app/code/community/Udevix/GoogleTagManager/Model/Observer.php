<?php
/**
 * Udevix
 *
 * @author     UdevixTeam <udevix@gmail.com>
 * @copyright  Copyright (c) 2015-2016 Udevix
 */

/**
 * Class Udevix_GoogleTagManager_Model_Observer
 */
class Udevix_GoogleTagManager_Model_Observer
{
    /**
     * Observer for event "checkout_cart_add_product_complete"
     *
     * @param Varien_Event_Observer $observer
     */
    public function cartAddProductComplete($observer)
    {
        $event = $observer->getEvent();

        /**
         * @var Udevix_GoogleTagManager_Helper_Data $helper
         */
        $helper = Mage::helper('udevix_google_tag_manager');

        /**
         * @var Mage_Catalog_Model_Product $product
         */
        $product = $event->getProduct();

        // Get added products
        $products = Mage::getSingleton('core/session')->getGtmCartAddProducts();

        $productArray = array(
            'name'     => $product->getName(),
            'id'       => $product->getSku(),
            'price'    => $helper->getProductPrice($product),
            'category' => $helper->getProductCategory($product),
            'quantity' => intval($product->getCartQty())
        );

        if (is_null($products)) {
            $products = array($productArray);
        } else {
            $products[] = $productArray;
        }

        // Write products to the session
        Mage::getSingleton('core/session')->setGtmCartAddProducts($products);
    }
}
