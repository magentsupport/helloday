<?php
class Helloday_Campaign_Block_Adminhtml_Campaignlog_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
	public function __construct()
	{
		parent::__construct();
		$this->setId("campaignlog_tabs");
		$this->setDestElementId("edit_form");
		$this->setTitle(Mage::helper("campaign")->__("Item Information"));
	}
	protected function _beforeToHtml()
	{
		$this->addTab("form_section", array(
			"label" => Mage::helper("campaign")->__("Item Information"),
			"title" => Mage::helper("campaign")->__("Item Information"),
			"content" => $this->getLayout()->createBlock("campaign/adminhtml_campaignlog_edit_tab_form")->toHtml(),
		));
		return parent::_beforeToHtml();
	}

}
