<?php

$url		=	'http://staging.hello-day.com/';

$soapLogin	=	'Gert-Jan';

$soapPass	=	'7qYhuAfGim9u';

try {

	$soap = new SoapClient($url.'/api/?wsdl');

	$session = $soap->login($soapLogin, $soapPass);
}

	catch(Exception $e) {

 	echo $e->getMessage();

}

echo "<pre>";

//parameter is order id : You can pass the shipment increment id here

$ship_increment_id = '100000013';

$result = $soap->call($session, 'batchcode.batchcode_api',array($ship_increment_id));

print_r($result);

echo "</pre>";
