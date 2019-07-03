<?php

namespace Drupal\os_redirect_to_source\ExistingSite;

use Drupal\Core\Language\Language;
use Drupal\redirect\Entity\Redirect;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class EntityRedirectsTest.
 *
 * @group kernel
 * @group redirect
 *
 * @package Drupal\os_redirect_to_source\ExistingSite
 */
class EntityRedirectsTest extends OsExistingSiteTestBase {
  /**
   * Group administrator.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $groupAdmin;

  /**
   * Node.
   *
   * @var \Drupal\node\Entity\Node
   */
  protected $node;

  /**
   * Publication.
   *
   * @var \Drupal\bibcite_entity\Entity\Reference
   */
  protected $publication;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() :void {
    parent::setUp();

    $group = $this->createGroup([
      'type' => 'personal',
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $group);

    $this->node = $this->createNode([
      'type' => 'news',
      'field_date' => '20/06/2019',
      'field_redirect_to_source' => 'https://www.example.com',
    ]);

    $this->publication = $this->createReference([
      'type' => 'artwork',
      'field_redirect_to_source' => 'https://www.example.com',
    ]);

    $this->markEntityForCleanup($this->node);
    $this->entityTypeManager = $this->container->get('entity_type.manager');
  }

  /**
   * Test creation of redirects on entity creation.
   */
  public function testRedirectCreation() :void {
    // Test a redirect is created on node insertion.
    $path = 'node/' . $this->node->id();
    $redirect = $this->checkRedirect($path);
    $this->assertNotEmpty($redirect);

    // Test a redirect is created on publication insertion.
    $path = 'bibcite/reference/' . $this->publication->id();
    $redirect = $this->checkRedirect($path);
    $this->assertNotEmpty($redirect);
  }

  /**
   * Test deletion of redirects.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testRedirectDeletion() :void {
    // Test a redirect is created on node deletion.
    $path = 'node/' . $this->node->id();
    $this->entityTypeManager->getStorage('node')->load($this->node->id())->delete();
    $this->assertEmpty($this->checkRedirect($path));

    // Test a redirect is created on publication deletion.
    $path = 'bibcite/reference/' . $this->publication->id();
    $this->entityTypeManager->getStorage('bibcite_reference')->load($this->publication->id())->delete();
    $this->assertEmpty($this->checkRedirect($path));
  }

  /**
   * Checks if redirect exists.
   *
   * @param string $path
   *   Path to check redirect for.
   *
   * @return mixed
   *   Array or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function checkRedirect($path) : array {
    $language = Language::LANGCODE_NOT_SPECIFIED;
    $hash = Redirect::generateHash($path, [], $language);
    return $this->entityTypeManager->getStorage('redirect')->loadByProperties(['hash' => $hash]);
  }

}
