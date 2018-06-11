<?php

class Helloday_Campaign_Block_Adminhtml_Campaignlog_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
	public function __construct()
	{

		parent::__construct();
		$this->_objectId = "campaign_id";
		$this->_blockGroup = "campaign";
		$this->_controller = "adminhtml_campaignlog";
		$this->_updateButton("save", "label", Mage::helper("campaign")->__("Save Item"));
		$this->_updateButton("delete", "label", Mage::helper("campaign")->__("Delete Item"));

		$this->_addButton("saveandcontinue", array(
			"label"     => Mage::helper("campaign")->__("Save And Continue Edit"),
			"onclick"   => "saveAndContinueEdit()",
			"class"     => "save",
		), -100);



		$this->_formScripts[] = "

		function saveAndContinueEdit(){
			editForm.submit($('edit_form').action+'back/edit/');
		}
		";
	}

	public function getHeaderText()
	{
		if( Mage::registry("campaignlog_data") && Mage::registry("campaignlog_data")->getId() ){

			return Mage::helper("campaign")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("campaignlog_data")->getId()));

		} 
		else{

			return Mage::helper("campaign")->__("Add Item");

		}
	}
}