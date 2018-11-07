<?php

namespace Drupal\vsite\Plugin;


use Drupal\Component\Plugin\PluginManagerInterface;

interface AppManangerInterface extends PluginManagerInterface {

  public function getAppForBundle(string $bundle) : string;
}