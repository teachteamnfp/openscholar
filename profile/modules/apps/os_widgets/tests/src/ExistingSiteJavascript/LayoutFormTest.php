<?php

namespace Drupal\Tests\os_widgets\ExistingSiteJavascript;

use Drupal\Tests\openscholar\ExistingSiteJavascript\OsExistingSiteJavascriptTestBase;
use Drupal\Tests\os_widgets\Traits\WidgetCreationTrait;

/**
 * Tests for the layout form.
 */
class LayoutFormTest extends OsExistingSiteJavascriptTestBase {

  use WidgetCreationTrait;

  protected $user;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->user = $this->createUser();
    $this->group->addMember($this->user, [
      'group_roles' => [
        'personal-administrator',
      ],
    ]);
  }

  /**
   * Tests that the filter widget functionality works.
   */
  public function testFilterWidget() {
    $blocks[] = $this->createBlockContent([
      'info' => 'abcdef',
    ]);

    $blocks[] = $this->createBlockContent([
      'info' => 'ghijkl',
    ]);

    foreach ($blocks as $b) {
      $plugin_id = 'block_content:' . $b->uuid();
      $block_id = 'block_content|' . $b->uuid();
      $block = \Drupal::entityTypeManager()->getStorage('block')->create(['plugin' => $plugin_id, 'id' => $block_id]);
      $block->save();

      $this->group->addContent($b, 'group_entity:block_content');
    }

    $this->drupalLogin($this->user);
    $this->visitViaVsite('blog', $this->group);
    $this->getSession()->getDriver()->click('//a[contains(.,"Place block")]');

    $this->assertSession()->pageTextContains('Filter Widgets');
    $this->assertSession()->pageTextContains('abcdef');
    $this->assertSession()->pageTextContains('ghijkl');

    $this->getSession()->getPage()->fillField('filter-widgets', 'abcdef');
    $this->getSession()->executeScript('document.querySelector("#block-place-widget-selector-wrapper").scrollTo(5, 5);');
    $this->assertTrue($this->getSession()->getPage()->find('xpath', '//h3[contains(.,"abcdef")]')->isVisible());
    $this->assertNotTrue($this->getSession()->getPage()->find('xpath', '//h3[contains(.,"ghijkl")]')->isVisible());
  }

}
