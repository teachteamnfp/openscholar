<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;

/**
 * Class PublicationShortUrlTest.
 *
 * @group functional
 * @group test-test
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
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
    $this->drupalLogin($this->groupAdmin);
    $this->reference = $this->createReference([
      'title' => 'The Velvet Underground',
    ]);
    $this->cache = $this->container->get('cache.render');
  }

  /**
   * Test citation short url.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPublicationShortUrl(): void {

    // Test citations do not occur if citations is set to half.
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/publications");
    $edit = [
      'os_publications_shorten_citations' => FALSE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $ref_url = $this->reference->toUrl()->toString();
    $this->cache->invalidateAll();
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/$ref_url");
    $web_assert = $this->assertSession();
    $web_assert->elementNotExists('css', '.short-link');

    // Test citations occur when short url setting is on.
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/publications");
    $edit = [
      'os_publications_shorten_citations' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->cache->invalidateAll();
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/$ref_url");
    $web_assert->elementExists('css', '.short-link');
  }

}
