<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSiteJavascript;

use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Tests taxonomy_terms fields functionality.
 *
 * @group other
 * @group functional-javascript
 */
class TaxonomyTermsFieldTest extends CpTaxonomyExistingSiteJavascriptTestBase {

  protected $groupUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->groupUser = $this->createUser([], NULL, TRUE);
    $this->group1->addMember($this->groupUser);
    $this->group2->addMember($this->groupUser);
    $this->drupalLogin($this->groupUser);
    $this->createGroupVocabulary($this->group1, 'vocab_group_1', ['node:taxonomy_test_1']);
    $this->createGroupVocabulary($this->group2, 'vocab_group_2', ['node:taxonomy_test_1']);
  }

  /**
   * Test node taxonomy terms field autocomplete.
   */
  public function testNodeTaxonomyTermsFieldAutocompleteSuccess() {
    $this->createGroupTerm($this->group1, 'vocab_group_1', 'Term 1 group 1 vid1');
    $this->createGroupTerm($this->group1, 'vocab_group_1', 'Term 2 group 1 vid1');
    $this->createGroupTerm($this->group2, 'vocab_group_2', 'Term 1 group 2 vid2');
    $this->vsiteContextManager->activateVsite($this->group1);

    $this->visit($this->group1->get('path')->getValue()[0]['alias'] . "/node/add/taxonomy_test_1");
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $is_exists = $page->hasContent('Tag with Terms');
    $this->assertTrue($is_exists, 'Tag with Terms field is not visible.');
    $tags = $page->findField('field_taxonomy_terms[0][target_id]');
    $tags->setValue('Ter');
    $tags->keyDown('m');
    /** @var \Behat\Mink\Element\NodeElement $result */
    $result = $web_assert->waitForElementVisible('css', 'ul.ui-autocomplete');
    $this->assertNotNull($result, 'Autocomplete is not came up.');
    $list_markup = $result->getHtml();
    $this->assertContains('Term 1 group 1 vid1', $list_markup);
    $this->assertContains('Term 2 group 1 vid1', $list_markup);
    $this->assertNotContains('Term 1 group 2 vid2', $list_markup);
  }

  /**
   * Test media taxonomy terms field autocomplete.
   */
  public function testMediaTaxonomyTermsFieldAutocompleteSuccess() {
    $this->createGroupVocabulary($this->group1, 'vocab_media_group_1', ['media:taxonomy_test_file']);
    $this->createGroupTerm($this->group1, 'vocab_media_group_1', 'Term 1 group 1 vid1');
    $this->createGroupTerm($this->group1, 'vocab_media_group_1', 'Term 2 group 1 vid1');
    $this->vsiteContextManager->activateVsite($this->group1);

    $this->visit($this->group1->get('path')->getValue()[0]['alias'] . "/media/add/taxonomy_test_file");
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $is_exists = $page->hasContent('Tag with Terms');
    $this->assertTrue($is_exists, 'Tag with Terms field is not visible.');
    $tags = $page->findField('field_taxonomy_terms[0][target_id]');
    $tags->setValue('Ter');
    $tags->keyDown('m');
    /** @var \Behat\Mink\Element\NodeElement $result */
    $result = $web_assert->waitForElementVisible('css', 'ul.ui-autocomplete');
    file_put_contents('public://testMediaTaxonomyTermsFieldAutocompleteSuccess-autocomplete.png', $this->getSession()->getScreenshot());
    $this->assertNotNull($result, 'Autocomplete is not came up.');
    $list_markup = $result->getHtml();
    $this->assertContains('Term 1 group 1 vid1', $list_markup);
    $this->assertContains('Term 2 group 1 vid1', $list_markup);
  }

  /**
   * Test node taxonomy hidden field on node add page.
   */
  public function testNodeTaxonomyHiddenField() {
    $this->visit($this->group1->get('path')->getValue()[0]['alias'] . "/node/add/taxonomy_test_2");
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $is_exists = $page->hasContent('Tag with Terms');
    $this->assertFalse($is_exists, 'Tag with Terms field is visible.');
  }

  /**
   * {@inheritdoc}
   */
  public function tearDown() {
    $this->vsiteContextManager->activateVsite($this->group1);
    $vocabulary_1 = Vocabulary::load('vocab_group_1');
    $vocabulary_1->delete();
    $this->vsiteContextManager->activateVsite($this->group2);
    $vocabulary_2 = Vocabulary::load('vocab_group_2');
    $vocabulary_2->delete();
    parent::tearDown();
  }

}
