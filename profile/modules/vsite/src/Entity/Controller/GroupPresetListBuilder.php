<?php

namespace Drupal\vsite\Entity\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class GroupPresetListBuilder.
 *
 * @package Drupal\vsite\Entity\Controller
 */
class GroupPresetListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [
      'label' => $this->t('Label'),
      'description' => $this->t('Description'),
    ] + parent::buildHeader();

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = [
      'label' => [
        'data' => $entity->label(),
      ],
      'description' => [
        'data' => $entity->get('description'),
      ],
    ];
    $row += parent::buildRow($entity);

    return $row;
  }

}
