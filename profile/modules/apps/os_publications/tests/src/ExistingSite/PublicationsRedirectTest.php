<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class PublicationsRedirectTest.
 *
 * @group vsite
 * @group functional
 */
class PublicationsRedirectTest extends TestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->adminUser = $this->createUser([], NULL, TRUE);
  }

  /**
   * Tests publication redirect.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testRedirect() {
    $this->drupalLogin($this->adminUser);

    $this->drupalPostForm('/cp/settings/publications', [
      'os_publications_preferred_bibliographic_format' => 'harvard_chicago_author_date',
      'biblio_sort' => 'year',
      'biblio_order' => 'DESC',
      'os_publications_export_format[bibtex]' => 'bibtex',
      'os_publications_export_format[endnote8]' => 'endnote8',
      'os_publications_export_format[endnote7]' => 'endnote7',
      'os_publications_export_format[tagged]' => 'tagged',
      'os_publications_export_format[marc]' => 'marc',
      'os_publications_export_format[ris]' => 'ris',
    ], 'Save configuration');

    $this->drupalLogout();

    $this->visit('/publications');

    $web_assert = $this->assertSession();

    $web_assert->pageTextContains('Publications by Year');
  }

}
