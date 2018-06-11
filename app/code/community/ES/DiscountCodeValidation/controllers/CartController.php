<?php
require_once 'Mage/Checkout/controllers/CartController.php';

class ES_DiscountCodeValidation_CartController extends Mage_Checkout_CartController
{

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            // custom coupon code validation
            //$_validateExp = $this->_customCouponValidationExpDate($couponCode);

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                    $this->_getSession()->setCartCouponCode($couponCode);
                } else {
                    if(!Mage::getSingleton('customer/session')->isLoggedIn()) {
                        $this->_getSession()->addError(
                            $this->__('Coupon code "%s" is not valid. Please log in to redeem the code', Mage::helper('core')->escapeHtml($couponCode))
                        );
                    } else {
                        $this->_getSession()->addError(
                            $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                        );
                    }
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }


    public function _customCouponValidationExpDate($couponCode)
    {
        $codeValidTillTheDay = Mage::getStoreConfig('newsletter/coupon/couponcodelifetime');
        $couponCodeModel = Mage::getModel('salesrule/coupon');
        $coupons = $couponCodeModel->load($couponCode,'code');
        $currentDate = Mage::getModel('core/date')->date('Y-m-d H:i:s');

        $expiredDate =  Mage::getModel('core/date')->date('Y-m-d h:m:s', strtotime($coupons->getCreatedAt()." +$codeValidTillTheDay days"));

        if ( $currentDate <= $expiredDate ) {
            $_falg = 1;
        } else{
            $_falg = 0;
        }
        return $_falg;
    }

}

?>