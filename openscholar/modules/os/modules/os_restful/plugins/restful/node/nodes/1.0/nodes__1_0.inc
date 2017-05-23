<?php

$plugin = array(
  'label' => t('Nodes'),
  'resource' => 'nodes',
  'name' => 'nodes__1_0',
  'entity_type' => 'node',
  'bundle' => FALSE,
  'description' => t('Expose all bundles except personal'),
  'class' => 'NodesRestfulBase',
  'authentication_types' => TRUE,
  'authentication_optional' => TRUE,
);
