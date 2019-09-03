<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Drupal\views\Views;

/**
 * Class ReferenceViewTest.
 *
 * @group cp
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class ReferenceViewTest extends TestBase {

  protected $group1;
  protected $group2;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->group1 = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/group1',
      ],
    ]);
    $this->group2 = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/group2',
      ],
    ]);
  }

  /**
   * Test entity reference view.
   */
  public function testReferenceTaxonomyTermViewResult() {
    $vid = 'reference_test';
    $this->createGroupVocabulary($this->group1, $vid, ['node:taxonomy_test_1']);
    $this->createGroupVocabulary($this->group2, 'other_vocab', ['node:taxonomy_test_1']);
    $this->createGroupTerm($this->group1, $vid, ['name' => 'Test term 1']);
    $this->createGroupTerm($this->group2, $vid, ['name' => 'Test term 2']);
    $this->vsiteContextManager->activateVsite($this->group1);
    $view = Views::getView('reference_taxonomy_term');
    $view->setDisplay('entity_reference_1');
    $view->setArguments(['node:taxonomy_test_1']);
    $view->preExecute();
    $view->execute();
    $view->preview();
    $result = $view->result;
    $this->assertEquals(1, count($result));
    $this->assertEquals('Test term 1', $result[0]->_entity->label());
  }

}
