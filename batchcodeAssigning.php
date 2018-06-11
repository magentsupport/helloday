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


$shipmentId = '100000110';//Increment id here
$templateID = '8';//Email template id here
// $OrderId = '78';//Order id here
$itemsQty = array(
	array(
			'ItemId' => 573,
			'ProductId' => 59,
			'Qty' => 1,
			'BatchCode' => 'D1702'
		)
	,
	array(
			'ItemId' => 574,
			'ProductId' => 60,
			'Qty' => 2,
			'BatchCode' => 'D1701'
		)
	,
	array(
			'ItemId' => 575,
			'ProductId' => 11,
			'Qty' => 1,
			'BatchCode' => '15321'
		)
	);

$result = $soap->call($session, 'batchcode.batchcodeAssign',array($itemsQty,$shipmentId,$templateID));
print_r($result);

echo "</pre>";
