<?php
	
class Multidots_CompSignup_Block_Adminhtml_Compsignup_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
		public function __construct()
		{

				parent::__construct();
				$this->_objectId = "id";
				$this->_blockGroup = "compsignup";
				$this->_controller = "adminhtml_compsignup";
				$this->_updateButton("save", "label", Mage::helper("compsignup")->__("Save Item"));
				$this->_updateButton("delete", "label", Mage::helper("compsignup")->__("Delete Item"));

				$this->_addButton("saveandcontinue", array(
					"label"     => Mage::helper("compsignup")->__("Save And Continue Edit"),
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
				if( Mage::registry("compsignup_data") && Mage::registry("compsignup_data")->getId() ){

				    return Mage::helper("compsignup")->__("Edit Item '%s'", $this->htmlEscape(Mage::registry("compsignup_data")->getId()));

				} 
				else{

				     return Mage::helper("compsignup")->__("Add Item");

				}
		}
}