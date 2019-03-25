<?php

namespace Drupal\os_publications\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\os_publications\CitationDistributionModes;
use Drupal\os_publications\Plugin\CitationDistribution\CitationDistributePluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * API Configuration form.
 */
class CitationDistributeConfigForm extends ConfigFormBase {

  /**
   * The config setting which this form is supposed to alter.
   */
  const SETTINGS = 'os_publications.settings';

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
    return [
      self::SETTINGS,
    ];
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
    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $this->config(self::SETTINGS);

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
        CitationDistributionModes::BATCH => 'Batch Process Mode',
        CitationDistributionModes::PER_SUBMISSION => 'Per Submission Mode',
      ],
      '#required' => TRUE,
      '#default_value' => $config->get('citation_distribute_module_mode'),
      '#description' => $this->t('<strong>Batch mode</strong> is intended to be run by cron will update all meta files at once in a batch process.
	      <br><strong>Per Submission mode</strong> (<em>default</em>) will update or create a meta file whenever content submitted or updated.'),
    ];

    // List all our plugins, include autoflag checkboxes.
    $form['citation_distribute']['autoflag'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Automatic Node Flagging'),
      '#description' => $this->t('Publication nodes should automatically select the following services for distribution:<br /><br />'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    ];

    /** @var array $default_auto_flag_settings */
    $default_auto_flag_settings = $config->get('citation_distribute_autoflags');

    foreach ($plugins as $plugin) {
      /** @var string $id */
      $id = $plugin['id'];
      $form['citation_distribute']['autoflag'][$id] = [
        '#type' => 'checkbox',
        '#default_value' => $default_auto_flag_settings[$id] ?? FALSE,
        '#title' => "{$plugin['name']} ($id)",
      ];
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $auto_flag_settings = [];

    $this->config(self::SETTINGS)
      ->set('citation_distribute_module_mode', $form_state->getValue('citation_distribute_module_mode'))
      ->save();

    foreach ($this->citationDistributePluginManager->getDefinitions() as $plugin) {
      $auto_flag_settings[$plugin['id']] = $form_state->getValue($plugin['id']);
    }

    $this->config(self::SETTINGS)
      ->set('citation_distribute_autoflags', $auto_flag_settings)
      ->save();
  }

}
