<?php

namespace Drupal\os_widgets;


use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

class LayoutContextListBuilder extends ConfigEntityListBuilder {

  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);

    $row['label'] = $entity->label();
    //$row['description']['data'] = ['#markup' => $entity->getDescription()];

    return $row;
  }
}