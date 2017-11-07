<?php

/**
 * @file
 * Contains OsRestfulPurl.
 */

class OsRestfulPurl extends \RestfulBase implements \RestfulDataProviderInterface {

  /**
   * {@inheritdoc}
   */
  public static function controllersInfo() {
    return array(
      '' => array(
        \RestfulInterface::POST => 'save_site',
      ),
      'name' => array(
        \RestfulInterface::POST => 'check_user_name',
      ),
      'email' => array(
        \RestfulInterface::POST => 'check_email',
      ),
      '^.*$' => array(
        \RestfulInterface::GET => 'check_exiting_sites',
      )
    );
  }

  /**
   * Checking for user name
   */
  public function check_user_name() {
    if ($this->request['name'] != "") {
      $name = $this->request['name'];
      if ($user_error = user_validate_name($name)) {
        $msg[] = $user_error;
      }
      if ($user = user_load_by_name($name)) {
        $msg[] = t('Username %name is taken.  Please choose another.', array('%name' => $name));
      }
    } else {
      $msg[] = t('Please provide the desired username.');
    }
    return $msg;
  }

  /**
   * Checking for Email address
   */
  public function check_email() {
    if ($this->request['email'] != "") {
      $email = $this->request['email'];
      if ($mail_error = user_validate_mail($email)) {
        $msg[] = $mail_error;
      }
      module_load_include('inc', 'vsite_register', 'vsite_register.form');
      if (_vsite_register_mail_in_use($email)) {
        $msg[] = t('Email address already in use.  Please try another.');
      }
    } else {
      $msg[] = t('You must enter an e-mail address.');
    }
    return $msg;
  }

  /**
   * {@inheritdoc}
   */
  public function publicFieldsInfo() {}


  /**
   * Checking for existing sites.
   */
  public function check_exiting_sites($siteValue) {
    // Checking site creation permission
    if (!vsite_vsite_exists_access() || (function_exists('pinserver_user_has_associated_pin') && !os_pinserver_auth_vsite_register_form_page())) {
      $return['msg'] = "Not-Permissible";
      return $return;
    }
    //Validate new vsite URL
    $return['msg'] = '';
    module_load_include('inc', 'vsite_register', 'vsite_register.form');
    if (strlen($siteValue) < 3 || !valid_url($siteValue) || !_vsite_register_valid_url($siteValue)) {
      $return['msg'] = 'Invalid';
    }
    else if (($purl = purl_load(array('value' => $siteValue, 'provider' => 'spaces_og'), TRUE)) || menu_get_item($siteValue)) {
      $return['msg'] = "Not-Available";
    }
    else {
      $return['msg'] = "Available";
    }
    return $return;
  }



  /**
   * Callback save to create site.
   */
  function save_site() {
    // Checking site creation permission
    if (!vsite_vsite_exists_access() || (function_exists('pinserver_user_has_associated_pin') && !os_pinserver_auth_vsite_register_form_page())) {
      $commands[] = "Not-Permissible";
      return($commands[0]);
    }
    ctools_include('user', 'os');
    ctools_include('vsite', 'vsite');
    $values = &drupal_static('vsite_register_form_values');

    if($this->request['individualScholar'] != "") {
      $values['bundle'] = 'personal';
      $values['domain'] = $this->request['individualScholar'];
    } else if($this->request['projectLabSmallGroup'] != "") {
      $values['bundle'] = 'project';
      $values['domain'] = $this->request['projectLabSmallGroup'];
    } else {
      $values['bundle'] = 'department';
      $values['domain'] = $this->request['departmentSchool'];
    }
    $values['preset'] = $this->request['contentOption'];
    $values['vsite_private'] = $this->request['vsite_private'];

    $parent = FALSE;
    if($this->request['parent']){
      $parent = $this->request['parent'];
    }

    // The site has created on the behalf of a new user.
    $new_user = FALSE;

    // If the specified user account already exists...
    //if ($values['vicarious_user'] && $values['existing_username']) {
      // Loads that user account as site owner.
      //$site_owner = user_load_by_name($values['existing_username']);
    //}
    if (($this->request['vicarious_user'] && $this->request['name'])) {
      // Create user who has harvard pin but not OS uid.
      $name = $this->request['name'];
      $first_name = $this->request['first_name'];
      $last_name = $this->request['last_name'];
      $mail = $this->request['mail'];
      $password = $this->request['password'];
      $user_options = array(
        'name' => $name,
        'pass' => $password,
        'mail' => $mail,
        'status' => 1,
        'field_first_name' => $first_name,
        'field_last_name' => $last_name,
      );
      module_load_include('inc', 'os', 'includes/user');
      $site_owner = os_user_create($user_options);

      // We created a new user. After creating the vsite we'll grant him the vsite
      // admin role.
      $new_user = TRUE;

      // Logs in as the new user, if we're not already logged in.
      global $user;
      $user = $site_owner;

      // Link huid and uid
      if ($huid = pinserver_get_user_huid()) {
        pinserver_authenticate_set_user_huid($user->uid, $huid);
      }
    }
    else {
      // Creates site for current logged in user. No need to create a new user.
      global $user;
      $site_owner = $user;
    }

    // Creates the vsite node.
    $name = $purl = $values['domain'];
    $author = $site_owner->uid;
    $bundle = $values['bundle'];
    $preset = $values['preset'];
    $visibility = isset($values['vsite_private']) ? $values['vsite_private'] : FALSE;
    $state['additional_settings'] = empty($state['additional_settings']) ? array() : $state['additional_settings'];
    $vsite = vsite_create_vsite($name, $purl, $author, $bundle, $preset, $parent, $visibility, $state['additional_settings']);
    if ($vsite) {
      $message = vsite_register_message($form, $values['domain']);
      $commands[] = ajax_command_replace('#submit-suffix', $message);
      $commands[] = ajax_command_remove('#edit-submit');

      // Grant the proper roles to the user.
      if ($new_user) {
        os_role_grant($site_owner->uid, 'vsite admin', $vsite->nid);
      }
      // If we have gotten to this point, then the vsite registration was success.
      // Clears the errors.

      // Applying the theme
      if($this->request['themeKey']){
        $theme_key = $this->request['themeKey'];
        // Themes will be structured like 'hwpi_classic-os_featured_flavor-classic_indigo'
        // Where 'hwpi_classic' is the theme key and 'classic_indigo' is the flavor
        if (strpos($theme_key, '-os_featured_flavor-') !== FALSE) {
          list($theme_default, $theme_flavor) = explode('-os_featured_flavor-', $theme_key);
          // Set the theme key
          $vsite->controllers->variable->set('theme_default', $theme_default);
          // Set the flavor
          $vsite->controllers->variable->set('os_appearance_'.$theme_default.'_flavor', $theme_flavor);
        } else {
          // If there is no flavor associated with theme key
          $vsite->controllers->variable->set('theme_default', $theme_key);
          $vsite->controllers->variable->set('os_appearance_'.$theme_key.'_flavor', 'default');
        }
      }
    }
    else {
      $commands[] = _vsite_register_form_error();
    }

    // Check for a present queued og_tasks batch.
    $batch =& batch_get();
    if ($vsite && $batch) {
      // Run all the batch commands right now.
      $batch['progressive'] = FALSE;
      batch_process();
    }

    return($commands[0]);
  }
}