<?php

class Bridge_Batchcode_Model_Product_Api extends Mage_Api_Model_Resource_Abstract
{
	public function batchcode_api($ship_increment_id)
	{

		$collection = Mage::getResourceModel('sales/order_shipment_collection');

		$collection->addAttributeToFilter('increment_id', array('eq' => $ship_increment_id));

		

			foreach ($collection as $order) {
		      $items=array();

		      

		      	foreach ($order->getAllItems() as $item) {

			        $batches = array();

					$itemid = $item->getOrderItemId();

					$shipment = Mage::getModel('batchcode/order_item')->getCollection()

	                			->addFieldToFilter('item_id', $itemid);



	                $batche_name = array();

					foreach ($shipment as $batch) {

						$batches[]= $batch->getBatchId();



						$batchId = $batch->getBatchId();

						

						$batches[] = Mage::getModel('batchcode/batchcode')->load($batchId,'entity_id');

						$_batch = Mage::getModel('batchcode/batchcode')->getCollection()

	                			->addFieldToFilter('entity_id', $batchId);

							

							foreach ($_batch as $batchs) {

								$batche_name[]= $batchs->getBatchNumber();

							}

					}

			        $items[] = array(

			            'id'            => $order->getIncrementId(),

			            'name'          => $item->getName(),

			            'sku'           => $item->getSku(),

			            'Price'         => $item->getPrice(),

			            'Ordered Qty'   => $item->getQty(),

			            'Product Id'   	=> $item->getProductId(),

			            'Order Item Id' => $item->getOrderItemId(),

			            'Shipment Id' 	=> $order->getEntityId(),

			            'Batchcodes' 	=> $batche_name,

			            

			        );

		    	}

		        $orders['items'] = array(

		            'increment_id'  => $order->getIncrementId(),

		            'store'         => $order->getStoreId(),

		            'created_at'	=> $order->getCreatedAt(),

		            'updated_at'	=> $order->getUpdatedAt(),

		            'shipping_address_id' => $order->getShippingAddressId(),

		            'Order Id'      => $order->getOrderId(),

		            'total_qty'     => $order->getTotalQty(),

		            'items'        	=> $items,

		        );

		    }

		return $orders;

	}



	public function batchcode($shipId,$sku,$batchCode,$templateId)

	{

		$batch_Id = Mage::getModel('batchcode/batchcode')->load($batchCode,'batch_number');

		$batchId = $batch_Id->getEntityId();

		

		$orderId = Mage::getModel('sales/order_shipment')->loadByIncrementId( $shipId )->getOrderId();



		$product_id = Mage::getModel("catalog/product")->getIdBySku( $sku );



		$collection = Mage::getModel('batchcode/order_item')->getCollection()

                ->addFieldToFilter('order_id', $orderId)

                ->addFieldToFilter('product_id', $product_id)

                ->addFieldToFilter('batch_id', $batchId)

                ;



        $count = count($collection);

        if($sku == '' && $count == 0){

        	$status = 'Please add Product SKU';

        }elseif ($shipId == '' && $count == 0) {

        	$status = 'Please add Shipment Id';

        }elseif ($batchId == '' && $count == 0) {

        	$status = 'Please add Batch Id';

        }



        if($sku != '' && $shipId != '' && $batchId != '' && $count != 0)

        {

        	$status = 'Batch code exist';

        }elseif($sku != '' && $shipId != '' && $batchId != '' && $count == 0) {

        	$status = 'Batch code not exist';

        }

        

        if ($templateId != '') {

        	$sender = Array('name' => 'Hello-day',

				'email' => 'nicolas.guiraud@hello-day.com');

			//recepient

			$email = Mage::getStoreConfig('catalog/batch/emailaddress');

			$emaiName = Mage::getStoreConfig('catalog/batch/emailname');

			$vars = Array();

			$vars = Array(

				'status'=>$status,

				'Shipment_Id'=>$shipId,

				'Batch_Id'=>$batchId,

				'Sku'=>$sku

			);

			$storeId = Mage::app()->getStore()->getId();

			$translate = Mage::getSingleton('core/translate');

			Mage::getModel('core/email_template')

			->sendTransactional($templateId, $sender, $email, $emailName, $vars, $storeId);

			$translate->setTranslateInline(true);  

        }else{

        	$status = 'Please add the Email Template Id';

        }			 

        return $status;

	}

	public function batchcodeAssign($itemsQty,$shipmentId,$templateID)
	{
		if ($itemsQty != '' && $shipmentId != '') {
			
			$orderId = Mage::getModel('sales/order_shipment')->loadByIncrementId( $shipmentId )->getOrderId();
			
			$BatchStatus = array();
			$batchesSuccess = array();
			$batchesFailed = array();
			foreach ($itemsQty as $item) {

				$BatchCode = $item['BatchCode'];
				$productId = $item['ProductId'];
				$qty_waiting = $item['Qty'];
				$itemid = $item['ItemId'];

				$batchdetails = Mage::getModel('batchcode/batchcode')->load($BatchCode,'batch_number');
				$batchid = $batchdetails['entity_id'];
				$available_qty = $batchdetails['qty'];

				if ($qty_waiting > 0 && $available_qty > 0) {
	                if ($qty_waiting > $available_qty) {
	                    $shipped_qty = $available_qty;
	                } else {
	                    $shipped_qty = $qty_waiting;
	                }
	                $finalqty = $available_qty - $shipped_qty;
	                $status = ($finalqty ? 1 : 0);
	                $batchdetails
	                    ->setQty($finalqty)
	                    ->setOnsales($status)
	                    ->setEnabled($status)
	                    ->save();
	        		$batchorder_item = Mage::getModel('batchcode/order_item');
	                $batchorder_item
	                    ->setItemId($itemid)
	                    ->setOrderId($orderId)
	                    ->setBatchId($batchid)
	                    ->setProductId($productId)
	                    ->setQty($shipped_qty)
	                    ->save();
		            if($batchorder_item->getId()){
					    $BatchStatus[] = 'Batch Code Saved Successfully : '.$BatchCode;
					    $batchesSuccess[] = $BatchCode;
					} else {
					    $BatchStatus[] = 'Problem in saving Batch Code : '.$BatchCode;
					    $batchesFailed[] = $BatchCode;
					}      
	            }else{
	            	$BatchStatus = 'Problem in saving Batch Code : '.$BatchCode;
	            }
			}
			if ($batchesSuccess != '') {
				$B_Status = implode(", ",$batchesSuccess);
				$B_Status = 'Batch Code Saved Successfully : '.$B_Status;
			}
			else{
				$B_Status = '';
			}
			if (count($batchesFailed) != '') {
				$B_StatusF = implode(", ",$batchesFailed);
				$B_StatusF = 'Problem in saving Batch Code : '.$B_StatusF;
			}else{
				$B_StatusF = '';
			}
			if ($templateID != '') {
	        	$sender = Array('name' => 'Hello-day',
					'email' => 'nicolas.guiraud@hello-day.com');
				$email = Mage::getStoreConfig('catalog/batch/emailaddress');//recepient
				$emaiName = Mage::getStoreConfig('catalog/batch/emailname');
				$vars = Array();
				$vars = Array(
					'status'=> $B_Status,
					'Failed'=> $B_StatusF,
					'Shipment_Id'=>$shipmentId
				);
				$storeId = Mage::app()->getStore()->getId();
				$translate = Mage::getSingleton('core/translate');
				Mage::getModel('core/email_template')
				->sendTransactional($templateID, $sender, $email, $emailName, $vars, $storeId);
				$translate->setTranslateInline(true);  
	        }else{
	        	$BatchStatus = 'Please add the Email Template Id';
	        }
		} else {
			$BatchStatus = 'Problem in saving Batch Code : '.$BatchCode;
		}
		
        return $BatchStatus;
	}

	public function getValue($key)
    {
    	$storage_json = Mage::getModuleDir('Model', 'Bridge_Batchcode');
        $storage_file = $storage_json. '/model/storage.json';
        $storage = json_decode(file_get_contents($storage_file), true);
        if (array_key_exists($key, $storage)) { 
            return $storage[$key];
        }
        return null;
    }

    /**
     * Function to persist some data for the example
     * @param string $key
     * @param string $value
     */
    public function setValue($key, $value)
    {
        $storage_json = Mage::getModuleDir('Model', 'Bridge_Batchcode');
        $storage_file = $storage_json. '/model/storage.json';
        $storage       = json_decode(file_get_contents($storage_file), true);
        $storage[$key] = $value;
        file_put_contents($storage_file, json_encode($storage));
    }

	public function shipmentConnector($order_id)
	{
		$order = Mage::getModel('sales/order')->load($order_id);
		$orderData = $order->getData();
		$state = $orderData['status'];
        
        // if($state == "processing" || $state == "complete"){

			// Create the Exact client
            $connection = $this->connect();

            // Get the customers from exact
            try {
            	$exactOrder = new \Picqer\Financials\Exact\SalesOrder($connection);
            	$orders = $exactOrder->filter("OrderNumber eq $order_id");

                foreach($orders as $_order)
                        $OrderID    = $_order->OrderID;
                    	$c_guid    = $_order->OrderedBy;

                    	$orderItems = $order->getAllItems();
                        $products = array();
                        foreach ($orderItems as $_item) {
                            $code = $_item->getSku();
                            $Type = $_item->getProductType();
                            if( $code != '' && $Type == 'simple' ){
                                $item = new \Picqer\Financials\Exact\Item($connection);
                                $E_item = $item->filter("trim(Code) eq '$code'");
                                    foreach($E_item as $items)
                                        $item_id        = $items->ID;
                                if(isset($item_id)) {
                                    
                                    $products[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered()];
                                }else{
                                    $p_item = new \Picqer\Financials\Exact\Item($connection);
                                    $p_item->Code = $code;
                                    $p_item->CostPriceStandard = $_item->getPrice();
                                    $p_item->Description = $_item->getName();
                                    $p_item->IsSalesItem = true;
                                    $p_item->save();
                                    $item = new \Picqer\Financials\Exact\Item($connection);
                                    $items = $item->filter("trim(Code) eq '$code'");
                                    foreach($items as $item)
                                        $item_id        = $item->ID;
                                        $products[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered()];
                                }
                            }
                        }
                    	// $Warehouse_ID   = "e9113af3-da31-4c7b-9608-cfd2d02802c8";//warehouse
                    	$Warehouse_ID   = "28a2550c-7a63-4641-9eb3-80a44624e1a5";//warehouse Diamond

                    if(isset($OrderID)){
                        $exactOrder = new \Picqer\Financials\Exact\SalesOrder($connection);
                        $exactOrder->OrderID = $OrderID;
                        $exactOrder->OrderNumber = $order_id;
                        $exactOrder->OrderedBy = $c_guid;
                        $exactOrder->DeliverTo = $c_guid;
                        $exactOrder->InvoiceTo = $c_guid;
                        $exactOrder->Status = 20;
                        $exactOrder->WarehouseID = $Warehouse_ID;
                        $exactOrder->save();

                        $salesInvoice = new \Picqer\Financials\Exact\SalesInvoice($connection);
	                    // $salesInvoice->Description = 'Orders of Bundy Shoes';
	                    $salesInvoice->OrderNumber = $order_id;
	                    $salesInvoice->InvoiceTo = $c_guid;
	                    $salesInvoice->OrderedBy = $c_guid;
	                    $salesInvoice->DeliverTo = $c_guid;
	                    $salesInvoice->YourRef = $order_id;
	                    $salesInvoice->SalesInvoiceLines = $products;
	                    $salesInvoice->save();

                    }else{

                        if($orderData['customer_is_guest'] == 1 ){
			                if ($orderData['customer_id'] === null) {
			                    $websiteId = Mage::app()->getWebsite()->getId();
			                    $store = Mage::app()->getStore();
			                    $tempCustomer = Mage::getModel("customer/customer");
			                    $tempCustomer->setWebsiteId($websiteId)
			                        ->setStore($store)
			                        ->setFirstname($orderData['customer_firstname'])
			                        ->setLastname($orderData['customer_lastname'])
			                        ->setEmail($orderData['customer_email'])
			                        ->setPassword('temporary');

			                    $tempCustomer->save();

			                    $customers = Mage::getModel('customer/customer')->getCollection();
			                    $customer_id = $customers->getLastItem()->getId();

			                    Mage::register('isSecureArea', true);
			                    $tempCustomer = Mage::getModel('customer/customer')->load($customer_id);
			                    $tempCustomer->delete();
			                    Mage::unregister('isSecureArea');
			                }
			            }else{
			                $customer_id = $orderData['customer_id'];
			            }

			            $address = Mage::getModel('sales/order_address')->load($orderData['shipping_address_id']);
			            $custAddr = $address->getStreetFull();
			            $Country = $address->getCountryId();
			            $city = $address->getCity();
			            $postcode = $address->getPostcode();

			            $name = $orderData['customer_firstname'].' '.$orderData['customer_lastname'];
			            $email = $orderData['customer_email'];

			            $description = $orderData['shipping_description'];
			            $AmountFC = $orderData['subtotal'];
			            $shipping_method = $orderData['shipping_method'];
                    
		                $customers = new \Picqer\Financials\Exact\Account($connection);
		                $customers = $customers->filter("trim(Code) eq '$customer_id'");

		                if(empty($customers))
		                {
		                    $account = new \Picqer\Financials\Exact\Account($connection);
		                    $account->Email = $email;
		                    $account->Code = $customer_id;
		                    $account->IsSales = 'true';
		                    $account->Name = $name;
		                    $account->City = $city;
		                    $account->Postcode = $postcode;
		                    $account->Country = $Country;
		                    $account->Description = $name;
		                    $account->Status = 'C';
		                    $account->save();

		                    $customers = $account->filter("trim(Code) eq '$customer_id'");

		                    foreach ($customers as $_customer)
		                        $c_guid   = $_customer->ID;
		                }else{
		                    foreach ($customers as $_customer)
		                        $c_guid   = $_customer->ID;
		                }
		                $orderItems = $order->getAllItems();
		                $products = array();
		                foreach ($orderItems as $_item) {
		                    $code = $_item->getSku();
		                    $Type = $_item->getProductType();
		                    if( $code != '' && $Type == 'simple' ){
		                        $item = new \Picqer\Financials\Exact\Item($connection);
		                        $E_item = $item->filter("trim(Code) eq '$code'");
		                            foreach($E_item as $items)
		                                $item_id        = $items->ID;
		                        if(isset($item_id)) {
		                            
		                            $products[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered()];
		                        }else{
		                            $p_item = new \Picqer\Financials\Exact\Item($connection);
		                            $p_item->Code = $code;
		                            $p_item->CostPriceStandard = $_item->getPrice();
		                            $p_item->Description = $_item->getName();
		                            $p_item->IsSalesItem = true;
		                            $p_item->save();
		                            $item = new \Picqer\Financials\Exact\Item($connection);
		                            $items = $item->filter("trim(Code) eq '$code'");
		                            foreach($items as $item)
		                                $item_id        = $item->ID;
		                                $products[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered()];
		                        }
		                    }
		                }
		                
		                $order = new \Picqer\Financials\Exact\SalesOrder($connection);
		                $order->OrderedBy = $c_guid;
		                $order->OrderNumber = $order_id;
		                $order->InvoiceTo = $c_guid;
		                $order->WarehouseID = $Warehouse_ID;
		                $order->AmountFC = $AmountFC;
		                $order->ShippingMethodDescription = $shipping_method;
		                $order->DeliverTo = $c_guid;
		                $order->SalesOrderLines = $products;
		                $order->save();

		                $salesInvoice = new \Picqer\Financials\Exact\SalesInvoice($connection);
	                    $salesInvoice->OrderNumber = $order_id;
	                    $salesInvoice->InvoiceTo = $c_guid;
	                    $salesInvoice->OrderedBy = $c_guid;
	                    $salesInvoice->DeliverTo = $c_guid;
	                    $salesInvoice->YourRef = $order_id;
	                    $salesInvoice->SalesInvoiceLines = $products;
	                    $salesInvoice->save();
                    
                }   
            } catch (\Exception $e) {
                echo get_class($e) . ' :: ' . $e->getMessage();
        	}
        return $storage_vendor;
	}

	public function connect()
    {
        $connection = new \Picqer\Financials\Exact\Connection();
        $connection->setRedirectUrl('http://staging.hello-day.com/index.php/batchcode/index/batch');
        $connection->setExactClientId('354be896-915a-4b8e-b98d-c7b523bad43b');
        $connection->setExactClientSecret('Jbhc5rSntQXf');
        $connection->setBaseUrl('https://start.exactonline.co.uk');

        if ($this->getValue('authorizationcode')) // Retrieves authorizationcode from database
        {
            $connection->setAuthorizationCode($this->getValue('authorizationcode'));
        }

        if ($this->getValue('accesstoken')) // Retrieves accesstoken from database
        {
            $connection->setAccessToken($this->getValue('accesstoken'));
        }

        if ($this->getValue('refreshtoken')) // Retrieves refreshtoken from database
        {
            $connection->setRefreshToken($this->getValue('refreshtoken'));
        }

        if ($this->getValue('expires_in')) // Retrieves expires timestamp from database
        {
            $connection->setTokenExpires($this->getValue('expires_in'));
        }

        // Make the client connect and exchange tokens
        try {
            $connection->connect();
        } catch (\Exception $e) {
            throw new Exception('Could not connect to Exact: ' . $e->getMessage());
        }

        // Save the new tokens for next connections
        $this->setValue('accesstoken', $connection->getAccessToken());
        $this->setValue('refreshtoken', $connection->getRefreshToken());

        // Save expires time for next connections
        $this->setValue('expires_in', $connection->getTokenExpires());

        //$connection = $this->connect();

        return $connection;
    }

}

