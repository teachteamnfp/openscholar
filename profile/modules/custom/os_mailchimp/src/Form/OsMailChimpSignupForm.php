<?php

namespace Drupal\os_mailchimp\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * {@inheritdoc}
 */
class OsMailChimpSignupForm extends FormBase {

  /**
   * Mailchimp list object from API.
   *
   * @var \stdClass
   */
  private $list;

  private $allowedVars = [
    'EMAIL',
    'FNAME',
    'LNAME',
  ];

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os_mailchimp_signup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, \stdClass $list = NULL) {
    if (empty($list)) {
      return $form;
    }
    $this->list = $list;

    $form['#prefix'] = '<div id="os_mailchimp_modal_signup_form">';
    $form['#suffix'] = '</div>';

    // The status messages that will contain any form errors.
    $form['status_messages'] = [
      '#type' => 'status_messages',
      '#weight' => -10,
    ];

    $form['mergevars'] = [
      '#prefix' => '<div id="mailchimp-newsletter-' . $this->list->id . '-mergefields" class="mailchimp-newsletter-mergefields">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    $user = $this->currentUser();
    $mergevar_options = $this->getMergevarOptions([$this->list->id]);
    foreach ($mergevar_options as $tag => $mergevar) {
      if (!in_array($tag, $this->allowedVars)) {
        continue;
      }
      if (!empty($mergevar)) {
        $form['mergevars'][$tag] = mailchimp_insert_drupal_form_tag($mergevar);
        if (!empty($user) && $tag == 'EMAIL') {
          $form['mergevars'][$tag]['#default_value'] = $user->getEmail();
        }
      }
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['send'] = [
      '#type' => 'submit',
      '#value' => $this->t('Subscribe'),
      '#attributes' => [
        'class' => [
          'use-ajax',
        ],
      ],
      '#ajax' => [
        'callback' => [$this, 'submitModalFormAjax'],
        'event' => 'click',
      ],
    ];

    $form['#attached']['library'][] = 'core/drupal.dialog.ajax';

    return $form;
  }

  /**
   * AJAX callback handler that displays any errors or a success message.
   */
  public function submitModalFormAjax(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // If there are any form errors, re-display the form.
    if ($form_state->hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#os_mailchimp_modal_signup_form', $form));
    }
    else {
      $response->addCommand(new OpenModalDialogCommand("Subscribed!", 'You have successfully subscribed to list.', ['width' => 300]));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $mergevars = $form_state->getValue('mergevars');

    $email = $mergevars['EMAIL'];

    $current_status = mailchimp_get_memberinfo($this->list->id, $email);
    $interests = [];
    if (isset($current_status->interests)) {
      $current_interests = [];
      foreach ($current_status->interests as $id => $selected) {
        if ($selected) {
          $current_interests[$id] = $id;
        }
      }
      $interests[] = $current_interests;
    }

    $result = mailchimp_subscribe($this->list->id, $email, $mergevars, $interests);

    if (empty($result)) {
      $this->messenger->addMessage(t('There was a problem with your newsletter signup to %list.', [
        '%list' => $this->list->name,
      ]), 'warning');
    }
  }

  /**
   * Collect all merge var from MC. (function copy from mailchimp_signup)
   *
   * @param array $mc_lists
   *   List of mc ids.
   *
   * @return array
   *   Merged and collected MC variables.
   *
   * @see Drupal\mailchimp_signup\Form\MailchimpSignupForm::getMergevarOptions()
   */
  private function getMergevarOptions(array $mc_lists) {
    $mergevar_settings = mailchimp_get_mergevars(array_filter($mc_lists));
    $mergevar_options = [];
    foreach ($mergevar_settings as $list_mergevars) {
      foreach ($list_mergevars as $mergevar) {
        if ($mergevar->public) {
          $mergevar_options[$mergevar->tag] = $mergevar;
        }
      }
    }

    return $mergevar_options;
  }

}
