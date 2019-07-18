<?php

namespace Drupal\Tests\os_widgets\ExistingSiteJavascript;

use Drupal\group\Entity\GroupRole;
use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;

/**
 * Class LayoutFormTests.
 *
 * @package Drupal\Tests\os_widgets\ExistingSiteJavascript
 * @group functional-javascript
 * @group layout
 */
class LayoutFormTest extends OsExistingSiteJavascriptTestBase {

  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->user = $this->createUser();
    $this->group->addMember($this->user, [
      'group_roles' => [
        'personal-administrator'
      ]
    ]);
  }

  /**
   * Tests that certain values are different between.
   */
  public function testSiteIndependence() {
    $group2 = $this->createGroup();
    $group2Alias = $group2->get('path')->first()->getValue()['alias'];
    $group2->addMember($this->user, [
      'group_roles' => [
        'personal-administrator'
      ]
    ]);
    $this->drupalLogin($this->user);
    // Event ajaxSend is global, and fires on every ajax request.
    $script = <<<JS
    jQuery(document).bind("ajaxSend", function(e, xhr, ajaxOptions) {
      window.phpunit__ajax_url = ajaxOptions.url;
    });
JS;

    $this->visitViaVsite('blog', $this->group);
    $this->getSession()->getPage()->clickLink('Place block');
    error_log($this->getSession()->getCurrentUrl());
    $this->getSession()->wait(5);
    $this->getSession()->executeScript($script);
    $this->getSession()->getPage()->pressButton('Save');
    $url = $this->getSession()->evaluateScript('window.phpunit__ajax_url');
    $this->assertContains($this->groupAlias . '/cp/layout/save', $url);

    $this->visitViaVsite('blog', $group2);
    $this->getSession()->getPage()->clickLink('Place block');
    $this->getSession()->executeScript($script);
    $this->getSession()->getPage()->pressButton('Save');
    $url = $this->getSession()->evaluateScript('window.phpunit__ajax_url');
    $this->assertContains($group2Alias . '/cp/layout/save', $url);

  }

}
