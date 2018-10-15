<?php

class os_importer_views_handler_node_entry_delete_button extends views_handler_area_result {
  function options_form(&$form, &$form_state) {

  }

  function render($empty = FALSE) {
    if (!empty($this->view->args)) {
      $node = node_load($this->view->args[0]);
      if (empty($node->nid) || !node_access('delete', $node)) {
        return '';
      }
      if (!in_array($node->type, ['blog_import', 'feed_importer'])) {
        return '';
      }
      $form = drupal_get_form('os_importer_node_entry_delete_button_form', $node);
      return drupal_render($form);
    }
  }
}
