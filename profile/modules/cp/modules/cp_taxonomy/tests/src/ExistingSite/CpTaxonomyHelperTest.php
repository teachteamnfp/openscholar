<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

/**
 * Class CpTaxonomyHelperTest.
 *
 * @group cp
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class CpTaxonomyHelperTest extends TestBase {

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
    $settings['allowed_entity_types'] = [
      'media:*' => 'media:*',
      'node:events' => 0,
      'node:faq' => 0,
      'node:link' => 0,
      'node:taxonomy_test_1' => 0,
      'node:taxonomy_test_2' => 'node:taxonomy_test_2',
    ];
    $settings['widget_type'] = '';
    $this->helper->saveVocabularySettings($vid, $settings);
    $settings = $this->helper->getVocabularySettings($vid);
    $this->assertCount(2, $settings['allowed_vocabulary_reference_types']);
    $this->assertSame('media:*', $settings['allowed_vocabulary_reference_types'][0]);
    $this->assertSame('node:taxonomy_test_2', $settings['allowed_vocabulary_reference_types'][1]);
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
   * Test get widget types default value.
   */
  public function testGetWidgetTypesDefaultValue() {
    $vid = 'test_vocab';
    $this->createGroupVocabulary($this->group, $vid, ['node:taxonomy_test_1']);
    // Test default widget type.
    $widget_types = $this->helper->getWidgetTypes('node:taxonomy_test_1');
    $this->assertSame('cp_entity_reference_autocomplete', $widget_types[$vid]['widget_type']);
  }

  /**
   * Test get widget types value.
   */
  public function testGetWidgetTypesSetValue() {
    $vid = 'test_vocab';
    $this->createGroupVocabulary($this->group, $vid, ['node:taxonomy_test_1'], 'cp_options_select');
    // Test default widget type.
    $widget_types = $this->helper->getWidgetTypes('node:taxonomy_test_1');
    $this->assertSame('cp_options_select', $widget_types[$vid]['widget_type']);
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
