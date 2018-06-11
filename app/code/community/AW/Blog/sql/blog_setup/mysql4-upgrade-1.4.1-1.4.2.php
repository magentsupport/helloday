<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("alter table aw_blog ADD column product_sku VARCHAR(255) NULL AFTER image");
} catch (Exception $e) {
    
}
$installer->endSetup();
