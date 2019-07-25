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
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
  }

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

  /**
   * Tests link creation from publication edit page.
   */
  public function testPublicationMenuLinkAdd(): void {

    $this->visitViaVsite('bibcite/reference/add/journal_article', $this->group);
    $edit = [
      'bibcite_year[0][value]' => '2019',
      'bibcite_secondary_title[0][value]' => 'Journal Link',
      'menu[enabled]' => TRUE,
      'menu[title]' => 'Menu Link title',
    ];
    $this->submitForm($edit, 'Save');

    $this->assertSession()->linkExists('Menu Link title');
  }

}
