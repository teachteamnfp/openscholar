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
}

/**
 * Submit handler; Change login method.
 */
function cp_change_login_method_submit($form, &$form_state) {
  $group_type = $form_state['values']['group_type'];
  $gid = $form_state['values']['gid'];
  $state = $form_state['values']['state'];
  $og_membership = $form_state['og_membership'];

  $change_login_method_set = variable_get('site_login_methods');
  $alternative_login_method_label = variable_get('alternative_login_method_label');
  $login_method_label = "OpenScholar default login";
  if ((! is_null($_POST['yes_or_no_box'])) && (! variable_get('site_login_methods')[$gid])) {
    $change_login_method_set[$gid] = $alternative_login_method_label;
  } else {
    unset($change_login_method_set[$gid]);
  }
  variable_set('site_login_methods', $change_login_method_set);

  $group = entity_load_single($group_type, $gid);
  drupal_set_message(t('Users in %group-title must login through %login-method.', 
      array('%group-title' => entity_label($group_type, $group), 
            '%login-method' => $change_login_method_set[$gid])));
}

/**
 * Change site's login method
 */
function cp_change_login_method($form, &$form_state, $group_type, $gid) {

  $group = entity_load_single($group_type, $gid);
  $label = entity_label($group_type, $group);

  $form['group_type'] = array('#type' => 'value', '#value' => $group_type);
  $form['gid'] = array('#type' => 'value', '#value' => $gid);

  $form['site_login_methods'] = array(
    '#type' => 'fieldset',
    '#title' => t('Change login method for %group', array('%group' => $label)),
  );

  $alternative_login = variable_get('alternative_login_method_label');
  $login_method_label = (isset(variable_get('site_login_methods')[$gid]) ? $alternative_login : 'default login');
  $form['site_login_methods']['yes_or_no_box'] = array(
    '#type' => 'checkbox',
    '#default_value' => (isset(variable_get('site_login_methods')[$gid]) ? 1 : 0),
    '#return_value' => 1,
    '#title' => t('Site members must login using ') . $alternative_login,
  );

  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array('#type' => 'submit', '#value' => t('Save'));

  return $form;
}

