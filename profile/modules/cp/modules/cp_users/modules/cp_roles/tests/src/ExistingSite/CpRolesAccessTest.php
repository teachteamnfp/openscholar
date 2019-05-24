<?php

namespace Drupal\Tests\cp_roles\ExistingSite;

/**
 * CpRolesAccessTest.
 *
 * @group functional
 * @group cp
 */
class CpRolesAccessTest extends CpRolesExistingSiteTestBase {

  /**
   * Tests whether custom role access is correctly working or not.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    // Setup role.
    $group_role = $this->createRoleForGroup($this->group);
    $group_role->grantPermissions([
      'create group_node:faq entity',
      'create group_node:faq content',
    ])->save();

    // Setup user.
    $member = $this->createUser();
    $this->group->addMember($member, [
      'group_roles' => [
        $group_role->id(),
      ],
    ]);

    // Perform tests.
    $this->drupalLogin($member);

    $this->visit("/{$this->group->get('path')->getValue()[0]['alias']}/node/add/faq");

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

}
