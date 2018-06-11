<?php
/**
 * Bridge Batchcode
 *
 * @category      Bridge
 * @package       Bridge_Batchcode
 * @copyright     Copyright (c) 2013 Bridge India. (http://bridge-india.in)
 * @license       http://bridge-india.in/disclaimer/magento
 */

require __DIR__ . '/vendor/autoload.php';
class Bridge_Batchcode_Model_Observer
{
    const FLAG_SHOW_CONFIG = 'showConfig';
    const FLAG_SHOW_CONFIG_FORMAT = 'showConfigFormat';
    private $request;
    public function checkForConfigRequest($observer)
    {

    }

    public function test($observer)
    {

    }

    public function ModuleStatus($observer)
    {

    }

    public function salesOrderShipmentSaveAfter(Varien_Event_Observer $observer)
    {
        if( Mage::getSingleton('core/session')->getBatchAssign()) { 
            Mage::getSingleton('core/session')->setBatchAssign(false);
        }
        elseif (Mage::registry('batch_assigned_to_items')){ 
        Mage::unregister('batch_assigned_to_items');
        }else
        {
        Mage::register('batch_assigned_to_items',true);
        $shipment = $observer->getEvent()->getShipment();
        $post = $observer->getEvent()->getPost();
        if(isset($post['batch_id']))

        {

        $selectedbatches = $post['batch_id'];

        }

        

        $order = $shipment->getOrder();

        $storeId = $order->getStoreId();

        $orderId = $order->getId();

        

        $items = $shipment->getItemsCollection();   

        

        foreach ($items as $shipment_item) {

        

        $qty_toship = $shipment_item->getQty();



        $order_item = Mage::getModel('sales/order_item')->load($shipment_item->getOrderItemId());

        $type = $order_item->getProductType(); 

        $parentid = $order_item->getId();

        if($type=='configurable')

        {

            $prd_id = Mage::getModel('sales/order_item')->load($order_item->getId(),'parent_item_id');

            $productId = $prd_id->getProductId();

            $itemid = $prd_id->getId();

        } else {  

            $itemid = $shipment_item->getOrderItemId();

            $productId = $shipment_item->getProductId();

        }

        $qty_waiting = $qty_toship; 

        $batches = array();

        if(isset($selectedbatches[$itemid]))

        {

            $batches = $selectedbatches[$itemid];

        }

            if(isset($batches)){

            foreach($batches as &$batchid){



            $batchdetails =  Mage::getModel('batchcode/batchcode')->load($batchid);

                $available_qty = $batchdetails->getQty();

                

                if ($qty_waiting > 0) {

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



                    $qty_waiting = $qty_waiting - $available_qty;
                }
            }
        }
        }
        }
           return;
    }

    public function catalogProductDeleteAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $product_id = $product->getId();
        $collection = Mage::getModel('batchcode/batchcode_product')
               ->getCollection()
               ->addFieldToFilter('product_id', $product_id);
        foreach($collection as $batchcode_products)
        {
            $id = $batchcode_products->getId();
            $batchcode_product =  Mage::getModel('batchcode/batchcode_product')->load($id);  
                           $products_id = $batchcode_product->getProductId();
                           $batchcode_product->setProductId(0);
                           $batchcode_product->save();
        }          
    }

    private function setHeader()
    {
        $format = isset($this->request->{self::FLAG_SHOW_CONFIG_FORMAT}) ?
                $this->request->{self::FLAG_SHOW_CONFIG_FORMAT} : 'xml';
        switch ($format) {
            case 'text':
                header("Content-Type: text/plain");
                break;
            default:
                header("Content-Type: text/xml");
        }
    }

    private function outputConfig()
    {
        die(Mage::app()->getConfig()->getNode()->asXML());
    }

    public function getValue($key)
    {
        $storage_file = __DIR__ . '/storage.json';
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
        $storage_file = __DIR__ . '/storage.json';
        $storage       = json_decode(file_get_contents($storage_file), true);
        $storage[$key] = $value;
        file_put_contents($storage_file, json_encode($storage));
    }

    public function authorize()
    {
        $redirectUrl = Mage::getStoreConfig('catalog/batch/RedirectUrl');
        $clientId = Mage::getStoreConfig('catalog/batch/ExactClientId');
        $clientSecret = Mage::getStoreConfig('catalog/batch/ExactClientSecret');

        $connection = new \Picqer\Financials\Exact\Connection();
        $connection->setRedirectUrl($redirectUrl);
        $connection->setExactClientId($clientId);
        $connection->setExactClientSecret($clientSecret);
        $connection->setBaseUrl('https://start.exactonline.co.uk');
        $connection->redirectForAuthorization();
    }

    public function saveOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        $orders = $observer->getEvent()->getOrderIds();
        foreach ($orders as $order_id)
        {
            $ids = [];
            $ids[] = $order_id;
            $order = Mage::getModel('sales/order')->load($order_id);
            $order_id = $order_id+100;
            $orderData = $order->getData();
            $orderIncrementId = $order->getIncrementId();
            $state = $orderData['status'];
            if($state == "pending") {
            $address = Mage::getModel('sales/order_address')->load($orderData['shipping_address_id']);
            $custAddr = $address->getStreetFull();
            $Country = $address->getCountryId();
            $city = $address->getCity();
            $postcode = $address->getPostcode();
            $name = $orderData['customer_firstname'].' '.$orderData['customer_lastname'];
            $email = $orderData['customer_email'];
            if ($Country == 'CH') {
                $VATCode = '17';
            } else if ($Country == 'BE') {
            	$VATCode = '18';
        	} else {
                $VATCode = '01';
            }
            $shipPrice = $orderData['base_shipping_amount'];
            $shipPriceVat = $orderData['base_shipping_tax_amount'];
            $AmountFC = $orderData['subtotal'];
            $shipping_method = $orderData['shipping_method'];
            $shippingDescription = $orderData['shipping_description'];
            $base_discount_amount = $orderData['base_discount_amount'];
            $subtotalInclTax = $orderData['subtotal_incl_tax'];
            $discountPercentage = 0;
            if ($base_discount_amount != '') {
                $discount = (($base_discount_amount/$subtotalInclTax)*100);
                $discountPercentage = explode('-', $discount);
                $discountPercentage = $discountPercentage[1];
                $discountPercentage = $discountPercentage/100;
            }
            // Create the Exact client
            $connection = $this->connect();
            // Get the customers from exact
            try {
                $customers = new \Picqer\Financials\Exact\Account($connection);
                $customers = $customers->filter("trim(Email) eq '$email'");
                if (empty($customers))
                {
                    if($orderData['customer_is_guest'] == 1 ){
                        if ($orderData['customer_id'] === null) {
                            $websiteId = Mage::app()->getWebsite()->getId();
                            $store = Mage::app()->getStore();
                            $tempCustomer = Mage::getModel("customer/customer");
                            $tempCustomer->setWebsiteId($websiteId)
                                ->setStore($store)
                                ->setFirstname($orderData['customer_firstname'])
                                ->setLastname($orderData['customer_lastname'])
                                ->setEmail('temporary@temporary.com')
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
                    $account = new \Picqer\Financials\Exact\Account($connection);
                    $account->Code = $customer_id;
                    $account->Email = $email;
                    $account->IsSales = 'true';
                    $account->Name = $name;
                    $account->City = $city;
                    $account->AddressLine1 = $custAddr;
                    $account->Postcode = $postcode;
                    $account->Country = $Country;
                    $account->Description = $name;
                    $account->Status = 'C';
                    $account->save();
                    $customers = $account->filter("trim(Email) eq '$email'");
                    foreach ($customers as $_customer)
                        $c_guid   = $_customer->ID;
                } else {
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
                                $unit        = $items->Unit;
                        if(isset($item_id)) {
                            if ($discountPercentage != '') {
                                $products[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=>$_item->getQtyOrdered(),'VATCode'=>$VATCode,'Discount'=>$discountPercentage];
                            }else{
                                $products[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=>$_item->getQtyOrdered(),'VATCode'=>$VATCode];
                            }
                        }else{
                            $p_item = new \Picqer\Financials\Exact\Item($connection);
                            $p_item->Code = $code;
                            $p_item->CostPriceStandard = $_item->getPrice();
                            $p_item->Description = $_item->getName();
                            $p_item->Unit = 'BOX';
                            $p_item->IsSalesItem = true;
                            $p_item->save();
                            $item = new \Picqer\Financials\Exact\Item($connection);
                            $items = $item->filter("trim(Code) eq '$code'");
                            foreach($items as $item)
                                $item_id        = $item->ID;
                            if ($discountPercentage != '') {
                                $products[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=>$_item->getQtyOrdered(),'VATCode'=>$VATCode,'Discount'=>$discountPercentage];
                            }else{
                                $products[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=>$_item->getQtyOrdered(),'VATCode'=>$VATCode];
                            }
                        }
                    }
                }
                $ShippingItem = new \Picqer\Financials\Exact\Item($connection);
                $Ship_item = $ShippingItem->filter("trim(Code) eq '$shipping_method'");
                foreach($Ship_item as $S_item)
                    $S_ID = $S_item->ID;
                    if (isset($S_ID)) {
                        $products[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];
                    } else {
                        $p_ship = new \Picqer\Financials\Exact\Item($connection);
                        $p_ship->Code = $shipping_method;
                        $p_ship->CostPriceStandard = $shipPrice;
                        $p_ship->Description = $shippingDescription;
                        $p_ship->Unit = 'BOX';
                        $p_ship->IsSalesItem = true;
                        $p_ship->save();
                        $p_shipping = new \Picqer\Financials\Exact\Item($connection);
                        $Ship_item = $p_shipping->filter("trim(Code) eq '$shipping_method'");
                        foreach($Ship_item as $S_item)
                            $products[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];
                    }
                //Search for Warehouse 
                $WarehouseCode = "Diamond";
                $Warehouse = new \Picqer\Financials\Exact\Warehouse($connection);
                $Warehouse = $Warehouse->filter("trim(Code) eq '$WarehouseCode'");
                foreach($Warehouse as $_Warehouse)
                    $Warehouse_ID  = $_Warehouse->ID;
                	$order = new \Picqer\Financials\Exact\SalesOrder($connection);
                	$order->OrderedBy = $c_guid;
                	$order->InvoiceTo = $c_guid;
                	$order->OrderNumber = $order_id;
	                $order->Description = $orderIncrementId;
	                $order->WarehouseID = $Warehouse_ID;
	                $order->AmountFC = $AmountFC;
	                $order->ShippingMethodDescription = $shipping_method;
	                $order->SalesOrderLines = $products;
	                // $order->PaymentCondition = '10';
	                $order->save();                
            } catch ( \Exception $e ) {
                get_class($e) . ' :: ' . $e->getMessage();
                //echo $e->getMessage();
                //echo $order_id;
            }
        }
    }//die;
    }

    public function salesOrderStatusAfter(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $state = $order->getStatus();

        $ShippingMethod = $order->getShippingMethod();
        $order_inc_id = $order->getIncrementId();
        $order_id = $order->getId();

        //Get invoice ID's
        $orderInvoice = Mage::getModel('sales/order')->loadByIncrementId($order_inc_id);
        if ($orderInvoice->hasInvoices()) {
            $invIncrementIDs = array();
            foreach ($orderInvoice->getInvoiceCollection() as $inv) {
                $invIncrementIDs[] = $inv->getIncrementId();
            }
        }

        $invoiceIds = implode(",",$invIncrementIDs);

        //Get Tracking Numbers
        $orderTrack = Mage::getModel('sales/order')->loadByIncrementId($order_inc_id);
        $shipmentCollection = Mage::getResourceModel('sales/order_shipment_collection')
            ->setOrderFilter($orderTrack)
            ->load();
        $alltrackback = '';
        foreach ($shipmentCollection as $shipment) {
            $alltrackback = $shipment->getAllTracks();
        }
        $tracknumber = '';
        foreach ($alltrackback as $tracking) {
            $tracknumber = $tracking->TrackNumber; 
        }

        $order = Mage::getModel('sales/order')->load($order_id);
        $orderData = $order->getData();
        // echo "<pre>";

        $OpeningBalanceFC = $orderData['grand_total'];
        $address = Mage::getModel('sales/order_address')->load($orderData['shipping_address_id']);
        $custAddr = $address->getStreetFull();
        $Country = $address->getCountryId();
        $city = $address->getCity();
        $postcode = $address->getPostcode();

        $name = $orderData['customer_firstname'].' '.$orderData['customer_lastname'];
        $email = $orderData['customer_email'];

        $description = $orderData['shipping_description'];
        $shipPrice = $orderData['base_shipping_amount'];
        $AmountFC = $orderData['subtotal'];
        $shipping_method = $orderData['shipping_method'];
        $shippingDescription = $orderData['shipping_description'];

        $discountPercentage = 0;
        $base_discount_amount = $orderData['base_discount_amount'];
        $subtotalInclTax = $orderData['subtotal_incl_tax'];
        if ($base_discount_amount != '') {
            $discount = (($base_discount_amount/$subtotalInclTax)*100);
            $discountPercentage = explode('-', $discount);
            $discountPercentage = trim($discountPercentage[1]);
            $discountPercentage = $discountPercentage/100;
        }

        if ($Country == 'CH') {
            $VATCode = '17';
        } else if ($Country == 'BE') {
            $VATCode = '18';
        } else {
            $VATCode = '01';
        }

        $date = date("Y-m-d");

        if($state == "processing" || $state == "complete"){

            // Create the Exact client
            $connection = $this->connect();

            // Get the customers from exact
            try {
                $exactOrder = new \Picqer\Financials\Exact\SalesOrder($connection);
                $orders = $exactOrder->filter( "OrderNumber eq $order_id" );

                foreach($orders as $_order)
                        $OrderID   = $_order->OrderID;
                        $c_guid    = $_order->OrderedBy;
                        $SalesOrder = $_order->SalesOrderLines;
                        $products_invoice = array();
                        $GoodsDeliverylines = array();

                        foreach ($SalesOrder as $_salesOrder) {
                            $sku = $_salesOrder->ItemCode;
                            $product_id = Mage::getModel("catalog/product")->getIdBySku( $sku );
                            $BatchNumber = array();
                            $batches = Mage::getModel('batchcode/batchcode')
                                        ->getCollection()
                                        ->getBatchByProduct($product_id);
                            foreach ($batches as $_batch) {
                                $BatchNumber[] = ['BatchNumber'=> $_batch->batch_number, 'Quantity'=> $_salesOrder->Quantity];
                            }

                            $GoodsDeliverylines[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'QuantityOrdered' => $_salesOrder->Quantity,'QuantityDelivered' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'SalesOrderLineNumber'=>$_salesOrder->OrderNumber, 'SalesOrderLineID'=>$_salesOrder->ID,'DeliveryDate'=>$_salesOrder->DeliveryDate,'BatchNumbers'=>$BatchNumber];

                            if ($discountPercentage != '') {
                                $products_invoice[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'Quantity' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'UnitPrice'=>$_salesOrder->UnitPrice,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];
                            }else{
                                $products_invoice[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'Quantity' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'UnitPrice'=>$_salesOrder->UnitPrice,'VATCode'=>$VATCode];
                            }
                        }

                        $orderItems = $order->getAllItems();
                        $products_order = array();
                        $CashEntries = array();
                        //Search for GLAccount 
                        $GLAccountCode = "15400";
                        $GLAccount = new \Picqer\Financials\Exact\GLAccount($connection);
                        $GLAccount = $GLAccount->filter("trim(Code) eq '$GLAccountCode'");
                        foreach($GLAccount as $_GLaccount)
                            $GLaccount_Id  = $_GLaccount->ID;

                        foreach ($orderItems as $_item) {
                            $code = $_item->getSku();
                            $Type = $_item->getProductType();
                            if( $code != '' && $Type == 'simple' ){
                                $item = new \Picqer\Financials\Exact\Item($connection);
                                $E_item = $item->filter("trim(Code) eq '$code'");

                                foreach($E_item as $items)
                                    $item_id        = $items->ID;
                                    $item_OrderID   = $items->OrderID;
                                if (isset($item_id)) {
                                    if ($discountPercentage != '') {
                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];
                                    }else{
                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode];
                                    }
                                    $CashEntries[] = ['GLAccountCode'=>$GLAccountCode,'Account'=>$c_guid,'GLAccount'=>$GLaccount_Id,'Description'=>$order_inc_id];
                                } else {
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
                                        $item_OrderID   = $item->OrderID;
                                        if ($discountPercentage != '') {
                                            $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];
                                        }else{
                                            $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode];
                                        }
                                        $CashEntries[] = ['GLAccountCode'=>$GLAccountCode,'Account'=>$c_guid,'GLAccount'=>$GLaccount_Id,'Description'=>$order_inc_id];
                                }
                            }
                        }

                        //Search for Warehouse 
                        $WarehouseCode = "Diamond";
                        $Warehouse = new \Picqer\Financials\Exact\Warehouse($connection);
                        $Warehouse = $Warehouse->filter("trim(Code) eq '$WarehouseCode'");
                        foreach($Warehouse as $_Warehouse)
                            $Warehouse_ID  = $_Warehouse->ID;
                    if (isset($OrderID)) {
                    	$ShippingItem = new \Picqer\Financials\Exact\Item($connection);
		                $Ship_item = $ShippingItem->filter("trim(Code) eq '$shipping_method'");
		                foreach($Ship_item as $S_item)

                        if (isset($S_item->ID)) {
                            $products_order[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];
                        } else {
                            $p_ship = new \Picqer\Financials\Exact\Item($connection);
                            $p_ship->Code = $shipping_method;
                            $p_ship->CostPriceStandard = $shipPrice;
                            $p_ship->Description = $shippingDescription;
                            $p_ship->Unit = 'BOX';
                            $p_ship->IsSalesItem = true;
                            $p_ship->save();

                            $p_shipping = new \Picqer\Financials\Exact\Item($connection);
                            $Ship_item = $p_shipping->filter("trim(Code) eq '$shipping_method'");
                            foreach($Ship_item as $S_item)
                                $products_order[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];
                        }

                        $exactOrder = new \Picqer\Financials\Exact\SalesOrder($connection);
                        $exactOrder->OrderID = $OrderID;
                        $exactOrder->OrderNumber = $order_id;
                        $exactOrder->OrderedBy = $c_guid;
                        $exactOrder->InvoiceTo = $c_guid;
                        $exactOrder->DeliverTo = $c_guid;
                        $exactOrder->Description = $order_inc_id;
                        $exactOrder->SalesOrderLines = $products_order;
                        $exactOrder->WarehouseID = $Warehouse_ID;
                        $exactOrder->save();

                        $GoodsDeliveries = new \Picqer\Financials\Exact\GoodsDelivery($connection);
                        $GoodsDeliveries->GoodsDeliveryLines = $GoodsDeliverylines;
                        $GoodsDeliveries->TrackingNumber = $tracknumber;
                        $GoodsDeliveries->DeliveryDate = $date;
                        $GoodsDeliveries->Description = $order_inc_id;
                        $GoodsDeliveries->WarehouseID = $Warehouse_ID;
                        $GoodsDeliveries->save();

                        $salesInvoice = new \Picqer\Financials\Exact\SalesInvoice($connection);
                        $salesInvoice->OrderNumber = $order_id;
                        $salesInvoice->Description = $invoiceIds;
                        $salesInvoice->InvoiceTo = $c_guid;
                        $salesInvoice->OrderedBy = $c_guid;
                        $salesInvoice->DeliverTo = $c_guid;
                        $salesInvoice->YourRef = $order_id;
                        $salesInvoice->SalesInvoiceLines = $products_invoice;
                        $salesInvoice->save();

                        $cashEntry = new \Picqer\Financials\Exact\CashEntry($connection);
                        $cashEntry->ClosingBalanceFC = $OpeningBalanceFC;
                        $cashEntry->OpeningBalanceFC = $OpeningBalanceFC;
                        $cashEntry->JournalCode = 10;
                        $cashEntry->CashEntryLines = $CashEntries;
                        $cashEntry->save();

                    } else {

                        $customers = new \Picqer\Financials\Exact\Account($connection);
                        $customers = $customers->filter("trim(Email) eq '$email'");

                        if (empty($customers)) {

                            if ($orderData['customer_is_guest'] == 1 ) {
                                if ($orderData['customer_id'] === null) {
                                    $websiteId = Mage::app()->getWebsite()->getId();
                                    $store = Mage::app()->getStore();
                                    $tempCustomer = Mage::getModel("customer/customer");
                                    $tempCustomer->setWebsiteId($websiteId)
                                        ->setStore($store)
                                        ->setFirstname($orderData['customer_firstname'])
                                        ->setLastname($orderData['customer_lastname'])
                                        ->setEmail('temporary@temporary.com')
                                        ->setPassword('temporary');

                                    $tempCustomer->save();

                                    $customers = Mage::getModel('customer/customer')->getCollection();
                                    $customer_id = $customers->getLastItem()->getId();

                                    Mage::register('isSecureArea', true);
                                    $tempCustomer = Mage::getModel('customer/customer')->load($customer_id);
                                    $tempCustomer->delete();
                                    Mage::unregister('isSecureArea');
                                }
                            } else {
                                $customer_id = $orderData['customer_id'];
                            }
                            $account = new \Picqer\Financials\Exact\Account($connection);
                            $account->Email = $email;
                            $account->Code = $customer_id;
                            $account->IsSales = 'true';
                            $account->Name = $name;
                            $account->AddressLine1 = $custAddr;
                            $account->City = $city;
                            $account->Postcode = $postcode;
                            $account->Country = $Country;
                            $account->Description = $name;
                            $account->Status = 'C';
                            $account->save();

                            $customers = $account->filter("trim(Email) eq '$email'");

                            foreach ($customers as $_customer)
                                $c_guid   = $_customer->ID;
                        } else {
                            foreach ($customers as $_customer)
                                $c_guid   = $_customer->ID;
                        }
                        $orderItems = $order->getAllItems();
                        $products_order = array();
                        $products_invoice = array();
                        $GoodsDeliverylines = array();

                        foreach ($orderItems as $_item) {
                            $code = $_item->getSku();
                            $Type = $_item->getProductType();
                            if ( $code != '' && $Type == 'simple' ) {
                                $item = new \Picqer\Financials\Exact\Item($connection);
                                $E_item = $item->filter("trim(Code) eq '$code'");
                                    foreach($E_item as $items)
                                        $item_id        = $items->ID;
                                if (isset($item_id)) {
                                    if ($discountPercentage != '') {
                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];
                                    }else{
                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode];
                                    }
                                } else {
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
                                    if ($discountPercentage != '') {
                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(),'VATCode'=>$VATCode,'Discount'=>$discountPercentage];
                                    }else{
                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(),'VATCode'=>$VATCode];
                                    }
                                }
                            }
                        }

                        $ShippingItem = new \Picqer\Financials\Exact\Item($connection);
		                $Ship_item = $ShippingItem->filter("trim(Code) eq '$shipping_method'");
		                foreach($Ship_item as $S_item)

                        if (isset($S_item->ID)) {
                            $products_order[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];
                        } else {
                            $p_ship = new \Picqer\Financials\Exact\Item($connection);
                            $p_ship->Code = $shipping_method;
                            $p_ship->CostPriceStandard = $shipPrice;
                            $p_ship->Description = $shippingDescription;
                            $p_ship->Unit = 'BOX';
                            $p_ship->IsSalesItem = true;
                            $p_ship->save();

                            $p_shipping = new \Picqer\Financials\Exact\Item($connection);
                            $Ship_item = $p_shipping->filter("trim(Code) eq '$shipping_method'");
                            foreach($Ship_item as $S_item)
                                $products_order[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];
                        }

                        $order = new \Picqer\Financials\Exact\SalesOrder($connection);
                        $order->OrderedBy = $c_guid;
                        $order->OrderNumber = $order_id;
                        $order->InvoiceTo = $c_guid;
                        $order->Description = $order_inc_id;
                        $order->WarehouseID = $Warehouse_ID;
                        $order->AmountFC = $AmountFC;
                        $order->ShippingMethodDescription = $shipping_method;
                        $order->DeliverTo = $c_guid;
                        $order->SalesOrderLines = $products_order;
                        $order->save();

                        $exactOrder = new \Picqer\Financials\Exact\SalesOrder($connection);
                        $orders = $exactOrder->filter( "OrderNumber eq $order_id" );

                        foreach($orders as $_order)
                        $OrderID    = $_order->OrderID;
                        $c_guid    = $_order->OrderedBy;
                        $SalesOrder = $_order->SalesOrderLines;
                        $products_invoice = array();
                        $GoodsDeliverylines = array();

                        foreach ($SalesOrder as $_salesOrder) {

                            $sku = $_salesOrder->ItemCode;
                            $product_id = Mage::getModel("catalog/product")->getIdBySku( $sku );
                            $batches = Mage::getModel('batchcode/batchcode')
                                        ->getCollection()
                                        ->getBatchByProduct($product_id);
                            $BatchNumber = array();
                            foreach ($batches as $_batch) {
                                $BatchNumber[] = ['BatchNumber'=> $_batch->batch_number, 'Quantity'=> $_salesOrder->Quantity];
                            }

                            $GoodsDeliverylines[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'QuantityOrdered' => $_salesOrder->Quantity,'QuantityDelivered' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'SalesOrderLineNumber'=>$_salesOrder->OrderNumber, 'SalesOrderLineID'=>$_salesOrder->ID,'DeliveryDate'=>$_salesOrder->DeliveryDate,'BatchNumbers'=>$BatchNumber];
                            if ($discountPercentage != '') {
                                $products_invoice[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'Quantity' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'UnitPrice'=>$_salesOrder->UnitPrice,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];
                            }else{
                                $products_invoice[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'Quantity' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'UnitPrice'=>$_salesOrder->UnitPrice,'VATCode'=>$VATCode];
                            }
                        }

                        $GoodsDeliveries = new \Picqer\Financials\Exact\GoodsDelivery($connection);
                        $GoodsDeliveries->GoodsDeliveryLines = $GoodsDeliverylines;
                        $GoodsDeliveries->TrackingNumber = $tracknumber;
                        $GoodsDeliveries->Creator = $c_guid;
                        $GoodsDeliveries->DeliveryDate = $date;
                        $GoodsDeliveries->Description = $order_inc_id;
                        $GoodsDeliveries->WarehouseID = $Warehouse_ID;
                        $GoodsDeliveries->save();

                        $salesInvoice = new \Picqer\Financials\Exact\SalesInvoice($connection);
                        $salesInvoice->Description = $invoiceIds;
                        $salesInvoice->OrderNumber = $order_id;
                        $salesInvoice->InvoiceTo = $c_guid;
                        $salesInvoice->OrderedBy = $c_guid;
                        $salesInvoice->DeliverTo = $c_guid;
                        $salesInvoice->YourRef = $order_id;
                        $salesInvoice->SalesInvoiceLines = $products_invoice;
                        $salesInvoice->save();

                        $cashEntry = new \Picqer\Financials\Exact\CashEntry($connection);
                        $cashEntry->ClosingBalanceFC = $OpeningBalanceFC;
                        $cashEntry->OpeningBalanceFC = $OpeningBalanceFC;
                        $cashEntry->JournalCode = 10;
                        $cashEntry->CashEntryLines = $CashEntries;
                        $cashEntry->save();
                    }
                }
                catch (\Exception $e) {
                    get_class($e) . ' : ' . $e->getMessage();//
                }
            }
    }



    public function salesShipmentTrackSaveAfter(Varien_Event_Observer $observer)
    {

        $event = $observer->getEvent();

        $track = $event->getTrack();

        $tracknumber = $track->getNumber();

        $shipment = $track->getShipment();

        $order = $shipment->getOrder();



        $state = $order->getStatus();



        $ShippingMethod = $order->getShippingMethod();

        $order_inc_id = $order->getIncrementId();

        $order_id = $order->getId();

        

        //Get invoice ID's

        $orderInvoice = Mage::getModel('sales/order')->loadByIncrementId($order_inc_id);

        if ($orderInvoice->hasInvoices()) {

            $invIncrementIDs = array();

            foreach ($orderInvoice->getInvoiceCollection() as $inv) {

                $invIncrementIDs[] = $inv->getIncrementId();

            }

        }

        $invoiceIds = implode(",",$invIncrementIDs);

        

        $order = Mage::getModel('sales/order')->load($order_id);

        $orderData = $order->getData();       

        

        $OpeningBalanceFC = $orderData['grand_total'];



        $address = Mage::getModel('sales/order_address')->load($orderData['shipping_address_id']);

        $custAddr = $address->getStreetFull();

        $Country = $address->getCountryId();

        $city = $address->getCity();

        $postcode = $address->getPostcode();



        $name = $orderData['customer_firstname'].' '.$orderData['customer_lastname'];

        $email = $orderData['customer_email'];



        $description = $orderData['shipping_description'];

        $shipPrice = $orderData['base_shipping_amount'];

        $AmountFC = $orderData['subtotal'];

        $shipping_method = $orderData['shipping_method'];

        $shippingDescription = $orderData['shipping_description'];



        $discountPercentage = 0;

        $base_discount_amount = $orderData['base_discount_amount'];

        $subtotalInclTax = $orderData['subtotal_incl_tax'];

        if ($base_discount_amount != '') {

            $discount = (($base_discount_amount/$subtotalInclTax)*100);

            $discountPercentage = explode('-', $discount);

            $discountPercentage = trim($discountPercentage[1]);

            $discountPercentage = $discountPercentage/100;

        }

        

        if ($Country == 'CH') {

            $VATCode = '17';

        } else if ($Country == 'BE') {

            $VATCode = '18';

        } else {

            $VATCode = '01';

        }



        $date = date("Y-m-d");



        if($state == "processing" || $state == "complete"){



        // Create the Exact client

        $connection = $this->connect();

                

        // Get the customers from exact

        try

        {
            $order_id = $order_id+100;
            $exactOrder = new \Picqer\Financials\Exact\SalesOrder($connection);

            $orders = $exactOrder->filter( "OrderNumber eq $order_id" );



            foreach($orders as $_order)

                $OrderID   = $_order->OrderID;

                $c_guid    = $_order->OrderedBy;



                $SalesOrder = $_order->SalesOrderLines;

                $products_invoice = array();

                $GoodsDeliverylines = array();

                        

                foreach ($SalesOrder as $_salesOrder) {

                    $sku = $_salesOrder->ItemCode;

                    $product_id = Mage::getModel("catalog/product")->getIdBySku( $sku );

                    $BatchNumber = array();

                    $batches = Mage::getModel('batchcode/batchcode')

                        ->getCollection()

                        ->getBatchByProduct($product_id);

                    foreach ($batches as $_batch) {

                        $BatchNumber[] = ['BatchNumber'=> $_batch->batch_number, 'Quantity'=> $_salesOrder->Quantity];

                    }



                    $GoodsDeliverylines[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'QuantityOrdered' => $_salesOrder->Quantity,'QuantityDelivered' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'SalesOrderLineNumber'=>$_salesOrder->OrderNumber, 'SalesOrderLineID'=>$_salesOrder->ID,'DeliveryDate'=>$_salesOrder->DeliveryDate,'BatchNumbers'=>$BatchNumber];

                    if ($discountPercentage != '') {

                        $products_invoice[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'Quantity' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'UnitPrice'=>$_salesOrder->UnitPrice,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];

                    } else {

                        $products_invoice[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'Quantity' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'UnitPrice'=>$_salesOrder->UnitPrice,'VATCode'=>$VATCode];

                    }

                }

                        

                $orderItems = $order->getAllItems();

                $products_order = array();

                $CashEntries = array();

                //Search for GLAccount 

                $GLAccountCode = "15400";

                $GLAccount = new \Picqer\Financials\Exact\GLAccount($connection);

                $GLAccount = $GLAccount->filter("trim(Code) eq '$GLAccountCode'");

                foreach($GLAccount as $_GLaccount)

                    $GLaccount_Id  = $_GLaccount->ID;

                        

                    foreach ($orderItems as $_item) {

                        $code = $_item->getSku();

                        $Type = $_item->getProductType();

                        if ( $code != '' && $Type == 'simple' ) {

                            $item = new \Picqer\Financials\Exact\Item($connection);

                            $E_item = $item->filter("trim(Code) eq '$code'");

                                

                            foreach($E_item as $items)

                                $item_id        = $items->ID;

                                $item_OrderID   = $items->OrderID;

                            if (isset($item_id)) {

                                if ($discountPercentage != '') {

                                    $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];

                                } else {

                                    $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode];

                                }

                                $CashEntries[] = ['GLAccountCode'=>$GLAccountCode,'Account'=>$c_guid,'GLAccount'=>$GLaccount_Id,'Description'=>$order_inc_id];

                            } else {

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

                                    $item_OrderID   = $item->OrderID;

                                if ($discountPercentage != '') {

                                    $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];

                                } else {

                                    $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode];

                                }

                                    $CashEntries[] = ['GLAccountCode'=>$GLAccountCode,'Account'=>$c_guid,'GLAccount'=>$GLaccount_Id,'Description'=>$order_inc_id];

                            }

                        }

                    }

                        

                    //Search for Warehouse 

                    $WarehouseCode = "Diamond";

                    $Warehouse = new \Picqer\Financials\Exact\Warehouse($connection);

                    $Warehouse = $Warehouse->filter("trim(Code) eq '$WarehouseCode'");

                    foreach($Warehouse as $_Warehouse)

                            $Warehouse_ID  = $_Warehouse->ID;



                    if (isset($OrderID)) {

                        $ShippingItem = new \Picqer\Financials\Exact\Item($connection);

                        $Ship_item = $ShippingItem->filter("trim(Code) eq '$shipping_method'");

                        foreach($Ship_item as $S_item)



                        if (isset($S_item->ID)) {

                            $products_order[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];

                        } else {

                            $p_ship = new \Picqer\Financials\Exact\Item($connection);

                            $p_ship->Code = $shipping_method;

                            $p_ship->CostPriceStandard = $shipPrice;

                            $p_ship->Description = $shippingDescription;

                            $p_ship->Unit = 'BOX';

                            $p_ship->IsSalesItem = true;

                            $p_ship->save();



                            $p_shipping = new \Picqer\Financials\Exact\Item($connection);

                            $Ship_item = $p_shipping->filter("trim(Code) eq '$shipping_method'");

                            foreach($Ship_item as $S_item)

                                $products_order[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];

                        }



                        $exactOrder = new \Picqer\Financials\Exact\SalesOrder($connection);

                        $exactOrder->OrderID = $OrderID;

                        $exactOrder->OrderNumber = $order_id;

                        $exactOrder->OrderedBy = $c_guid;

                        $exactOrder->InvoiceTo = $c_guid;

                        $exactOrder->DeliverTo = $c_guid;

                        $exactOrder->Description = $order_inc_id;

                        $exactOrder->SalesOrderLines = $products_order;

                        $exactOrder->WarehouseID = $Warehouse_ID;

                        $exactOrder->save();



                        $GoodsDeliveries = new \Picqer\Financials\Exact\GoodsDelivery($connection);

                        $GoodsDeliveries->GoodsDeliveryLines = $GoodsDeliverylines;

                        $GoodsDeliveries->TrackingNumber = $tracknumber;

                        $GoodsDeliveries->DeliveryDate = $date;

                        $GoodsDeliveries->Description = $order_inc_id;

                        $GoodsDeliveries->WarehouseID = $Warehouse_ID;

                        $GoodsDeliveries->save();



                        $salesInvoice = new \Picqer\Financials\Exact\SalesInvoice($connection);

                        $salesInvoice->OrderNumber = $order_id;

                        $salesInvoice->Description = $invoiceIds;

                        $salesInvoice->InvoiceTo = $c_guid;

                        $salesInvoice->OrderedBy = $c_guid;

                        $salesInvoice->DeliverTo = $c_guid;

                        $salesInvoice->YourRef = $order_id;

                        $salesInvoice->SalesInvoiceLines = $products_invoice;

                        $salesInvoice->save();



                        // $cashEntry = new \Picqer\Financials\Exact\CashEntry($connection);

                        // $cashEntry->ClosingBalanceFC = $OpeningBalanceFC;

                        // $cashEntry->OpeningBalanceFC = $OpeningBalanceFC;

                        // $cashEntry->JournalCode = 10;

                        // $cashEntry->CashEntryLines = $CashEntries;

                        // $cashEntry->save();



                    } else {

                                            

                        $customers = new \Picqer\Financials\Exact\Account($connection);

                        $customers = $customers->filter("trim(Email) eq '$email'");



                        if (empty($customers)) {



                            if ($orderData['customer_is_guest'] == 1 ) {

                                if ($orderData['customer_id'] === null) {

                                    $websiteId = Mage::app()->getWebsite()->getId();

                                    $store = Mage::app()->getStore();

                                    $tempCustomer = Mage::getModel("customer/customer");

                                    $tempCustomer->setWebsiteId($websiteId)

                                        ->setStore($store)

                                        ->setFirstname($orderData['customer_firstname'])

                                        ->setLastname($orderData['customer_lastname'])

                                        ->setEmail('temporary@temporary.com')

                                        ->setPassword('temporary');



                                    $tempCustomer->save();



                                    $customers = Mage::getModel('customer/customer')->getCollection();

                                    $customer_id = $customers->getLastItem()->getId();



                                    Mage::register('isSecureArea', true);

                                    $tempCustomer = Mage::getModel('customer/customer')->load($customer_id);

                                    $tempCustomer->delete();

                                    Mage::unregister('isSecureArea');

                                }

                            } else {

                                $customer_id = $orderData['customer_id'];

                            }

                            $account = new \Picqer\Financials\Exact\Account($connection);

                            $account->Email = $email;

                            $account->Code = $customer_id;

                            $account->IsSales = 'true';

                            $account->Name = $name;

                            $account->AddressLine1 = $custAddr;

                            $account->City = $city;

                            $account->Postcode = $postcode;

                            $account->Country = $Country;

                            $account->Description = $name;

                            $account->Status = 'C';

                            $account->save();



                            $customers = $account->filter("trim(Email) eq '$email'");



                            foreach ($customers as $_customer)

                                $c_guid   = $_customer->ID;

                        } else {

                            foreach ($customers as $_customer)

                                $c_guid   = $_customer->ID;

                        }

                        $orderItems = $order->getAllItems();

                        $products_order = array();

                        $products_invoice = array();

                        $GoodsDeliverylines = array();



                        foreach ($orderItems as $_item) {

                            $code = $_item->getSku();

                            $Type = $_item->getProductType();

                            if ( $code != '' && $Type == 'simple' ) {

                                $item = new \Picqer\Financials\Exact\Item($connection);

                                $E_item = $item->filter("trim(Code) eq '$code'");

                                    foreach($E_item as $items)

                                        $item_id        = $items->ID;

                                if (isset($item_id)) {

                                    if ($discountPercentage != '') {

                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];

                                    }else{

                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity'=> $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(), 'OrderID'=> $OrderID, 'OrderNumber'=>$order_id,'VATCode'=>$VATCode];

                                    }

                                } else {

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

                                    if ($discountPercentage != '') {

                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(),'VATCode'=>$VATCode,'Discount'=>$discountPercentage];

                                    } else {

                                        $products_order[] = ['Description'=>$_item->getName(),'Item'=>$item_id,'UnitPrice'=>$_item->getPrice(),'Quantity' => $_item->getQtyOrdered(),'ItemCode'=>$_item->getSku(),'QuantityDelivered'=>$_item->getQtyOrdered(),'VATCode'=>$VATCode];

                                    }

                                }

                            }

                        }



                        $ShippingItem = new \Picqer\Financials\Exact\Item($connection);

                        $Ship_item = $ShippingItem->filter("trim(Code) eq '$shipping_method'");

                        foreach($Ship_item as $S_item)



                        if (isset($S_item->ID)) {

                            $products_order[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];

                        } else {

                            $p_ship = new \Picqer\Financials\Exact\Item($connection);

                            $p_ship->Code = $shipping_method;

                            $p_ship->CostPriceStandard = $shipPrice;

                            $p_ship->Description = $shippingDescription;

                            $p_ship->Unit = 'BOX';

                            $p_ship->IsSalesItem = true;

                            $p_ship->save();



                            $p_shipping = new \Picqer\Financials\Exact\Item($connection);

                            $Ship_item = $p_shipping->filter("trim(Code) eq '$shipping_method'");

                            foreach($Ship_item as $S_item)

                                $products_order[] = ['Description'=>$S_item->Description,'Item'=>$S_item->ID,'UnitPrice'=>$shipPrice,'Quantity'=>1,'VATCode'=>$VATCode];

                        }

                        

                        $order = new \Picqer\Financials\Exact\SalesOrder($connection);

                        $order->OrderedBy = $c_guid;

                        $order->OrderNumber = $order_id;

                        $order->InvoiceTo = $c_guid;

                        $order->Description = $order_inc_id;

                        $order->WarehouseID = $Warehouse_ID;

                        $order->AmountFC = $AmountFC;

                        $order->ShippingMethodDescription = $shipping_method;

                        $order->DeliverTo = $c_guid;

                        $order->SalesOrderLines = $products_order;

                        $order->save();



                        $exactOrder = new \Picqer\Financials\Exact\SalesOrder($connection);

                        $orders = $exactOrder->filter( "OrderNumber eq $order_id" );



                        foreach($orders as $_order)

                        $OrderID    = $_order->OrderID;

                        $c_guid    = $_order->OrderedBy;

                        $SalesOrder = $_order->SalesOrderLines;

                        $products_invoice = array();

                        $GoodsDeliverylines = array();



                        foreach ($SalesOrder as $_salesOrder) {



                            $sku = $_salesOrder->ItemCode;

                            $product_id = Mage::getModel("catalog/product")->getIdBySku( $sku );

                            $batches = Mage::getModel('batchcode/batchcode')

                                        ->getCollection()

                                        ->getBatchByProduct($product_id);

                            $BatchNumber = array();

                            foreach ($batches as $_batch) {

                                $BatchNumber[] = ['BatchNumber'=> $_batch->batch_number, 'Quantity'=> $_salesOrder->Quantity];

                            }



                            $GoodsDeliverylines[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'QuantityOrdered' => $_salesOrder->Quantity,'QuantityDelivered' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'SalesOrderLineNumber'=>$_salesOrder->OrderNumber, 'SalesOrderLineID'=>$_salesOrder->ID,'DeliveryDate'=>$_salesOrder->DeliveryDate,'BatchNumbers'=>$BatchNumber];

                            if ($discountPercentage != '') {

                                $products_invoice[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'Quantity' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'UnitPrice'=>$_salesOrder->UnitPrice,'VATCode'=>$VATCode,'Discount'=>$discountPercentage];

                            }else{

                                $products_invoice[] = ['Description'=>$_salesOrder->Description,'Item'=>$_salesOrder->Item,'Quantity' => $_salesOrder->Quantity,'ItemCode'=>$_salesOrder->ItemCode,'UnitPrice'=>$_salesOrder->UnitPrice,'VATCode'=>$VATCode];

                            }

                        }



                        $GoodsDeliveries = new \Picqer\Financials\Exact\GoodsDelivery($connection);

                        $GoodsDeliveries->GoodsDeliveryLines = $GoodsDeliverylines;

                        $GoodsDeliveries->TrackingNumber = $tracknumber;

                        $GoodsDeliveries->Creator = $c_guid;

                        $GoodsDeliveries->DeliveryDate = $date;

                        $GoodsDeliveries->Description = $order_inc_id;

                        $GoodsDeliveries->WarehouseID = $Warehouse_ID;

                        $GoodsDeliveries->save();





                        $salesInvoice = new \Picqer\Financials\Exact\SalesInvoice($connection);

                        $salesInvoice->Description = $invoiceIds;

                        $salesInvoice->OrderNumber = $order_id;

                        $salesInvoice->InvoiceTo = $c_guid;

                        $salesInvoice->OrderedBy = $c_guid;

                        $salesInvoice->DeliverTo = $c_guid;

                        $salesInvoice->YourRef = $order_id;

                        $salesInvoice->SalesInvoiceLines = $products_invoice;

                        $salesInvoice->save();



                        // $cashEntry = new \Picqer\Financials\Exact\CashEntry($connection);

                        // $cashEntry->ClosingBalanceFC = $OpeningBalanceFC;

                        // $cashEntry->OpeningBalanceFC = $OpeningBalanceFC;

                        // $cashEntry->JournalCode = 10;

                        // $cashEntry->CashEntryLines = $CashEntries;

                        // $cashEntry->save();

                    }

                Mage::log($orderData,null,'batchcode.log');

            }

            catch (\Exception $e) {

                get_class($e) . ' : ' . $e->getMessage();

                Mage::log($e->getMessage(),null,'batchcode_error.log');

            }

        }

    }



    public function connect()

    {

        $redirectUrl = Mage::getStoreConfig('catalog/batch/RedirectUrl');

        $clientId = Mage::getStoreConfig('catalog/batch/ExactClientId');

        $clientSecret = Mage::getStoreConfig('catalog/batch/ExactClientSecret');



        $connection = new \Picqer\Financials\Exact\Connection();

        $connection->setRedirectUrl($redirectUrl);

        $connection->setExactClientId($clientId);

        $connection->setExactClientSecret($clientSecret);

        

        // $connection = new \Picqer\Financials\Exact\Connection();

        // $connection->setRedirectUrl('http://staging.hello-day.com/index.php/batchcode/index/batch');

        // $connection->setExactClientId('354be896-915a-4b8e-b98d-c7b523bad43b');

        // $connection->setExactClientSecret('Jbhc5rSntQXf');

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

            //throw new Exception('Could not connect to Exact: ' . $e->getMessage());

            Mage::log($e->getMessage(), null, 'exactconnect.log', true);

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

