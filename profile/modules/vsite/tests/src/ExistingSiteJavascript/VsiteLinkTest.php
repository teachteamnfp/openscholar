<?php

namespace Drupal\Tests\vsite\ExistingSiteJavascript;

/**
 * Class LinkTest.
 *
 * @package Drupal\Tests\vsite\ExistingSite
 * @group link
 * @group existing-javascript
 */
class VsiteLinkTest extends VsiteExistingSiteJavascriptTestBase {

  /**
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * @var string
   */
  protected $groupAlias;

  /**
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   *
   */
  public function setUp() {
    parent::setUp();
    $this->groupAlias = $this->randomMachineName();
    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/' . $this->groupAlias,
      ],
    ]);

    $this->vsiteContextManager = $this->container->get('vsite.context_manager');
  }

  /**
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testLinks() {
    $this->visit('/' . $this->groupAlias);
    $this->assertSession()->statusCodeNotEquals(404);
    $this->visit('/' . $this->groupAlias . '/link-test');
    $this->assertSession()->statusCodeEquals(200);
    $page = $this->getCurrentPage();
    $this->assertSession()->pageTextContains('vsite active');

    $this->assertSession()->pageTextContains($this->groupAlias . '/link-test');
  }

}
