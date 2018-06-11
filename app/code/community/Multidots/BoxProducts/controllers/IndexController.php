<?php

class Multidots_BoxProducts_IndexController extends Mage_Core_Controller_Front_Action {
    /*
     * 
     */

    public function addToCartBoxAction() {
        $productId = $this->getRequest()->getParam('id');
        try {
            if (!is_null($productId)) {
                $upsell_product = Mage::helper('boxproducts')->getUpsellProducts($productId);
                if (count($upsell_product) > 0) {
                    $_all_products_instock = 0;
                    $currentDate = Mage::getModel('core/date')->date('m/d/Y', strtotime("-1 days"));
                    foreach ($upsell_product as $product) {
                        $products = Mage::getModel('catalog/product')->load($product->getId());
                        $_inStock = $products->getStockItem()->getIsInStock();
                        $productPreOrderDate = "";
                        $preProDate = $products->getProductDate();
                        if (isset($preProDate) && $preProDate != null) {
                            $productPreOrderDate = date('m/d/Y', strtotime($products->getProductDate()));
                        }
                        if (!$_inStock || ( $productPreOrderDate < $currentDate && $productPreOrderDate != null)) {
                            $_all_products_instock = 1;
                        }
                    }
                    if ($_all_products_instock == 0) {
                        foreach ($upsell_product as $product) {
                            $cart = Mage::getModel('checkout/cart');
                            $cart->init();
                            $products = Mage::getModel('catalog/product')->load($product->getId());
                            $preOrderData = $products->getProductDate();
                            $productPreOrderText = $products->getData('product_pre_order_text');
                            $extraOptions = '';
                            if (!is_null($preOrderData) && !is_null($productPreOrderText)) {
                                $productDate = date('m/d/Y', strtotime($preOrderData));
                                $extraOptions = $productPreOrderText . ' ' . $productDate;
                            }

                            if (!is_null($extraOptions) && !empty($extraOptions)) {
                                $additionalOptions = array();
                                $additionalOptions[] = array(
                                    'label' => Mage::helper('boxproducts')->getPreOrderText(),
                                    'code' => 'additional_options',
                                    'value' => $extraOptions
                                );
                                $products->addCustomOption('additional_options', serialize($additionalOptions));
                            }
                            $cart->addProduct($products, array('qty' => 1));
                            $cart->save();
                            Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                        }
                        Mage::getSingleton('core/session')->addSuccess('Product(s) added successfully');
                        $this->_redirect('checkout/cart');
                    } else {
//                        $products = Mage::getModel('catalog/product')->load($productId);
                        Mage::getSingleton('core/session')->addError('Some of package\'s products are out of stock or pre order date\'s are exceeds.');
                        $this->_redirectUrl(Mage::helper('core/http')->getHttpReferer());
                    }
                }
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

    /*
     * http://site-url.com/boxproducts/index/wellbeinglist/
     * wellbeing List Action
     */

    public function wellbeinglistAction() {
        try {
            $this->loadLayout();
            $this->getLayout()->getBlock('head')
                    ->setTitle('Wellbeing | Caring For Your Health | Hello Day')
                    ->setDescription('The stress of modern living can result in a lack of appetite, libido & sleep, poor digestion, circulation & immunity. Hello Day can help improve your health & wellbeing.');
            $this->renderLayout();
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

    /*
     * http://site-url.com/boxproducts/index/wellbeingview/id/10/
     * wellbeing view Action 
     * @id : Category Id
     */

    public function wellbeingviewAction() {
        $categoryId = $this->getRequest()->getParam('id');
        try {
            $this->loadLayout();
            if (!is_null($categoryId)) {
                $category = Mage::getModel('catalog/category')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($categoryId);
                $this->getLayout()->getBlock('head')
                        ->setTitle($category->getMetaTitle())
                        ->setKeywords($category->getMetaKeywords())
                        ->setDescription($category->getMetaDescription());
                $this->renderLayout();
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

    public function deleteallAction() {
        try {
            $cartHelper = Mage::helper('checkout/cart');
            $items = $cartHelper->getCart()->getItems();
            foreach ($items as $item) {
                $itemId = $item->getItemId();
                $cartHelper->getCart()->removeItem($itemId)->save();
            }
            $this->_redirectUrl(Mage::helper('core/http')->getHttpReferer());
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

}