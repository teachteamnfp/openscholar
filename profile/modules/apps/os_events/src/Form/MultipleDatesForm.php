<?php

namespace Drupal\os_events\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Implements the SelectList for repeating event dates.
 */
class MultipleDatesForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'repeating_events_select_list';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array $data = []) {

    $form['#prefix'] = '<div class="repeating_events_select_list os-slider visually-hidden">';
    $form['#suffix'] = '</div>';

    $form['nid'] = [
      '#type' => 'item',
      '#value' => $data['nid'],
    ];

    $form['rdates'] = [
      '#type' => 'select',
      '#options' => ['' => $this->t('-Select a different occurrence-')] + $data['options'],
      '#ajax' => [
        'callback' => '::ajaxCallback',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRebuild(TRUE);
  }

  /**
   * Ajax callback to change the timestamp.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $values = $form_state->getValues();
    $timestamp = $values['rdates'];

    $url = Url::fromRoute('os_events.signup_modal_form', ['nid' => $values['nid'], 'timestamp' => $timestamp], [
      'attributes' => [
        'class' => ['use-ajax'],
        'data-dialog-type' => 'modal',
        'data-dialog-options' => json_encode(['width' => '100%']),
        'id' => 'events_signup_modal_form',
      ],
    ]);
    $link = Link::fromTextAndUrl('Signup for this event', $url)->toString();

    $command = new ReplaceCommand('#registration-link-' . $values['nid'] . ' #events_signup_modal_form', $link);
    return $response->addCommand($command);
  }

}
