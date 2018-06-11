<?php

class Multidots_BoxProducts_Block_Boxproducts extends Mage_Catalog_Block_Product_Abstract {

    public function getChildCategoryCollection() {
        try {
            $all_child_categories = Mage::getModel('catalog/category')
                    ->getCollection()
                    ->addAttributeToFilter("parent_id", '7')
                    ->addAttributeToFilter('is_active', 1)
                    ->addAttributeToSort('position', 'asc');
            return $all_child_categories;
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

    public function getCurrentCategory() {
        $categoryId = $this->getRequest()->getParam('id');
        try {
            if (!is_null($categoryId)) {
                $category = Mage::getModel('catalog/category')
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->load($categoryId);
                return $category;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

    public function getCategoryProductCollection($category) {

        try {
            if (!is_null($category)) {
                $productCollection = Mage::getResourceModel('catalog/product_collection')
                        ->addCategoryFilter($category);
                return $productCollection;
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }
    }

}

