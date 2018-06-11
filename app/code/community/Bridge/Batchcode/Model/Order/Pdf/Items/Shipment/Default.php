<?php

/**
 * Bridge Batchcode
 *
 * @category      Bridge
 * @package       Bridge_Batchcode
 * @copyright     Copyright (c) 2013 Bridge India. (http://bridge-india.in)
 * @license       http://bridge-india.in/disclaimer/magento
 */
class Bridge_Batchcode_Model_Order_Pdf_Items_Shipment_Default extends Mage_Sales_Model_Order_Pdf_Items_Shipment_Default {

    /**
     * Draw item line
     */
    public function draw() {
        $item = $this->getItem();

        $pdf = $this->getPdf();
        $page = $this->getPage();

        $lines = array();
        //echo $item->getName();die;
        
        if ($item->getName()) {
                $feed = 65;
                $name = $item->getName();
                
                // draw Batch Code
                $order_item = Mage::getModel('sales/order_item')->load($item->getOrderItemId());
                $type = $order_item->getProductType();

                if($type=='configurable')
                {
                    $prd_id = Mage::getModel('sales/order_item')->load($order_item->getId(),'parent_item_id');
                    $productId = $prd_id->getProductId();
                    $itemid = $prd_id->getId();

                    $collection = Mage::getModel('batchcode/order_item')->getCollection()
                    ->addFieldToFilter('item_id', $itemid)
                    ->addFieldToFilter('qty', array("neq" =>0))
                    ->addFieldToSelect('batch_id')
                    ->addFieldToSelect('id');
                }else {
                    $collection = Mage::getModel('batchcode/order_item')->getCollection()
                    ->addFieldToFilter('item_id', $item['order_item_id'])
                    ->addFieldToFilter('qty', array("neq" =>0))
                    ->addFieldToSelect('batch_id')
                    ->addFieldToSelect('id');
                }
                $count = count($collection);
                
              if($count!=0){ 
                  
                  $test=array();
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
                                          
                    
            }else{ 

                $batchcode = 'No Batchcodes';

            }
        }
        $text = array();
            foreach (Mage::helper('core/string')->str_split($name, 60, true, true) as $part) {
                $text[] = $part;
            }
               foreach (Mage::helper('core/string')->str_split($batchcode, 60, true, true) as $part) {
                $text[] = $part;
            }
            
          // draw Product name
        $lines[0] = array(array(
                'text' => $text,
                'feed' => $feed
        ));
                

        // draw QTY
        $lines[0][] = array(
            'text' => $item->getQty() * 1,
            'feed' => 35
        );

        
        // draw SKU
        $lines[0][] = array(
            'text' => Mage::helper('core/string')->str_split($this->getSku($item), 25),
            'feed' => 565,
            'align' => 'right'
        );

        // Custom options
        $options = $this->getItemOptions(); 
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), 70, true, true),
                    'font' => 'italic',
                    'feed' => 110
                );

                // draw options value
                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, 50, true, true),
                            'feed' => 115
                        );
                    }
                }
            }
        }

        $lineBlock = array(
            'lines' => $lines,
            'height' => 20
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}