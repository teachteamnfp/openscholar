<?php

$plugin = array(
  'label' => t('List content type'),
  'description' => t('End point for cp content filter options.'),
  'resource' => 'content_types',
  'class' => 'OsContentTypeList',
  'authentication_types' => TRUE,
  'authentication_optional' => TRUE
);
