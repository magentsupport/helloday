<?php

class Helloday_Campaign_Block_Adminhtml_Campaignlog_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

	public function __construct()
	{
		parent::__construct();
		$this->setId("campaignlogGrid");
		$this->setDefaultSort("campaign_id");
		$this->setDefaultDir("DESC");
		$this->setSaveParametersInSession(true);
	}

	protected function _prepareCollection()
	{
		$collection = Mage::getModel("campaign/campaignlog")->getCollection();
		$this->setCollection($collection);
		return parent::_prepareCollection();
	}
	protected function _prepareColumns()
	{
		$this->addColumn("campaign_id", array(
			"header" => Mage::helper("campaign")->__("ID"),
			"align" =>"right",
			"width" => "50px",
			"type" => "number",
			"index" => "campaign_id",
		));

		$this->addColumn("customer_id", array(
			"header" => Mage::helper('campaign')->__("Customer ID"),
			"index" => "customer_id",
		));

		$this->addColumn('order_id', array(
			"header" => Mage::helper('campaign')->__('Order ID'),
			"index" => "order_id",
		));

		$this->addColumn('promo_used', array(
			"header" => Mage::helper('campaign')->__('Promo Used'),
			"index" => "promo_used",
		));

		$this->addColumn('coupon_code', array(
			"header" => Mage::helper('campaign')->__('Coupon Code Used'),
			"index" => "coupon_code",
		));

		$this->addColumn('order_return', array(
			'header' => Mage::helper('campaign')->__('Order Return'),
			'index' => 'order_return',
			'type' => 'options',
			'options'=> Helloday_Campaign_Block_Adminhtml_Campaignlog_Grid::getOptionArray4(),				
		));

		$this->addColumn('order_date', array(
			'header'    => Mage::helper('campaign')->__('Order Date'),
			'index'     => 'order_date',
			'type'      => 'datetime',
		));
		$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
		$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

		return parent::_prepareColumns();
	}

	public function getRowUrl($row)
	{
		//return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		return "#";
	}



	protected function _prepareMassaction()
	{
		$this->setMassactionIdField('campaign_id');
		$this->getMassactionBlock()->setFormFieldName('campaign_ids');
		$this->getMassactionBlock()->setUseSelectAll(true);
		$this->getMassactionBlock()->addItem('remove_campaignlog', array(
			'label'=> Mage::helper('campaign')->__('Remove Campaignlog'),
			'url'  => $this->getUrl('*/adminhtml_campaignlog/massRemove'),
			'confirm' => Mage::helper('campaign')->__('Are you sure?')
		));
		return $this;
	}

	static public function getOptionArray4()
	{
		//$data_array=array(); 
		$data_array = array(
	         0 => Mage::helper('campaign')->__('No'),
	         1 => Mage::helper('campaign')->__('Yes')
	     );

		return($data_array);
	}
	static public function getValueArray4()
	{
		$data_array=array(
            array(
                'value' => 0,
                'label' => Mage::helper('campaign')->__('No'),
            ),
            array(
                'value' => 1,
                'label' => Mage::helper('campaign')->__('Yes'),
            ),
        );
		return($data_array);
	}


}