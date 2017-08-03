<?php 

/**
 * @file
 * 
 * Admin forms for cp module
 */

function cp_admin_form() {
  $form = array(
    'cp_support_getsatisfaction_widget' => array(
      '#title' => t('GetSatisfaction widget'),
      '#description' => t('Replace the contact form with a GetSatisfaction widget.  Create the widget in your GetSatisfaction community.  Get its four digit id from the embed code.  (ie &lt;div id="getsat-widget-<b>1234</b>"&gt; ...).  Set to 0 to disable.'),
      '#type' => 'textfield',
      '#default_value' => variable_get('cp_support_getsatisfaction_widget', 0),
      '#element_validate' => array('element_validate_integer'),
    ),  
  );
  
  return system_settings_form($form);
}

/**
 * Validate handler; Change login method.
 */
function cp_change_login_method_validate($form, &$form_state) {
  
  /* Place holder.  No need to validate a single checkbox */
  return true;
}

/**
 * Submit handler; Change login method.
 */
function cp_change_login_method_submit($form, &$form_state) {
  $group_type = $form_state['values']['group_type'];
  $gid = $form_state['values']['gid'];


  // $state = $form_state['values']['state'];
  // $og_membership = $form_state['og_membership'];
  // // Set the user and state.
  // field_attach_submit('og_membership', $og_membership, $form['membership_fields'], $form_state);
  // $account = user_load_by_name($form_state['values']['name']);
  // // Ungroup user, in case they were already registered.
  // og_ungroup($group_type, $gid, 'user', $account);

  // $og_membership->etid = $account->uid;
  // $og_membership->state = $state;
  // $og_membership->save();
  // // Assign roles.
  // if (!empty($form_state['values']['roles'])) {
  //   foreach ($form_state['values']['roles'] as $rid) {
  //     og_role_grant($group_type, $gid, $og_membership->etid, $rid);
  //   }
  // }

  // $group = entity_load_single($group_type, $gid);
  // drupal_set_message(t('%user has been added to the group %group-title.', array('%user' => format_username($account), '%group-title' => entity_label($group_type, $group))));
}

/**
 * Change site's login method
 */
function cp_change_login_method($form, &$form_state, $group_type, $gid) {

  $group = entity_load_single($group_type, $gid);
  $label = entity_label($group_type, $group);
  $account = user_load($og_membership->etid);

  $form['group_type'] = array('#type' => 'value', '#value' => $group_type);
  $form['gid'] = array('#type' => 'value', '#value' => $gid);

  $form['og_user'] = array(
    '#type' => 'fieldset',
    '#title' => t('Change login method for %group', array('%group' => $label)),
  );
  $form['og_user']['name'] = array(
    '#type' => 'checkbox',
    '#title' => t('Site members must login using Harvard Key.'),
  );

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save'));

  return $form;
}

