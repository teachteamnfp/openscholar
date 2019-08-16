<?php

namespace Drupal\os_app_access\Access;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\Access\AccessInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\os_app_access\AppAccessLevels;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AppAccess.
 */
class AppAccess implements AccessInterface, ContainerInjectionInterface {

  /**
   * Config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Vsite context manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Creates a new AppAccess object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   Config factory service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager.
   */
  public function __construct(ConfigFactoryInterface $config_factory, VsiteContextManagerInterface $vsite_context_manager) {
    $this->configFactory = $config_factory;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('vsite.context_manager')
    );
  }

  /**
   * Returns access.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user account.
   * @param string $app_name
   *   The app name.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result.
   */
  public function access(AccountInterface $account, $app_name): AccessResultInterface {
    /** @var \Drupal\Core\Config\ImmutableConfig $levels */
    $levels = $this->configFactory->get('os_app_access.access');
    /** @var int $access_level */
    $access_level = (int) $levels->get($app_name);
    /** @var \Drupal\Core\Access\AccessResult $result */
    $result = AccessResult::neutral();

    if ($access_level === AppAccessLevels::PRIVATE) {
      /** @var \Drupal\group\Entity\GroupInterface|null $active_vsite */
      $active_vsite = $this->vsiteContextManager->getActiveVsite();

      if ($active_vsite) {
        if ($active_vsite->hasPermission('access private apps', $account)) {
          $result = AccessResult::allowed();
        }
        else {
          $result = AccessResult::forbidden();
        }
      }
    }
    if ($access_level === AppAccessLevels::DISABLED) {
      $result = AccessResult::forbidden('This App has been disabled.');
    }

    $result->addCacheTags(['app:access_changed']);
    $result->addCacheContexts(['vsite']);

    return $result;
  }

}
