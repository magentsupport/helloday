<?php
/**
 * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2016 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
 *
 * @category	Customweb
 * @package		Customweb_BarclaycardCw
 */

class Customweb_BarclaycardCw_Model_Source_PayPalCurrency
{
	public function toOptionArray()
	{
		$options = array(
			array('value' => 'AED', 'label' => Mage::helper('adminhtml')->__("United Arab Emirates dirham (AED)")),
			array('value' => 'ANG', 'label' => Mage::helper('adminhtml')->__("Netherlands Antillean guilder (ANG)")),
			array('value' => 'ARS', 'label' => Mage::helper('adminhtml')->__("Argentine peso (ARS)")),
			array('value' => 'AUD', 'label' => Mage::helper('adminhtml')->__("Australian dollar (AUD)")),
			array('value' => 'AWG', 'label' => Mage::helper('adminhtml')->__("Aruban florin (AWG)")),
			array('value' => 'BGN', 'label' => Mage::helper('adminhtml')->__("Bulgarian lev (BGN)")),
			array('value' => 'BRL', 'label' => Mage::helper('adminhtml')->__("Brazilian real (BRL)")),
			array('value' => 'BYR', 'label' => Mage::helper('adminhtml')->__("Belarusian ruble (BYR)")),
			array('value' => 'CAD', 'label' => Mage::helper('adminhtml')->__("Canadian dollar (CAD)")),
			array('value' => 'CHF', 'label' => Mage::helper('adminhtml')->__("Swiss franc (CHF)")),
			array('value' => 'CNY', 'label' => Mage::helper('adminhtml')->__("Chinese yuan (CNY)")),
			array('value' => 'CZK', 'label' => Mage::helper('adminhtml')->__("Czech koruna (CZK)")),
			array('value' => 'DKK', 'label' => Mage::helper('adminhtml')->__("Danish krone (DKK)")),
			array('value' => 'EGP', 'label' => Mage::helper('adminhtml')->__("Egyptian pound (EGP)")),
			array('value' => 'EUR', 'label' => Mage::helper('adminhtml')->__("Euro (EUR)")),
			array('value' => 'GBP', 'label' => Mage::helper('adminhtml')->__("Pound sterling (GBP)")),
			array('value' => 'GEL', 'label' => Mage::helper('adminhtml')->__("Georgian lari (GEL)")),
			array('value' => 'HKD', 'label' => Mage::helper('adminhtml')->__("Hong Kong dollar (HKD)")),
			array('value' => 'HRK', 'label' => Mage::helper('adminhtml')->__("Croatian kuna (HRK)")),
			array('value' => 'HUF', 'label' => Mage::helper('adminhtml')->__("Hungarian forint (HUF)")),
			array('value' => 'ILS', 'label' => Mage::helper('adminhtml')->__("Israeli new shekel (ILS)")),
			array('value' => 'ISK', 'label' => Mage::helper('adminhtml')->__("Icelandic króna (ISK)")),
			array('value' => 'JPY', 'label' => Mage::helper('adminhtml')->__("Japanese yen (JPY)")),
			array('value' => 'KRW', 'label' => Mage::helper('adminhtml')->__("South Korean won (KRW)")),
			array('value' => 'LTL', 'label' => Mage::helper('adminhtml')->__("Lithuanian litas (LTL)")),
			array('value' => 'LVL', 'label' => Mage::helper('adminhtml')->__("Latvian lats (LVL)")),
			array('value' => 'MAD', 'label' => Mage::helper('adminhtml')->__("Moroccan dirham (MAD)")),
			array('value' => 'MXN', 'label' => Mage::helper('adminhtml')->__("Mexican peso (MXN)")),
			array('value' => 'NOK', 'label' => Mage::helper('adminhtml')->__("Norwegian krone (NOK)")),
			array('value' => 'NZD', 'label' => Mage::helper('adminhtml')->__("New Zealand dollar (NZD)")),
			array('value' => 'PLN', 'label' => Mage::helper('adminhtml')->__("Polish złoty (PLN)")),
			array('value' => 'RON', 'label' => Mage::helper('adminhtml')->__("Romanian new leu (RON)")),
			array('value' => 'RUB', 'label' => Mage::helper('adminhtml')->__("Russian rouble (RUB)")),
			array('value' => 'SEK', 'label' => Mage::helper('adminhtml')->__("Swedish krona (SEK)")),
			array('value' => 'SGD', 'label' => Mage::helper('adminhtml')->__("Singapore dollar (SGD)")),
			array('value' => 'THB', 'label' => Mage::helper('adminhtml')->__("Thai baht (THB)")),
			array('value' => 'TRY', 'label' => Mage::helper('adminhtml')->__("Turkish lira (TRY)")),
			array('value' => 'UAH', 'label' => Mage::helper('adminhtml')->__("Ukrainian hryvnia (UAH)")),
			array('value' => 'USD', 'label' => Mage::helper('adminhtml')->__("United States dollar (USD)")),
			array('value' => 'XAF', 'label' => Mage::helper('adminhtml')->__("CFA franc BEAC (XAF)")),
			array('value' => 'XOF', 'label' => Mage::helper('adminhtml')->__("CFA franc BCEAO (XOF)")),
			array('value' => 'XPF', 'label' => Mage::helper('adminhtml')->__("CFP franc (XPF)")),
			array('value' => 'ZAR', 'label' => Mage::helper('adminhtml')->__("South African rand (ZAR)"))
		);
		return $options;
	}
}
