<?php

/**
 * @file
 * The OpenScholar Help - Documentation and Support poupup screens
 */

/**
 * Implements hook_cp_settings().
 */
function os_help_cp_settings() {

  $form = array();
  $document_content = _vsite_register_welcome_page();
  $document_prefix = !empty($document_content['welcome_page']['#markup']) ? $document_content['welcome_page']['#markup'] : '';
  $form['os_help'] = array(
    'group' => array(
      '#id' => 'documentation',
      '#title' => t('Documentation'),
      '#weight' => 50,
      '#menu_parent' => 'help'
    ),
    'form' => array(
      '#prefix' => $document_prefix,
      '#type' => 'markup',
      '#access' => TRUE,
    ),
  );

  $id = variable_get('cp_support_getsatisfaction_widget');
  if ($id) {
    $loading = drupal_get_path('theme', 'os_basetheme') . '/images/throbber.gif';
    $image = theme('image', array('path' => $loading));
    $form['os_support_gsfn'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 51,
        '#menu_parent' => 'help',
        '#help_link' => l(t('Learn about Support and Training from Harvard Web Publishing'), variable_get('support_training_url'), array('attributes' => array('target' => '_blank'))),
      ),
      'form' => array(
        '#prefix' => '<p class="helpClass">Every question you ask adds to the power of our OpenScholar Community. Type your question below and click the Continue button. You will need to login or create an account to finish posting.</p><p>If you don\'t want to post publicly to the community, click Send Privately</p>',
        '#type' => 'markup',
        '#loading' => $loading,
        '#access' => TRUE,
      ),
    );
    $form['os_support_gsfn_js'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 52,
        '#menu_parent' => 'help',
        '#help_link' => l(t('Learn about Support and Training from Harvard Web Publishing'), variable_get('support_training_url'), array('attributes' => array('target' => '_blank'))),
      ),
      'form' => array(
        '#type' => 'help',
        '#gsfnId' => $id,
        '#loading' => $loading,
        '#access' => TRUE,
      ),
    );
  }
  else {
    module_load_include('inc', 'contact', 'contact.pages');
    // Check to make sure the site email address are set.

    $results = db_select('contact', 'c')
      ->fields('c', array('cid', 'recipients'))
      ->condition('c.category', 'Website feedback')
      ->execute()
      ->fetchAssoc();

    if (empty($results['recipients'])) {
      // The recipient email isn't set, set them.
      $query = db_select('contact', 'c');
      $query->fields('c', array('cid', 'recipients'));
      $contact_result = $query->execute();
      while ($contact = $contact_result->fetchAssoc()) {
        if ($contact['recipients'] == '') {
          // Update the record.
          db_update('contact')
            ->fields(array('recipients' => variable_get('site_mail', '')))
            ->condition('cid', $contact['cid'])
            ->execute();
        }
      }
    }

    global $user;
    // Get an array of the categories and the current default category.
    $categories = db_select('contact', 'c')
      ->addTag('translatable')
      ->fields('c', array('cid', 'category'))
      ->orderBy('weight')
      ->orderBy('category')
      ->execute()
      ->fetchAllKeyed();
    $default_category = db_select('contact', 'c')
      ->fields('c', array('selected'))
      ->condition('c.selected', 1)
      ->execute()
      ->fetchField();

    // If there is more than one category available and no default category has
    // been selected, prepend a default placeholder value.
    if (!$default_category) {
      if (count($categories) > 1) {
        $categories = array(0 => t('- Please choose -')) + $categories;
      }
      else {
        $default_category = key($categories);
      }
    }

    $form['os_support_name'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 51,
        '#menu_parent' => 'help'
      ),
      'form' => array(
        '#prefix' => '<p>Use the form below to send us your comments or report any problems you experienced using your website. We read all feedback carefully and will respond quickly.</p>',
        '#type' => 'textfield',
        '#title' => t('Your name'),
        '#default_value' => $user->uid ? format_username($user) : '',
        '#required' => TRUE,
        '#attributes' => array('class' => array('contact-form')),
        '#access' => TRUE,
      ),
      'rest_validate' => 'os_help_contact_form_validate'
    );
    $form['os_support_mail'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 52,
        '#menu_parent' => 'help'
      ),
      'form' => array(
        '#type' => 'textfield',
        '#title' => t('Your e-mail address'),
        '#default_value' => $user->uid ? $user->mail : '',
        '#required' => TRUE,
        '#access' => TRUE,
      ),
      'rest_validate' => 'os_help_contact_form_validate'
    );
    $form['os_support_subject'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 53,
        '#menu_parent' => 'help'
      ),
      'form' => array(
        '#type' => 'textfield',
        '#title' => t('Subject'),
        '#required' => TRUE,
        '#access' => TRUE,
      ),
      'rest_validate' => 'os_help_contact_form_validate'
    );
    $form['os_support_cid'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 54,
        '#menu_parent' => 'help'
      ),
      'form' => array(
        '#type' => 'select',
        '#title' => t('Category'),
        '#default_value' => $default_category,
        '#options' => $categories,
        '#required' => TRUE,
        '#access' => TRUE,
      ),
      'rest_validate' => 'os_help_contact_form_validate'
    );
    $form['os_support_message'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 55,
        '#menu_parent' => 'help'
      ),
      'form' => array(
        '#type' => 'textarea',
        '#title' => t('Message'),
        '#required' => TRUE,
        '#access' => TRUE,
      ),
      'rest_validate' => 'os_help_contact_form_validate'
    );
    $form['os_support_copy'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 56,
        '#menu_parent' => 'help'
      ),
      'form' => array(
        '#type' => 'checkbox',
        '#title' => t('Send yourself a copy.')
       ),
    );
    $form['os_support_submit'] = array(
      'group' => array(
        '#id' => 'support',
        '#title' => t('Support'),
        '#weight' => 56,
        '#menu_parent' => 'help'
      ),
      'form' => array(
        '#type' => 'submit',
        '#value' => t('Send Message'),
        '#access' => TRUE,
      ),
      'rest_submit' => 'os_help_rest_cp_settings_submit'
    );
  }
  return $form;
}

function os_help_contact_form_validate($value) {
  if (!empty($value)) {
    return true;
  }
  return false;
}

function os_help_rest_cp_settings_submit($var, $value) {

  $values['sender'] = $user;
  $values['sender']->name = variable_get('os_support_name');
  $values['sender']->mail = variable_get('os_support_mail');
  $values['category'] = contact_load(variable_get('os_support_cid'));
  $values['copy'] = variable_get('os_support_copy');
  $values['message'] = variable_get('os_support_message');
  $values['subject'] = variable_get('os_support_subject');

  // Get the to and from e-mail addresses.
  $to = $values['category']['recipients'];
  $from = $values['sender']->mail;

  // Send the e-mail to the recipients using the site default language.
  drupal_mail('contact', 'page_mail', $to, language_default(), $values, $from);

  // If the user requests it, send a copy using the current language.
  if ($values['copy']) {
    drupal_mail('contact', 'page_copy', $from, $language, $values, $from);
  }
  variable_del('os_support_name');
  variable_del('os_support_mail');
  variable_del('os_support_cid');
  variable_del('os_support_copy');
  variable_del('os_support_message');
  variable_del('os_support_subject');
  flood_register_event('contact', variable_get('contact_threshold_window', 3600));
}
