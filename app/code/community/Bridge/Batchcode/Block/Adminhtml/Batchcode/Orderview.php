<?php

/**
 * Bridge Batchcode
 *
 * @category      Bridge
 * @package       Bridge_Batchcode
 * @copyright     Copyright (c) 2013 Bridge India. (http://bridge-india.in)
 * @license       http://bridge-india.in/disclaimer/magento
 */
class Bridge_Batchcode_Block_Adminhtml_Batchcode_Orderview extends Mage_Adminhtml_Block_Template {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('Bridge/batchcode/orderview.phtml');
    }

    /**
     * Get all batchcode of the shipped product
     *
     * @return Batchcode & Expiry date
     */


    public function getBatchcode() {
        $batch = array();
        $item = $this->getData('item_id');
        $collection = Mage::getModel('batchcode/order_item')->getCollection()
                ->addFieldToFilter('item_id', $item)
                ->addFieldToFilter('qty', array("neq" =>0))
                ->addFieldToSelect('batch_id')
                ->addFieldToSelect('id');
        foreach ($collection as $item) {
            $batch[] = Mage::getModel('batchcode/batchcode')->load($item->getBatchId());
        }
        return $batch;
    }
    
    public function getBatchcodeqty() {
        $qty = array();
        $item = $this->getData('item_id');
        $collection = Mage::getModel('batchcode/order_item')->getCollection()
                ->addFieldToFilter('item_id', $item)
                ->addFieldToFilter('qty', array("neq" =>0))
                ->addFieldToSelect('batch_id')
                ->addFieldToSelect('qty')
                ->addFieldToSelect('id');
        foreach ($collection as $item) {
            $qty[] = $item->getQty();
            $batch[] = Mage::getModel('batchcode/batchcode')->load($item->getBatchId());
        }
        return $qty;
    }
    public function getBatchcodeshipqty() {
        $ship_qty = array();
        $item = $this->getData('item_id');
        $collection = Mage::getModel('sales/order_item')->getCollection()
                ->addFieldToFilter('item_id', $item)
                ->addFieldToSelect('qty_shipped')
                ->addFieldToSelect('parent_item_id');
        foreach ($collection as $item) {
            $ship_qty[] = $item->getQtyShipped();
            $product_item_id[] = $item->getParentItemId();
            if($product_item_id)
            {
                foreach ($product_item_id as $parentitemid) {
                    
                    $ship_quantity = Mage::getModel('sales/order_item')->load($parentitemid,'item_id');
                    $product_type = $ship_quantity->getProductType();
                    if($product_type=='configurable')
                    {
                        $ship_qty[] = $ship_quantity->getQtyShipped();
                    }
                  }
                
            }
            } 
            return $ship_qty;
    }

}
