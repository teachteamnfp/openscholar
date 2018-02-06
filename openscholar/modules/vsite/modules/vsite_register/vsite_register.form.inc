<?php

/**
 * Form AJAX validate callback; Returns errors on ajax elements.
 */
function vsite_register_ajax_validation(&$form, &$state) {
  // Clears this to avoid having notices clutter the form during AJAX validate.
  drupal_get_messages();
  $elem = $state['triggering_element']['#name'];
  return theme('vsite_register_form_errors', (array(
        'name' => $elem,
        'errors' => _vsite_register_validate($elem, $state['values']),
      )));
  //$errors = form_get_errors();//$state['triggering_element']['#name']);
}

/**
 * Form validate callback; checks all common values.
 *
 * Invoked in both:
 *  - vsite_register_ajax_validation()
 *  - vsite_register_form_validate()
 *
 * @param string $element
 * 	Name of element to validate
 * @param array $values
 * 	values array from returned form state
 *
 * @return mixed
 *   NULL if no errors, otherwise an array of strings for all errors found.
 */
function _vsite_register_validate($element, &$values) {
  $msg = array();
  switch ($element) {
    case 'domain':
      if (strlen($values['domain']) < 3 || !valid_url($values['domain']) || !_vsite_register_valid_url($values['domain'])) {
        $msg[] = t('Invalid URL specified.');
      }
      if (($purl = purl_load(array('value' => $values['domain'], 'provider' => 'spaces_og'), TRUE)) || menu_get_item($values['domain'])) {
        $msg[] = t('URL %url is taken.  Please choose another.', array('%url' => $values['domain']));
      }
      break;

    case 'mail':
      if ($mail_error = user_validate_mail($values['mail'])) {
        $msg[] = $mail_error;
      }

      if (_vsite_register_mail_in_use($values['mail'])) {
        $msg[] = t('Email address already in use.  Please try another.');
      }
      break;

    case 'name':
      if ($user_error = user_validate_name($values['name'])) {
        $msg[] = $user_error;
      }

      if ($user = user_load_by_name($values['name'])) {
        $msg[] = t('Username %name is taken.  Please choose another.', array('%name' => $values['name']));
      }
      break;

    // Checks that all dependent fields are provided.

    case 'form':
      // If "on behalf" is selected, but no user specified...
      if ($values['vicarious_user'] && !(strlen($values['existing_username']) || $values['create_new_user'])) {
        $msg[] = t('You have selected to create this site on behalf of another user.  Please specify an existing user or create a new account.');
      }

      // If "create new user" is selected...
      if ($values['create_new_user']) {
        if (!(strlen($values['name']) && strlen($values['mail']) && strlen($values['password']))) {
          $msg[] = t('You have selected to create a new user.  Please provide a username, email address, and password for the new user.');
        }

        // Checks password matches confirmed password.
        $pass1 = trim($_POST['password']['pass1']);
        $pass2 = trim($_POST['password']['pass2']);
        if (!empty($pass1) || !empty($pass2)) {
          if (strcmp($pass1, $pass2)) {
            $msg[] = t('The specified passwords do not match.');
          }
        }
        else {
          $msg[] = t('The password field is required.');
        }

        if (module_exists('password_policy')) {
          //borrowed from `password_policy_user_profile_form_validate()`
          $account = user_load(0);
          $policies = PasswordPolicy::matchedPolicies($account);
          $errors = array();
          foreach ($policies as $policy) {
            $errors = $errors + $policy->check($pass1, $account);
          }
          $msg = array_merge($msg, $errors);
        }
      }

      // If an existing user is selected...
      if ($values['vicarious_user'] && !$values['create_new_user']) {
        if (!user_load_by_name($values['existing_username'])) {
          $msg[] = t('User @name does not exist.  Please try another user name or create a new user account instead.', array('@name' => $values['existing_username']));
        }
      }
      break;
  }

  return (count($msg)) ? $msg : NULL;
}

/**
 * Returns TRUE if an email address is already in use by another user.
 *
 * Code borrowed from user.module's user_account_form_validate()
 *
 * @param string $mail
 * 	The email address a visitor is attempting to register a new account with.
 *
 * @see user_account_form_validate()
 */
function _vsite_register_mail_in_use($mail) {
  return (bool) db_select('users')
    ->fields('users', array('uid'))
    ->condition('mail', db_like($mail, 'LIKE'))
    ->range(0, 1)
    ->execute()
    ->fetchField();
}

/**
 * Returns TRUE if a URL has no restricted characters and passes purl validate.
 *
 * @see _vsite_register_validate()
 * @see purl_validate()
 */
function _vsite_register_valid_url($url) {
  foreach (array('!', ';', '/', '?', ':', '@', '&', '=', '"', "'",
    ) as $char) {
    if (strpos($url, $char) !== FALSE) {
      return FALSE;
    }
  }

  return purl_validate(array('value' => $url, 'provider' => 'spaces_og'));
}

/**
 * Sends emails using drupal_mail() to both new site owner and admin account.
 */
function vsite_register_notify($vsite) {
  // Invokes drupal_mail() to send email notification to new site owner.
  vsite_register_notify_author($vsite);

  // Invokes drupal_mail() to send email notification to admin account,
  // or whatever email is specified in variable "vsite_register_admin_mail".
  $admin_mail = variable_get('vsite_register_admin_mail', '');
  if ($admin_mail) {
    vsite_register_notify_admin($vsite, $admin_mail);
  }
}

/**
 * Form submit AJAX callback; sends out an email to the vsite owner user.
 *
 * Notifies the vsite owner of their newly created site.
 *
 * @see vsite_register_form_submit()
 */
function vsite_register_notify_author($vsite) {
  // Prepares replacement values for email token text.
  $key = 'vsite_register_notify_author';
  $author = user_load($vsite->group->uid);
  $replacements = _vsite_register_mail_replacements($vsite, $author, $key);

  $subject = t('An OpenScholar web site has been created for you.');

  // Checks to see if the user logged in matched the newly created user.
  global $user;
  $body_text = _vsite_register_notify_author_body_text($user, $author);
  $body = t($body_text, $replacements);

  global $language;

  $params = array();
  $params['vsite'] = $vsite;
  $params['user'] = $author;
  $params['subject'] = $subject;
  $params['body'] = $body;

  $from = variable_get('site_mail', ini_get('sendmail_from'));

  $result = drupal_mail('vsite_register', 'vsite_register_notify_author', $author->mail, $language->language, $params, $from, $send = TRUE);
  if ($result == FALSE) {
    drupal_set_message(t('Error sending notification mail to user.'), 'error');
  }
}

/**
 * Form submit AJAX callback; sends out a 2nd email to the administrator.
 *
 * Notifies the administrator that a new site has been registered.
 *
 * @see vsite_register_form_submit()
 */
function vsite_register_notify_admin($vsite, $admin_mail) {
  // Prepares replacement values for email token text.
  $key = 'vsite_register_notify_admin';
  $author = user_load($vsite->group->uid);
  $replacements = _vsite_register_mail_replacements($vsite, $author, $key);

  $subject = t(
    "User !username created a site \"!site_name\" at !sitewide_name",
    $replacements
  );

  $body = t(
      "Hello,\n\n"
      . "On !date,\na new site was registered at !sitewide_name.\n\n"
      . "Link: !site_login_uri"
      . "\n\n&nbsp;\n\n"
      . "<h2>Site information</h2>\n"
      . "Site name:\t\t!site_name\n"
      . "Site URL:\t\t!site_url\n"
      . "Site type:\t\t!site_type\n"
      . "Site visibility:\t!site_visibility"
      . "\n\n&nbsp;\n\n"
      . "<h2 style='font-weight:bold;'>User information</h2>\n"
      . "Username:\t\t!username\n"
      . "Full name:\t\t!full_name\n"
      . "Email:\t\t\t\t!user_mail\n"
      . "Total sites owned:\t!vsite_count\n\n"
      . "Best wishes,\nthe !sitewide_name server",
      $replacements
  );

  $from = variable_get('site_mail', ini_get('sendmail_from'));

  $params = array();
  $params['subject'] = $subject;
  $params['body'] = $body;
  $params['vsite'] = $vsite;
  $params['user'] = $author;

  global $language;
  $result = drupal_mail('vsite_register', $key, $admin_mail, $language->language, $params, $from, $send = TRUE);
  if ($result == FALSE) {
    drupal_set_message(t('Error sending notification mail to admin.'), 'error');
  }
}

/**
 * Implements hook_mail().
 */
function vsite_register_mail($key, &$message, $params) {
  $message['subject'] = $params['subject'];
  $message['body'][] = $params['body'];

  // Prepares values to be used with any hook_mail_alter() implementations.
  $message['vsite'] = $params['vsite'];
  $message['user'] = $params['user'];
}

/**
 * Returns an array of string replacement values for vsite register mails.
 *
 * @param object $vsite
 *   The newly created vsite object, whose link, etc. will be a replacement val.
 * @param string $key
 *   The mail key used by drupal_mail(), to load certain variables.
 */
function _vsite_register_mail_replacements($vsite, $author, $key) {
  global $base_url;
  $purl_base_domain = variable_get('purl_base_domain', $base_url);
  // Initialize replacements array with values common to both mail keys.
  $replacements = array(
    '!username' => $author->name,
    '!site_login_uri' => url($purl_base_domain . '/' . $vsite->group->purl . '/user/login', array('absolute' => TRUE, 'query' => array('destination' => $vsite->group->purl))),
    '!site_uri' => url($purl_base_domain . '/user', array('absolute' => TRUE, 'query' => array('destination' => $vsite->group->purl))),
    '!user_mail' => $author->mail,
    '!site_url' => url($purl_base_domain . '/' . $vsite->group->purl, array('absolute' => TRUE)),
    '!sitewide_name' => variable_get('site_name', 'OpenScholar'),
  );

  // Adds additional replacement values depending on which email is being sent.
  switch ($key) {
    case 'vsite_register_notify_admin':

      // Prepares vsite count value (total number of vsites owned by author).
      $count = count(vsite_get_vsite_by_owner($author->uid));

      // Prepares the new vsite owner (user)'s full name.
      $name_values = array();
      $name_fields = array('field_first_name', 'field_last_name');
      foreach ($name_fields as $field) {
        $items = field_get_items('user', $author, $field);
        if (isset($items[0]['value']) && !empty($items[0]['value'])) {
          $name_values[] = $items[0]['value'];
        }
      }
      $full_name = sizeof($name_values) > 1 ? implode(' ', $name_values) : t('(No entry)');

      // Prepares the site visibility value and fetches human-readable name.
      $items = field_get_items('node', $vsite->group, VSITE_ACCESS_FIELD);
      $visibility = (isset($items[0]['value'])) ? $items[0]['value'] : FALSE;
      if ($visibility !== FALSE) {
        $values = vsite_access_privacy_available_values();
        if (isset($values[$visibility])) {
          $visibility = $values[$visibility];
        }
      }

      // Prepares a site type like "department (os_department_minimal preset)".
      $site_type = isset($vsite->group->type) ? $vsite->group->type : '';
      if ($site_type) {
        if (isset($vsite->controllers->variable->values['space']['spaces_preset_og'])) {
          $preset = $vsite->controllers->variable->values['space']['spaces_preset_og'];
          $site_type .= " ($preset preset)";
        }
      }

      // Prepares the date for the email message.
      global $language;
      $date = format_date(time(), 'long', '', NULL, $language->language);

      $replacements += array(
        '!vsite_count' => $count,
        '!full_name' => $full_name,
        '!site_name' => $vsite->group->title,
        '!site_type' => $site_type,
        '!site_visibility' => $visibility,
        '!date' => $date,
      );

      break;
    case 'author_notify':
      $replacements += array(
        '!user_edit_uri' => url('user/' . $author->uid . '/edit', array(
          'absolute' => TRUE
        )),
        '!user_login_uri' => url('user', array(
          'absolute' => TRUE
        )),
      );
      break;
  }

  return $replacements;
}

/**
 * Returns special instructions for resetting password in certain cases.
 *
 * @param object $current_user
 *   The currently logged-in user.
 * @param object $author
 *   The newly registered vsite's author.
 *
 * @return string $body_text
 *   The email body message text to return, optionally containing instructions
 *   to reset password.
 */
function _vsite_register_notify_author_body_text($current_user, $author) {
  // Checks if the site was created "vicariously", on behalf of author.
  $author_is_current_user = (bool) $current_user->uid == $author->uid;
  // Checks if user is creating first site or additional site.
  $returning_author = (bool) count(vsite_get_vsite_by_owner($author->uid)) > 1;
  // In either of these cases, no password reset is required.
  $no_reset_required = (bool) $returning_author || $author_is_current_user;
  if ($no_reset_required) {
    $body_text = "Hi !username,\n\n"
      . "Your new web site at !sitewide_name has been activated."
      . "Your web site url is !site_url\n\n"
      . "You will be able to log into your site at !site_login_uri "
      . "with username:\n\n!username\n\n"
      . "We hope you will enjoy your new web site.\n\n"
      . "Sincerely,\nThe !sitewide_name Team";
  }
  // Otherwise, this site was created on the author's behalf, or this is the
  // author's very first site.
  // Includes instructions to reset password in order to login.
  else {
    $body_text = "Hi !username,\n\n"
      . "Your new web site at !sitewide_name has been activated.\n\n"
      . "Your web site url is !site_url\n\n"
      . "You may now log in and reset your password by clicking on this link "
      . "or copying and pasting it in your browser: \n\n !user_login_url \n\n"
      . "This login can be used only once. "
      . "After logging in, you will be redirected to !user_edit_url "
      . "so you can change your password. "
      . "Once you have set your own password, "
      . "you will be able to log in at !site_login_uri "
      . "as username:\n\n!username\n\n"
      . "We hope you will enjoy your new web site.\n\n"
      . "Sincerely,\nThe !sitewide_name Team";
  }

  drupal_alter('vsite_register_notify_author_body_text', $body_text);
  return $body_text;
}

function vsite_register_validate($type, $value) {
  $output = array(
    'valid' => false,
  );

  $values[$type] = $value;
  $msg = _vsite_register_validate($type, $values);
  if (is_array($msg)) {
    $output['message'] = $msg;
  }
  else {
    $output['valid'] = true;
  }

  print drupal_json_encode($output);
  exit;
}

function vsite_register_site_login($form, $form_state, $site_id, $uid, $timestamp, $token) {
  global $user;
  $space = spaces_load('og', $site_id);
  $purl_params = array(
    'purl' => array(
      'provider' => "spaces_{$space->type}",
      'id' => $space->id,
    ),
    'fragment' => 'overlay=cp/welcome'
  );

  // When processing the one-time login link, we have to make sure that a user
  // isn't already logged in.
  if ($user->uid) {
    // The existing user is already logged in.
    if ($user->uid == $uid) {
      purl_goto('<front>', $purl_params);
    }
    // A different user is already logged in on the computer.
    else {
      $reset_link_account = user_load($uid);
      if (!empty($reset_link_account)) {
        drupal_set_message(t('Another user (%other_user) is already logged into the site on this computer, but you tried to use a one-time link for user %resetting_user. Please <a href="!logout">logout</a> and try using the link again.',
          array('%other_user' => $user->name, '%resetting_user' => $reset_link_account->name, '!logout' => url('user/logout'))));
      } else {
        // Invalid one-time link specifies an unknown user.
        drupal_set_message(t('The one-time login link you clicked is invalid.'));
      }
    }
    drupal_goto();
  }
  else {
    // Time out, in seconds, until login URL expires. Defaults to 24 hours =
    // 86400 seconds.
    $timeout = variable_get('user_password_reset_timeout', 86400);
    $current = REQUEST_TIME;
    // Some redundant checks for extra security ?
    $users = user_load_multiple(array($uid), array('status' => '1'));
    if ($timestamp <= $current && $account = reset($users)) {
      // No time out for first time login.
      if ($account->login && $current - $timestamp > $timeout) {
        drupal_set_message(t('You have tried to use a one-time login link that has expired. You must log in through the Admin Login link.'));

        purl_goto('<front>', $purl_params);
      }
      elseif ($account->uid && $timestamp >= $account->login && $timestamp <= $current && $token == user_pass_rehash($account->pass, $timestamp, $account->login)) {
        // First stage is a confirmation form, then login
        // Set the new user.
        $user = $account;
        // user_login_finalize() also updates the login timestamp of the
        // user, which invalidates further use of the one-time login link.
        user_login_finalize();
        watchdog('user', 'User %name used one-time login link at time %timestamp.', array('%name' => $account->name, '%timestamp' => $timestamp));
        drupal_set_message(t('You have just used your one-time login link. You will no longer be able to use this link to log in.'));

        purl_goto('<front>', $purl_params);
      }
      else {
        drupal_set_message(t('You have tried to use a one-time login link that has either been used or is no longer valid. You must log in through the Admin Login link.'));

        purl_goto('<front>', $purl_params);
      }
    }
    else {
      // Deny access, no more clues.
      // Everything will be in the watchdog's URL for the administrator to check.
      drupal_access_denied();
    }
  }
}

function vsite_register_user_exists($name) {
  $output = array(
    'uid' => 0,
  );
  if ($account = user_load_by_name($name)) {}
  elseif ($account = user_load_by_mail($name)) {}

  if ($account) {
    $output['uid'] = $account->uid;
  }

  echo drupal_json_encode($output);
  exit;
}
