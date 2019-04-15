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
   *
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
