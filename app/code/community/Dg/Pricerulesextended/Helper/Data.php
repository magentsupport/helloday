<?php
/***
 * Dg_Pricerulesextended is licensed under a Creative Commons Attribution-ShareAlike 3.0 Unported License (http://creativecommons.org/licenses/by-sa/3.0/)
 * Original work at www.drewgillson.com
***/
class Dg_Pricerulesextended_Helper_Data extends Mage_Core_Helper_Abstract {

	private function generatePromoCode($length = null) {
		$rndId = crypt(uniqid(rand(),1)); 
		$rndId = strip_tags(stripslashes($rndId)); 
		$rndId = str_replace(array(".", "$"),"",$rndId); 
		$rndId = strrev(str_replace("/","",$rndId));
		if (!is_null($rndId)){
			return strtoupper(substr($rndId, 0, $length));
		} 
		return strtoupper($rndId);
	} 

	public function addPromoCode($email, $value, $min = null, $max = null, $expires = 0) {

        $promo_name = '$' . $value . ' credit for ' . $email;

        $redeemed = false;
        $collection = Mage::getModel('salesrule/rule')->getResourceCollection();
        foreach ($collection as $rule) {
                if ($rule->getName() == $promo_name) {
                        $redeemed = true;
                }
        }

        if ($redeemed) {
        	Mage::getSingleton('core/session')->addNotice('A credit was already redeemed for ' . $email);                
        }
        else {
			$uniqueId = $this->generatePromoCode(6);

			$customerGroups = Mage::getModel('customer/group')->getCollection();
			$groups = array();
			foreach ($customerGroups as $group){
				$groups[] = $group->getId();
			}

			$rule = Mage::getModel('salesrule/rule');
			$rule->setName($promo_name);
			$rule->setDescription('Generated automatically by Dg/PriceRulesExtended');
			$rule->setFromDate(date('Y-m-d'));
			$rule->setToDate($expires);
			$rule->setCouponCode($uniqueId);
			$rule->setUsesPerCoupon(1);
			$rule->setUsesPerCustomer(1);
			$rule->setCustomerGroupIds($groups);
			$rule->setIsActive(1);
			$rule->setStopRulesProcessing(1);
			$rule->setIsRss(0);
			$rule->setIsAdvanced(1);
			$rule->setProductIds('');
			$rule->setSortOrder(0);			 
			$rule->setSimpleAction('cart_fixed');
			$rule->setDiscountAmount($value);
			$rule->setDiscountQty(0);
			$rule->setDiscountStep(0);
			$rule->setSimpleFreeShipping(0);
			$rule->setApplyToShipping(0);
			$rule->setWebsiteIds(1);
			 
			// Thanks to http://marius-strajeru.blogspot.ca/2010/04/create-bulk-discount-rules.html

			if ($min) {
				$conditions = array();
				$conditions[1] = array(
				'type' => 'salesrule/rule_condition_combine',
				'aggregator' => 'all',
				'value' => 1,
				'new_child' => ''
				);

				$conditions['1--1'] = Array
				(
				'type' => 'salesrule/rule_condition_address',
				'attribute' => 'base_subtotal',
				'operator' => '>=',
				'value' => $min
				);

				if ($max) {
					$conditions['1--2'] = Array
					(
					'type' => 'salesrule/rule_condition_address',
					'attribute' => 'base_subtotal',
					'operator' => '<',
					'value' => $max
					);
				}

				$rule->setData('conditions',$conditions);
			}

			$rule->loadPost($rule->getData());
			$rule->setCouponType(2);

			$labels = array();
			$labels[1] = '$' . $value . ' credit';
			
			$rule->setStoreLabels($labels);
			
			$rule->save();

            $email_text = Mage::getStoreConfig('pricerulesextended/promocode/emailtext');
            if (Mage::getStoreConfig('pricerulesextended/email/enable')) {
                $this->sendEmail($email, $email_text, $uniqueId, $value, $min);
                Mage::getSingleton('core/session')->addNotice('A credit of $' . $value . ' was sent to ' . $email);
            }
			return $uniqueId;
		}
	}

	public function sendEmail($email, $template, $code, $value = null, $min = null) {

		if (!empty($code)) {

			$subject = 'You have a $' . $value . ' credit at ' . Mage::app()->getStore()->getName();

			$template = str_replace('{promo_code}', $code, $template);
			$template = str_replace('{promo_value}', $value, $template);
			$template = str_replace('{promo_min}', $min, $template);

			if ($subject != '' && $template != '') {
				$from = Mage::getStoreConfig('trans_email/ident_general/email');
				$headers  = 'MIME-Version: 1.0' . "\r\n";
				$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
				$headers .= 'From: ' . Mage::app()->getStore()->getName() . ' <' . $from . '>' . "\r\n";
		
				mail($email, $subject, $template, $headers);
			}
		}

		// temporarily disable SMTP (this prevents the _default_ newsletter success message from being sent)
		Mage::app()->getStore()->setConfig('system/smtp/disable', '1', 'default', 0);
	}
}