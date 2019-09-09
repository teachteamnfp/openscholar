<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Drupal\Tests\openscholar\Traits\CpTaxonomyTestTrait;
use Drupal\Tests\vsite\ExistingSite\VsiteExistingSiteTestBase;

/**
 * TestBase for cp_taxonomy tests.
 */
abstract class TestBase extends VsiteExistingSiteTestBase {

  use CpTaxonomyTestTrait;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Taxonomy relation helper service.
   *
   * @var \Drupal\cp_taxonomy\CpTaxonomyHelper
   */
  protected $taxonomyHelper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->configFactory = $this->container->get('config.factory');
    $this->taxonomyHelper = $this->container->get('cp.taxonomy.helper');
  }

}
