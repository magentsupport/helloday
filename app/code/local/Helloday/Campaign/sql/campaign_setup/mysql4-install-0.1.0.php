<?php
$installer = $this;
$installer->startSetup();
$sql=<<<SQLTEXT
create table campaign_log(
campaign_id int not null auto_increment, 
customer_id int(11) not null,
order_id varchar(50) not null,
promo_used int(11) not null,
coupon_code varchar(255) default null,
customer_group_id int(11),
order_return smallint(6) NOT NULL DEFAULT '0',
order_date timestamp,
primary key(campaign_id));		
SQLTEXT;

$installer->run($sql);

$installer->endSetup();
	 