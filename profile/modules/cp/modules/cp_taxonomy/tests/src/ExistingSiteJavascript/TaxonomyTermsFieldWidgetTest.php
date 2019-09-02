<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\cp_taxonomy\Plugin\Field\FieldWidget\TaxonomyTermsWidget;
use Drupal\Tests\openscholar\Traits\CpTaxonomyTestTrait;

/**
 * Tests taxonomy_terms fields functionality with settings.
 *
 * @group functional-javascript
 * @group cp
 */
class TaxonomyTermsFieldWidgetTest extends CpTaxonomyExistingSiteJavascriptTestBase {

  use CpTaxonomyTestTrait;

  protected $testVid;
  protected $term1;
  protected $term2;
  protected $node;
  protected $config;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->drupalLogin($group_admin);
    $this->testVid = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $this->testVid, ['node:taxonomy_test_1']);
    $this->term1 = $this->createGroupTerm($this->group, $this->testVid, ['name' => 'Term1']);
    $this->term2 = $this->createGroupTerm($this->group, $this->testVid, ['name' => 'Term2']);
    $this->node = $this->createNode([
      'type' => 'taxonomy_test_1',
      'field_taxonomy_terms' => [
        $this->term1->id(),
      ],
    ]);
    $this->group->addContent($this->node, 'group_node:taxonomy_test_1');
    $this->config = $this->configFactory->getEditable('cp_taxonomy.settings');
  }

  /**
   * Test node taxonomy terms field settings: autocomplete.
   */
  public function testNodeTaxonomyTermsFieldSettingsAutocomplete() {
    $this->assertTaxonomyTermsFieldByWidgetType(TaxonomyTermsWidget::WIDGET_TYPE_AUTOCOMPLETE, 'data-autocomplete-path');

    // Test add new node page.
    $this->visitViaVsite('node/add/taxonomy_test_1', $this->group);
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $terms = $page->findField('field_taxonomy_terms[' . $this->testVid . '][0][target_id]');
    $terms->setValue('Term1');
    $result = $web_assert->waitForElementVisible('css', '.ui-autocomplete li');
    $this->assertNotNull($result);
    // Click the autocomplete option.
    $result->click();
    $web_assert->pageTextContains('Term1 (' . $this->term1->id() . ')');
    $add_more_button = $page->findButton('Add another item');
    $add_more_button->press();
    $second_element = $web_assert->waitForElement('css', '[name="field_taxonomy_terms[' . $this->testVid . '][1][target_id]"].ui-autocomplete-input');
    $this->assertNotEmpty($second_element, 'Add more button is not working.');
    $terms = $page->findField('field_taxonomy_terms[' . $this->testVid . '][1][target_id]');
    $terms->setValue('Term2');
    $result = $web_assert->waitForElementVisible('css', '.ui-autocomplete li');
    $this->assertNotNull($result);
    // Click the autocomplete option.
    $result->click();
    $this->saveNodeAndAssertTerms($page);
  }

  /**
   * Test node taxonomy terms field settings: select list.
   */
  public function testNodeTaxonomyTermsFieldSettingsSelectList() {
    $this->assertTaxonomyTermsFieldByWidgetType(TaxonomyTermsWidget::WIDGET_TYPE_OPTIONS_SELECT, 'form-select chosen-enable');

    // Test add new node page.
    $this->visitViaVsite('node/add/taxonomy_test_1', $this->group);
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $chosen_wrapper = $page->findById('edit_field_taxonomy_terms_' . strtolower($this->testVid) . '_chosen');
    $input = $chosen_wrapper->find('css', '.chosen-search-input');
    $input->click();
    $result = $web_assert->waitForElementVisible('css', '.active-result.highlighted');
    file_put_contents('public://testNodeTaxonomyTermsFieldSettingsSelectList.png', $this->getSession()->getScreenshot());
    $this->assertNotEmpty($result, 'Chosen popup is not visible.');
    $web_assert->pageTextContains('Term1');
    $web_assert->pageTextContains('Term2');
    // Select two terms.
    $page->find('css', '.active-result.highlighted')->click();
    $input = $chosen_wrapper->find('css', '.chosen-search-input');
    $input->click();
    $page->find('css', '.active-result.highlighted')->click();
    file_put_contents('public://testNodeTaxonomyTermsFieldSettingsSelectList_selected.png', $this->getSession()->getScreenshot());
    $this->saveNodeAndAssertTerms($page);
  }

  /**
   * Test node taxonomy terms field settings: checkboxes / radio buttons.
   */
  public function testNodeTaxonomyTermsFieldSettingsCheckboxesRadio() {
    $this->assertTaxonomyTermsFieldByWidgetType(TaxonomyTermsWidget::WIDGET_TYPE_OPTIONS_BUTTONS, 'class="form-checkbox"');

    // Test add new node page.
    $this->visitViaVsite('node/add/taxonomy_test_1', $this->group);
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $page->findField('field_taxonomy_terms[' . $this->testVid . '][' . $this->term1->id() . ']')->check();
    $page->findField('field_taxonomy_terms[' . $this->testVid . '][' . $this->term2->id() . ']')->check();
    $this->saveNodeAndAssertTerms($page);
  }

  /**
   * Test node taxonomy terms field settings: tree.
   */
  public function testNodeTaxonomyTermsFieldSettingsTree() {
    $this->assertTaxonomyTermsFieldByWidgetType(TaxonomyTermsWidget::WIDGET_TYPE_TREE, '<ul class="term-reference-tree-level ">');

    // Test add new node page.
    $this->visitViaVsite('node/add/taxonomy_test_1', $this->group);
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $page->findField('field_taxonomy_terms[' . $this->testVid . '][0][' . $this->term1->id() . '][' . $this->term1->id() . ']')->check();
    $page->findField('field_taxonomy_terms[' . $this->testVid . '][0][' . $this->term2->id() . '][' . $this->term2->id() . ']')->check();
    $this->saveNodeAndAssertTerms($page);
  }

  /**
   * Assert function to check field markup depend on widget type settings.
   *
   * @param string $widget_type
   *   Vocabulary widget type settings.
   * @param string $assert_markup
   *   Expected html markup for field.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function assertTaxonomyTermsFieldByWidgetType(string $widget_type, string $assert_markup): void {
    $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $this->testVid);
    $config_vocab
      ->set('widget_type', $widget_type)
      ->save(TRUE);
    $this->visitViaVsite('node/' . $this->node->id() . '/edit', $this->group);
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($this->testVid);
    $page = $this->getCurrentPage();
    $field_taxonomy_element = $page->find('css', '.field--name-field-taxonomy-terms');
    $this->assertContains($this->term1->label(), $field_taxonomy_element->getHtml());
    $this->assertContains($assert_markup, $field_taxonomy_element->getHtml());
  }

  /**
   * Save node and assert terms are saved properly.
   */
  protected function saveNodeAndAssertTerms($page): void {
    $title = $this->randomMachineName();
    $page->fillField('title[0][value]', $title);
    $page->findButton('URL alias')->press();
    $page->fillField('path[0][alias]', '/' . $this->randomMachineName());
    $page->pressButton('Save');
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['title' => $title]);
    $this->assertNotEmpty($nodes, 'Test node is not created.');
    foreach ($nodes as $node) {
      /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $terms_values */
      $terms_values = $node->get('field_taxonomy_terms');
      $this->assertCount(2, $terms_values);
      $item_value = $terms_values[0]->getValue();
      $this->assertEquals($this->term1->id(), $item_value['target_id']);
      $item_value = $terms_values[1]->getValue();
      $this->assertEquals($this->term2->id(), $item_value['target_id']);
      $this->markEntityForCleanup($node);
    }
  }

}
