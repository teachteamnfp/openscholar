<?php

namespace Drupal\os_app_access\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\vsite\Plugin\AppManangerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates node/add links for the control panel.
 *
 * @package Drupal\os_app_access\Plugin\Deriver
 */
class OsAppAccessMenuLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * Manager for the app plugins.
   *
   * @var \Drupal\vsite\Plugin\AppManangerInterface
   */
  protected $appManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(AppManangerInterface $appManager) {
    $this->appManager = $appManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('vsite.app.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    /** @var array[] $definitions */
    $definitions = $this->appManager->getDefinitions();

    $links = [];
    foreach ($definitions as $plugin_id => $d) {
      $p = $this->appManager->createInstance($plugin_id);
      $links = array_merge($links, $p->getCreateLinks());
    }

    return $links;
  }

}
