<?php

class Helloday_Campaign_Adminhtml_CampaignlogController extends Mage_Adminhtml_Controller_Action
{
		protected function _isAllowed()
		{
		//return Mage::getSingleton('admin/session')->isAllowed('campaign/campaignlog');
			return true;
		}

		protected function _initAction()
		{
				$this->loadLayout()->_setActiveMenu("campaign/campaignlog")->_addBreadcrumb(Mage::helper("adminhtml")->__("Campaignlog  Manager"),Mage::helper("adminhtml")->__("Campaignlog Manager"));
				return $this;
		}
		public function indexAction() 
		{
			    $this->_title($this->__("Campaign"));
			    $this->_title($this->__("Manager Campaignlog"));

				$this->_initAction();
				$this->renderLayout();
		}
		public function editAction()
		{			    
			    $this->_title($this->__("Campaign"));
				$this->_title($this->__("Campaignlog"));
			    $this->_title($this->__("Edit Item"));
				
				$id = $this->getRequest()->getParam("id");
				$model = Mage::getModel("campaign/campaignlog")->load($id);
				if ($model->getId()) {
					Mage::register("campaignlog_data", $model);
					$this->loadLayout();
					$this->_setActiveMenu("campaign/campaignlog");
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Campaignlog Manager"), Mage::helper("adminhtml")->__("Campaignlog Manager"));
					$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Campaignlog Description"), Mage::helper("adminhtml")->__("Campaignlog Description"));
					$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);
					$this->_addContent($this->getLayout()->createBlock("campaign/adminhtml_campaignlog_edit"))->_addLeft($this->getLayout()->createBlock("campaign/adminhtml_campaignlog_edit_tabs"));
					$this->renderLayout();
				} 
				else {
					Mage::getSingleton("adminhtml/session")->addError(Mage::helper("campaign")->__("Item does not exist."));
					$this->_redirect("*/*/");
				}
		}

		public function newAction()
		{

		$this->_title($this->__("Campaign"));
		$this->_title($this->__("Campaignlog"));
		$this->_title($this->__("New Item"));

        $id   = $this->getRequest()->getParam("id");
		$model  = Mage::getModel("campaign/campaignlog")->load($id);

		$data = Mage::getSingleton("adminhtml/session")->getFormData(true);
		if (!empty($data)) {
			$model->setData($data);
		}

		Mage::register("campaignlog_data", $model);

		$this->loadLayout();
		$this->_setActiveMenu("campaign/campaignlog");

		$this->getLayout()->getBlock("head")->setCanLoadExtJs(true);

		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Campaignlog Manager"), Mage::helper("adminhtml")->__("Campaignlog Manager"));
		$this->_addBreadcrumb(Mage::helper("adminhtml")->__("Campaignlog Description"), Mage::helper("adminhtml")->__("Campaignlog Description"));


		$this->_addContent($this->getLayout()->createBlock("campaign/adminhtml_campaignlog_edit"))->_addLeft($this->getLayout()->createBlock("campaign/adminhtml_campaignlog_edit_tabs"));

		$this->renderLayout();

		}
		public function saveAction()
		{

			$post_data=$this->getRequest()->getPost();


				if ($post_data) {

					try {

						

						$model = Mage::getModel("campaign/campaignlog")
						->addData($post_data)
						->setId($this->getRequest()->getParam("id"))
						->save();

						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Campaignlog was successfully saved"));
						Mage::getSingleton("adminhtml/session")->setCampaignlogData(false);

						if ($this->getRequest()->getParam("back")) {
							$this->_redirect("*/*/edit", array("id" => $model->getId()));
							return;
						}
						$this->_redirect("*/*/");
						return;
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						Mage::getSingleton("adminhtml/session")->setCampaignlogData($this->getRequest()->getPost());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					return;
					}

				}
				$this->_redirect("*/*/");
		}



		public function deleteAction()
		{
				if( $this->getRequest()->getParam("id") > 0 ) {
					try {
						$model = Mage::getModel("campaign/campaignlog");
						$model->setId($this->getRequest()->getParam("id"))->delete();
						Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item was successfully deleted"));
						$this->_redirect("*/*/");
					} 
					catch (Exception $e) {
						Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
						$this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
					}
				}
				$this->_redirect("*/*/");
		}

		
		public function massRemoveAction()
		{
			try {
				$ids = $this->getRequest()->getPost('campaign_ids', array());
				foreach ($ids as $id) {
                      $model = Mage::getModel("campaign/campaignlog");
					  $model->setId($id)->delete();
				}
				Mage::getSingleton("adminhtml/session")->addSuccess(Mage::helper("adminhtml")->__("Item(s) was successfully removed"));
			}
			catch (Exception $e) {
				Mage::getSingleton("adminhtml/session")->addError($e->getMessage());
			}
			$this->_redirect('*/*/');
		}
			
		/**
		 * Export order grid to CSV format
		 */
		public function exportCsvAction()
		{
			$fileName   = 'campaignlog.csv';
			$grid       = $this->getLayout()->createBlock('campaign/adminhtml_campaignlog_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
		} 
		/**
		 *  Export order grid to Excel XML format
		 */
		public function exportExcelAction()
		{
			$fileName   = 'campaignlog.xml';
			$grid       = $this->getLayout()->createBlock('campaign/adminhtml_campaignlog_grid');
			$this->_prepareDownloadResponse($fileName, $grid->getExcelFile($fileName));
		}
}
