<?php
$installer = $this;
$installer->startSetup();

$this->setConfigData('contacts/email/email_template', 'contacts_email_email_template');

$templates = array(
    array(
        "name" => "Helloday Newsletter Signup On Registration",
        "code" => "helloday_newsletter_signup_registration_template",
        "config" => "newsletter/subscription/success_email_template"
    ),
    array(
        "name" => "Helloday Newsletter Signup",
        "code" => "helloday_newsletter_signup_popup_template",
        "config" => "newsletter/subscription/success_email_template_popup"
    ),
    array(
        "name" => "Helloday Password reset confirmation",
        "code" => "helloday_customer_password_forgot_email_template",
        "config" => "customer/password/forgot_email_template",
        "subject" => "Password reset confirmation for {{var customer.name}}"
    ),
    array(
        "name" => "Helloday New account",
        "code" => "helloday_customer_create_account_email_template",
        "config" => "customer/create_account/email_template",
        "subject" => "Welcome, {{var customer.name}}!"
    ),
    array(
        "name" => "Helloday Contact Form",
        "code" => "helloday_contacts_email_email_template",
        "config" => "contacts/email/email_template_recipient",
        "subject" => "Thank You For Your Inquiry"
    ),
    array(
        "name" => "Helloday New Order",
        "code" => "helloday_sales_email_order_template",
        "config" => array("sales_email/order/template","sales_email/order/guest_template"),
        "subject" => "{{var store.getFrontendName()}}: New Order # {{var order.increment_id}}"
    ),
    array(
        "name" => "Helloday Newsletter featuring a theme",
        "code" => "helloday_newsletter_featuring_a_theme",
        "subject" => "Helloday featuring a theme"
    ),
    array(
        "name" => "Helloday Newsletter featuring our seasonal box",
        "code" => "helloday_newsletter_featuring_our_seasonal_box",
        "subject" => "Helloday featuring our seasonal box"
    )
);

foreach ($templates as $template) {

    // Load email template from file
    $fileTemplate = Mage::getModel('core/email_template')->loadDefault($template["code"]);
    $subject = isset($template["subject"]) ? $template["subject"] : $fileTemplate->getTemplateSubject();
    // Create email template
    /**
     * @var Mage_Core_Model_Email_Template $templateDb
     */
    $templateDb = Mage::getModel('core/email_template')->load($template["name"], 'template_code');
    $templateDb
        ->setTemplateCode($template["name"])
        ->setTemplateSubject($subject)
        ->setTemplateText($fileTemplate->getTemplateText())
        ->setTemplateStyles($fileTemplate->getTemplateStyles())
        ->setModifiedAt(Mage::getSingleton('core/date')->gmtDate())
        ->setOrigTemplateCode($template["code"])
        ->setOrigTemplateVariables($fileTemplate->getOrigTemplateVariables())
        ->setTemplateType(Mage_Core_Model_Email_Template::TYPE_HTML)
        ->save();
    // Set this template in config
    if (isset($template["config"])) {
        if (is_array($template["config"])) {
            foreach ($template["config"] as $configPath) {
                $installer->setConfigData($configPath, $templateDb->getId());
            }
        } else {
            $installer->setConfigData($template["config"], $templateDb->getId());
        }
    }
}

$this->endSetup();
