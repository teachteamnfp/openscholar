<?php

namespace Drupal\os_mailchimp\Form;

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

  /**
   * Set mailchimp list.
   *
   * @param \stdClass $list
   *   Mailchimp list object from API.
   */
  public function setList(\stdClass $list) {
    $this->list = $list;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os_mailchimp_signup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    if (empty($this->list)) {
      return $form;
    }
    $form['mergevars'] = [
      '#prefix' => '<div id="mailchimp-newsletter-' . $this->list->id . '-mergefields" class="mailchimp-newsletter-mergefields">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    ];

    $mergevar_options = $this->getMergevarOptions([$this->list->id]);
    foreach ($mergevar_options as $tag => $mergevar) {
      if (!empty($mergevar)) {
        $form['mergevars'][$tag] = mailchimp_insert_drupal_form_tag($mergevar);
      }
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Subscribe'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Implement submitForm() method.
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
