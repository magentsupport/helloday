<?php

class Helloday_Campaign_Block_Adminhtml_Campaignlog extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{
		$this->_controller = "adminhtml_campaignlog";
		$this->_blockGroup = "campaign";
		$this->_headerText = Mage::helper("campaign")->__("Campaignlog Manager");
		//$this->_addButtonLabel = Mage::helper("campaign")->__("Add New Item");
		parent::__construct();
		$this->_removeButton('add');
	}
}