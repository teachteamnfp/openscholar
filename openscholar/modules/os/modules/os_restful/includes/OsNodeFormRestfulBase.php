<?php

class OsNodeFormRestfulBase extends RestfulEntityBaseNode {

  public static function controllersInfo() {
    return array(
      'form' => array(
        RestfulInterface::GET => 'getNodeForm'
      )
    );
  }

  /**
   * Get node form based on bundle.
   */
  public function getNodeForm() {
    if (!empty($this->request['nid'])) {
      $node = node_load($this->request['nid']);
    }
    else {
      $node = new stdClass;
      $node->type = $this->getBundle();
      node_object_prepare($node);
    }
    $form = array();
    $form_state = array();
    $options = array();
    $form_state['node'] = $node;
    $form['#bundle'] = $node->type;

    $function = node_type_get_base($node) . '_form';
    if (function_exists($function) && ($extra = $function($node, $form_state))) {
      // @todo: We need to look for a better solution on this.
      unset($extra['#validate']);
      unset($extra['#cache']);
      foreach ($extra as $key => $form_field) {
        $form[$key] = $form_field;
      }
    }
    $extra_fields =  _field_invoke_default('form', 'node', $node, $form, $form_state, $options);
    foreach ($extra_fields as $key => $field) {
      $extra_info = array();
      $field_info = field_info_instance('node', $field[LANGUAGE_NONE]['#field_name'], $node->type);
      if ($field_info['widget']['type'] == 'media_draggable_file') {
        $extra_info = array(
          '#upload_location' => $field[LANGUAGE_NONE]['drop']['#upload_location'],
          '#upload_validators' => $field[LANGUAGE_NONE]['drop']['#upload_validators'],
        );
      }
      $form[$key] = array(
        // @Todo: We Need look for better function that will give us proper
        // field type instead of 'text_textarea' or 'text_textfield'.
        '#type' => str_replace('text-', '', str_replace('_', '-', $field_info['widget']['type'])),
        '#title' => $field_info['label'],
        '#weight' => $field['#weight'],
        '#required' => $field[LANGUAGE_NONE]['#required'],
        '#description' => $field[LANGUAGE_NONE]['#description'],
        '#access' => $field['#access'],
        '#default_value' => '',
        '#extra_info' => $extra_info,
      );
    }
    // Node revision information for administrators.
    $form['revision_information'] = array(
      '#type' => 'fieldset',
      '#title' => t('Revision information'),
      '#collapsible' => TRUE,
      '#collapsed' => !$node->revision,
      '#group' => 'additional_settings',
      '#weight' => 20,
      '#access' => $node->revision || user_access('administer nodes'),
      'revision' => array(
        '#type' => 'checkbox',
        '#title' => t('Create new revision'),
        '#default_value' => $node->revision,
        '#access' => user_access('administer nodes'),
      ),
      'log' => array(
        '#type' => 'textarea',
        '#title' => t('Revision log message'),
        '#rows' => 4,
        '#default_value' => !empty($node->log) ? $node->log : '',
        '#description' => t('Provide an explanation of the changes you are making. This will help other authors understand your motivations.'),
        '#access' => user_access('administer nodes'),
      ),
    );
    // Node author information for administrators.
    $form['author'] = array(
      '#type' => 'fieldset',
      '#access' => user_access('administer nodes'),
      '#title' => t('Post Created/Edited By'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#group' => 'additional_settings',
      '#weight' => 90,
      'author_name' => array(
        '#type' => 'textfield',
        '#title' => t('Posted by'),
        '#maxlength' => 60,
        '#autocomplete_path' => 'user/autocomplete',
        '#default_value' => !empty($node->name) ? $node->name : '',
        '#weight' => -1,
        '#description' => t('You may change this if posting on behalf of someone else.'),
      ),
      'date' => array(
        '#type' => 'textfield',
        '#title' => t('Posted on'),
        '#maxlength' => 25,
        '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($node->date) ? date_format(date_create($node->date), 'Y-m-d H:i:s O') : format_date($node->created, 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($node->date) ? date_format(date_create($node->date), 'O') : format_date($node->created, 'custom', 'O'))),
        '#default_value' => !empty($node->date) ? $node->date : '',
      ),
    );
    $form['options'] = array(
      '#type' => 'fieldset',
      '#access' => user_access('administer nodes'),
      '#title' => t('Publishing options'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#group' => 'additional_settings',
      '#weight' => 95,
      'status' => array(
        '#type' => 'checkbox',
        '#title' => t('Published'),
        '#default_value' => $node->status,
      ),
      'promote' => array(
        '#type' => 'checkbox',
        '#title' => t('Promoted to front page'),
        '#default_value' => $node->promote,
      ),
      'sticky' => array(
        '#type' => 'checkbox',
        '#title' => t('Sticky at top of lists'),
        '#default_value' => $node->sticky,
      ),
    );

    // Invoke hook_form_alter(), hook_form_BASE_FORM_ID_alter(), and
    // hook_form_FORM_ID_alter() implementations.
    $form_id = $node->type . '_node_form';
    $hooks = array('form', 'form_node_form', 'form_' . $node->type . '_node_form');
    drupal_alter($hooks, $form, $form_state, $form_id);

    // Assign to a group.
    $form['options']['#group'] = 'additional_settings';
    $form['author']['#group'] = 'additional_settings';
    $form['revision_information']['#group'] = 'additional_settings';
    $form['os_menu']['#group'] = 'additional_settings';
    $form['path']['#group'] = 'additional_settings';

    // @todo: We need to look for a better solution on this.
    unset($form['#entity']);
    unset($form['#after_build']);
    unset($form['#validate']);
    unset($form['#attached']);
    unset($form['actions_top']);
    unset($form['actions']);
    unset($form['feeds']);
    unset($form['#feed_id']);
    unset($form['field_child_site']);
    unset($form['#attributes']);
    unset($form['#bundle']);
    unset($form['author']['name']);  

    return $form;
  }

}
