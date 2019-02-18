<?php

namespace Drupal\os_publications\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * API Configuration form.
 */
class CitationDistributeConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() {
    return ['citation_distribute.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'citation_distribute_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('citation_distribute.settings');

    $unconfigured = [];
    foreach (_citation_distribute_plugins() as $plugin) {
      if (isset($plugin['name']) && !_citation_distribute_is_service_configured($plugin)) {
        $unconfigured[] = $plugin['name'];
      }
    }

    if (count($unconfigured)) {
      $form['citation_distribute_config_message'] = [
        '#type' => 'markup',
        '#markup' => '<div class="messages warning">' . $this->t('The following plugin(s) need to be configured:') . implode(', ', $unconfigured) . '</div>',
      ];
    }

    $form['citation_distribute'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configure Citation Distribute'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    // Module mode.
    $form['citation_distribute']['citation_distribute_module_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the mode for this module'),
      '#options' => [
        'api' => 'API Mode',
        'batch' => 'Batch Process Mode',
        'per_submission' => 'Per Submission Mode',
      ],
      '#required' => TRUE,
      '#default_value' => $config->get('module_mode'),
      '#description' => $this->t('<strong>API mode</strong> does nothing by default, but allows developers to call Citation Distribute manually.
	      <br><strong>Batch mode</strong> is intended to be run by cron will update all meta files at once in a batch process.
	      <br><strong>Per Submission mode</strong> (<em>default</em>) will update or create a meta file whenever content submitted or updated.'),
    ];

    // Cron limit.
    $form['citation_distribute']['citation_distribute_cron_limit'] = [
      '#type' => 'textfield',
      '#title' => 'Batch Size Limit',
      '#description' => $this->t('(Batch mode only) Limit how many publications can be submitted per cron run.'),
      '#required' => FALSE,
      '#default_value' => $config->get('cron_limit'),
    ];

    // List all our plugins, include autoflag checkboxes.
    $form['citation_distribute']['autoflag'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Automatic Node Flagging'),
      '#description' => $this->t('Publication nodes should automatically select the following services for distribution:<br /><br />'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    foreach (_citation_distribute_plugins() as $plugin) {
      if (isset($plugin['name'])) {
        $name = $plugin['id'];
        $form['citation_distribute']['autoflag'][$name . '_auto_flag'] = [
          '#type' => 'checkbox',
          '#default_value' => $config->get($name . '_auto_flag'),
          '#title' => $plugin['name'] . '  (' . $name . ')',
        ];
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * Validate batch size limit.
   */
  public function validate($form, &$form_state) {
    if ((int) $form_state['values']['citation_distribute_cron_limit'] <= 0) {
      $form_state->setErrorByName('citation_distribute_cron_limit', $this->t('Batch size limit must be a positive integer.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $this->config('dash.settings')
      ->set('dash_username', $form_state->getValue('username'))
      ->set('dash_password', $form_state->getValue('password'))
      ->save();
  }

}
