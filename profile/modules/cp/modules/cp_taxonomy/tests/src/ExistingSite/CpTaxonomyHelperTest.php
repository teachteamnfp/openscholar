<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Drupal\Tests\openscholar\Traits\CpTaxonomyTestTrait;

/**
 * Class CheckingFieldsTest.
 *
 * @group cp
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class CpTaxonomyHelperTest extends TestBase {

  use CpTaxonomyTestTrait;

  /**
   * Cp Taxonomy Helper.
   *
   * @var \Drupal\cp_taxonomy\CpTaxonomyHelperInterface
   */
  private $helper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->helper = $this->container->get('cp.taxonomy.helper');
  }

  /**
   * Test saving allowed bundles.
   */
  public function testSavingAllowedBundlesToVocabulary() {
    $vid = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vid, ['node:taxonomy_test_1']);
    $form_state_array = [
      'media:*' => 'media:*',
      'node:events' => 0,
      'node:faq' => 0,
      'node:link' => 0,
      'node:taxonomy_test_1' => 0,
      'node:taxonomy_test_2' => 'node:taxonomy_test_2',
    ];
    $this->helper->saveAllowedBundlesToVocabulary($vid, $form_state_array);
    $form['vid']['#default_value'] = $vid;
    $options_default = $this->helper->getSelectedBundles($form);
    $this->assertCount(2, $options_default);
    $this->assertSame('media:*', $options_default[0]);
    $this->assertSame('node:taxonomy_test_2', $options_default[1]);
  }

  /**
   * Test get selectable bundles value.
   */
  public function testSelectableBundlesValue() {
    $selectable_bundles = $this->helper->getSelectableBundles();
    $this->assertArrayHasKey('media:*', $selectable_bundles);
    $this->assertArrayHasKey('node:taxonomy_test_1', $selectable_bundles);
    $this->assertArrayHasKey('node:taxonomy_test_2', $selectable_bundles);
    $this->assertSame('Media', $selectable_bundles['media:*']->__toString());
    $this->assertSame('Taxonomy Test 1', $selectable_bundles['node:taxonomy_test_1']);
    $this->assertSame('Taxonomy Test 2', $selectable_bundles['node:taxonomy_test_2']);
  }

  /**
   * Test empty build PageVisibility.
   */
  public function testEmptyBuildPageVisibility() {
    $build = [];
    $this->helper->checkTaxonomyTermsPageVisibility($build, []);
    $this->assertEmpty($build);
  }

  /**
   * Test empty build ListingVisibility.
   */
  public function testEmptyBuildListingVisibility() {
    $build = [];
    $this->helper->checkTaxonomyTermsListingVisibility($build, '');
    $this->assertEmpty($build);
  }

}
