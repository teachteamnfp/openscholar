<?php

namespace Drupal\Tests\os\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Checks whether vsite roles has access to control node metadata.
 *
 * @group functional-javascript
 * @group os
 */
class NodeMetadataAccessTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Checks whether vsite admin has access to control node metadata.
   *
   * @covers ::os_form_node_form_alter
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testGroupAdmin(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $group_member = $this->createUser();
    $this->group->addMember($group_member);
    $news_title = $this->randomMachineName();
    $news_alias = $this->randomMachineName();

    $this->drupalLogin($group_admin);

    $this->visitViaVsite('node/add/news', $this->group);

    // Tests.
    $url_alias_edit_option = $this->getSession()->getPage()->find('css', '#edit-path-0 summary');
    $this->assertNotNull($url_alias_edit_option);
    $url_alias_edit_option->click();

    $author_edit_option = $this->getSession()->getPage()->find('css', '#edit-author summary');
    $this->assertNotNull($author_edit_option);
    $author_edit_option->click();

    $sticky_edit_option = $this->getSession()->getPage()->find('css', '#edit-options summary');
    $this->assertNotNull($sticky_edit_option);
    $sticky_edit_option->click();

    $this->submitForm([
      'title[0][value]' => $news_title,
      'field_date[0][value][date]' => '2019-08-15',
      'path[0][pathauto]' => 0,
      'path[0][alias]' => "/$news_alias",
      'uid[0][target_id]' => "{$group_member->getAccountName()} ({$group_member->id()})",
      'created[0][value][date]' => '2019-08-15',
      'created[0][value][time]' => '00:00:00',
      'sticky[value]' => 1,
    ], 'Save');

    // Assert alias.
    $this->visitViaVsite($news_alias, $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($news_title);

    // Assert node metadata.
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $nodes = $entity_type_manager
      ->getStorage('node')
      ->loadByProperties(['title' => $news_title]);
    $node = reset($nodes);

    $this->assertEquals($group_member->id(), $node->get('uid')->first()->getValue()['target_id']);
    $this->assertEquals('15/08/2019', date('d/m/Y', $node->get('created')->first()->getValue()['value']));
    $this->assertEquals(1, $node->get('status')->first()->getValue()['value']);

    // Cleanup.
    $node->delete();
  }

  /**
   * Checks whether vsite member has access to control node metadata.
   *
   * @covers ::os_form_node_form_alter
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testGroupMember(): void {
    // Setup.
    $group_member = $this->createUser();
    $this->group->addMember($group_member);
    $news_title = $this->randomMachineName();
    $news_alias = $this->randomMachineName();

    $this->drupalLogin($group_member);

    $this->visitViaVsite('node/add/news', $this->group);

    // Tests.
    $url_alias_edit_option = $this->getSession()->getPage()->find('css', '#edit-path-0 summary');
    $this->assertNotNull($url_alias_edit_option);
    $url_alias_edit_option->click();

    $sticky_edit_option = $this->getSession()->getPage()->find('css', '#edit-options summary');
    $this->assertNotNull($sticky_edit_option);
    $sticky_edit_option->click();

    $this->submitForm([
      'title[0][value]' => $news_title,
      'field_date[0][value][date]' => '2019-08-15',
      'path[0][pathauto]' => 0,
      'path[0][alias]' => "/$news_alias",
      'sticky[value]' => 1,
    ], 'Save');

    // Assert alias.
    $this->visitViaVsite($news_alias, $this->group);
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains($news_title);

    // Assert node metadata.
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');
    $nodes = $entity_type_manager
      ->getStorage('node')
      ->loadByProperties(['title' => $news_title]);
    $node = reset($nodes);

    $this->assertEquals($group_member->id(), $node->get('uid')->first()->getValue()['target_id']);
    $this->assertEquals(1, $node->get('sticky')->first()->getValue()['value']);

    // Cleanup.
    $node->delete();
  }

}
