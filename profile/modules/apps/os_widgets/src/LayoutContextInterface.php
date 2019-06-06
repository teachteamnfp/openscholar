<?php

namespace Drupal\os_widgets;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\layout_builder\LayoutBuilderEnabledInterface;
use Drupal\layout_builder\SectionListInterface;

interface LayoutContextInterface extends SectionListInterface, ConfigEntityInterface {

  public function getDescription();

  public function getActivationRules();

  public function getWeight();

  public function applies(): bool;

  public function getBlockPlacements();

  public function setBlockPlacements(array $blocks);

}
