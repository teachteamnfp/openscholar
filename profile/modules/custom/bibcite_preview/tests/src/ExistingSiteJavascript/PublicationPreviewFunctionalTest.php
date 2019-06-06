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
   * Test publication edit and press preview, change view mode and get back.
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

    // Check preview page, contains modified title.
    $web_assert->pageTextContains($modified_title);
    $web_assert->elementNotExists('css', '.bibcite-citation');

    // Check to modify to citation view mode.
    $page = $this->getCurrentPage();
    $page->fillField('view_mode', 'citation');
    $web_assert->waitForElement('css', '.bibcite-citation');
    $web_assert->elementExists('css', '.bibcite-citation');
    $back_link = $page->findById('edit-backlink');
    $back_link->press();
    $web_assert->statusCodeEquals(200);

    // Check going back to edit page and see modified title.
    $page = $this->getCurrentPage();
    $this->assertContains($modified_title, $page->getHtml());
  }

}
