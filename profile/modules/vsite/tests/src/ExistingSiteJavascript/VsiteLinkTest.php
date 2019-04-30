<?php

namespace Drupal\Tests\vsite\ExistingSiteJavascript;

use Behat\Mink\Exception\Exception;

/**
 * Class LinkTest.
 *
 * @package Drupal\Tests\vsite\ExistingSite
 * @group functional-javascript
 * @group vsite
 */
class VsiteLinkTest extends VsiteExistingSiteJavascriptTestBase {

  /**
   * The group we're testing against.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * The generated alias.
   *
   * @var string
   */
  protected $groupAlias;

  /**
   * Vsite Context Manager.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->groupAlias = $this->randomMachineName();
    $this->group = $this->createGroup([
      'path' => [
        'alias' => '/' . $this->groupAlias,
      ],
    ]);
  }

  /**
   * Test that our controller outputs the right strings.
   *
   * These strings depict underlying request state.
   */
  public function testLinks() {
    try {
      $this->visit('/' . $this->groupAlias);
      $this->assertSession()->statusCodeNotEquals(404);
      $this->visit('/' . $this->groupAlias . '/link-test');
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains('vsite active');

      $this->assertSession()->pageTextContains($this->groupAlias . '/link-test');
    }
    catch (Exception $e) {
      $this->fail($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    }
  }

}
