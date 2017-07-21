<?php

$plugin = array(
  'label' => t('Nodes'),
  'resource' => 'node',
  'name' => 'node__1_0',
  'entity_type' => 'node',
  'bundle' => FALSE,
  'description' => t('Expose all bundles except couple of bundles'),
  'class' => 'NodeRestfulBase',
  'authentication_types' => TRUE,
  'authentication_optional' => TRUE,
);
