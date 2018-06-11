<?php
class Multidots_CompSignup_Block_Adminhtml_Compsignup_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
		protected function _prepareForm()
		{

				$form = new Varien_Data_Form();
				$this->setForm($form);
				$fieldset = $form->addFieldset("compsignup_form", array("legend"=>Mage::helper("compsignup")->__("Item information")));

				
						$fieldset->addField("title", "text", array(
						"label" => Mage::helper("compsignup")->__("Title"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "title",
						));
					
						$fieldset->addField("first_name", "text", array(
						"label" => Mage::helper("compsignup")->__("First Name"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "first_name",
						));
					
						$fieldset->addField("surname", "text", array(
						"label" => Mage::helper("compsignup")->__("Surname"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "surname",
						));
					
						$fieldset->addField("email", "text", array(
						"label" => Mage::helper("compsignup")->__("E-mail"),					
						"class" => "required-entry",
						"required" => true,
						"name" => "email",
						));
									
						 $fieldset->addField('is_email', 'select', array(
						'label'     => Mage::helper('compsignup')->__('Electronic marketing messages'),
						'values'   => Multidots_CompSignup_Block_Adminhtml_Compsignup_Grid::getValueArray4(),
						'name' => 'is_email',
						));

				if (Mage::getSingleton("adminhtml/session")->getCompsignupData())
				{
					$form->setValues(Mage::getSingleton("adminhtml/session")->getCompsignupData());
					Mage::getSingleton("adminhtml/session")->setCompsignupData(null);
				} 
				elseif(Mage::registry("compsignup_data")) {
				    $form->setValues(Mage::registry("compsignup_data")->getData());
				}
				return parent::_prepareForm();
		}
}
