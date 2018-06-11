<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("alter table aw_blog ADD column image VARCHAR(255) NULL AFTER title");
} catch (Exception $e) {
    
}
$installer->endSetup();
