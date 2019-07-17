<?php

namespace Drupal\cp_settings\Access;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\cp_settings\Plugin\CpSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Custom access checker for CpSettings.
 */
class CpSettingsAccessCheck implements AccessInterface, ContainerInjectionInterface {

  /**
   * CpSettings manager.
   *
   * @var \Drupal\cp_settings\Plugin\CpSettingsManagerInterface
   */
  protected $cpSettingsManager;

  /**
   * Creates a new CpSettingsAccessCheck object.
   *
   * @param \Drupal\cp_settings\Plugin\CpSettingsManagerInterface $cp_settings_manager
   *   CpSettings manager.
   */
  public function __construct(CpSettingsManagerInterface $cp_settings_manager) {
    $this->cpSettingsManager = $cp_settings_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('cp_settings.manager'));
  }

  /**
   * Checks whether the CpSetting is accessible to the user.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user.
   * @param string $setting_group_id
   *   The CpSetting group id.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   */
  public function access(AccountInterface $account, $setting_group_id): AccessResultInterface {
    /** @var \Drupal\cp_settings\CpSettingInterface[] $plugins */
    $plugins = $this->cpSettingsManager->getPluginsForGroup($setting_group_id);
    $instance = reset($plugins);
    return $instance->access($account);
  }

}
