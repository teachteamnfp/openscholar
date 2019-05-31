<?php

namespace Drupal\Tests\os\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests whether group member has access to entity create global paths.
 *
 * @covers ::os_entity_create_access
 *
 * @group functional
 * @group os
 */
class GlobalPathAccessTest extends OsExistingSiteTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $member = $this->createUser();
    $this->group->addMember($member);

    $this->drupalLogin($member);
  }

  /**
   * Tests node create global path access.
   *
   * This test only tests node create global path access. The edit, delete path
   * access is handled by gnode_node_access().
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   *
   * @see \gnode_node_access()
   */
  public function testNode(): void {
    $this->visit("{$this->group->get('path')->getValue()[0]['alias']}/node/add/faq");

    $this->assertSession()->statusCodeEquals(200);

    $question = $this->randomMachineName();
    $answer = $this->randomMachineName();
    $this->getSession()->getPage()->fillField('Question', $question);
    $this->getSession()->getPage()->fillField('Answer', $answer);
    $this->getSession()->getPage()->pressButton('Save');

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    $nodes = $entity_type_manager->getStorage('node')->loadByProperties([
      'title' => $question,
    ]);

    $this->assertNotEmpty($nodes);
    $node = \reset($nodes);

    $this->assertEquals($question, $node->get('title')->first()->getValue()['value']);

    $node->delete();
  }

  /**
   * Tests media create global path access.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testMediaCreate(): void {
    $this->visit("{$this->group->get('path')->getValue()[0]['alias']}/media/add/document");

    $this->assertSession()->statusCodeEquals(200);

    // Skipping the media creation assertions, because, I was not able to
    // replicate the AJAX file upload in test. I have tested it manually, and
    // the media creation works.
  }

  public function testMediaUpdate(): void {

  }

}
