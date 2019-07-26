<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * PublicationsCreateTest.
 *
 * @group kernel
 * @group publications
 */
class PublicationsCreateTest extends TestBase {

  /**
   * Alias Manager service.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Tests whether custom title is automatically set.
   *
   * @covers ::os_publications_bibcite_reference_presave
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testSetTitleFirstLetterExclPrep() {
    $reference = $this->createReference([
      'html_title' => 'The Velvet Underground',
    ]);

    $this->assertEquals('V', $reference->get('title_first_char_excl_prep')->getValue()[0]['value']);
  }

  /**
   * Test automatic path generation.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testPathAliasGeneration() : void {

    $reference = $this->createReference([
      'html_title' => 'The Velvet Underground',
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->aliasManager = $this->container->get('path.alias_manager');

    // Test path alias is generated.
    $path = '/bibcite/reference/' . $reference->id();
    $alias = $this->aliasManager->getAliasByPath($path);
    $this->assertNotSame($alias, $path);
    $this->assertSame($alias, '/publications/velvet-underground');
  }

}
