<?php

namespace Drupal\Tests\cp_taxonomy\ExistingSite;

use Drupal\Core\Entity\EntityInterface;
use Drupal\cp_taxonomy\CpTaxonomyHelper;
use Drupal\node\Entity\Node;

/**
 * Class TaxonomyTermsWidgetHelperTest.
 *
 * @group cp
 * @group kernel
 *
 * @package Drupal\Tests\cp_taxonomy\ExistingSite
 */
class TaxonomyTermsWidgetHelperTest extends TestBase {

  protected $unSavedNode;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->unSavedNode = Node::create([
      'type' => 'class',
    ]);
  }

  /**
   * Test widget autocomplete on node form.
   */
  public function testTaxonomyTermWidgetAutocompleteForm() {
    $vid = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vid, ['node:class'], CpTaxonomyHelper::WIDGET_TYPE_AUTOCOMPLETE);

    $form = $this->buildNodeForm($this->unSavedNode);
    $this->assertNotEmpty($form['field_taxonomy_terms']['widget'][$vid], 'Autocomplete widget is not exists.');
    $widget_element = $form['field_taxonomy_terms']['widget'][$vid];
    $this->assertNotEmpty($widget_element[0], 'Elements are not exist.');
    $this->assertNotEmpty($widget_element['add_more'], 'Add more button is not exists.');
    $this->assertEquals('field_taxonomy_terms_add_more_vid_' . $vid, $widget_element['add_more']['#name']);
    $this->assertEquals('node:class|' . $vid, $widget_element[0]['target_id']['#selection_settings']['view']['arguments'][0]);
  }

  /**
   * Test widget select on node form.
   */
  public function testTaxonomyTermWidgetSelectForm() {
    $vid = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vid, ['node:class'], CpTaxonomyHelper::WIDGET_TYPE_OPTIONS_SELECT);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Select term 1']);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Select term 2']);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Select term 3']);

    $form = $this->buildNodeForm($this->unSavedNode);
    $this->assertNotEmpty($form['field_taxonomy_terms']['widget'][$vid], 'Select widget is not exists.');
    $widget_element = $form['field_taxonomy_terms']['widget'][$vid];
    $this->assertNotEmpty($widget_element['#options'], 'Elements are not exist.');
    $this->assertCount(3, $widget_element['#options']);
    $this->assertEquals(1, $widget_element['#chosen']);
  }

  /**
   * Test widget checkboxes on node form.
   */
  public function testTaxonomyTermWidgetCheckboxesForm() {
    $vid = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vid, ['node:class'], CpTaxonomyHelper::WIDGET_TYPE_OPTIONS_BUTTONS);
    $term1 = $this->createGroupTerm($this->group, $vid, ['name' => 'Buttons term 1']);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Buttons term 2']);
    $subterm = $this->createGroupTerm($this->group, $vid, ['name' => 'Buttons term 1-2', 'parent' => $term1->id()]);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Buttons term 3']);

    $form = $this->buildNodeForm($this->unSavedNode);
    $this->assertNotEmpty($form['field_taxonomy_terms']['widget'][$vid], 'Select widget is not exists.');
    $widget_element = $form['field_taxonomy_terms']['widget'][$vid];
    $this->assertNotEmpty($widget_element['#options'], 'Elements are not exist.');
    $this->assertCount(4, $widget_element['#options']);
    $this->assertEquals('-Buttons term 1-2', $widget_element['#options'][$subterm->id()]->__toString());
    $this->assertEquals('checkboxes', $widget_element['#type']);
  }

  /**
   * Test widget tree term on node form.
   */
  public function testTaxonomyTermWidgetTreeForm() {
    $vid = $this->randomMachineName();
    $this->createGroupVocabulary($this->group, $vid, ['node:class'], CpTaxonomyHelper::WIDGET_TYPE_TREE);
    $term1 = $this->createGroupTerm($this->group, $vid, ['name' => 'Tree term 1']);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Tree term 2']);
    $subterm = $this->createGroupTerm($this->group, $vid, ['name' => 'Tree term 1-2', 'parent' => $term1->id()]);
    $this->createGroupTerm($this->group, $vid, ['name' => 'Tree term 3']);

    $form = $this->buildNodeForm($this->unSavedNode);
    $this->assertNotEmpty($form['field_taxonomy_terms']['widget'][$vid], 'Tree widget is not exists.');
    $widget_element = $form['field_taxonomy_terms']['widget'][$vid];
    $this->assertNotEmpty($widget_element[0], 'Elements are not exist.');
    $this->assertNotEmpty($widget_element['#options'], 'Elements are not exist.');
    $this->assertNotEmpty($widget_element['#vocabularies'][$vid], 'Vocabularies are not exist.');
    $this->assertCount(4, $widget_element['#options']);
    $this->assertNotEmpty($widget_element['#options_tree'][0]->children);
    $this->assertEquals('checkbox_tree', $widget_element['#theme']);
    $this->assertEquals($subterm->id(), $widget_element['#options_tree'][0]->children[0]->tid);
  }

  /**
   * Helper function to render node form.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   Current node.
   *
   * @return array
   *   Built form array.
   */
  protected function buildNodeForm(EntityInterface $node) {
    $form = $this->container->get('entity_type.manager')
      ->getFormObject('node', 'default')
      ->setEntity($node);
    return $this->container->get('form_builder')->getForm($form);
  }

}
