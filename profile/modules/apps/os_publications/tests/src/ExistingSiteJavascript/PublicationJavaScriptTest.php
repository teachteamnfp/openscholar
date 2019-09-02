<?php

namespace Drupal\Tests\os_publications\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * A WebDriver test suitable for testing Ajax and client-side interactions.
 *
 * @group functional-javascript
 * @group publications
 */
class PublicationJavaScriptTest extends OsExistingSiteJavascriptTestBase {

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
  }

  /**
   * Test show/hide of citation examples.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testPreviewToggle(): void {
    $this->drupalLogin($this->groupAdmin);
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/apps-settings/publications");

    $web_assert = $this->assertSession();
    $web_assert->statusCodeEquals(200);

    $page = $this->getCurrentPage();

    // Test Modern Language hover.
    $format = $page->findField('edit-os-publications-preferred-bibliographic-format-modern-language-association');
    $format->mouseOver();
    $result = $web_assert->waitForElementVisible('css', '#modern_language_association');
    $this->assertNotNull($result);
    $value = ucwords(str_replace('_', ' ', $result->getValue()));
    // Verify the text on the page.
    $web_assert->pageTextContains($value);

    // Test APA hover.
    $format = $page->findField('edit-os-publications-preferred-bibliographic-format-ieee');
    $format->mouseOver();
    $result = $web_assert->waitForElementVisible('css', '#ieee');
    $this->assertNotNull($result);
    $value = ucwords(str_replace('_', ' ', $result->getValue()));
    // Verify the text on the page.
    $web_assert->pageTextContains($value);
  }

  /**
   * Test various revision information changes/alteration.
   */
  public function testRevisionsOnEditForm(): void {
    // Test tab does not show up if new entity,.
    $this->drupalLogin($this->groupAdmin);
    $this->visitViaVsite('bibcite/reference/add/artwork', $this->group);
    $this->assertSession()->pageTextNotContains('Revision Information');

    // Test tab shows up when existing entity.
    $reference = $this->createReference([
      'html_title' => 'Mona Lisa',
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->visitViaVsite('bibcite/reference/' . $reference->id() . '/edit', $this->group);
    $this->assertSession()->pageTextContains('Revision Information');

    // Test Revision text/link does not appear when no revisions done as yet.
    $this->assertSession()->elementNotExists('css', '#revisons-links');

    // Make changes to create a new revison and test revision link appears.
    $this->submitForm([], 'edit-submit');
    $this->visitViaVsite('bibcite/reference/' . $reference->id() . '/edit', $this->group);
    $this->assertSession()->elementExists('css', '#revisons-links');

    // Test help link appears.
    $this->assertSession()->elementExists('css', '#edit-help');

    // Test count is correct.
    $expected = '1 revisions';
    $this->assertContains($expected, $this->getCurrentPage()->getHtml());

    // Test links works fine and redirects to correct revisions page.
    $this->getCurrentPage()->clickLink('Revision Information');
    $this->getCurrentPage()->find('css', '#revisons-links')->click();
    $this->assertContains($reference->id() . '/revisions', $this->getSession()->getCurrentUrl());

  }

  /**
   * Test various revision information changes/alteration.
   */
  public function testAbstractToggle(): void {
    $this->drupalLogin($this->groupAdmin);

    // Test link exists when abstract is entered entity.
    $reference = $this->createReference([
      'html_title' => 'Mona Lisa',
      'bibcite_abst_e' => 'This is a test for abstract field.',
    ]);
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->linkExists('Abstract');

    // Test links works fine and toggles data for the field.
    $abst_field = $this->getCurrentPage()->find('css', '.field--abstract');
    $abst_field->hasClass('visually-hidden');
    $this->getCurrentPage()->clickLink('Abstract');
    $this->assertSession()->pageTextContains('This is a test for abstract field.');

  }

}
