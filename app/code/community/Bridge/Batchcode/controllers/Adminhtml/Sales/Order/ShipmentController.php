<?php

/**
 * Bridge Batchcode
 *
 * @category      Bridge
 * @package       Bridge_Batchcode
 * @copyright     Copyright (c) 2013 Bridge India. (http://bridge-india.in)
 * @license       http://bridge-india.in/disclaimer/magento
 */
require_once 'Mage/Adminhtml/controllers/Sales/Order/ShipmentController.php';
class Bridge_Batchcode_Adminhtml_Sales_Order_ShipmentController extends Mage_Adminhtml_Sales_Order_ShipmentController
{
    
    /**
     * Save shipment
     * We can save only new shipment. Existing shipments are not editable
     *
     * @return null
     */
    public function saveAction()
    {
        $data = $this->getRequest()->getPost('shipment');
        //condition
        $items = $data['items'];
        $totals = $data['total'];
        foreach ($totals as $itemid=>$free_qty)
        {
                   $simple_item = Mage::getModel('sales/order_item')->load($itemid,'item_id');
                   $parent_item_id = $simple_item->getParentItemId();
                   if($parent_item_id)
                   {
                     $parent_item = Mage::getModel('sales/order_item')->load($parent_item_id,'item_id');
                     $parent_type = $parent_item->getProductType();
                     if($parent_type=='bundle')
                     {
                    $t = $simple_item->getProductOptions();  
                    $s = $t['bundle_selection_attributes'];
                    if($s)
                    {
                    $p = unserialize($s);
                    }
		   if(sizeof($p))
		   {
                         $unit_qty =$p['qty'];
			 $data['items'][$itemid] = $data['items'][$parent_item_id]*$unit_qty;   
		   }  
                     }
                     else if($parent_type=='configurable')
                     {
                         $data['items'][$itemid] = $data['items'][$parent_item_id]; 
                     }
                   }
                   if(isset($data['batch_id'][$itemid]))
                   {
                  $selectedbatches = $data['batch_id'][$itemid];
                   }
                  $qty_to_ship = $data['items'][$itemid];
                  $total_qty   = $data['total'][$itemid];
                  $sum = $total_qty;
              if(isset($selectedbatches))
                {
                if(count($selectedbatches))
                    { 
                    foreach ($selectedbatches as $batch_id)
                        {
                        $batchdetails =  Mage::getModel('batchcode/batchcode')->load($batch_id);
                        $available_batch_qty = $batchdetails->getQty();
                        $sum+= $available_batch_qty;
                        }
                    }
                }
                   if($qty_to_ship <= $sum)
                        {
                            continue;
                        }
                        else 
                        {
                            $this->_getSession()->addError($this->__('Not Enough Quantity to ship.'));
                            $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
                            return false;
                        } 
        }
        //end condition
        
        
        if (!empty($data['comment_text'])) {
            Mage::getSingleton('adminhtml/session')->setCommentText($data['comment_text']);
        }

        try {
            $shipment = $this->_initShipment();
            if (!$shipment) {
                $this->_forward('noRoute');
                return;
            }

            $shipment->register();
            $comment = '';
            if (!empty($data['comment_text'])) {
                $shipment->addComment(
                    $data['comment_text'],
                    isset($data['comment_customer_notify']),
                    isset($data['is_visible_on_front'])
                );
                if (isset($data['comment_customer_notify'])) {
                    $comment = $data['comment_text'];
                }
            }

            if (!empty($data['send_email'])) {
                $shipment->setEmailSent(true);
            }

            $shipment->getOrder()->setCustomerNoteNotify(!empty($data['send_email']));
            $responseAjax = new Varien_Object();
            $isNeedCreateLabel = isset($data['create_shipping_label']) && $data['create_shipping_label'];

            if ($isNeedCreateLabel && $this->_createShippingLabel($shipment)) {
                $responseAjax->setOk(true);
            }

            $this->_saveShipment($shipment);

            Mage::dispatchEvent('sales_order_shipment_save_custom', array(
                'post'             => $data,
                'shipment'            => $shipment,
            ));


            $shipment->sendEmail(!empty($data['send_email']), $comment);

            $shipmentCreatedMessage = $this->__('The shipment has been created.');
            $labelCreatedMessage    = $this->__('The shipping label has been created.');

            $this->_getSession()->addSuccess($isNeedCreateLabel ? $shipmentCreatedMessage . ' ' . $labelCreatedMessage
                : $shipmentCreatedMessage);
            Mage::getSingleton('adminhtml/session')->getCommentText(true);
        } catch (Mage_Core_Exception $e) {
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage($e->getMessage());
            } else {
                $this->_getSession()->addError($e->getMessage());
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }
        } catch (Exception $e) {
            Mage::logException($e);
            if ($isNeedCreateLabel) {
                $responseAjax->setError(true);
                $responseAjax->setMessage(
                    Mage::helper('sales')->__('An error occurred while creating shipping label.'));
            } else {
                $this->_getSession()->addError($this->__('Cannot save shipment.'));
                $this->_redirect('*/*/new', array('order_id' => $this->getRequest()->getParam('order_id')));
            }

        }
        if ($isNeedCreateLabel) {
            $this->getResponse()->setBody($responseAjax->toJson());
        } else {
            $this->_redirect('*/sales_order/view', array('order_id' => $shipment->getOrderId()));
        }
    
    
    }
}
