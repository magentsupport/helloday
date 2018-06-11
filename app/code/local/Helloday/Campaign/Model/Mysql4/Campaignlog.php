<?php
class Helloday_Campaign_Model_Mysql4_Campaignlog extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init("campaign/campaignlog", "campaign_id");
    }
}