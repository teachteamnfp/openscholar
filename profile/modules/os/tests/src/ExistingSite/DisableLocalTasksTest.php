<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Core\Url;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests whether menu local tasks for certain routes are disabled.
 *
 * @group functional
 * @group os
 */
class DisableLocalTasksTest extends OsExistingSiteTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->drupalLogin($group_admin);
  }

  /**
   * Tests whether local tasks are disabled for nodes.
   *
   * @covers ::os_menu_local_tasks_alter
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testNode(): void {
    $node = $this->createNode();
    $this->visit("/node/{$node->id()}");

    $edit_url = Url::fromRoute('entity.node.edit_form', [
      'node' => $node->id(),
    ]);
    $delete_url = Url::fromRoute('entity.node.delete_form', [
      'node' => $node->id(),
    ]);

    $this->assertSession()->linkByHrefNotExists($edit_url->toString());
    $this->assertSession()->linkByHrefNotExists($delete_url->toString());
  }

  /**
   * Tests whether local tasks are disabled for publications.
   *
   * @covers ::os_menu_local_tasks_alter
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBibciteReference(): void {
    $reference = $this->createReference();
    $this->visit(Url::fromRoute('entity.bibcite_reference.canonical', [
      'bibcite_reference' => $reference->id(),
    ])->toString());

    $edit_url = Url::fromRoute('entity.bibcite_reference.edit_form', [
      'bibcite_reference' => $reference->id(),
    ]);
    $delete_url = Url::fromRoute('entity.bibcite_reference.delete_form', [
      'bibcite_reference' => $reference->id(),
    ]);

    $this->assertSession()->linkByHrefNotExists($edit_url->toString());
    $this->assertSession()->linkByHrefNotExists($delete_url->toString());
  }

}
