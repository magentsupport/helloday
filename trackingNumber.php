<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$url		=	'https://hello-day.com/';

$soapLogin	=	'anumary';

$soapPass	=	'hellodaymagentosoapkey';

try {
	$soap = new SoapClient($url.'/api/?wsdl');

	$session = $soap->login($soapLogin, $soapPass);
}
	catch(Exception $e) {

 	echo $e->getMessage();
}
//
// View orderinfo, 2nd parameter is ordernumber
echo "<pre>";

// $result = $soap->call($session, 'batchcode.batchcode_api',array($sku));
// print_r($result);

$result = $soap->call($session, 'sales_order_shipment.addTrack', array('shipmentIncrementId' => '100000134', 'carrier' => 'ups', 'title' => 'FDBHNJD', 'trackNumber' => '8592085828'));
print_r($result);

echo "</pre>";
