<?php

namespace Drupal\vsite_privacy\Plugin\CpSetting;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\cp_settings\CpSettingBase;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * CP setting for vsite privacy.
 *
 * @CpSetting(
 *   id = "vsite_privacy_setting",
 *   title = @Translation("Vsite Privacy Setting Form"),
 *   group = {
 *    "id" = "privacy",
 *    "title" = @Translation("Site Visibility"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class VsitePrivacyForm extends CpSettingBase {

  /**
   * Vsite privacy level manager.
   *
   * @var \Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface
   */
  protected $vsitePrivacyLevelManager;

  /**
   * VsitePrivacyForm constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface $vsite_privacy_level_manager
   *   Vsite privacy level manager.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VsitePrivacyLevelManagerInterface $vsite_privacy_level_manager, VsiteContextManagerInterface $vsite_context_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $vsite_context_manager);
    $this->vsitePrivacyLevelManager = $vsite_privacy_level_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('vsite.privacy.manager'),
      $container->get('vsite.context_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getEditableConfigNames() : array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getForm(array &$form, ConfigFactoryInterface $configFactory) {
    /** @var array|null $default_privacy_level */
    $default_privacy_level = $this->activeVsite->get('field_privacy_level')->getValue();
    $form['privacy_levels'] = [
      '#type' => 'radios',
      '#title' => $this->t('Site Visibility'),
      '#options' => $this->vsitePrivacyLevelManager->getOptions(),
      '#default_value' => $default_privacy_level[0]['value'] ?? NULL,
    ];
    $descriptions = $this->vsitePrivacyLevelManager->getDescriptions();
    foreach ($descriptions as $key => $description) {
      $form['privacy_levels'][$key] = [
        '#description' => $description,
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(FormStateInterface $form_state, ConfigFactoryInterface $config_factory) {
    $this->activeVsite->set('field_privacy_level', [
      'value' => $form_state->getValue('privacy_levels'),
    ]);
    $this->activeVsite->save();
  }

}
