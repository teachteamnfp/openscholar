<?php

namespace Drupal\Tests\os_app_access\ExistingSite;

/**
 * AppAccessFunctionalTest.
 *
 * @coversDefaultClass \Drupal\os_app_access\Access\AppAccess
 * @group functional
 * @group os
 */
class AppAccessFunctionalTest extends AppAccessTestBase {

  /**
   * @covers ::access
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function test(): void {
    // Setup.
    $group_admin = $this->createUser();
    $this->addGroupAdmin($group_admin, $this->group);
    $news = $this->createNode([
      'type' => 'news',
      'field_date' => [
        'value' => '2019-08-15',
      ],
    ]);
    $this->group->addContent($news, 'group_node:news');

    // Tests.
    // Test default config.
    $this->visitViaVsite("node/{$news->id()}", $this->group);
    $this->assertSession()->statusCodeEquals(200);

    // Test disabled app setting.
    $this->drupalLogin($group_admin);
    $this->visitViaVsite('cp/settings/app-access', $this->group);
    $this->submitForm([
      'enabled[blog][privacy]' => 0,
      'enabled[class][privacy]' => 0,
      'enabled[event][privacy]' => 0,
      'enabled[faq][privacy]' => 0,
      'enabled[links][privacy]' => 0,
      'enabled[news][privacy]' => 1,
      'enabled[page][privacy]' => 0,
      'enabled[presentations][privacy]' => 0,
      'enabled[profiles][privacy]' => 0,
      'enabled[publications][privacy]' => 0,
      'enabled[software][privacy]' => 0,
    ], 'Save configuration');

    $this->drupalLogout();

    $this->visitViaVsite("node/{$news->id()}", $this->group);
    $this->assertSession()->statusCodeEquals(403);
  }

}
