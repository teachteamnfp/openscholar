<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class PublicationShortUrlTest.
 *
 * @group functional
 * @group publications
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 * @covers \os_publications_preprocess_bibcite_citation
 */
class PublicationShortUrlTest extends OsExistingSiteTestBase {
  /**
   * Group administrator.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $groupAdmin;

  /**
   * Alias Manager service.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * Reference content.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-shorturl',
      ],
    ]);
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
    $this->reference = $this->createReference([
      'html_title' => 'The Velvet Underground',
    ]);
    $this->group->addContent($this->reference, 'group_entity:bibcite_reference');
    $this->cacheRender = $this->container->get('cache.render');
    $this->aliasManager = $this->container->get('path.alias_manager');
  }

  /**
   * Test citation short url.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPublicationShortUrl(): void {

    // Test citations do not occur if short citations is off.
    $this->visit("/test-shorturl/cp/settings/publications");
    $edit = [
      'os_publications_shorten_citations' => FALSE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->visit("/test-shorturl/bibcite/reference/" . $this->reference->id());
    $web_assert = $this->assertSession();
    $web_assert->elementNotExists('css', '.short-link');

    // Test citations occur when short url setting is on.
    $this->drupalGet("/test-shorturl/cp/settings/publications");
    $edit = [
      'os_publications_shorten_citations' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->cacheRender->invalidateAll();
    $this->visit("/test-shorturl/bibcite/reference/" . $this->reference->id());
    $web_assert->elementExists('css', '.short-link');
  }

}
