<?php

namespace Drupal\Tests\vsite\ExistingSiteJavascript;

use Behat\Mink\Element\NodeElement;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Tests contextual link alterations for vsites.
 *
 * @group functional-javascript
 * @group vsite
 */
class VsiteContextualLinksTest extends OsExistingSiteJavascriptTestBase {

  /**
   * Tests whether the destination parameter is valid in node listings.
   *
   * @covers ::vsite_node_view_alter
   */
  public function testNodeDestinationParameterInListing(): void {
    // Setup.
    $blog = $this->createNode([
      'type' => 'blog',
    ]);
    $this->group->addContent($blog, 'group_node:blog');
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->drupalLogin($group_admin);

    $this->visitViaVsite('blog', $this->group);
    $this->assertSession()->waitForElement('css', '.contextual button');

    // Tests.
    /** @var \Behat\Mink\Element\NodeElement|null $edit_contextual_link */
    $edit_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitynodeedit-form a');
    $this->assertNotNull($edit_contextual_link);
    $this->assertEquals("{$this->groupAlias}/blog", $this->getDestinationParameterValue($edit_contextual_link));

    /** @var \Behat\Mink\Element\NodeElement|null $delete_contextual_link */
    $delete_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitynodedelete-form a');
    $this->assertNotNull($delete_contextual_link);
    $this->assertEquals("{$this->groupAlias}/blog", $this->getDestinationParameterValue($delete_contextual_link));
  }

  /**
   * Tests whether destination parameter is valid in node full view.
   *
   * @covers ::vsite_node_view_alter
   */
  public function testNodeDestinationParameterInFullView(): void {
    // Setup.
    $blog = $this->createNode([
      'type' => 'blog',
    ]);
    $this->group->addContent($blog, 'group_node:blog');
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->drupalLogin($group_admin);

    $this->visitViaVsite("node/{$blog->id()}", $this->group);
    $this->assertSession()->waitForElement('css', '.contextual-links .entitynodeedit-form');

    // Tests.
    /** @var \Behat\Mink\Element\NodeElement|null $edit_contextual_link */
    $edit_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitynodeedit-form a');
    $this->assertNotNull($edit_contextual_link);
    $this->assertEquals("{$this->groupAlias}/node/{$blog->id()}", $this->getDestinationParameterValue($edit_contextual_link));

    /** @var \Behat\Mink\Element\NodeElement|null $delete_contextual_link */
    $delete_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitynodedelete-form a');
    $this->assertNotNull($delete_contextual_link);
    $this->assertEquals("{$this->groupAlias}/blog", $this->getDestinationParameterValue($delete_contextual_link));
  }

  /**
   * Tests whether destination parameter is valid in publication full view.
   *
   * @covers ::vsite_bibcite_reference_view_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBibciteReferenceDestinationParameterInFullView(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $reference = $this->createReference();
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->drupalLogin($group_admin);

    $this->visitViaVsite("bibcite/reference/{$reference->id()}", $this->group);
    $this->assertSession()->waitForElement('css', '.contextual-links .entitybibcite-referenceedit-form');

    // Tests.
    /** @var \Behat\Mink\Element\NodeElement|null $edit_contextual_link */
    $edit_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitybibcite-referenceedit-form a');
    $this->assertNotNull($edit_contextual_link);
    $this->assertEquals("{$this->groupAlias}/bibcite/reference/{$reference->id()}", $this->getDestinationParameterValue($edit_contextual_link));

    /** @var \Behat\Mink\Element\NodeElement|null $delete_contextual_link */
    $delete_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitybibcite-referencedelete-form a');
    $this->assertNotNull($delete_contextual_link);
    $this->assertEquals("{$this->groupAlias}/publications", $this->getDestinationParameterValue($delete_contextual_link));
  }

  /**
   * Tests whether the destination parameter is valid in publications listing.
   *
   * @covers ::vsite_bibcite_reference_view_alter
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testBibciteReferenceDestinationParameterInListing(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $reference = $this->createReference();
    $this->group->addContent($reference, 'group_entity:bibcite_reference');
    $this->drupalLogin($group_admin);

    $this->visitViaVsite('publications', $this->group);
    $this->assertSession()->waitForElement('css', '.contextual button');

    // Tests.
    /** @var \Behat\Mink\Element\NodeElement|null $edit_contextual_link */
    $edit_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitybibcite-referenceedit-form a');
    $this->assertNotNull($edit_contextual_link);
    $this->assertEquals("{$this->groupAlias}/publications", $this->getDestinationParameterValue($edit_contextual_link));

    /** @var \Behat\Mink\Element\NodeElement|null $delete_contextual_link */
    $delete_contextual_link = $this->getSession()->getPage()->find('css', '.contextual-links .entitybibcite-referencedelete-form a');
    $this->assertNotNull($delete_contextual_link);
    $this->assertEquals("{$this->groupAlias}/publications", $this->getDestinationParameterValue($delete_contextual_link));
  }

  /**
   * Tests Preview works with destination param in the url for node.
   *
   * As if coming from contextual link.
   *
   * @covers ::vsite_node_preview_submit
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testNodePreviewWithDestination(): void {

    $blog = $this->createNode([
      'type' => 'blog',
    ]);
    $this->group->addContent($blog, 'group_node:blog');
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $this->drupalLogin($group_admin);

    $destination = '?destination=' . $this->group->get('path')->getValue()[0]['alias'];
    $this->visitViaVsite("node/{$blog->id()}/edit" . $destination, $this->group);
    $this->getSession()->getPage()->pressButton('Preview');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->waitForElementVisible('css', '#edit-backlink');
    $current_url = $this->getSession()->getCurrentUrl();
    $this->assertContains('preview', $current_url);
    $this->assertContains('full', $current_url);
  }

  /**
   * Retrieves the destination parameter value from a link.
   *
   * @param \Behat\Mink\Element\NodeElement $element
   *   The link element.
   *
   * @return string
   *   The destination.
   */
  protected function getDestinationParameterValue(NodeElement $element): string {
    $href = $element->getAttribute('href');
    list(, $query) = explode('?', $href);
    list(, $value) = explode('=', $query);

    return $value;
  }

}
