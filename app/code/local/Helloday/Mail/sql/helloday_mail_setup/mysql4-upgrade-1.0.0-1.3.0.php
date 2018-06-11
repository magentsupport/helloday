<?php
$installer = $this;
$installer->startSetup();

$this->run("
ALTER TABLE `{$this->getTable('core/email_template')}` MODIFY `template_text` LONGTEXT NOT NULL;
");


$this->endSetup();
