<?php

class Helloday_Campaign_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getBanchProducts()
	{

		$preproducts[1] = array(
        array(
            'product_id' => '59',
            'product_name' => 'Return Label Royal mail',
            'sku' => '19108-RETURN-18',
            'qty' => 1
        ), 
        array(
            'product_id' => '60',
            'product_name' => 'Carton box',
            'sku' => '18107-CODFC-18',
            'qty' => 1
        ), 
        array(
            'product_id' => '64',
            'product_name' => 'Sticker Hello Day',
            'sku' => '14108-STIHEL-17',
            'qty' => 2
        ), 
        array(
            'product_id' => '61',
            'product_name' => 'Leaflet Immunity Defence 20%',
            'sku' => '20110-IMMD20-18',
            'qty' => 1    
        ),
        array(
            'product_id' => '62',
            'product_name' => 'Small 2nd leaflet',
            'sku' => '20110-HOWTO-18',
            'qty' => 1
        ), 
    );

	$preproducts[2] = array(
	        array(
	            'product_id' => '59',
	            'product_name' => 'Return Label Royal mail',
	            'sku' => '19108-RETURN-18',
	            'qty' => 1
	        ), 
	        array(
	            'product_id' => '61',
	            'product_name' => 'Leaflet Immunity Defence 20%',
	            'sku' => '20110-IMMD20-18',
	            'qty' => 1    
	        ),
	        array(
	            'product_id' => '62',
	            'product_name' => 'Small 2nd leaflet',
	            'sku' => '20110-HOWTO-18',
	            'qty' => 1
	        ), 
	    );

	$preproducts[3] = array(
	        array(
	            'product_id' => '60',
	            'product_name' => 'Carton box',
	            'sku' => '18107-CODFC-18',
	            'qty' => 1
	        ), 
	        array(
	            'product_id' => '64',
	            'product_name' => 'Sticker Hello Day',
	            'sku' => '14108-STIHEL-17',
	            'qty' => 2
	        ), 
	        array(
	            'product_id' => '63',
	            'product_name' => 'Leaflet Vitality Summer 10%',
	            'sku' => '20110-V10S10-18',
	            'qty' => 1    
	        ),
	    );

	$preproducts[4] = array(
	        array(
	            'product_id' => '63',
	            'product_name' => 'Leaflet Vitality Summer 10%',
	            'sku' => '20110-V10S10-18',
	            'qty' => 1    
	        ),
	    );

		return $preproducts;
	}

	public function getBoxBanchProducts($carton_box_qty)
	{
		return array(
	        array(
	            'product_id' => '65',
	            'product_name' => 'Carton box',
	            'sku' => '14107-COFFR-17',
	            'qty' => $carton_box_qty
	        )
    	);
	}

	public function getDisallowProducts()
	{
		return array('20110-V10S10-18', '20110-HOWTO-18', '20110-IMMD20-18', '14107-COFFR-17', '19108-RETURN-18', '18109-CHRISTMAS-17', '14108-STIHEL-17', '18107-CODFC-18');
	}

	public function getBoxProducts()
	{
		return array('12104SA-SUMME-BOX17', '12104SA-SPRIN-BOX17', '12104SA-WINTE-BOX17', '12104SA-AUTOM-BOX17');
	}
}
	 