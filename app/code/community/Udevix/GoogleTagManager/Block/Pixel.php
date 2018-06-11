<?php
/**
 * Udevix
 *
 * @author     UdevixTeam <udevix@gmail.com>
 * @copyright  Copyright (c) 2015-2016 Udevix
 */

/**
 * Class Udevix_GoogleTagManager_Block_Pixel
 */
class Udevix_GoogleTagManager_Block_Pixel extends Mage_Core_Block_Template
{
    /**
     * Return event data
     *
     * @return bool|array
     */
    public function getEventData()
    {
        $result = array(
            'event' => '',
            'data'  => array()
        );

        // Get type of opened page
        $pageType = $this->getPageType();

        /**
         * @var Udevix_GoogleTagManager_Helper_Data $helper
         */
        $helper = Mage::helper('udevix_google_tag_manager');

        // Generate data by page type
        switch ($pageType) {
            case 'product':
                /**
                 * @var Mage_Catalog_Model_Product $product
                 */
                $product = Mage::registry('current_product');
                $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

                $result = json_encode(array(
                    'event'                => 'udxViewContent',
                    'udx_value'            => $helper->getProductPrice($product),
                    'udx_currency'         => $currencyCode,
                    'udx_content_name'     => $product->getName(),
                    'udx_content_type'     => 'product',
                    'udx_content_category' => $helper->getProductCategory($product),
                    'udx_content_ids'      => array($product->getSku()),
                ));

                break;

            case 'initiate_checkout':
                /**
                 * @var Mage_Sales_Model_Quote $quote
                 */
                $quote = Mage::getSingleton('checkout/cart')->getQuote();
                $grandTotal = $quote->getGrandTotal();
                $products = $quote->getAllItems();
                $ids = array();

                /**
                 * @var Mage_Catalog_Model_Product $item
                 */
                foreach ($products as $item) {
                    $ids[] = $item->getSku();
                }

                $ids = array_unique($ids);

                if (empty($ids)) {
                    return false;
                }

                $currencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();

                $result = json_encode(array(
                    'event'            => 'udxInitiateCheckout',
                    'udx_value'        => round($grandTotal, 2),
                    'udx_currency'     => $currencyCode,
                    'udx_content_type' => 'product',
                    'udx_content_ids'  => $ids,
                ));

                break;

            case 'purchase':
                $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();

                if ($orderId) {
                    /**
                     * @var Mage_Sales_Model_Order $order
                     */
                    $order = Mage::getModel('sales/order')->loadByAttribute('increment_id', $orderId);
                    $items = $order->getAllItems();

                    $ids = array();

                    foreach ($items as $item) {
                        $ids[] = $item->getProductId();
                    }

                    $products = Mage::getModel('catalog/product')
                        ->getCollection()
                        ->addAttributeToSelect('sku')
                        ->addIdFilter($ids);

                    $ids = array();

                    /**
                     * @var Mage_Catalog_Model_Product $item
                     */
                    foreach ($products as $item) {
                        $ids[] = $item->getSku();
                    }

                    $ids = array_unique($ids);

                    if (empty($ids)) {
                        return false;
                    }

                    $result = json_encode(array(
                        'event'            => 'udxPurchase',
                        'udx_value'        => round($order->getGrandTotal(), 2),
                        'udx_currency'     => $order->getOrderCurrencyCode(),
                        'udx_content_type' => 'product',
                        'udx_content_ids'  => $ids,
                    ));
                }

                break;

            default:
                return false;
        }

        return $result;
    }
}
