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

$shipId = '100000039';//Increment id here

$sku = '10101SA-DIGES-2A';//Sku here

$batchId = '13435';//Batch id here

$templateId = '1';//Email template id her


$result = $soap->call($session, 'batchcode.batchcode',array($shipId,$sku,$batchId,$templateId));

print_r($result);

echo "</pre>";
