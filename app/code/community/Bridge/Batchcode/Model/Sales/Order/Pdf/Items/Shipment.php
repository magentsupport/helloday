<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales Order Shipment Pdf items renderer
 *
 * @category   Mage
 * @package    Mage_Bundle
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Bridge_Batchcode_Model_Sales_Order_Pdf_Items_Shipment extends Mage_Bundle_Model_Sales_Order_Pdf_Items_Shipment
{
    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();

        $this->_setFontRegular();

        $shipItems = $this->getChilds($item);
        $items = array_merge(array($item->getOrderItem()), $item->getOrderItem()->getChildrenItems());

        $_prevOptionId = '';
        $drawItems = array();

        foreach ($items as $_item) {
            $line   = array();

            $attributes = $this->getSelectionAttributes($_item);
            if (is_array($attributes)) {
                $optionId   = $attributes['option_id'];
            }
            else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = array(
                    'lines'  => array(),
                    'height' => 15
                );
            }

            if ($_item->getParentItem()) { 
                if ($_prevOptionId != $attributes['option_id']) {
                    $line[0] = array(
                        'font'  => 'italic',
                        'text'  => Mage::helper('core/string')->str_split($attributes['option_label'], 60, true, true),
                        'feed'  => 60
                    );

                    $drawItems[$optionId] = array(
                        'lines'  => array($line),
                        'height' => 15
                    );

                    $line = array();

                    $_prevOptionId = $attributes['option_id'];
                }
            }

            if (($this->isShipmentSeparately() && $_item->getParentItem())
                || (!$this->isShipmentSeparately() && !$_item->getParentItem())
            ) {
                if (isset($shipItems[$_item->getId()])) {
                    $qty = $shipItems[$_item->getId()]->getQty()*1;
                } else if ($_item->getIsVirtual()) {
                    $qty = Mage::helper('bundle')->__('N/A');
                } else {
                    $qty = 0;
                }
            } else {
                $qty = '';
            }

            $line[] = array(
                'text'  => $qty,
                'feed'  => 35
            );
            
            // draw Name
            if ($_item->getParentItem()) {
                $feed = 65;
                $name = $this->getValueHtml($_item);
                // draw Batch Code
            $order_item = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
            $type = $order_item->getProductType();
            
            $prd_id = Mage::getModel('sales/order_item')->load($order_item->getId(),'parent_item_id');
                $productId = $prd_id->getProductId();
                $itemid = $prd_id->getId();
                
                $collection = Mage::getModel('batchcode/order_item')->getCollection()
                ->addFieldToFilter('item_id', $_item->getItemId())
                ->addFieldToFilter('qty', array("neq" =>0))
                ->addFieldToSelect('batch_id')
                ->addFieldToSelect('id');
                //print_r($collection);die;
            
        $count = count($collection);
        if($count!=0){ 

                $batchid=array();
                $batchcode=array();
   
                foreach($collection as $slips){ 
                       $batchid[]=$slips->getBatchId();
                   }
                   
                    foreach($batchid as &$batchh) {
                        
                        $no = $batchh;
                        $batch = Mage::getModel('batchcode/batchcode')->load($no);
                        $product = $batch->getBatchNumber();
                        $batchcode[] = $product;
                       } 
                                                               
                     
                     $batchs = implode(', ',$batchcode);
                     $batchcode = 'Batchcodes : '.$batchs;
        } else {
            $batchcode = 'No Batchcodes';
            
        }
                
            } else {
                $feed = 60;
                $name = $_item->getName();
                
            }
           
                 
            $text = array();
            foreach (Mage::helper('core/string')->str_split($name, 60, true, true) as $part) {
                $text[] = $part;
            }
            if(!empty($batchcode)){
               foreach (Mage::helper('core/string')->str_split($batchcode, 60, true, true) as $part) {
                $text[] = $part;
            }
            }
            
            $line[] = array(
                'text'  => $text,
                'feed'  => $feed
            );
            
            

            // draw SKUs
            $text = array(); 
            foreach (Mage::helper('core/string')->str_split($_item->getSku(), 25) as $part) {
                $text[] = $part;
            }
            $line[] = array(
                'text'  => $text,
                'feed'  => 565,
                'align' => 'right'
            );

            $drawItems[$optionId]['lines'][] = $line;
        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = array();
                    $lines[][] = array(
                        'text'  => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
                        'font'  => 'italic',
                        'feed'  => 110
                    );

                    if ($option['value']) {
                        $text = array();
                        $_printValue = isset($option['print_value'])
                            ? $option['print_value']
                            : strip_tags($option['value']);
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            foreach (Mage::helper('core/string')->str_split($value, 50, true, true) as $_value) {
                                $text[] = $_value;
                            }
                        }

                        $lines[][] = array(
                            'text'  => $text,
                            'feed'  => 115
                        );
                    }

                    $drawItems[] = array(
                        'lines'  => $lines,
                        'height' => 15
                    );
                }
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, array('table_header' => true));
        $this->setPage($page);
    }
}
