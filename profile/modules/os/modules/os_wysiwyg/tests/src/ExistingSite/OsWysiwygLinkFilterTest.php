<?php

namespace Drupal\Tests\os_wysiwyg\ExistingSite;

use Drupal\editor\Entity\Editor;
use Drupal\filter\Entity\FilterFormat;
use Drupal\Tests\openscholar\ExistingSite\OsExistingSiteTestBase;
use Drupal\Tests\openscholar\Traits\ExistingSiteTestTrait;

/**
 * Class MediaEntityResourceTest.
 *
 * @package Drupal\Tests\os_rest\ExistingSite
 * @group kernel
 * @group wysiwyg
 */
class OsWysiwygLinkFilterTest extends OsExistingSiteTestBase {

  use ExistingSiteTestTrait;

  protected $adminUser;

  /**
   * A set up for all tests.
   */
  public function setUp() {
    parent::setUp();

    // Create a text format and enable the os_link_filter filter.
    $format = FilterFormat::create([
      'format' => 'custom_format',
      'name' => 'Custom format',
      'filters' => [
        'os_link_filter' => [
          'status' => 1,
        ],
      ],
    ]);
    $format->save();
    $this->markEntityForCleanup($format);

    $editor_group = [
      'name' => 'Os Link',
      'items' => [
        'url',
      ],
    ];
    $editor = Editor::create([
      'format' => 'custom_format',
      'editor' => 'ckeditor',
      'settings' => [
        'toolbar' => [
          'rows' => [[$editor_group]],
        ],
      ],
    ]);
    $editor->save();
    $this->markEntityForCleanup($editor);

    // Create a admin user.
    $this->adminUser = $this->createAdminUser();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test for simple data url.
   */
  public function testDataUrlProcess() {
    $content = '<a data-url="http://example.com">Simple url</a>';
    $settings = [];
    $settings['type'] = 'page';
    $settings['title'] = 'Test data url';
    $settings['body'] = [['value' => $content, 'format' => 'custom_format']];
    $node = $this->createNode($settings);
    $this->drupalGet('node/' . $node->id());
    $this->assertContains('href="http://example.com"', $this->getCurrentPageContent());
  }

}
