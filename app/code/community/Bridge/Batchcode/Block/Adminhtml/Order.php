<?php

/**
 * Bridge Batchcode
 *
 * @category      Bridge
 * @package       Bridge_Batchcode
 * @copyright     Copyright (c) 2013 Bridge India. (http://bridge-india.in)
 * @license       http://bridge-india.in/disclaimer/magento
 */
class Bridge_Batchcode_Block_Adminhtml_Order extends Mage_Adminhtml_Block_Widget_Grid_Container {

    public function __construct() {
        $this->_controller = 'adminhtml_order';
        $this->_blockGroup = 'batchcode';
        $batch_code = $this->getBatchCode();

        $this->_headerText = Mage::helper('batchcode')->__('Orders of batch code : ') . $batch_code;
        parent::__construct();
        $this->_removeButton('add');
    }

    /**
     * Get current batch number
     *
     * @return string
     */
    public function getBatchCode() {
        $params = $this->getRequest()->getPost();
        if(isset($params['batch_number']))
            {
        $batch_number = $params['batch_number'];
            }
        if (empty($batch_number)){
            if(isset($params['entity_id']))
            {
            $id = (int) $params['entity_id'];
            }
            if(empty($id)){
                $id = $this->getRequest()->getParam('id', null); //Get batch id
            }
            if ($id) {
                $batch_number = Mage::getModel('batchcode/batchcode')
                        ->load($id)
                        ->getBatchNumber();
            }
        }

        if (empty($batch_number)) {
            $batch_number = Mage::helper('batchcode')->__('Invalid');
        }

        return $batch_number;
    }

}
