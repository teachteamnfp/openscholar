<?php

namespace Drupal\Tests\bibcite_preview\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Class PublicationPreviewFunctionalTest.
 *
 * @group functional-javascript
 * @group publications
 *
 * @package Drupal\Tests\bibcite_preview\ExistingSiteJavascript
 */
class PublicationPreviewFunctionalTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Reference Entity.
   *
   * @var \Drupal\bibcite_entity\Entity\ReferenceInterface
   */
  protected $reference;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->drupalLogin($this->createAdminUser());
    $this->reference = $this->createReference();
  }

  /**
   * Test publication edit and press preview and get back.
   */
  public function testPublicationEditPreviewAndBack() {
    $modified_title = $this->randomMachineName();
    // Visit edit page.
    $this->visit('/bibcite/reference/' . $this->reference->id() . '/edit');
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $web_assert->pageTextContains($this->reference->label());
    $page = $this->getCurrentPage();
    $page->fillField('title[0][value]', $modified_title);

    // Find and press Preview button.
    $preview_button = $page->findButton('Preview');
    $preview_button->press();
    $web_assert->statusCodeEquals(200);

    $back_link = $page->findById('edit-backlink');
    $back_link->press();
    $web_assert->statusCodeEquals(200);

    // Check going back to edit page and see modified title.
    $page = $this->getCurrentPage();
    $this->assertContains($modified_title, $page->getHtml());
  }

  /**
   * Test publication create and press preview get back.
   */
  public function testPublicationCreatePreviewAndBack() {
    $title = $this->randomMachineName();
    // Visit edit page.
    $this->visit('/bibcite/reference/add/artwork');
    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $page->fillField('title[0][value]', $title);
    $page->fillField('bibcite_year[0][value]', '1990');

    // Find and press Preview button.
    $preview_button = $page->findButton('Preview');
    $preview_button->press();
    $web_assert->statusCodeEquals(200);

    $back_link = $page->findById('edit-backlink');
    $back_link->press();
    $web_assert->statusCodeEquals(200);

    // Check going back to edit page and see modified title.
    $page = $this->getCurrentPage();
    $this->assertContains($title, $page->getHtml());
  }

}
