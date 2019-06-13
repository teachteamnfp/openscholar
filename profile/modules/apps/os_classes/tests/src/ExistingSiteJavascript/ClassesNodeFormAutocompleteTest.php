<?php

namespace Drupal\Tests\os_classes\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;
use Drupal\Tests\os_classes\Traits\OsClassesTestTrait;

/**
 * Tests os_classes module.
 *
 * @group functional-javascript
 * @group classes
 * @coversDefaultClass \Drupal\os_classes\Form\SemesterFieldOptionsForm
 */
class ClassesNodeFormAutocompleteTest extends OsExistingSiteJavascriptTestBase {

  use OsClassesTestTrait;

  /**
   * Test group.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * Group administrator.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $groupAdmin;

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
    $this->groupAdmin = $this->createUser();
    $this->addGroupAdmin($this->groupAdmin, $this->group);
  }

  /**
   * Tests os_classes autocomplete on node form.
   *
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function testAutocompleteNodeForm(): void {
    $this->drupalLogin($this->groupAdmin);
    $node = $this->createClass([
      'field_year_offered' => '2016',
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
