<?php
class Helloday_Campaign_Block_Adminhtml_Campaignlog_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
	protected function _prepareForm()
	{

		$form = new Varien_Data_Form();
		$this->setForm($form);
		$fieldset = $form->addFieldset("campaign_form", array("legend"=>Mage::helper("campaign")->__("Item information")));


		$fieldset->addField("customer_id", "text", array(
			"label" => Mage::helper("campaign")->__("Customer ID"),					
			"class" => "required-entry",
			"required" => true,
			"name" => "customer_id",
		));

		$fieldset->addField("order_id", "text", array(
			"label" => Mage::helper("campaign")->__("Order ID"),					
			"class" => "required-entry",
			"required" => true,
			"name" => "order_id",
		));

		$fieldset->addField("promo_used", "text", array(
			"label" => Mage::helper("campaign")->__("Promo Used"),					
			"class" => "required-entry",
			"required" => true,
			"name" => "promo_used",
		));

		$fieldset->addField("coupon_code", "text", array(
			"label" => Mage::helper("campaign")->__("Coupon Code Used"),
			"name" => "coupon_code",
		));

		$fieldset->addField('order_return', 'select', array(
			'label'     => Mage::helper('campaign')->__('Order Return Request'),
			'values'   => Helloday_Campaign_Block_Adminhtml_Campaignlog_Grid::getValueArray4(),
			'name' => 'order_return',
		));
		$dateFormatIso = Mage::app()->getLocale()->getDateTimeFormat(
			Mage_Core_Model_Locale::FORMAT_TYPE_SHORT
		);

		$fieldset->addField('order_date', 'date', array(
			'label'        => Mage::helper('campaign')->__('Order Date'),
			'name'         => 'order_date',
			'time' => true,
			'image'        => $this->getSkinUrl('images/grid-cal.gif'),
			'format'       => $dateFormatIso
		));

		if (Mage::getSingleton("adminhtml/session")->getCampaignlogData())
			{
				$form->setValues(Mage::getSingleton("adminhtml/session")->getCampaignlogData());
				Mage::getSingleton("adminhtml/session")->setCampaignlogData(null);
			} 
			elseif(Mage::registry("campaignlog_data")) {
				$form->setValues(Mage::registry("campaignlog_data")->getData());
			}
			return parent::_prepareForm();
		}
	}
