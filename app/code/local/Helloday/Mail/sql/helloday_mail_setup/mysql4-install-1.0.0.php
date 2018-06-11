<?php
$installer = $this;
$installer->startSetup();
$templates = array(
    array(
        "name" => "Helloday Header",
        "code" => "helloday_mail_header_template",
        "config" => "design/email/header",
        "subject" => "Newsletter subscription success"
    ),
    array(
        "name" => "Helloday Footer",
        "code" => "helloday_mail_footer_template",
        "config" => "design/email/footer",
    ),
    array(
        "name" => "Helloday Newsletter Signup",
        "code" => "helloday_newsletter_signup_template",
        "config" => "newsletter/subscription/success_email_template",
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
        "config" => "contacts/email/email_template",
        "subject" => "Contact Form"
    ),
    array(
        "name" => "Helloday New Order",
        "code" => "helloday_sales_email_order_template",
        "config" => array("sales_email/order/template","sales_email/order/guest_template"),
        "subject" => "{{var store.getFrontendName()}}: New Order # {{var order.increment_id}}"
    )
);
foreach ($templates as $template) {
    // Load email template from file
    $fileTemplate = Mage::getModel('core/email_template')->loadDefault($template["code"]);
    $subject = isset($template["subject"]) ? $template["subject"] : $fileTemplate->getTemplateSubject();
    // Create email template
    $templateDb = Mage::getModel('core/email_template')
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

$installer->setConfigData('design/email/css_non_inline', 'helloday/email-non-inline.css');

$installer->getConnection()
    ->addColumn($installer->getTable('core/email_template'),'mailchimp_template_id', array(
        'type'      => Varien_Db_Ddl_Table::TYPE_TEXT,
        'nullable'  => false,
        'length'    => 255,
        'comment'   => 'Mailchimp Template Id'
    ));

$this->endSetup();
