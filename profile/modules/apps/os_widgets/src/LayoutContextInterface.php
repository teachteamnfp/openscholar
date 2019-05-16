<?php

namespace Drupal\os_widgets;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\layout_builder\LayoutBuilderEnabledInterface;
use Drupal\layout_builder\SectionListInterface;

interface LayoutContextInterface extends SectionListInterface, LayoutBuilderEnabledInterface, ConfigEntityInterface {

  public function getDescription();

  public function getActivationRules();

}