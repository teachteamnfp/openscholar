<?php

namespace Drupal\Tests\os_publications\ExistingSite;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Class PublicationShortUrlTest.
 *
 * @group functional
 * @group publications
 *
 * @package Drupal\Tests\os_publications\ExistingSite
 * @covers \os_publications_preprocess_bibcite_citation
 */
class PublicationShortUrlTest extends OsExistingSiteJavascriptTestBase {
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

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-menu',
      ],
    ]);
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

    // Test citations do not occur if short citations is off.
    $this->visit("/test-menu/cp/settings/publications");
    $edit = [
      'os_publications_shorten_citations' => FALSE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $ref_url = $this->reference->toUrl()->toString();
    $this->visit("/test-menu/$ref_url");
    $web_assert = $this->assertSession();
    $web_assert->elementNotExists('css', '.short-link');

    // Test citations occur when short url setting is on.
    $this->drupalGet("/test-menu/cp/settings/publications");
    $edit = [
      'os_publications_shorten_citations' => TRUE,
    ];
    $this->submitForm($edit, 'edit-submit');
    $this->visit("/test-menu/$ref_url");
    $web_assert->elementExists('css', '.short-link');
  }

}
