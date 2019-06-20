<?php

namespace Drupal\vsite;

use Drupal\group\Entity\GroupInterface;
use Drupal\vsite\Entity\GroupPresetInterface;

/**
 * This class solely exists to test presets.
 *
 * @package Drupal\vsite
 */
class TestTask {

  public function testMethod(GroupInterface $group, GroupPresetInterface $preset, array &$context) {
    if (empty($context['sandbox'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = 100;
    }

    $limit = 5;
    for ($i = 0; $i < $limit; $i++) {
      sleep(5);
      $i++;
      $context['sandbox']['progress']++;
    }

    if ($context['sandbox']['progress'] < $context['sandbox']['progress']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }

}
