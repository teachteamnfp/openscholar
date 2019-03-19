<?php

namespace Drupal\Tests\os_redirect\ExistingSite;

/**
 * Tests os_redirect module.
 *
 * @group redirect
 * @group kernel
 *
 * @coversDefaultClass \Drupal\os_redirect\EventSubscriber\OsRedirectRequestSubscriber
 */
class RedirectOnVsiteTest extends OsRedirectTestBase {

  protected $siteUser;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Set the current user so group creation can rely on it.
    $this->container->get('current_user')->setAccount($this->createUser());

    // Enable the user_as_content plugin on the default group type.
    /** @var \Drupal\group\Entity\Storage\GroupContentTypeStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('group_content_type');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface[] $plugin */
    $plugins = $storage->loadByContentPluginId('group_entity:redirect');
    /** @var \Drupal\group\Entity\GroupContentTypeInterface $plugin */
    $plugin = reset($plugins);

    $redirect = $this->createRedirect([
      'redirect_source' => [
        'path' => 'working-redirect',
      ],
      'redirect_redirect' => [
        // TODO: find a better way, <front> does not working.
        'uri' => 'https://google.com',
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
    $this->assertContains('Google', $this->getCurrentPageContent());

  }

}
