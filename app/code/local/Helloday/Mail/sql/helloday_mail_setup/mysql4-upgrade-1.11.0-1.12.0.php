<?php
$installer = $this;
$installer->startSetup();

$this->setConfigData('contacts/email/email_template', 'contacts_email_email_template');

$templates = array(
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
