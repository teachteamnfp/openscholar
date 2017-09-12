<?php
$plugin = array(
  'label' => t('vsite themes'),
  'description' => t('End point for vsite themes.'),
  'resource' => 'themes',
  'name' => 'vsite_themes__1_0',
  'class' => 'OsRestfulThemes',
  'authentication_types' => TRUE,
  'authentication_optional' => TRUE,
);