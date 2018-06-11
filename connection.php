<?php
$url = 'https://www.customweb.com/build/license/connection';

require_once 'lib/loader.php';
require_once 'lib/Customweb/Http/Request.php';

$request = new Customweb_Http_Request($url);
$request->setMethod('GET');
$request->deactivateCertifcateAuthorityCheck();
$request->appendCustomHeaders(array('Content-Type' => 'application/json'));
$request->appendCustomHeaders(array('Accept-Charset' => 'utf-8'));

try {
	$response = $request->send();
	if ($response->getStatusCode() == 200 && $response->getBody() == 'success') {
		echo "<h2>Success</h2><p>Succeeded accessing " . parse_url($url, PHP_URL_PATH) . ".</p>";
	} else {
		echo $response->getBody();
	}
} catch (Exception $e) {
	echo "<h2>Failed</h2><p>Problem accessing " . parse_url($url, PHP_URL_PATH) . ". Reason:</p><pre>\t" . $e->getMessage() . "</pre>";
}