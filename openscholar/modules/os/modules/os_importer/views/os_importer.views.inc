<?php

/**
 * Implements hook_views_data().
 */
function os_importer_views_data() {
  $data = array();
  $data['views']['os_importer_node_entry_delete_button'] = array(
    'title' => t('Import entry delete button'),
    'help' => t('If blog or feed entry exists in contextual, this will render a delete button.'),
    'area' => array(
      'handler' => 'os_importer_views_handler_node_entry_delete_button',
    ),
  );

  return $data;

}
