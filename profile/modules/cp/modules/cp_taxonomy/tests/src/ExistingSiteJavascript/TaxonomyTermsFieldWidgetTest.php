<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Behat\Mink\Element\DocumentElement;
use Drupal\cp_taxonomy\CpTaxonomyHelper;
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
    $this->setTestVocabularyWidget(CpTaxonomyHelper::WIDGET_TYPE_AUTOCOMPLETE);
    $this->assertTaxonomyTermsFieldVisible('data-autocomplete-path');

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
    $this->setTestVocabularyWidget(CpTaxonomyHelper::WIDGET_TYPE_OPTIONS_SELECT);
    $this->assertTaxonomyTermsFieldVisible('form-select chosen-enable');

    // Test add new node page.
    $this->visitViaVsite('node/add/taxonomy_test_1', $this->group);
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();
    $chosen_wrapper = $page->findById('edit_field_taxonomy_terms_' . strtolower($this->testVid) . '_chosen');
    $input = $chosen_wrapper->find('css', '.chosen-search-input');
    $input->click();
    $result = $web_assert->waitForElementVisible('css', '.active-result.highlighted');
    $this->assertNotEmpty($result, 'Chosen popup is not visible.');
    $web_assert->pageTextContains('Term1');
    $web_assert->pageTextContains('Term2');
    // Select two terms.
    $page->find('css', '.active-result.highlighted')->click();
    $input = $chosen_wrapper->find('css', '.chosen-search-input');
    $input->click();
    $page->find('css', '.active-result.highlighted')->click();
    $this->saveNodeAndAssertTerms($page);
  }

  /**
   * Test node taxonomy terms field settings: checkboxes / radio buttons.
   */
  public function testNodeTaxonomyTermsFieldSettingsCheckboxesRadio() {
    $this->setTestVocabularyWidget(CpTaxonomyHelper::WIDGET_TYPE_OPTIONS_BUTTONS);
    $this->assertTaxonomyTermsFieldVisible('class="form-checkbox"');

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
    $this->setTestVocabularyWidget(CpTaxonomyHelper::WIDGET_TYPE_TREE);
    $this->assertTaxonomyTermsFieldVisible('<ul class="term-reference-tree-level ">');

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
   * Test multiple vocabularies on same entity type.
   */
  public function testMultipleTaxonomyTermWidgetsWithExistsValues() {
    // Create autocomplete vocab.
    $vocab_autocomplete = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vocab_autocomplete, ['node:taxonomy_test_1'], CpTaxonomyHelper::WIDGET_TYPE_AUTOCOMPLETE);
    $autocomplete_term_1 = $this->createGroupTerm($this->group, $vocab_autocomplete, ['name' => 'Term autocomplete 1']);
    $autocomplete_term_2 = $this->createGroupTerm($this->group, $vocab_autocomplete, ['name' => 'Term autocomplete 2']);
    // Create select vocab.
    $vocab_select = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vocab_select, ['node:taxonomy_test_1'], CpTaxonomyHelper::WIDGET_TYPE_OPTIONS_SELECT);
    $select_term_1 = $this->createGroupTerm($this->group, $vocab_select, ['name' => 'Term select 1']);
    $select_term_2 = $this->createGroupTerm($this->group, $vocab_select, ['name' => 'Term select 2']);
    // Create options vocab.
    $vocab_options = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vocab_options, ['node:taxonomy_test_1'], CpTaxonomyHelper::WIDGET_TYPE_OPTIONS_BUTTONS);
    $options_term_1 = $this->createGroupTerm($this->group, $vocab_options, ['name' => 'Term options 1']);
    $options_term_2 = $this->createGroupTerm($this->group, $vocab_options, ['name' => 'Term options 2']);
    // Create tree vocab.
    $vocab_tree = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vocab_tree, ['node:taxonomy_test_1'], CpTaxonomyHelper::WIDGET_TYPE_TREE);
    $tree_term_1 = $this->createGroupTerm($this->group, $vocab_tree, ['name' => 'Term tree 1']);
    $tree_term_2 = $this->createGroupTerm($this->group, $vocab_tree, ['name' => 'Term tree 2']);
    $node = $this->createNode([
      'type' => 'taxonomy_test_1',
      'field_taxonomy_terms' => [
        $autocomplete_term_1->id(),
        $select_term_1->id(),
        $options_term_2->id(),
        $tree_term_2->id(),
      ],
    ]);
    $this->group->addContent($node, 'group_node:taxonomy_test_1');

    $this->visitViaVsite('node/' . $node->id() . '/edit', $this->group);
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($this->testVid);
    $page = $this->getCurrentPage();
    $field_taxonomy_element = $page->find('css', '.field--name-field-taxonomy-terms');
    $this->assertContains($autocomplete_term_1->label(), $field_taxonomy_element->getHtml());
    $this->assertContains($select_term_1->label(), $field_taxonomy_element->getHtml());
    $this->assertContains($select_term_2->label(), $field_taxonomy_element->getHtml());
    $this->assertContains($options_term_1->label(), $field_taxonomy_element->getHtml());
    $this->assertContains($options_term_2->label(), $field_taxonomy_element->getHtml());
    $this->assertContains($tree_term_1->label(), $field_taxonomy_element->getHtml());
    $this->assertContains($tree_term_2->label(), $field_taxonomy_element->getHtml());

    // Check autocomplete widget modify values.
    $page = $this->getCurrentPage();
    $terms = $page->findField('field_taxonomy_terms[' . $vocab_autocomplete . '][1][target_id]');
    $terms->setValue($autocomplete_term_2->label());
    $result = $web_assert->waitForElementVisible('css', '.ui-autocomplete li');
    $this->assertNotNull($result);
    // Click the autocomplete option.
    $result->click();

    // Check select widget modify values.
    $chosen_wrapper = $page->findById('edit_field_taxonomy_terms_' . strtolower($vocab_select) . '_chosen');
    $input = $chosen_wrapper->find('css', '.chosen-search-input');
    $input->click();
    // Select $select_term_2 in popup.
    $page->find('css', '.active-result.highlighted')->click();
    // Remove $select_term_1 from field.
    $page->find('css', '.search-choice-close')->click();

    // Check options widget modifies.
    $page->findField('field_taxonomy_terms[' . $vocab_options . '][' . $options_term_1->id() . ']')->check();
    $page->findField('field_taxonomy_terms[' . $vocab_options . '][' . $options_term_2->id() . ']')->uncheck();

    // Check tree widget modifies.
    $page->findField('field_taxonomy_terms[' . $vocab_tree . '][0][' . $tree_term_1->id() . '][' . $tree_term_1->id() . ']')->check();
    $page->findField('field_taxonomy_terms[' . $vocab_tree . '][0][' . $tree_term_2->id() . '][' . $tree_term_2->id() . ']')->uncheck();

    // Save node with modified values.
    $page->findButton('URL alias')->press();
    $page->fillField('path[0][alias]', '/' . $this->randomMachineName());
    $page->pressButton('Save');
    $nodes = $this->entityTypeManager->getStorage('node')
      ->loadByProperties(['title' => $node->getTitle()]);
    $this->assertNotEmpty($nodes, 'Test node is not saved.');
    $saved_tids = [];
    $node = array_shift($nodes);
    /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $terms_values */
    $terms_values = $node->get('field_taxonomy_terms');
    $this->assertCount(5, $terms_values);
    $entities = $terms_values->referencedEntities();
    foreach ($entities as $saved_term) {
      $saved_tids[] = $saved_term->id();
    }
    $this->assertContains($autocomplete_term_1->id(), $saved_tids);
    $this->assertContains($autocomplete_term_2->id(), $saved_tids);
    $this->assertContains($select_term_2->id(), $saved_tids);
    $this->assertContains($options_term_1->id(), $saved_tids);
    $this->assertContains($tree_term_1->id(), $saved_tids);
    $this->assertNotContains($select_term_1->id(), $saved_tids);
    $this->assertNotContains($options_term_2->id(), $saved_tids);
    $this->assertNotContains($tree_term_2->id(), $saved_tids);
  }

  /**
   * Assert function to check field markup.
   *
   * @param string $assert_markup
   *   Expected html markup for field.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  protected function assertTaxonomyTermsFieldVisible(string $assert_markup): void {
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
   *
   * @param \Behat\Mink\Element\DocumentElement $page
   *   Current document page element.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function saveNodeAndAssertTerms(DocumentElement $page): void {
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

  /**
   * Set test vocabulary widget settings.
   *
   * @param string $widget_type
   *   Widget type.
   */
  protected function setTestVocabularyWidget(string $widget_type): void {
    $config_vocab = $this->configFactory->getEditable('taxonomy.vocabulary.' . $this->testVid);
    $config_vocab
      ->set('widget_type', $widget_type)
      ->save(TRUE);
  }

}
