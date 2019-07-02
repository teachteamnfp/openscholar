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
  }

  /**
   * Test citation short url.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPublicationShortUrl(): void {

    // Test citations do not occur by default.
    $web_assert = $this->assertSession();
    $ref_url = $this->reference->toUrl()->toString();
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/$ref_url");
    $web_assert->elementNotExists('css', '.short-link');

    // Test citations occur when short url setting is on.
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/publications");
    $edit = [
      'os_publications_shorten_citations' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->drupalGet("{$this->group->get('path')->first()->getValue()['alias']}/$ref_url");
    $web_assert->elementExists('css', '.short-link');
  }

}
