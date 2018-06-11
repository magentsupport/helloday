<?php

class Helloday_Campaign_Model_Observer
{

	public function campaignOrderValidation(Varien_Event_Observer $observer)
	{
		$order = $observer->getEvent()->getOrder();

		if(Mage::getStoreConfig('immunitycampaign/promo/enable', $order->getStoreId())) {
		
		$immunity_qty_ordered = 0; 
		$banch_code = 0;
		$other_item_count = 0;
		$box_product_count = 0;
		$carton_box_qty = 1;

		$box_products = Mage::helper('campaign')->getBoxProducts();

		foreach($order->getAllItems() as $item) {
		    //check for immunity defence order items
		    if($item->getProduct()->getSku() == '11100SA-IMMUN-30')
		    {
		        $immunity_qty_ordered = $item->getQtyOrdered();
		    } else {
		        $other_item_count = 1;
		    }

		    //check for box produts in order
		    if(in_array($item->getProduct()->getSku(), $box_products)) {
		        $box_product_count = $box_product_count + $item->getQtyOrdered();
		    }
		}

		if($order->getCustomerGroupId() == 1 && $immunity_qty_ordered) {
		    if($immunity_qty_ordered > 3 || ($immunity_qty_ordered <= 3 && $other_item_count)) {
		        $banch_code = 2;
		    } else {
		        $banch_code = 1;
		    }

		    $promo_used = 1;
		    $change_group_id = 5;
		    $this->addCampaignLog($promo_used, $change_group_id, $order);
		}

		if($order->getCouponCode() == 'IMMUNITY20') {
		     if($immunity_qty_ordered > 3 || ($immunity_qty_ordered <= 3 && $other_item_count)) {
		        $banch_code = 4;
		    } else {
		        $banch_code = 3;
		    }

		    $promo_used = 2;
		    $change_group_id = 6;
		    $this->addCampaignLog($promo_used, $change_group_id, $order);
		}

		if($order->getCouponCode() == 'VITALITY10' || $order->getCouponCode() == 'BOX10') {
		    $promo_used = 3;
		    $change_group_id = 7;
		    $this->addCampaignLog($promo_used, $change_group_id, $order);
		}

		$banchProducts = Mage::helper('campaign')->getBanchProducts();

		if($banch_code) {
			$banchcodeproducts = $banchProducts[$banch_code];

			foreach($banchcodeproducts as $product)
			{
				$orderItem = Mage::getModel('sales/order_item')
			        ->setStoreId($order->getStore()->getStoreId())
			        ->setQuoteItemId(NULL)
			        ->setQuoteParentItemId(NULL)
			        ->setProductId($product['product_id'])
			        ->setProductType('simple')
			        ->setQtyBackordered(NULL)
			        ->setTotalQtyOrdered($product['qty'])
			        ->setQtyOrdered($product['qty'])
			        ->setName($product['product_name'])
			        ->setSku($product['sku'])
			        ->setPrice(0)
			        ->setBasePrice(0)
			        ->setOriginalPrice(0)
			        ->setRowTotal(0)
			        ->setBaseRowTotal(0)
			        ->setOrder($order);
				$orderItem->save();
			}
		}

		if($box_product_count) {
			if($box_product_count > 2) {
				if($box_product_count % 2) {
					$box_product_count++;	
				} 
				$carton_box_qty = $box_product_count / 2;
			}

			$banchcodeproducts = Mage::helper('campaign')->getBoxBanchProducts($carton_box_qty);

			foreach($banchcodeproducts as $product)
			{
				$orderItem = Mage::getModel('sales/order_item')
			        ->setStoreId($order->getStore()->getStoreId())
			        ->setQuoteItemId(NULL)
			        ->setQuoteParentItemId(NULL)
			        ->setProductId($product['product_id'])
			        ->setProductType('simple')
			        ->setQtyBackordered(NULL)
			        ->setTotalQtyOrdered($product['qty'])
			        ->setQtyOrdered($product['qty'])
			        ->setName($product['product_name'])
			        ->setSku($product['sku'])
			        ->setPrice(0)
			        ->setBasePrice(0)
			        ->setOriginalPrice(0)
			        ->setRowTotal(0)
			        ->setBaseRowTotal(0)
			        ->setOrder($order);
				$orderItem->save();
			}
		}
	}	
	}

	public function addCampaignLog($promo_used, $change_group_id, $order)
	{
		$customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
	    $customer->setData('group_id', $change_group_id); 
        $customer->save();

        $campaignlog = Mage::getModel('campaign/campaignlog');
        $campaignlog->setCustomerId($order->getCustomerId());
        $campaignlog->setOrderId($order->getIncrementId());
        $campaignlog->setPromoUsed($promo_used);
        $campaignlog->setCouponCode($order->getCouponCode());
        $campaignlog->setCustomerGroupId($change_group_id);
        $campaignlog->setOrderDate(NOW());
        $campaignlog->save();
	}
}
