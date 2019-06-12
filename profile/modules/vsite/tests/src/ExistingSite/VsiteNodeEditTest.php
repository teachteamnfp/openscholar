<?php

namespace Drupal\Tests\vsite\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Tests node edit via vsite.
 *
 * @group functional
 * @group vsite
 */
class VsiteNodeEditTest extends OsExistingSiteTestBase {

  /**
   * Test.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function test(): void {
    // Setup.
    $test_news = $this->createNode([
      'type' => 'news',
      'title' => 'Test News',
      'field_date' => [
        'value' => '2019-01-01',
      ],
    ]);
    $this->group->addContent($test_news, 'group_node:news');
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);

    // Tests.
    $this->drupalLogin($group_admin);

    $this->visitViaVsite("node/{$test_news->id()}/edit", $this->group);
    $this->getSession()->getPage()->fillField('title[0][value]', 'Test News Edited');
    $this->getSession()->getPage()->pressButton('Save');

    $this->assertSession()->statusCodeEquals(200);

    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = $this->container->get('entity_type.manager');

    $nodes = $entity_type_manager->getStorage('node')->loadByProperties([
      'type' => 'news',
      'title' => 'Test News Edited',
    ]);

    $this->assertNotEmpty($nodes);
    $node = \reset($nodes);

    $this->assertEquals('Test News Edited', $node->get('title')->first()->getValue()['value']);

    $node->delete();
  }

}
