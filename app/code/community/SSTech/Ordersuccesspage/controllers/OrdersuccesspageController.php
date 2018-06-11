<?php
class SSTech_Ordersuccesspage_OrdersuccesspageController extends Mage_Core_Controller_Front_Action
{
	public function printAction() {
		$orderId = (int) $this->getRequest()->getParam('id');
		$order = Mage::getModel('sales/order')->load($orderId);			
		if ($order->getId())	{
			Mage::register('current_order', $order);	
	 		$this->loadLayout('print');  
			$block_info=$this->getLayout()->createBlock('sales/order_info')->setTemplate('ordersuccesspage/info.phtml');
			$block_items=$this->getLayout()->createBlock('sales/order_items')->setTemplate('ordersuccesspage/items.phtml'); 
			$this->getLayout()->getBlock('content')->append($block_info);
			$this->getLayout()->getBlock('content')->append($block_items);
			$this->renderLayout();
			return true;
		}else {
			$this->_forward('noRoute');
		}
	}
}