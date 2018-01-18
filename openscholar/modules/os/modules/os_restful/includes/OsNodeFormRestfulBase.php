<?php

class OsNodeFormRestfulBase extends RestfulEntityBaseNode {

  public static function controllersInfo() {
    return array(
      'form' => array(
        RestfulInterface::GET => 'getNodeForm'
      )
    ) + parent::controllersInfo();
  }

  /**
   * Get node form based on bundle.
   */
  public function getNodeForm() {
    $form = array();
    $form_state = array();
    // Activate space in this context.
    if (!empty($this->request['vsite'])) {
      $space = spaces_load('og', $this->request['vsite']);
      spaces_set_space($space);
    }
    // Handle node edit.
    if (!empty($this->request['nid'])) {
      $node = node_load($this->request['nid']);
      $form['nid']['#value'] = $node->nid;
      $form['#node'] = $node;
      $node->date = format_date($node->created, 'custom', 'Y-m-d H:i:s O');
      $node->revision = !empty($node->revision_timestamp) ? TRUE : FALSE;
      $form['node_access'] = array(
        '#type' => 'hidden',
        '#access' => FALSE,
        '#default_value' => $this->checkEntityAccess('delete', $this->entityType, $node)
      );
    }
    else {
      $node = new stdClass;
      $node->type = $this->getBundle();
      node_object_prepare($node);
    }
    $form_state['node'] = $node;
    $form['#bundle'] = $node->type;
    
    $function = node_type_get_base($node) . '_form';
    if (function_exists($function) && ($extra = $function($node, $form_state))) {
      // Unset: not require in frontend.
      unset($extra['#validate']);
      unset($extra['#cache']);
      foreach ($extra as $key => $form_field) {
        $form[$key] = $form_field;
      }
      $form['title']['#type'] = 'os-node-title-textfield'; 
    }
    $extra_fields =  _field_invoke_default('form', 'node', $node, $form, $form_state);
    foreach ($extra_fields as $key => $field) {
      $field_info = field_info_instance('node', $field[LANGUAGE_NONE]['#field_name'], $node->type);
      $form[$key] = array(
        '#type' => str_replace('text-', '', str_replace('_', '-', $field_info['widget']['type'])),
        '#title' => $field_info['label'],
        '#weight' => $field['#weight'],
        '#required' => $field[LANGUAGE_NONE]['#required'],
        '#description' => $field[LANGUAGE_NONE]['#description'],
        '#access' => $field['#access'],
        '#default_value' => !empty($node->{$key}[LANGUAGE_NONE]) ? $node->{$key}[LANGUAGE_NONE] : $field[LANGUAGE_NONE]['#default_value'],
      );
      $file_upload_info = array();
      if ($field_info['widget']['type'] == 'media_draggable_file') {
        $file_upload_info = array(
          '#id' => 'edit-' . str_replace('_', '-', $key),
          '#custom_directive_parameters' => array(
            'cardinality' => $field[LANGUAGE_NONE]['drop']['#cardinality'],
            'panes' => array('upload', 'library'),
            'hide_helpicon' => false,
            'droppable_text' => $field[LANGUAGE_NONE]['drop']['#droppable_area_text'],
            'upload_text' =>  $field[LANGUAGE_NONE]['drop']['#upload_button_text'],
            'max_filesize' => $field[LANGUAGE_NONE]['drop']['#file_upload_max_size'],
            'types' => implode(',', $field_info['widget']['settings']['allowed_types']),
          ),
          '#upload_location' => $field[LANGUAGE_NONE]['drop']['#upload_location'],
          '#upload_validators' => $field[LANGUAGE_NONE]['drop']['#upload_validators']
        );
        $form[$key] = array_merge($form[$key], $file_upload_info);
      }
      if ($field_info['widget']['type'] == 'og_vocab_complex') {
        $form[$key] = array_merge($form[$key], array(
          '#bundle' => $this->getBundle(),
          '#access' => $this->og_vocab_access_bundle($this->getBundle(), $this->request['vsite']),
          )
        );
      }
    }
    // Node revision information for administrators.
    $form['revision_information'] = array(
      '#type' => 'fieldset',
      '#title' => t('Revision information'),
      '#collapsible' => TRUE,
      '#collapsed' => !$node->revision,
      '#group' => 'additional_settings',
      '#weight' => -6,
      '#access' => $node->revision || user_access('administer nodes'),
      'revision' => array(
        '#type' => 'checkbox',
        '#title' => t('When checked, a new version of this content will be created'),
        '#default_value' => $node->revision,
        '#access' => user_access('administer nodes'),
      ),
      'log' => array(
        '#type' => 'textarea',
        '#title' => t('Revision log message'),
        '#rows' => 4,
        '#default_value' => !empty($node->log) ? $node->log : '',
        '#description' => t('Provide an explanation of the changes you are making.</br></br> !help_link', array('!help_link' => l(t('What’s being stored as a revision?'), 'https://docs.openscholar.harvard.edu/revisions', array('attributes' => array('target' => array('_blank')))))),
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
      '#weight' => -7,
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
      '#weight' => -10,
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
        '#title' => t('Display at top of lists'),
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
    $form['os_menu']['#weight'] = -8;
    $form['path']['#group'] = 'additional_settings';
    $form['path']['#weight'] = -9;
    $form['path']['alias']['#default_value'] = !empty($this->request['nid']) ? explode('/', drupal_get_path_alias('node/' . $node->nid))[1] : '';
    $form['title']['#required'] = TRUE;

    // Unset unnecessary form elements to send clean json output to frontend. 
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
    unset($form['og_group_ref']);
    unset($form['nid']);
    unset($form['#node']);
    unset($form['#space']);
    unset($form['max_revisions']);
    unset($form['revisions']);
       
    return $form;
  }

  public function og_vocab_access_bundle($bundle, $vsite) {
    $query = db_select('og_vocab_relation', 'ogr');
    $query->join('og_vocab', 'ov', 'ov.vid = ogr.vid');
    // We need to check if a vocabulary is assigned to a bundle.
    $result = $query
      ->fields('ogr')
      ->condition('group_type', 'node')
      ->condition('gid', $vsite)
      ->condition('ov.bundle', $bundle)
      ->execute()
      ->fetchAllAssoc('vid');

    return (count($result) > 0)  ? TRUE : FALSE;

  }

  public function propertyValuesPreprocess($property_name, $value, $public_field_name) {
    switch ($property_name) {
      case 'author':
        return user_load_by_name($value)->uid;
        break;

      case 'field_upload':
        $fid = array();
        if (empty($value)) {
          return array();
        }
        foreach($value as $v) {
          $fid[] = !empty($v['fid']) ? $v['fid'] : $v;
        }
        $value = $fid;
        break;
  
    }

    $field_info = field_info_field($property_name);
    switch ($field_info['type']) {
      default:
        return parent::propertyValuesPreprocess($property_name, $value, $public_field_name);
    }
  }
  
  /**
   * Override this function to save fields value without exposing fields as 
   * public.
   *
   * @param EntityMetadataWrapper $wrapper
   *   The wrapped entity object, passed by reference.
   * @param bool $null_missing_fields
   *   Determine if properties that are missing form the request array should
   *   be treated as NULL, or should be skipped. Defaults to FALSE, which will
   *   set the fields to NULL.
   *
   * @throws RestfulBadRequestException
   */
  protected function setPropertyValues(EntityMetadataWrapper $wrapper, $null_missing_fields = FALSE) {
    // Node save limited to page content type. In future we won't need this 
    // condtion.
    if ($this->entityType == 'node' && $this->bundle == 'page') {
      $request = $this->getRequest();
      static::cleanRequest($request);
      $save = FALSE;
      $original_request = $request;

      if (empty($original_request['title'])) {
        throw new RestfulForbiddenException("Title field is required.");
      }
      else {
        $processed_unknown_property = array();
        $processed_property = array();

        foreach ($original_request as $property_name => $value) {
          if (is_array($original_request[$property_name]['fields'])) {
            foreach ($original_request[$property_name]['fields'] as $key => $value) {
              $processed_unknown_property[$key] = $value;
            }
          }
          if (!empty($wrapper->$property_name)) {
            $processed_property[$property_name] = $value;
          }
        }
        $processed_property = array_merge($processed_property, $processed_unknown_property);
        foreach ($processed_property as $property_name => $value) {
          if (!empty($wrapper->{$property_name})) {
            $field_value = $this->propertyValuesPreprocess($property_name, $value, $property_name);
            $wrapper->{$property_name}->set($field_value);
          }
        }
        $wrapper->save();
        $save = TRUE;
        $entity = entity_load_single($this->entityType, $wrapper->getIdentifier());
        foreach ($processed_unknown_property as $property_name => $value) {
          if ($property_name == 'date' && !empty($value)) {
            $entity->created = strtotime($value);
          }
          if ($property_name == 'noindex' && !empty($value)) {
            $entity->noindex = $value;
          }
          if ($property_name == 'pathauto') {
            if (empty($value) && !empty($processed_unknown_property['pathalias'])) {
              $this->updatePathAlias($entity->nid, $processed_unknown_property['pathalias']);
            }
            else {
              $entity->path['pathauto'] = TRUE;
            }
          }
          if ($property_name == 'os_menu') {
            $link = array();
            $link['link_path'] = 'node/' . $entity->nid;
            $link['link_title'] = $value['link_title'];
            $link['menu_name'] = $value['parent'];
            if (!empty($value['enabled'])) {
              vsite_menu_menu_link_save($link, $processed_property['og_group_ref']);
            }
            elseif ($mlid = vsite_menu_get_link_path($link['menu_name'], $link['link_path'])) {
              vsite_menu_delete_menu_link($link['menu_name'], $mlid);
            }
          }
        }
        entity_save($this->entityType, $entity);
      }
      if (!$save) {
        // No request was sent.
        throw new \RestfulBadRequestException('No values were sent with the request');
      }
    }
  }
  
  /**
   * Update pathalias.
   *
   * @param int $entity_id
   *   Id of an entity object.
   * @param string $alias
   *   Alias string.
   * @return bool 
   *   Return TRUE if database update successful.
   */
  public function updatePathAlias($entity_id, $alias, $vsite) {
    $vsite = vsite_get_vsite();
    if (!$vsite) {
      // No VSite.
      return FALSE;
    }
    $update_auto_aliases = db_update('url_alias')
      ->fields(array(
        'alias' => $vsite->group->title . '/' . $alias,
      ))
      ->condition('source', $this->entityType . '/' . $entity_id, '=')
      ->execute();
  }
}
