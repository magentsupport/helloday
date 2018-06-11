<?php

class Multidots_CompSignup_Block_Adminhtml_Compsignup_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

		public function __construct()
		{
				parent::__construct();
				$this->setId("compsignupGrid");
				$this->setDefaultSort("id");
				$this->setDefaultDir("DESC");
				$this->setSaveParametersInSession(true);
		}

		protected function _prepareCollection()
		{
				$collection = Mage::getModel("compsignup/compsignup")->getCollection();
				$this->setCollection($collection);
				return parent::_prepareCollection();
		}
		protected function _prepareColumns()
		{
				$this->addColumn("id", array(
				"header" => Mage::helper("compsignup")->__("ID"),
				"align" =>"right",
				"width" => "50px",
			    "type" => "number",
				"index" => "id",
				));
                
				$this->addColumn("title", array(
				"header" => Mage::helper("compsignup")->__("Title"),
				"index" => "title",
				));
				$this->addColumn("first_name", array(
				"header" => Mage::helper("compsignup")->__("First Name"),
				"index" => "first_name",
				));
				$this->addColumn("surname", array(
				"header" => Mage::helper("compsignup")->__("Surname"),
				"index" => "surname",
				));
				$this->addColumn("email", array(
				"header" => Mage::helper("compsignup")->__("E-mail"),
				"index" => "email",
				));
						$this->addColumn('is_email', array(
						'header' => Mage::helper('compsignup')->__('Electronic marketing messages'),
						'index' => 'is_email',
						'type' => 'options',
						'options'=>Multidots_CompSignup_Block_Adminhtml_Compsignup_Grid::getOptionArray4(),				
						));
						
			$this->addExportType('*/*/exportCsv', Mage::helper('sales')->__('CSV')); 
			$this->addExportType('*/*/exportExcel', Mage::helper('sales')->__('Excel'));

				return parent::_prepareColumns();
		}

		public function getRowUrl($row)
		{
			   return $this->getUrl("*/*/edit", array("id" => $row->getId()));
		}


		
		protected function _prepareMassaction()
		{
			$this->setMassactionIdField('id');
			$this->getMassactionBlock()->setFormFieldName('ids');
			$this->getMassactionBlock()->setUseSelectAll(true);
			$this->getMassactionBlock()->addItem('remove_compsignup', array(
					 'label'=> Mage::helper('compsignup')->__('Remove Compsignup'),
					 'url'  => $this->getUrl('*/adminhtml_compsignup/massRemove'),
					 'confirm' => Mage::helper('compsignup')->__('Are you sure?')
				));
			return $this;
		}
			
		static public function getOptionArray4()
		{
            $data_array=array(); 
			$data_array[0]='Yes';
			$data_array[1]='No';
            return($data_array);
		}
		static public function getValueArray4()
		{
            $data_array=array();
			foreach(Multidots_CompSignup_Block_Adminhtml_Compsignup_Grid::getOptionArray4() as $k=>$v){
               $data_array[]=array('value'=>$k,'label'=>$v);		
			}
            return($data_array);

		}
		

}