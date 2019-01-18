<?php

namespace Drupal\Tests\os_classes\ExistingSiteJavascript;

/**
 * Tests os_classes module.
 *
 * @group classes
 * @group functional-javascript
 * @coversDefaultClass \Drupal\os_classes\Form\SemesterFieldOptionsForm
 */
class ClassesNodeFormAutocompleteTest extends ClassesExistingSiteJavascriptTestBase {

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Test admin user.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /**
   * Alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->container->get('entity_type.manager');
    $this->config = $this->container->get('config.factory');
    $this->aliasManager = $this->container->get('path.alias_manager');
    /** @var \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager */
    $vsiteContextManager = $this->container->get('vsite.context_manager');
    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/test-alias',
      ],
    ]);
    $vsiteContextManager->activateVsite($this->group);
    $this->adminUser = $this->createUser(['administer nodes', 'bypass node access']);
  }

  /**
   * Tests os_classes autocomplete on node form.
   */
  public function testAutocompleteNodeForm() {
    $this->drupalLogin($this->adminUser);
    $node = $this->createClass([
      'field_year_offered' => '2016'
    ]);
    $this->group->addContent($node, "group_node:{$node->bundle()}");

    $this->visit("{$this->group->get('path')->first()->getValue()['alias']}/node/add/class");

    $web_assert = $this->assertSession();
    $page = $this->getCurrentPage();
    $tags = $page->findField('field_year_offered[0][value]');
    $tags->setValue('20');
    $tags->keyDown('1');
    $result = $web_assert->waitForElementVisible('css', '.ui-autocomplete li');
    $this->assertNotNull($result);
    // Click the autocomplete option.
    $result->click();
    // Verify that correct the input is selected.
    $web_assert->pageTextContains('2016');
  }

}
