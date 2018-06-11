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
error_reporting(1);

class Bridge_Batchcode_IndexController extends Mage_Core_Controller_Front_Action
{
    /**
     * Front end entry point
     * Always redirects to the startup page url
     */
    public function indexAction()
    {
        $this->getLayout();
        $this->renderLayout();
    }

    function getValue($key)
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

	function setValue($key, $value)
	{
		$storage_file = __DIR__ . '/storage.json';
	    $storage       = json_decode(file_get_contents($storage_file), true);
	    $storage[$key] = $value;
	    file_put_contents($storage_file, json_encode($storage));
	}

	/**
	 * Function to authorize with Exact, this redirects to Exact login promt and retrieves authorization code
	 * to set up requests for oAuth tokens
	 */

	function authorize()
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

    public function batchAction()
    {
        if (isset($_GET['code']) && is_null($this->getValue('authorizationcode'))) {echo "testtt";
		    $this->setValue('authorizationcode', $_GET['code']);
		}

		// If we do not have a authorization code, authorize first to setup tokens
		if ($this->getValue('authorizationcode') === null) {
		    $this->authorize();
		}

		// Create the Exact client
		$connection = $this->connect();
		// echo $_SESSION["favcolor"];
		// Get the journals from our administration

		try {
			$account = new \Picqer\Financials\Exact\Account($connection);

			$account->Code = '2000';

			$account->AddressLine1 = 'test test';

			$account->IsSales = 'true';

			$account->Name = 'Mary';

			$account->Postcode = '365289';

			$account->Status = 'C';

			$account->save();

			// $account->Country = $customer['country'];

			// $account->AddressLine2 = $customer['address2'];

			// $account->City = $customer['city'];



			// $item = new \Picqer\Financials\Exact\Item($connection);

   //          $items = $item->filter("trim(Code) eq 'mary test1'");

   //          foreach($items as $item)

   //              $item_id        = $item->ID;

   //              $products[] = ['Description'=>'test test333','Item'=>$item_id,'UnitPrice'=>'10','Quantity'=>'1'];			

		

			// 	$Warehouse_ID   = "b11f50e9-f647-43e2-b3cb-56ff52e02945";

   //              // $Warehouse_ID   = "e9113af3-da31-4c7b-9608-cfd2d02802c8";//warehouse

   //              $Exact_order = new \Picqer\Financials\Exact\SalesOrder($connection);

   //              $Exact_order->OrderedBy = $c_guid;

   //              $Exact_order->OrderNumber = $order_id;

   //              // $order->InvoiceTo = $c_guid;

   //              $Exact_order->WarehouseID = $Warehouse_ID;

   //              // $order->ShippingMethod = $ShippingMethod;

   //              // $order->ShippingMethodDescription = $description;

   //              // $order->DeliverTo = $c_guid;

   //              $Exact_order->Status = '12';

   //              $Exact_order->SalesOrderLines = $products;

   //              $Exact_order->save();	    

			// $item = new \Picqer\Financials\Exact\Item($connection);

		    // $item->Code = 'mary test1';

		    // $item->CostPriceStandard = '10';

		    // $item->Description = 'test test333';

		    // $item->Unit = 'Box';

		    // $item->IsSalesItem = true;

		    // $item->save();

		    

		} catch (\Exception $e) {

		    echo get_class($e) . ' :: ' . $e->getMessage();

		}

    }

    function connect()
	{
		echo $redirectUrl = Mage::getStoreConfig('catalog/batch/RedirectUrl');

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
	        throw new Exception('Could not connect to Exact: ' . $e->getMessage());
	    }

	    // Save the new tokens for next connections

	    $this->setValue('accesstoken', $connection->getAccessToken());

	    $this->setValue('refreshtoken', $connection->getRefreshToken());

	    // Save expires time for next connections

	    $this->setValue('expires_in', $connection->getTokenExpires());

	    return $connection;
	}
}
