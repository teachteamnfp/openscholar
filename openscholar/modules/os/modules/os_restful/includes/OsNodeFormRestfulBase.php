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
    $form = array();
    $form_state = array();
    $options = array();
    if (!empty($this->request['nid'])) {
      $node = node_load($this->request['nid']);
    }
    else {
      $node = new stdClass;
      $node->type = $this->getBundle();
    }
    $function = node_type_get_base($node) . '_form';
    if (function_exists($function) && ($extra = $function($node, $form_state))) {
      // @Todo: Must be a better way to handle these.
      unset($extra['#validate']);
      unset($extra['#cache']);
      //print_r($extra);
      foreach ($extra as $key => $form_field) {
        $form[$key] = $form_field;
      }
    }
    $extra_fields =  _field_invoke_default('form', 'node', $node, $form, $form_state, $options);
    foreach ($extra_fields as $key => $field) {
      // Debug $field;
      //print_r($field);
      $extra_info = array();
      $field_info = field_info_instance('node', $field[LANGUAGE_NONE]['#field_name'], $node->type);
      // Debug $field_info;
      //print_r($field_info);
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
      // Collapsed by default when "Create new revision" is unchecked.
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
    $form['author_information'] = array(
      '#type' => 'fieldset',
      '#access' => user_access('administer nodes'),
      '#title' => t('Authoring information'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
      '#group' => 'additional_settings',
      '#weight' => 90,
      'author_name' => array(
        '#type' => 'textfield',
        '#title' => t('Authored by'),
        '#maxlength' => 60,
        '#autocomplete_path' => 'user/autocomplete',
        '#default_value' => !empty($node->name) ? $node->name : '',
        '#weight' => -1,
        '#description' => t('Leave blank for %anonymous.', array('%anonymous' => variable_get('anonymous', t('Anonymous')))),
      ),
      'date' => array(
        '#type' => 'textfield',
        '#title' => t('Authored on'),
        '#maxlength' => 25,
        '#description' => t('Format: %time. The date format is YYYY-MM-DD and %timezone is the time zone offset from UTC. Leave blank to use the time of form submission.', array('%time' => !empty($node->date) ? date_format(date_create($node->date), 'Y-m-d H:i:s O') : format_date($node->created, 'custom', 'Y-m-d H:i:s O'), '%timezone' => !empty($node->date) ? date_format(date_create($node->date), 'O') : format_date($node->created, 'custom', 'O'))),
        '#default_value' => !empty($node->date) ? $node->date : '',
      ),
    );

    // Node options for administrators.
    $form['publishing_options'] = array(
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

    // @todo Must be a better way to handle these.
    unset($form['#entity']);
    unset($form['#after_build']);
    unset($form['#validate']);
    unset($form['#attached']);

    return $form;
  }

}
