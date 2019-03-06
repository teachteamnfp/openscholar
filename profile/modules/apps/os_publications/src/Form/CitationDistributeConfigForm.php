<?php

namespace Drupal\os_publications\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * API Configuration form.
 */
class CitationDistributeConfigForm extends ConfigFormBase {

  /**
   * Citation distribute plugin manager.
   *
   * @var \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager
   */
  protected $citationDistributePluginManager;

  /**
   * CitationDistributeConfigForm constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory.
   * @param \Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager $citation_distribute_plugin_manager
   *   Citation distribute plugin manager.
   */
  public function __construct(ConfigFactory $config_factory, CitationDistributePluginManager $citation_distribute_plugin_manager) {
    parent::__construct($config_factory);
    $this->citationDistributePluginManager = $citation_distribute_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('os_publications.manager_citation_distribute')
    );
  }

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
    /** @var array $plugins */
    $plugins = $this->citationDistributePluginManager->getDefinitions();

    $form['citation_distribute'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Configure Citation Distribute'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    $form['citation_distribute']['citation_distribute_module_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the mode for this module'),
      '#options' => [
        'api' => 'API Mode',
        'batch' => 'Batch Process Mode',
        'per_submission' => 'Per Submission Mode',
      ],
      '#required' => TRUE,
      '#default_value' => $config->get('citation_distribute_module_mode'),
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
      '#default_value' => $config->get('citation_distribute_cron_limit'),
    ];

    // List all our plugins, include autoflag checkboxes.
    $form['citation_distribute']['autoflag'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Automatic Node Flagging'),
      '#description' => $this->t('Publication nodes should automatically select the following services for distribution:<br /><br />'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    foreach ($plugins as $plugin) {
      $name = $plugin['id'];
      $form['citation_distribute']['autoflag'][$name . '_auto_flag'] = [
        '#type' => 'checkbox',
        '#default_value' => $config->get($name . '_auto_flag'),
        '#title' => $plugin['name'] . '  (' . $name . ')',
      ];
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
    $this->config('citation_distribute.settings')
      ->set('citation_distribute_module_mode', $form_state->getValue('citation_distribute_module_mode'))
      ->set('citation_distribute_cron_limit', $form_state->getValue('citation_distribute_cron_limit'))
      ->save();
    foreach ($this->citationDistributePluginManager->getDefinitions() as $plugin) {
      $this->config('citation_distribute.settings')
        ->set($plugin['id'] . '_auto_flag', $form_state->getValue($plugin['id'] . '_auto_flag'))
        ->save();
    }
  }

}
