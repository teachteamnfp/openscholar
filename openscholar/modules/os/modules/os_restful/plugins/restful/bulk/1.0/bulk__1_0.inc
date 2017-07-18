<?php

$plugin = array(
  'label' => t('Bulk'),
  'resource' => 'bulk',
  'name' => 'bulk__1_0',
  'bundle' => FALSE,
  'description' => t('Bulk operation on entites'),
  'class' => 'bulkEnitityRestfulBase',
  'authentication_types' => TRUE,
  'authentication_optional' => TRUE,
);
