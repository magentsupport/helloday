<?php 
	
class ES_Newssubscribers_Block_Renderer_Name extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{ 
	 public function render(Varien_Object $row) {
	     return $row->getData('subscriber_firstname').' '.$row->getData('subscriber_lastname');
	 }
}
?>