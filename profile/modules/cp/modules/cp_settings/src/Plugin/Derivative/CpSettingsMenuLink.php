<?php

namespace Drupal\cp_settings\Plugin\Derivative;


use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\cp_settings\Plugin\CpSettingsManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CpSettingsMenuLink extends DeriverBase implements ContainerDeriverInterface {

  /** @var CpSettingsManagerInterface */
  protected $cpSettingsManager;

  public function __construct($base_plugin_id, CpSettingsManagerInterface $cpSettingsManager) {
    $this->cpSettingsManager = $cpSettingsManager;
  }

  /**
   * @inheritDoc
   */
  public static function create (ContainerInterface $container, $base_plugin_id) {
    return new static(
      $base_plugin_id,
      $container->get('cp_settings.manager')
    );
  }

  public function getDerivativeDefinitions ($base_plugin_definition) {
    dpm('deriving menu links');
    return $this->cpSettingsManager->generateMenuLinks ($base_plugin_definition);
  }
}