<?php

namespace Drupal\vsite_privacy\Plugin\CpSetting;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_settings\CpSettingInterface;
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
 *    "title" = @Translation("Privacy"),
 *    "parent" = "cp.settings.global"
 *   }
 * )
 */
class VsitePrivacyForm extends PluginBase implements CpSettingInterface, ContainerFactoryPluginInterface {

  /**
   * Vsite privacy level manager.
   *
   * @var \Drupal\vsite_privacy\Plugin\VsitePrivacyLevelManagerInterface
   */
  protected $vsitePrivacyLevelManager;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Active vsite.
   *
   * @var \Drupal\group\Entity\GroupInterface|null
   */
  protected $activeVsite = NULL;

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
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->vsitePrivacyLevelManager = $vsite_privacy_level_manager;
    $this->vsiteContextManager = $vsite_context_manager;
    $this->activeVsite = $this->vsiteContextManager->getActiveVsite();
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
      '#title' => $this->t('Privacy Levels'),
      '#options' => $this->vsitePrivacyLevelManager->getOptions(),
      '#default_value' => $default_privacy_level[0]['value'] ?? NULL,
    ];

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

  /**
   * {@inheritdoc}
   */
  public function access(AccountInterface $account) : AccessResultInterface {
    if (!$this->activeVsite) {
      return AccessResult::forbidden();
    }

    return AccessResult::allowed();
  }

}
