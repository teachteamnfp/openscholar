<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

/**
 * Class CheckingFieldsTest.
 *
 * @group other
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class CpTaxonomyHelperTest extends TestBase {

  private $group;
  private $helper;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/' . $this->randomMachineName(),
      ],
    ]);
    $this->helper = $this->container->get('cp.taxonomy.helper');
  }

  /**
   * Test saving allowed bundles.
   */
  public function testSavingAllowedBundlesToVocabulary() {
    $vid = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vid, ['node:taxonomy_test_1']);
    $form_state_array = [
      'media:executable' => 0,
      'media:taxonomy_test_file' => 'media:taxonomy_test_file',
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
    $this->assertSame('media:taxonomy_test_file', $options_default[0]);
    $this->assertSame('node:taxonomy_test_2', $options_default[1]);
  }

  /**
   * Test get selectable bundles value.
   */
  public function testSelectableBundlesValue() {
    $selectable_bundles = $this->helper->getSelectableBundles();
    $this->assertArrayHasKey('media:taxonomy_test_file', $selectable_bundles);
    $this->assertArrayHasKey('node:taxonomy_test_1', $selectable_bundles);
    $this->assertArrayHasKey('node:taxonomy_test_2', $selectable_bundles);
    $this->assertSame('Media - Taxonomy Test File', $selectable_bundles['media:taxonomy_test_file']);
    $this->assertSame('Content - Taxonomy Test 1', $selectable_bundles['node:taxonomy_test_1']);
    $this->assertSame('Content - Taxonomy Test 2', $selectable_bundles['node:taxonomy_test_2']);
  }

}
