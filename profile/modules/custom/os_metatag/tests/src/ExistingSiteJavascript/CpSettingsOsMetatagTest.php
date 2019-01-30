<?php

namespace Drupal\Tests\os_metatag\ExistingSiteJavascript;

use weitzman\DrupalTestTraits\ExistingSiteWebDriverTestBase;
use Drupal\group\Entity\GroupInterface;
use Drupal\node\NodeInterface;


/**
 * Tests os_metatag module.
 *
 * @group metatag
 * @group functional-javascript
 */
class CpSettingsOsMetatagTest extends ExistingSiteWebDriverTestBase {

  /**
   * Admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->entityTypeManager = $this->container->get('entity_type.manager');

    $this->adminUser = $this->createUser([
      'access administration pages',
      'access control panel',
    ]);

    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-seo-alias',
      ],
    ]);

  }

  /**
   * Tests os_metatag cp settings form behavior.
   */
  public function testCpSettingsFormSave() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->adminUser);

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/seo");
    $this->getSession()->resizeWindow(1440, 900, 'current');
    $this->getSession()->executeScript("window.scrollBy(0,1000)");
    $web_assert->statusCodeEquals(200);
    file_put_contents('public://screenshot-1.jpg', $this->getSession()->getScreenshot());

    $edit = [
      'site_title' => 'Test Site Title',
      'meta_description' => 'LoremIpsumDolor',
      'publisher_url' => 'http://example-publisher.com/',
      'author_url' => 'http://example-author.com/',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $page = $this->getCurrentPage();
    $checkHtmlValue = $page->hasContent('The configuration options have been saved.');
    $this->assertTrue($checkHtmlValue, 'The form did not write the correct message.');

    // Check form elements load default values.
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/seo");
    $web_assert->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $fieldValue = $page->findField('site_title')->getValue();
    $this->assertSame('Test Site Title', $fieldValue, 'Form is not loaded site title value.');
    $fieldValue = $page->findField('meta_description')->getValue();
    $this->assertSame('LoremIpsumDolor', $fieldValue, 'Form is not loaded meta description value.');
    $fieldValue = $page->findField('publisher_url')->getValue();
    $this->assertSame('http://example-publisher.com/', $fieldValue, 'Form is not loaded publisher url value.');
    $fieldValue = $page->findField('author_url')->getValue();
    $this->assertSame('http://example-author.com/', $fieldValue, 'Form is not loaded author url value.');
  }

  /**
   * Tests os_metatag cp settings form behavior.
   */
  public function testHtmlHeadValuesOnFrontPage() {
    $web_assert = $this->assertSession();
    $this->drupalLogin($this->adminUser);
    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/cp/settings/seo");
    $this->getSession()->resizeWindow(1440, 900, 'current');
    $this->getSession()->executeScript("window.scrollBy(0,1000)");
    $web_assert->statusCodeEquals(200);
    file_put_contents('public://screenshot-2.jpg', $this->getSession()->getScreenshot());

    $edit = [
      'site_title' => 'Test Site Title<>',
      'meta_description' => 'LoremIpsumDolor<"">',
      'publisher_url' => 'http://example-publisher.com/"\'quote-test<>',
      'author_url' => 'http://example-author.com/"\'quote-test<>',
    ];
    $this->drupalPostForm(NULL, $edit, 'Save configuration');
    $web_assert->statusCodeEquals(200);

    $this->drupalLogout();
    $this->visit("/");
    $expectedHtmlValue = '<link rel="publisher" href="http://example-publisher.com/&amp;quot;&amp;#039;quote-test&amp;lt;&amp;gt;">';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains publisher link.');
    $expectedHtmlValue = '<link rel="author" href="http://example-author.com/&amp;quot;&amp;#039;quote-test&amp;lt;&amp;gt;">';
    $this->assertContains($expectedHtmlValue, $this->getCurrentPageContent(), 'HTML head not contains author link.');
  }

  /**
   * Creates a group.
   *
   * @param array $values
   *   (optional) The values used to create the entity.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The created group entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  protected function createGroup(array $values = []) : GroupInterface {
    $group = $this->entityTypeManager->getStorage('group')->create($values + [
        'type' => 'personal',
        'label' => $this->randomMachineName(),
      ]);
    $group->enforceIsNew();
    $group->save();

    $this->markEntityForCleanup($group);

    return $group;
  }
}
