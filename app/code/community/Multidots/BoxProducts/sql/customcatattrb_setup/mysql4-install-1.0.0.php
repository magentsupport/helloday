<?php

$this->startSetup();
$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'short_description', array(
    'group' => 'General Information',
    'input' => 'textarea',
    'type' => 'text',
    'sort_order' => 2,
    'label' => 'Short Description',
    'backend' => '',
    'visible' => true,
    'required' => false,
    'visible_on_front' => true,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'default' => ""
));

$this->addAttribute(Mage_Catalog_Model_Category::ENTITY, 'wellbeing_image', array(
    'type' => 'varchar',
    'label' => 'Wellbeing Image',
    'input' => 'image',
    'sort_order' => 3,
    'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_STORE,
    'group' => 'General Information',
    'backend' => 'catalog/category_attribute_backend_image',
    'user_defined' => true,
    'visible' => true,
    'required' => false,)
);
?>