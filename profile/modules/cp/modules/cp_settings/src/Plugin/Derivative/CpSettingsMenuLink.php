<?php

namespace Drupal\cp_settings\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\cp_settings\Plugin\CpSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates CP setting menu link.
 */
class CpSettingsMenuLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * CP setting manager.
   *
   * @var \Drupal\cp_settings\Plugin\CpSettingsManagerInterface
   */
  protected $cpSettingsManager;

  /**
   * Creates a new CpSettingsMenuLink object.
   *
   * @param string $base_plugin_id
   *   Base plugin id.
   * @param \Drupal\cp_settings\Plugin\CpSettingsManagerInterface $cpSettingsManager
   *   CP setting manager.
   */
  public function __construct($base_plugin_id, CpSettingsManagerInterface $cpSettingsManager) {
    $this->cpSettingsManager = $cpSettingsManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('cp_settings.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    return $this->cpSettingsManager->generateMenuLinks($base_plugin_definition);
  }

}
