<?php

namespace Drupal\Tests\os_publications\ExistingSite;

/**
 * Class PublicationsRedirectTest.
 *
 * @group functional
 * @group publications
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

    $this->adminUser = $this->createUser([
      'access control panel',
    ]);
  }

  /**
   * Tests publication redirect.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testRedirect() {
    $this->drupalLogin($this->adminUser);

    $this->visit('/cp/settings/publications');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalPostForm(NULL, [
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
