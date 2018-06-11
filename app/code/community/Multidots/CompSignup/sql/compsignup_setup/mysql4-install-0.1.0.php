<?php

$installer = $this;

$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('comp_signup')};
CREATE TABLE {$this->getTable('comp_signup')} (
`id` int(11) unsigned NOT NULL auto_increment,
`title` varchar(255) NOT NULL,
`first_name` varchar(255) NOT NULL,
`surname` varchar(255) NOT NULL,
`email` varchar(255) NOT NULL,
`is_email` varchar(255) NOT NULL,
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

$installer->endSetup();

$installer->endSetup();

