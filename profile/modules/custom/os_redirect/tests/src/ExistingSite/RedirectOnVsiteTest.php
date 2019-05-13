<?php

namespace Drupal\Tests\os_redirect\ExistingSite;

/**
 * Tests os_redirect module.
 *
 * @group redirect
 * @group kernel
 */
class RedirectOnVsiteTest extends OsRedirectTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $user = $this->createUser();
    $this->addGroupAdmin($user, $this->group);
    $this->drupalLogin($user);

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->container->get('entity_type.manager')->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_entity:redirect');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

    $redirect = $this->createRedirect([
      'redirect_source' => [
        'path' => '[vsite:' . $this->group->id() . ']/working-redirect',
      ],
      'redirect_redirect' => [
        // TODO: find a better way, <front> does not working.
        'uri' => 'https://google.com',
      ],
      'status_code' => 301,
    ]);
    $this->group->addContent($redirect, $plugin->getContentPluginId());

    $redirect = $this->createRedirect([
      'redirect_source' => [
        'path' => '[vsite:' . $this->group->id() . ']/working-internal-redirect',
      ],
      'redirect_redirect' => [
        'uri' => 'internal:/publications',
      ],
      'status_code' => 301,
    ]);
    $this->group->addContent($redirect, $plugin->getContentPluginId());
  }

  /**
   * Tests redirect on vsite success.
   */
  public function testRedirectSuccess301() {
    $web_assert = $this->assertSession();

    $this->visit($this->group->get('path')->getValue()[0]['alias'] . "/working-redirect");
    $web_assert->statusCodeEquals(200);
    $web_assert->addressEquals('/');
    $this->assertContains('Google', $this->getCurrentPageContent());

  }

  /**
   * Tests redirect on vsite internal success.
   */
  public function testRedirectInternalSuccess301() {
    $web_assert = $this->assertSession();

    $this->visit($this->group->get('path')->getValue()[0]['alias'] . "/working-internal-redirect");
    $web_assert->statusCodeEquals(200);
    $this->assertContains('Publications', $this->getCurrentPageContent());

  }

}
