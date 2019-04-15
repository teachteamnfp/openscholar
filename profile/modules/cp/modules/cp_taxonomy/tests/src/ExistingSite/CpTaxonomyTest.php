<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;


use Drupal\Tests\vsite\ExistingSite\VsiteExistingSiteTestBase;

class CpTaxonomyTest extends VsiteExistingSiteTestBase {

  protected $group;

  protected $groupAlias;

  protected $admin;

  public function setUp() {
    parent::setUp();

    $this->groupAlias = $this->getRandomGenerator()->name();

    $this->group = $this->createGroup([
      'purl' => [
        'alias' => $this->groupAlias
      ]
    ]);
  }
}