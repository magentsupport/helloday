<?php

/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Blog
 * @version    tip
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */
class AW_Blog_Block_Manage_Blog_Edit_Tab_Products extends Mage_Adminhtml_Block_Widget_Form {

    public function __construct() {
        parent::__construct();
        $this->setTemplate('aw_blog/product.phtml');
    }

    protected function getProductIds($postID) {


        if ($postID) {
            $_productList = array();
            $prd_model = Mage::getModel('blog/blog')->load($postID);
            if (!empty($prd_model['product_sku']) && $prd_model['product_sku'] != null) {
                $_productList = explode(',', $prd_model['product_sku']);
            }
        }

        return is_array($_productList) ? $_productList : null;
    }

    public function getIdsString($_post_id) {

        $productsId = $this->getProductIds($_post_id);
        if (count($productsId) > 0) {
            return implode(', ', $productsId);
        } else {
            return null;
        }
    }

}
