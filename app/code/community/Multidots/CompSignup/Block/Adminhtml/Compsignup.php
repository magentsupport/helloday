<?php


class Multidots_CompSignup_Block_Adminhtml_Compsignup extends Mage_Adminhtml_Block_Widget_Grid_Container{

	public function __construct()
	{

	$this->_controller = "adminhtml_compsignup";
	$this->_blockGroup = "compsignup";
	$this->_headerText = Mage::helper("compsignup")->__("Compsignup Manager");
	$this->_addButtonLabel = Mage::helper("compsignup")->__("Add New Item");
	parent::__construct();
	
	}

}