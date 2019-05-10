<?php

namespace Drupal\Tests\os_widgets\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests os_widgets module.
 *
 * @group functional-javascript
 * @group widgets
 */
class TaxonomyBlockJavascriptTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->configFactory = $this->container->get('config.factory');
    $this->adminUser = $this->createAdminUser();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Tests os_widgets taxonomy field vocabulary selection.
   */
  public function testTaxonomyVocabularyAndBundleAjax() {
    $web_assert = $this->assertSession();

    $vocabulary = $this->createVocabulary();
    $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $vocabulary->id());
    $config_vocab
      ->set('allowed_vocabulary_reference_types', [
        'node:taxonomy_test_1',
        'node:taxonomy_test_2',
      ])
      ->save(TRUE);

    $this->visit("/block/add/taxonomy");
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();

    $check_html_value = $page->hasContent('Vocabulary');
    $this->assertTrue($check_html_value, 'Vocabulary field is not visible.');
    $field_taxonomy_behavior = $page->findField('field_taxonomy_behavior');
    $field_taxonomy_behavior->selectOption('select');
    $vocabulary_field = $page->findField('field_taxonomy_vocabulary');
    $vocabulary_field->selectOption($vocabulary->id());
    $web_assert->assertWaitOnAjaxRequest();
    $bundle_field = $page->findField('field_taxonomy_bundle');
    $options = $bundle_field->getHtml();
    $this->assertEquals('<option value="_none" selected="selected">- None -</option><option value="node:taxonomy_test_1">Content - Taxonomy Test 1</option><option value="node:taxonomy_test_2">Content - Taxonomy Test 2</option>', $options);
  }

}
